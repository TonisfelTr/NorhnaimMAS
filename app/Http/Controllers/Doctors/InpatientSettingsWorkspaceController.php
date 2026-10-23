<?php

namespace App\Http\Controllers\Doctors;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Http\Controllers\Doctors\HospitalImpersonationController;
use App\Models\HospitalBuilding;
use App\Models\HospitalDepartment;
use App\Support\HospitalDepartmentAgeLimits;
use App\Models\HospitalRoom;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Временное окно настройки ДОЧЕРНЕГО стационара.
 * Никогда не меняет clinic_type, parent_clinic_id или doctor.clinic_id.
 * Не переключает клинику глобально в сеансе, не выдаёт доступ к медкартам.
 */
class InpatientSettingsWorkspaceController extends Controller
{
    private const DAYS = [
        'mon' => 'Понедельник', 'tue' => 'Вторник', 'wed' => 'Среда',
        'thu' => 'Четверг', 'fri' => 'Пятница', 'sat' => 'Суббота', 'sun' => 'Воскресенье',
    ];

    public function show(Request $request, int $hospital): View
    {
        $target = $this->getAuthorizedHospital($request, $hospital);
        $phones = $this->jsonArray($target->phones);
        $working = $this->jsonArray($target->inpatient_working_hours);
        $visiting = $this->jsonArray($target->inpatient_visiting_hours);
        // Корпуса стационара -- отдельная таблица hospital_buildings.
        // НЕ используем clinic_buildings, относящуюся к амбулаторным кабинетам.
        $hasHospitalBuildings = Schema::hasTable('hospital_buildings');
        $hospitalBuildings = $hasHospitalBuildings
            ? HospitalBuilding::query()->where('clinic_id', $target->id)
                ->orderBy('number')->orderBy('name')->get()
            : collect();
        $hospitalDepartments = HospitalDepartment::query()
            ->where('clinic_id', $target->id)
            ->orderBy('sort_order')->orderBy('number')->orderBy('name')->get();

        return view('doctors.settings.inpatient-workspace', [
            'hospital' => $target,
            'parentClinic' => $request->user()->doctor->clinic,
            'hospitalPhones' => array_values(array_filter($phones, 'is_array')),
            'hospitalBuildings' => $hospitalBuildings,
            'hospitalBuildingTableExists' => $hasHospitalBuildings,
            'hospitalBuildingNames' => $hospitalBuildings->pluck('name', 'id'),
            'hospitalDepartments' => $hospitalDepartments,
            'hospitalRooms' => HospitalRoom::query()
                ->with('department:id,name,number')->where('clinic_id', $target->id)
                ->orderBy('sort_order')->orderBy('number')->get(),
            'workingHours' => $working,
            'visitingHours' => $visiting,
            'weekdays' => self::DAYS,
            'saveUrl' => route('doctors.hospital-impersonation.workspace.save', ['hospital' => $target->id]),
            'addressApiUrl' => route('api-search-address'),
        ]);
    }

    public function save(Request $request, int $hospital): JsonResponse
    {
        // Даже при AJAX проверяем права по БД на КАЖДОМ запросе.
        $target = $this->getAuthorizedHospital($request, $hospital);
        $section = (string) $request->input('section');
        $createdId = null;

        if ($section === 'general') {
            $data = $request->validate([
                'section' => ['required', Rule::in(['general'])],
                'name' => ['required', 'string', 'max:255'],
                'address' => ['required', 'string', 'max:500'],
                'city' => ['nullable', 'string', 'max:255'],
                'description' => ['nullable', 'string', 'max:5000'],
                'latitude' => ['nullable', 'numeric', 'between:-90,90'],
                'longitude' => ['nullable', 'numeric', 'between:-180,180'],
                'phones' => ['nullable', 'array', 'max:20'],
                'phones.*.phone' => ['nullable', 'string', 'max:32'],
                'phones.*.label' => ['nullable', 'string', 'max:120'],
            ], [
                'name.required' => 'Укажите название стационара.',
                'address.required' => 'Укажите полный адрес стационара.',
            ]);

            $phones = [];
            foreach ($data['phones'] ?? [] as $row) {
                $number = trim((string) ($row['phone'] ?? ''));
                $label = trim((string) ($row['label'] ?? ''));
                if ($number === '' && $label === '') {
                    continue;
                }
                if ($number === '') {
                    throw ValidationException::withMessages([
                        'phones' => 'Заполните номер телефона либо удалите эту строку.',
                    ]);
                }
                $phones[] = [
                    'phone' => $number,
                    'label' => $label !== '' ? $label : 'Телефон стационара',
                ];
            }

            DB::transaction(function () use ($request, $hospital, $data, $phones): void {
                $record = $this->getAuthorizedHospital($request, $hospital, true);
                $wasAddress = (string) $record->address;
                $record->name = trim((string) $data['name']);
                $record->address = trim((string) $data['address']);
                $record->city = $this->nullableText($data['city'] ?? null);
                $record->description = $this->nullableText($data['description'] ?? null);

                if (Schema::hasColumn('clinics', 'latitude') && Schema::hasColumn('clinics', 'longitude')) {
                    if (isset($data['latitude'], $data['longitude'])) {
                        $record->latitude = (float) $data['latitude'];
                        $record->longitude = (float) $data['longitude'];
                    } elseif ($wasAddress !== $record->address) {
                        $record->latitude = null;
                        $record->longitude = null;
                    }
                }

                $record->save();
                // Колонка phones -- PostgreSQL jsonb. Не передаём в Eloquent PHP-массив.
                DB::update('UPDATE clinics SET phones = CAST(? AS jsonb) WHERE id = ?', [
                    json_encode($phones, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                    $record->id,
                ]);
            }, 3);
        } elseif ($section === 'schedule') {
            $mode = (string) $request->input('inpatient_mode', '');
            $isRoundTheClock = $mode === 'round_the_clock';
            $activeKey = $isRoundTheClock ? 'visiting_hours' : 'working_hours';

            // График работы задаётся только для дневного и ночного режима.
            // Круглосуточный стационар работает ежедневно без ограничения часами.
            // Посещения родственниками применяются только к круглосуточному режиму.
            $rules = [
                'section' => ['required', Rule::in(['schedule'])],
                'inpatient_mode' => ['required', Rule::in(['round_the_clock', 'day', 'night'])],
                $activeKey => ['required', 'array'],
            ];
            foreach (array_keys(self::DAYS) as $day) {
                $rules[$activeKey . '.' . $day] = ['required', 'array'];
                $rules[$activeKey . '.' . $day . '.enabled'] = ['required', 'boolean'];
                $rules[$activeKey . '.' . $day . '.from'] = ['nullable', 'date_format:H:i'];
                $rules[$activeKey . '.' . $day . '.to'] = ['nullable', 'date_format:H:i'];
            }
            $data = $request->validate($rules);
            $working = [];
            $visiting = [];
            $errors = [];

            if ($isRoundTheClock) {
                foreach (self::DAYS as $code => $label) {
                    // NULL означает отсутствие ограничений по времени, а НЕ 00:00-00:00.
                    $working[$code] = ['enabled' => true, 'from' => null, 'to' => null];
                    $entry = $data['visiting_hours'][$code];
                    $from = $this->nullableText($entry['from'] ?? null);
                    $to = $this->nullableText($entry['to'] ?? null);
                    $hasBoth = $from !== null && $to !== null;
                    $checkboxEnabled = filter_var($entry['enabled'], FILTER_VALIDATE_BOOLEAN);

                    // Любые два заполненных времени включают галочку автоматически.
                    // Одно заполненное поле или галочка без пары — ошибка, не тихая потеря данных.
                    if (($from === null) !== ($to === null) || ($checkboxEnabled && !$hasBoth)) {
                        $errors['visiting_hours.' . $code] = $label . ': укажите начало и конец посещений.';
                    } elseif ($hasBoth && $from >= $to) {
                        $errors['visiting_hours.' . $code] = $label . ': окончание посещений должно быть позже начала.';
                    }
                    $visiting[$code] = ['enabled' => $hasBoth, 'from' => $from, 'to' => $to];
                }
            } else {
                $activeDays = 0;
                foreach (self::DAYS as $code => $label) {
                    $entry = $data['working_hours'][$code];
                    $enabled = filter_var($entry['enabled'], FILTER_VALIDATE_BOOLEAN);
                    $from = $this->nullableText($entry['from'] ?? null);
                    $to = $this->nullableText($entry['to'] ?? null);

                    if ($enabled) {
                        $activeDays++;
                        if ($from === null || $to === null) {
                            $errors['working_hours.' . $code] = $label . ': укажите начало и конец работы.';
                        } elseif ($mode === 'day' && $from >= $to) {
                            $errors['working_hours.' . $code] = $label . ': для дневного режима окончание должно быть позже начала.';
                        } elseif ($mode === 'night' && $from <= $to) {
                            $errors['working_hours.' . $code] = $label . ': ночная смена должна переходить через полночь.';
                        }
                    }
                    $working[$code] = ['enabled' => $enabled, 'from' => $from, 'to' => $to];
                }
                if ($activeDays === 0) {
                    $errors['working_hours'] = 'Выберите хотя бы один рабочий день стационара.';
                }
            }

            if ($errors !== []) {
                throw ValidationException::withMessages($errors);
            }

            DB::transaction(function () use ($request, $hospital, $mode, $working, $visiting): void {
                $record = $this->getAuthorizedHospital($request, $hospital, true);

                if ($mode !== 'round_the_clock') {
                    // Скрытые посещения не должны действовать в дневном и ночном режиме.
                    // Сами интервалы сохраняем на случай возврата к круглосуточному режиму.
                    $oldVisiting = $this->jsonArray($record->inpatient_visiting_hours);
                    foreach (array_keys(self::DAYS) as $day) {
                        $existing = is_array($oldVisiting[$day] ?? null) ? $oldVisiting[$day] : [];
                        $visiting[$day] = [
                            'enabled' => false,
                            'from' => $this->nullableText($existing['from'] ?? null),
                            'to' => $this->nullableText($existing['to'] ?? null),
                        ];
                    }
                }

                $record->inpatient_mode = $mode;
                $record->save();
                DB::update(
                    'UPDATE clinics SET inpatient_working_hours = CAST(? AS json), inpatient_visiting_hours = CAST(? AS json) WHERE id = ?',
                    [
                        json_encode($working, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                        json_encode($visiting, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                        $record->id,
                    ]
                );
            }, 3);

        } elseif ($section === 'building_create' || $section === 'building') {
            // Стационарные корпуса не смешиваются с clinic_buildings амбулатории.
            abort_unless(Schema::hasTable('hospital_buildings'), 409,
                'В базе пока нет таблицы hospital_buildings. Проверьте миграции стационара.');
            $editing = $section === 'building';
            $rules = [
                'section' => ['required', Rule::in([$section])],
                'number' => ['required', 'string', 'max:50'],
                'name' => ['required', 'string', 'max:255'],
                'address' => ['nullable', 'string', 'max:500'],
                'enabled' => ['required', 'boolean'],
            ];
            if ($editing) {
                $rules['building_id'] = [
                    'required', 'integer',
                    Rule::exists('hospital_buildings', 'id')
                        ->where('clinic_id', (int) $target->id),
                ];
            }
            $data = $request->validate($rules, [
                'number.required' => 'Укажите номер корпуса.',
                'name.required' => 'Укажите название корпуса.',
            ]);
            $createdId = DB::transaction(function () use ($request, $hospital, $data, $editing): int {
                $record = $this->getAuthorizedHospital($request, $hospital, true);
                $number = $this->normalizeNumber($data['number']);
                if ($number === '') {
                    throw ValidationException::withMessages(['number' => 'Укажите номер корпуса после символа №.']);
                }
                $name = trim((string) $data['name']);
                $building = $editing
                    ? HospitalBuilding::query()->where('clinic_id', $record->id)
                        ->lockForUpdate()->findOrFail((int) $data['building_id'])
                    : new HospitalBuilding();
                $exists = HospitalBuilding::query()
                    ->where('clinic_id', $record->id)->where('number', $number)
                    ->when($editing, static fn ($q) => $q->where('id', '!=', $building->id))
                    ->exists();
                if ($exists) {
                    throw ValidationException::withMessages([
                        'number' => 'В этом стационаре уже есть корпус с таким номером.',
                    ]);
                }
                $enabled = (bool) $data['enabled'];
                if ($editing && !$enabled && Schema::hasColumn('hospital_departments', 'building_id')
                    && HospitalDepartment::query()->where('clinic_id', $record->id)
                        ->where('building_id', $building->id)->where('enabled', true)->exists()) {
                    throw ValidationException::withMessages([
                        'enabled' => 'Сначала отключите или перенесите активные отделения этого корпуса.',
                    ]);
                }
                $building->clinic_id = $record->id;
                $building->number = $number;
                $building->name = $name;
                $building->address = $this->nullableText($data['address'] ?? null);
                $building->enabled = $enabled;
                $building->save();
                return (int) $building->id;
            }, 3);
        } elseif ($section === 'department_create') {
            // Отделение создаётся исключительно в выбранном дочернем стационаре.
            // Врач, прикрепления сотрудников и клиника администратора не меняются.
            $buildingRules = ['nullable', 'integer'];
            if (Schema::hasTable('hospital_buildings')
                && Schema::hasColumn('hospital_departments', 'building_id')) {
                $buildingRules[] = Rule::exists('hospital_buildings', 'id')
                    ->where('clinic_id', (int) $target->id)
                    ->where('enabled', true);
            } else {
                $buildingRules[] = Rule::in([]);
            }

            $data = $request->validate([
                'section' => ['required', Rule::in(['department_create'])],
                'number' => ['required', 'string', 'max:50'],
                'name' => ['required', 'string', 'max:255'],
                'profile' => ['nullable', 'string', 'max:255'],
                'age_min_years' => ['nullable', 'integer', 'between:0,125'],
                'age_max_years' => ['nullable', 'integer', 'between:0,125'],
                'building_id' => $buildingRules,
                'sex_type' => ['required', Rule::in([
                    HospitalDepartment::SEX_TYPE_MALE,
                    HospitalDepartment::SEX_TYPE_FEMALE,
                    HospitalDepartment::SEX_TYPE_MIXED,
                ])],
                'inpatient_mode' => ['required', Rule::in(['day', 'night', 'round_the_clock'])],
                'description' => ['nullable', 'string', 'max:2000'],
                'enabled' => ['required', 'boolean'],
                'sort_order' => ['nullable', 'integer', 'min:0', 'max:100000'],
            ], [
                'number.required' => 'Укажите номер отделения.',
                'name.required' => 'Укажите название отделения.',
            ]);

            $createdId = DB::transaction(function () use ($request, $hospital, $data): int {
                $record = $this->getAuthorizedHospital($request, $hospital, true);
                $number = $this->normalizeNumber($data['number']);
                if ($number === '') {
                    throw ValidationException::withMessages(['number' => 'Введите номер отделения, например 1.']);
                }

                // Избегаем двух отделений с одинаковым номером в этом стационаре.
                if (HospitalDepartment::query()->where('clinic_id', $record->id)
                    ->where('number', $number)->exists()) {
                    throw ValidationException::withMessages([
                        'number' => 'Отделение с таким номером уже существует в этом стационаре.',
                    ]);
                }

                $attributes = [
                    'clinic_id' => $record->id,
                    'number' => $number,
                    'name' => trim((string) $data['name']),
                ];
                $optionalFields = [
                    'profile' => $this->nullableText($data['profile'] ?? null),
                    'building_id' => isset($data['building_id']) ? (int) $data['building_id'] : null,
                    'sex_type' => $data['sex_type'],
                    'inpatient_mode' => $data['inpatient_mode'],
                    'description' => $this->nullableText($data['description'] ?? null),
                    'enabled' => (bool) $data['enabled'],
                    'sort_order' => (int) ($data['sort_order'] ?? 0),
                    'use_clinic_visiting_hours' => true,
                ];
                foreach ($optionalFields as $column => $value) {
                    if (Schema::hasColumn('hospital_departments', $column)) {
                        $attributes[$column] = $value;
                    }
                }

                // Возрастные границы — явные поля. Профили 0–7, 8–15, 16+ фиксированы.
                $ageValues = HospitalDepartmentAgeLimits::validate($data, (string) ($data['profile'] ?? ''));
                foreach ($ageValues as $column => $value) $attributes[$column] = $value;
                $department = HospitalDepartment::query()->create($attributes);
                return (int) $department->id;
            }, 3);
        } elseif ($section === 'room_create') {
            $departmentId = (int) $request->input('department_id', 0);
            $data = $request->validate([
                'section' => ['required', Rule::in(['room_create'])],
                'department_id' => [
                    'required', 'integer',
                    Rule::exists('hospital_departments', 'id')
                        ->where('clinic_id', (int) $target->id),
                ],
                'number' => ['required', 'string', 'max:50',
                    Rule::unique('hospital_rooms', 'number')
                        ->where('department_id', $departmentId),
                ],
                'name' => ['nullable', 'string', 'max:255'],
                'floor' => ['nullable', 'string', 'max:50'],
                'sort_order' => ['nullable', 'integer', 'min:0', 'max:100000'],
            ], [
                'department_id.required' => 'Выберите отделение стационара.',
                'number.required' => 'Укажите номер палаты.',
                'number.unique' => 'В этом отделении палата с таким номером уже есть.',
            ]);

            $createdId = DB::transaction(function () use ($request, $hospital, $data): int {
                $record = $this->getAuthorizedHospital($request, $hospital, true);
                // Повторно проверяем связь непосредственно в транзакции.
                $department = HospitalDepartment::query()
                    ->where('clinic_id', $record->id)->lockForUpdate()
                    ->findOrFail((int) $data['department_id']);
                $number = trim((string) $data['number']);
                if (HospitalRoom::query()->where('clinic_id', $record->id)
                    ->where('department_id', $department->id)
                    ->where('number', $number)->exists()) {
                    throw ValidationException::withMessages([
                        'number' => 'В этом отделении уже есть палата с таким номером.',
                    ]);
                }

                $room = HospitalRoom::query()->create([
                    'clinic_id' => $record->id,
                    'department_id' => $department->id,
                    'number' => $number,
                    'name' => $this->nullableText($data['name'] ?? null),
                    'floor' => $this->nullableText($data['floor'] ?? null),
                    'canvas_width' => 900,
                    'canvas_height' => 560,
                    'enabled' => true,
                    'sort_order' => (int) ($data['sort_order'] ?? 0),
                ]);
                // Создание палаты само по себе НЕ добавляет койки и пациентов.
                return (int) $room->id;
            }, 3);
        } elseif ($section === 'department') {
            $buildingRules = ['present', 'nullable', 'integer'];
            if (Schema::hasTable('hospital_buildings')
                && Schema::hasColumn('hospital_departments', 'building_id')) {
                $buildingRules[] = Rule::exists('hospital_buildings', 'id')
                    ->where('clinic_id', (int) $target->id);
            } else {
                $buildingRules[] = Rule::in([]);
            }
            $data = $request->validate([
                'section' => ['required', Rule::in(['department'])],
                'department_id' => [
                    'required', 'integer', Rule::exists('hospital_departments', 'id')
                        ->where('clinic_id', (int) $target->id),
                ],
                'name' => ['required', 'string', 'max:255'],
                'number' => ['nullable', 'string', 'max:50'],
                'description' => ['nullable', 'string', 'max:2000'],
                'profile' => ['nullable', 'string', 'max:255'],
                'age_min_years' => ['nullable', 'integer', 'between:0,125'],
                'age_max_years' => ['nullable', 'integer', 'between:0,125'],
                'building_id' => $buildingRules,
                'sex_type' => ['nullable', Rule::in([
                    HospitalDepartment::SEX_TYPE_MALE,
                    HospitalDepartment::SEX_TYPE_FEMALE,
                    HospitalDepartment::SEX_TYPE_MIXED,
                ])],
                'inpatient_mode' => ['nullable', Rule::in(['day', 'night', 'round_the_clock'])],
                'enabled' => ['nullable', 'boolean'],
                'sort_order' => ['nullable', 'integer', 'min:0', 'max:100000'],
            ]);
            DB::transaction(function () use ($request, $hospital, $data): void {
                $record = $this->getAuthorizedHospital($request, $hospital, true);
                $department = HospitalDepartment::query()
                    ->where('clinic_id', $record->id)->lockForUpdate()
                    ->findOrFail((int) $data['department_id']);
                $department->name = trim((string) $data['name']);
                if (Schema::hasColumn('hospital_departments', 'number')) {
                    $number = $this->normalizeNumber($data['number'] ?? '');
                    if ($number === '') {
                        throw ValidationException::withMessages([
                            'number' => 'Номер отделения не должен быть пустым.',
                        ]);
                    }
                    if (HospitalDepartment::query()->where('clinic_id', $record->id)
                        ->where('number', $number)->where('id', '!=', $department->id)->exists()) {
                        throw ValidationException::withMessages([
                            'number' => 'В этом стационаре отделение с таким номером уже существует.',
                        ]);
                    }
                    $department->number = $number;
                }
                if (Schema::hasColumn('hospital_departments', 'building_id')) {
                    $selectedBuildingId = !empty($data['building_id']) ? (int) $data['building_id'] : null;
                    if ($selectedBuildingId !== null
                        && $selectedBuildingId !== (int) ($department->building_id ?? 0)) {
                        $selectedBuilding = HospitalBuilding::query()
                            ->where('clinic_id', $record->id)->findOrFail($selectedBuildingId);
                        if (!$selectedBuilding->enabled) {
                            throw ValidationException::withMessages([
                                'building_id' => 'Нельзя перенести отделение в неактивный корпус.',
                            ]);
                        }
                    }
                    $department->building_id = $selectedBuildingId;
                }
                if (Schema::hasColumn('hospital_departments', 'description')) {
                    $department->description = $this->nullableText($data['description'] ?? null);
                }
                foreach (['profile', 'sex_type', 'inpatient_mode', 'sort_order', 'enabled'] as $field) {
                    if (!array_key_exists($field, $data) || !Schema::hasColumn('hospital_departments', $field)) {
                        continue;
                    }
                    $value = $data[$field];
                    if ($field === 'profile') $value = $this->nullableText($value);
                    if ($field === 'enabled') $value = (bool) $value;
                    if ($field === 'sort_order') $value = (int) $value;
                    $department->{$field} = $value;
                }
                $ageValues = HospitalDepartmentAgeLimits::validate($data, (string) ($department->profile ?? ''));
                foreach ($ageValues as $column => $value) $department->{$column} = $value;
                $department->save();
            }, 3);
            $createdId = (int) $data['department_id'];
        } elseif ($section === 'room') {
            $data = $request->validate([
                'section' => ['required', Rule::in(['room'])],
                'room_id' => [
                    'required', 'integer', Rule::exists('hospital_rooms', 'id')
                        ->where('clinic_id', (int) $target->id),
                ],
                'name' => ['nullable', 'string', 'max:255'],
                'number' => ['required', 'string', 'max:50'],
                'floor' => ['nullable', 'string', 'max:50'],
            ]);
            DB::transaction(function () use ($request, $hospital, $data): void {
                $record = $this->getAuthorizedHospital($request, $hospital, true);
                $room = HospitalRoom::query()
                    ->where('clinic_id', $record->id)->lockForUpdate()
                    ->findOrFail((int) $data['room_id']);
                $duplicate = HospitalRoom::query()
                    ->where('clinic_id', $record->id)
                    ->where('department_id', $room->department_id)
                    ->where('number', trim((string) $data['number']))
                    ->where('id', '!=', $room->id)->exists();
                if ($duplicate) {
                    throw ValidationException::withMessages([
                        'number' => 'В выбранном отделении уже есть палата с таким номером.',
                    ]);
                }
                $room->number = trim((string) $data['number']);
                $room->name = $this->nullableText($data['name'] ?? null);
                $room->floor = $this->nullableText($data['floor'] ?? null);
                $room->save();
            }, 3);
            $createdId = (int) $data['room_id'];
        } else {
            throw ValidationException::withMessages(['section' => 'Неизвестный раздел настроек стационара.']);
        }

        Log::info('Inpatient settings saved from parent clinic', [
            'actor_user_id' => $request->user()->id,
            'parent_clinic_id' => (int) $request->user()->doctor->clinic_id,
            'hospital_id' => $target->id,
            'section' => $section,
        ]);

        return response()->json([
            'success' => true,
            'message' => match ($section) {
                'department_create' => 'Отделение создано. Можно продолжать настройку стационара.',
                'room_create' => 'Палата создана. Можно продолжать настройку стационара.',
                'building_create' => 'Корпус стационара создан.',
                'building' => 'Корпус стационара сохранён.',
                default => 'Настройки стационара сохранены.',
            },
            'hospital_id' => (int) $target->id,
            'section' => $section,
            'record_id' => $createdId,
        ]);
    }

    /** Убираем визуальный символ №: храним в БД только номер. */
    private function normalizeNumber(mixed $number): string
    {
        $value = trim((string) $number);
        return trim((string) preg_replace('/^№\s*/u', '', $value));
    }

    /** @return Clinic */
    private function getAuthorizedHospital(Request $request, int $hospital, bool $lock = false): Clinic
    {
        $user = $request->user();
        abort_unless(
            \App\Http\Middleware\ClinicSettingsAccessMiddleware::allows($user),
            403
        );
        $context = $request->session()->get(HospitalImpersonationController::SESSION_KEY);
        abort_unless(is_array($context)
            && (int) ($context['actor_user_id'] ?? 0) === (int) $user?->id
            && (int) ($context['hospital_id'] ?? 0) === $hospital, 403);

        $parentId = (int) ($user?->doctor?->clinic_id ?? 0);
        abort_unless($parentId > 0
            && $parentId === (int) ($context['origin_clinic_id'] ?? 0), 403);

        $parent = Clinic::query()
            ->whereKey($parentId)
            ->where('clinic_type', Clinic::TYPE_OUTPATIENT)
            ->whereNull('deleted_at')
            ->first();
        abort_unless($parent !== null, 403);

        $builder = Clinic::query()
            ->whereKey($hospital)
            ->where('clinic_type', Clinic::TYPE_INPATIENT)
            ->where('parent_clinic_id', $parentId)
            ->whereNull('deleted_at');
        return ($lock ? $builder->lockForUpdate() : $builder)->firstOrFail();
    }

    private static function jsonArray(mixed $value): array
    {
        if (is_array($value)) return $value;
        if (is_object($value)) return (array) $value;
        if (!is_string($value) || $value === '') return [];
        $decoded = json_decode($value, true);
        return is_array($decoded) ? $decoded : [];
    }

    private function nullableText(mixed $value): ?string
    {
        if (!is_scalar($value)) return null;
        $str = trim((string) $value);
        return $str === '' ? null : $str;
    }
}
