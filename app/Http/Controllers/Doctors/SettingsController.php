<?php

namespace App\Http\Controllers\Doctors;

use App\Http\Requests\ClinicSettingsUpdateRequest;
use App\Models\Clinic;
use App\Models\ClinicWorkShedule;
use App\Models\Doctor;
use App\Models\Nurse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class SettingsController extends SettingsBaseController
{
    private const NURSE_POSITIONS = [
        'Старшая медицинская сестра',
        'Медицинская сестра',
        'Процедурная медицинская сестра',
    ];

    private const SPECIALTY_MAP = [
        'Психиатрия' => 'Психиатр',
        'Психиатр-нарколог' => 'Психиатр-нарколог',
        'Детская психиатрия' => 'Детский психиатр',
        'Психотерапия' => 'Психотерапевт',
        'Клиническая психология' => 'Клинический психолог',
        'Сестринское дело' => 'Сестринское дело',
    ];

    public function index()
    {
        $doctor = auth()->user()->doctor;
        $isPrivatePractice = !$doctor?->clinic_id;

        if ($isPrivatePractice) {
            return $this->settingsView(
                'doctors.settings.general',
                [
                    'doctor' => $doctor,
                    'clinic' => null,
                    'isPrivatePractice' => true,
                    'doctors' => collect(),
                    'clinicDoctors' => collect(),
                    'clinicNurses' => collect(),
                    'clinicRegistrars' => collect(),
                    'availableDoctors' => collect(),
                    'availableNurses' => collect(),
                    'staffSummary' => [
                        'doctors' => 0,
                        'nurses' => 0,
                        'registrars' => 0,
                    ],
                    'clinicPhones' => [],
                    'clinicServices' => [
                        'in_person',
                        'telemedicine',
                    ],
                    'clinicType' => Clinic::TYPE_OUTPATIENT,
                    'inpatientMode' =>
                        Clinic::INPATIENT_MODE_ROUND_THE_CLOCK,
                    'weekDays' => [],
                    'inpatientWeekDays' => [],
                    'inpatientVisitingWeekDays' => [],
                ]
            );
        }

        $clinic = Clinic::findOrFail($doctor->clinic_id);

        /*
         * Принадлежность сотрудника клинике определяем только по clinic_id.
         * Старые doctor-записи с произвольным profession (например
         * "психиатр") не считаем оформленными сотрудниками настроек.
         */
        $clinicDoctorRows = Doctor::query()
            ->where('clinic_id', $clinic->id)
            ->orderBy('surname')
            ->orderBy('name')
            ->get();

        /*
         * Медсестёр показываем из nurses, а не через совпадение user_id.
         * nurses.id является идентификатором записи медсестры.
         */
        $clinicNurses = Nurse::query()
            ->where('clinic_id', $clinic->id)
            ->orderBy('surname')
            ->orderBy('name')
            ->get();

        /*
         * Регистраторы пока хранятся в doctors как системные
         * сотрудники, но считаются отдельной кадровой категорией.
         * В кабинетах и расписании врачей они уже отсекаются
         * по profession = "Медицинский регистратор".
         */
        $clinicRegistrars = $clinicDoctorRows
            ->filter(
                static fn (Doctor $employee): bool =>
                    mb_strtolower(
                        trim(
                            (string) $employee->profession
                        )
                    ) === 'медицинский регистратор'
            )
            ->values();

        /*
         * В списке сотрудников показываем ВСЕ Doctor-записи текущей
         * клиники, кроме явно сестринских/младших должностей.
         * Старые записи с profession = "психиатр" тоже являются врачами
         * клиники и не должны пропадать из интерфейса.
         */
        $doctors = $clinicDoctorRows
            ->reject(
                static function (Doctor $employee): bool {
                    $position = mb_strtolower(
                        trim((string) $employee->profession)
                    );

                    return in_array(
                        $position,
                        [
                            'старшая медицинская сестра',
                            'медицинская сестра',
                            'процедурная медицинская сестра',
                            'санитар',
                            'медицинский регистратор',
                        ],
                        true
                    );
                }
            )
            ->values();

        /*
         * Неврология удалена из доступных профилей. Старые значения
         * в doctors.status не показываем в интерфейсе даже до сохранения.
         */
        $homeVisitDistrictNames = collect();

        if (
            Schema::hasTable('clinic_districts')
            && Schema::hasColumn('clinic_districts', 'clinic_id')
            && Schema::hasColumn('clinic_districts', 'name')
            && Schema::hasColumn('clinic_districts', 'home_visit_doctor_id')
        ) {
            $homeVisitDistrictNames = DB::table('clinic_districts')
                ->where('clinic_id', $clinic->id)
                ->whereNotNull('home_visit_doctor_id')
                ->pluck('name', 'home_visit_doctor_id');
        }

        $doctors->each(
            function (Doctor $employee) use ($homeVisitDistrictNames): void {
                /*
                 * Старое profession = "психиатр" отображаем как обычную
                 * врачебную должность. В БД значение изменится только если
                 * пользователь действительно сохранит карточку сотрудника.
                 */
                if (
                    mb_strtolower(trim((string) $employee->profession))
                    === 'психиатр'
                ) {
                    $employee->profession = 'Врач';
                }

                $status = $this->specialtiesToStatus([
                    (string) ($employee->status ?? ''),
                ]);

                $employee->status = $status !== ''
                    ? $status
                    : null;

                $employee->setAttribute(
                    'home_visit_district_name',
                    $homeVisitDistrictNames->get($employee->id)
                );
            }
        );

        $clinicRegistrars->each(
            static function (Doctor $employee): void {
                $employee->profession =
                    'Медицинский регистратор';

                /*
                 * Регистратор не имеет медицинского профиля.
                 * Старый status не показываем даже до первого сохранения.
                 */
                $employee->status = null;
            }
        );

        /*
         * Один Doctor.id = один врач. Количество его специальностей
         * на состав и счётчик врачей не влияет.
         */
        $clinicDoctors = $doctors->values();

        $staffSummary = [
            /*
             * Один Doctor.id = один врач. Количество специальностей
             * в doctors.status на счётчик не влияет.
             */
            'doctors' => $clinicDoctors->unique('id')->count(),
            'nurses' => $clinicNurses->unique('id')->count(),
            'registrars' =>
                $clinicRegistrars
                    ->unique('id')
                    ->count(),
        ];

        /*
         * Доступные для добавления записи doctors: только те, кто ещё
         * не привязан к клинике. Doctor-дубли медсестёр здесь не показываем.
         */
        $availableDoctors = Doctor::query()
            ->where(
                static function ($query): void {
                    $query
                        ->whereNull('clinic_id')
                        ->orWhere('clinic_id', 0);
                }
            )
            ->where(
                static function ($query): void {
                    $query
                        ->whereNull('profession')
                        ->orWhereNotIn(
                            'profession',
                            self::NURSE_POSITIONS
                        );
                }
            )
            ->orderBy('surname')
            ->orderBy('name')
            ->get();

        $availableDoctors->each(
            function (Doctor $employee): void {
                $status = $this->specialtiesToStatus([
                    (string) ($employee->status ?? ''),
                ]);

                $employee->status = $status !== ''
                    ? $status
                    : null;
            }
        );

        /*
         * Доступные медсестры определяются только nurses.clinic_id.
         * user_id здесь специально не участвует.
         */
        $availableNurses = Nurse::query()
            ->where(
                static function ($query): void {
                    $query
                        ->whereNull('clinic_id')
                        ->orWhere('clinic_id', 0);
                }
            )
            ->orderBy('surname')
            ->orderBy('name')
            ->get();

        $clinicPhones = old(
            'phones',
            $clinic->phones ?? []
        );

        if (is_string($clinicPhones)) {
            $clinicPhones = json_decode(
                $clinicPhones,
                true
            ) ?: [];
        }

        if (empty($clinicPhones)) {
            $clinicPhones = [
                [
                    'phone' => '',
                    'label' => 'Основная регистратура',
                ],
            ];
        }

        $clinicServices = old(
            'services',
            $clinic->services ?? [
                'in_person',
                'telemedicine',
            ]
        );

        if (is_string($clinicServices)) {
            $clinicServices = json_decode(
                $clinicServices,
                true
            ) ?: [];
        }

        $clinicType = old(
            'clinic_type',
            $clinic->clinic_type ?: Clinic::TYPE_OUTPATIENT
        );

        $inpatientMode = old(
            'inpatient_mode',
            $clinic->inpatient_mode
                ?: Clinic::INPATIENT_MODE_ROUND_THE_CLOCK
        );

        $inpatientWorkingHours = old(
            'inpatient_working_hours',
            $clinic->inpatient_working_hours ?? []
        );

        $inpatientVisitingHours = old(
            'inpatient_visiting_hours',
            $clinic->inpatient_visiting_hours ?? []
        );

        if (!is_array($inpatientWorkingHours)) {
            $inpatientWorkingHours = [];
        }

        if (!is_array($inpatientVisitingHours)) {
            $inpatientVisitingHours = [];
        }

        $schedule = $clinic
            ->workSchedules()
            ->get()
            ->keyBy('day_number');

        $days = [
            1 => ['code' => 'mon', 'label' => 'ПН'],
            2 => ['code' => 'tue', 'label' => 'ВТ'],
            3 => ['code' => 'wed', 'label' => 'СР'],
            4 => ['code' => 'thu', 'label' => 'ЧТ'],
            5 => ['code' => 'fri', 'label' => 'ПТ'],
            6 => ['code' => 'sat', 'label' => 'СБ'],
            7 => ['code' => 'sun', 'label' => 'ВС'],
        ];

        $weekDays = [];
        $inpatientWeekDays = [];
        $inpatientVisitingWeekDays = [];

        $defaultInpatientFrom =
            $inpatientMode === Clinic::INPATIENT_MODE_NIGHT
                ? '20:00'
                : '08:00';

        $defaultInpatientTo =
            $inpatientMode === Clinic::INPATIENT_MODE_NIGHT
                ? '08:00'
                : '20:00';

        foreach ($days as $dayNumber => $day) {
            $item = $schedule->get($dayNumber);
            $code = $day['code'];

            $weekDays[] = [
                'code' => $code,
                'label' => $day['label'],
                'open' => $item
                    ? (bool) $item->enabled
                    : false,
                'from' => $item && $item->start_time
                    ? substr((string) $item->start_time, 0, 5)
                    : '08:00',
                'to' => $item && $item->end_time
                    ? substr((string) $item->end_time, 0, 5)
                    : '20:00',
            ];

            $inpatientHours = (array) (
                $inpatientWorkingHours[$code]
                ?? []
            );

            $inpatientWeekDays[] = [
                'code' => $code,
                'label' => $day['label'],
                'open' => array_key_exists(
                    'enabled',
                    $inpatientHours
                )
                    ? (bool) $inpatientHours['enabled']
                    : true,
                'from' => (string) (
                    $inpatientHours['from']
                    ?? $defaultInpatientFrom
                ),
                'to' => (string) (
                    $inpatientHours['to']
                    ?? $defaultInpatientTo
                ),
            ];

            $visitingHours = (array) (
                $inpatientVisitingHours[$code]
                ?? []
            );

            $inpatientVisitingWeekDays[] = [
                'code' => $code,
                'label' => $day['label'],
                'open' => (bool) (
                    $visitingHours['enabled']
                    ?? false
                ),
                'from' => (string) (
                    $visitingHours['from']
                    ?? ''
                ),
                'to' => (string) (
                    $visitingHours['to']
                    ?? ''
                ),
            ];
        }

        return $this->settingsView(
            'doctors.settings.general',
            compact(
                'doctor',
                'clinic',
                'isPrivatePractice',
                'doctors',
                'clinicDoctors',
                'clinicNurses',
                'clinicRegistrars',
                'availableDoctors',
                'availableNurses',
                'staffSummary',
                'clinicPhones',
                'clinicServices',
                'clinicType',
                'inpatientMode',
                'weekDays',
                'inpatientWeekDays',
                'inpatientVisitingWeekDays'
            )
        );
    }

    public function update(
        ClinicSettingsUpdateRequest $request
    ): RedirectResponse {
        $clinic = Clinic::findOrFail(
            auth()->user()->doctor->clinic_id
        );

        // Запрещаем отправлять другой clinic_type даже вручную через HTTP.
        if ($request->filled('clinic_type')
            && (string) $request->input('clinic_type') !== (string) $clinic->clinic_type) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'clinic_type' => 'Тип учреждения можно установить только при регистрации через Filament.',
            ]);
        }

        DB::transaction(
            function () use ($request, $clinic): void {
                // Тип учреждения фиксируется при создании в Filament.
                // Здесь его нельзя изменять: амбулатория не превращается
                // в стационар после сохранения обычной формы настроек.
                $clinic->name = $request->name;
                $clinic->address = $request->address;
                $clinic->description = $request->description;
                $clinic->phones = $request->phones;
                $clinic->city = $request->city;

                /*
                 * Форматы «Очный приём / Телемедицина» относятся к
                 * амбулаторной части. Для стационара блок скрыт и его
                 * поля не отправляются, поэтому прежнее значение не
                 * затираем: оно пригодится, если клинику снова переключат
                 * в амбулаторный режим.
                 */
                if (
                    $clinic->clinic_type
                    === Clinic::TYPE_OUTPATIENT
                ) {
                    $clinic->services = $request->services;
                } else {
                    $clinic->inpatient_mode = $request->string(
                        'inpatient_mode'
                    )->toString();

                    $clinic->inpatient_working_hours =
                        $request->array(
                            'inpatient_working_hours'
                        );

                    $clinic->inpatient_visiting_hours =
                        $request->array(
                            'inpatient_visiting_hours'
                        );
                }

                /*
                 * Координаты подтверждённого адреса должны храниться
                 * вместе с самой клиникой. Иначе после перезагрузки
                 * карта снова вынуждена геокодировать уже известный адрес.
                 */
                if (
                    Schema::hasColumn(
                        'clinics',
                        'latitude'
                    )
                ) {
                    $clinic->latitude =
                        $request->filled('latitude')
                            ? (float) $request->latitude
                            : null;
                }

                if (
                    Schema::hasColumn(
                        'clinics',
                        'longitude'
                    )
                ) {
                    $clinic->longitude =
                        $request->filled('longitude')
                            ? (float) $request->longitude
                            : null;
                }

                $clinic->save();

                if (
                    $clinic->clinic_type
                    === Clinic::TYPE_OUTPATIENT
                ) {
                    foreach (
                        $request->array('working_hours')
                        as $key => $value
                    ) {
                        ClinicWorkShedule::updateOrCreate(
                            [
                                'clinic_id' => $clinic->id,
                                'day_number' =>
                                    ClinicWorkShedule::dayToNumber(
                                        $key
                                    ),
                            ],
                            [
                                'enabled' =>
                                    (bool) ($value['enabled'] ?? false),
                                'start_time' =>
                                    $value['from'] ?? null,
                                'end_time' =>
                                    $value['to'] ?? null,
                            ]
                        );
                    }
                }

                /*
                 * Сохраняем не только clinic_id.
                 * Раньше именно здесь терялись должность
                 * и выбранные специальности.
                 */
                foreach (
                    $request->array('employees')
                    as $value
                ) {
                    if (empty($value['id'])) {
                        continue;
                    }

                    /*
                     * Нельзя через форму текущей клиники забрать
                     * сотрудника, который уже относится к другой клинике.
                     * Разрешаем сотрудников текущей клиники, а также
                     * непривязанных сотрудников (NULL / 0).
                     */
                    $employee = Doctor::query()
                        ->where(
                            'id',
                            (int) $value['id']
                        )
                        ->where(
                            function ($query) use ($clinic): void {
                                $query
                                    ->where(
                                        'clinic_id',
                                        $clinic->id
                                    )
                                    ->orWhereNull('clinic_id')
                                    ->orWhere('clinic_id', 0);
                            }
                        )
                        ->first();

                    if (!$employee) {
                        continue;
                    }

                    $employee->clinic_id = $clinic->id;

                    if (!empty($value['position'])) {
                        $employee->profession =
                            trim(
                                (string) $value['position']
                            );
                    }

                    $status = $this->specialtiesToStatus(
                        $value['specialties'] ?? []
                    );

                    if (
                        trim(
                            (string) (
                                $value['position']
                                ?? ''
                            )
                        )
                        === 'Медицинский регистратор'
                    ) {
                        $employee->status = null;
                    } elseif ($status !== '') {
                        $employee->status = $status;
                    }

                    $this->fillOptionalEmployeeFields(
                        $employee,
                        $value
                    );

                    $employee->save();
                }
            }
        );

        Cache::forget(
            'clinic_' . $clinic->id
        );

        return redirect()
            ->route('doctors.settings.index')
            ->with(
                'success',
                'Параметры клиники были успешно сохранены.'
            );
    }

    /**
     * Обработчик формы route('doctors.settings.employees.update').
     *
     * Если маршрут у тебя уже смотрит на updateEmployee(), просто
     * замени SettingsController этим файлом.
     */
    public function updateEmployee(
        Request $request
    ): RedirectResponse {
        $currentDoctor = auth()->user()->doctor;
        $clinicId = (int) $currentDoctor->clinic_id;
        $clinic = Clinic::findOrFail($clinicId);

        abort_unless(
            (int) $clinic->headmaster_id
                === (int) $currentDoctor->id,
            403
        );

        $validator = Validator::make(
            $request->all(),
            [
                'doctor_id' => [
                    'required',
                    'integer',
                    Rule::exists('doctors', 'id')
                        ->where('clinic_id', $clinicId),
                ],

                'position' => [
                    'required',
                    'string',
                    Rule::in([
                        'Главный врач',
                        'Врач',
                        'Детский психиатр',
                        'Старшая медицинская сестра',
                        'Медицинская сестра',
                        'Санитар',
                        'Медицинский психолог',
                        'Медицинский регистратор',
                    ]),
                ],

                'employment_status' => [
                    'required',
                    Rule::in([
                        'active',
                        'temporarily_absent',
                        'dismissed',
                    ]),
                ],

                'dismissed_at' => [
                    'nullable',
                    'date',
                ],

                'specialties' => [
                    'nullable',
                    'array',
                    'max:20',
                ],

                'specialties.*' => [
                    'string',
                    'distinct',
                    Rule::in(
                        array_keys(self::SPECIALTY_MAP)
                    ),
                ],

                'phone' => [
                    'nullable',
                    'string',
                    'max:32',
                ],

                'email' => [
                    'nullable',
                    'email',
                    'max:255',
                ],

                'new_headmaster_id' => [
                    'nullable',
                    'integer',
                    Rule::exists('doctors', 'id')
                        ->where('clinic_id', $clinicId),
                ],
            ],
            [
                'doctor_id.required' =>
                    'Не удалось определить сотрудника.',
                'doctor_id.exists' =>
                    'Сотрудник не относится к текущей клинике.',
                'specialties.*.in' =>
                    'Выбрана неизвестная специальность.',
                'new_headmaster_id.exists' =>
                    'Новый главный врач должен работать в этой клинике.',
            ]
        );

        if ($validator->fails()) {
            return back()
                ->withErrors(
                    $validator,
                    'employee'
                )
                ->withInput();
        }

        $data = $validator->validated();

        $employee = Doctor::where(
            'clinic_id',
            $clinicId
        )
            ->findOrFail(
                (int) $data['doctor_id']
            );

        $isCurrentHeadmaster =
            (int) $clinic->headmaster_id
            === (int) $employee->id;

        $headmasterTransferRequired =
            $isCurrentHeadmaster
            && (
                $data['employment_status'] === 'dismissed'
                || $data['position'] !== 'Главный врач'
            );

        if (
            $headmasterTransferRequired
            && empty($data['new_headmaster_id'])
        ) {
            return back()
                ->withErrors(
                    [
                        'new_headmaster_id' =>
                            'Выберите нового главного врача.',
                    ],
                    'employee'
                )
                ->withInput();
        }

        if (
            !empty($data['new_headmaster_id'])
            && (int) $data['new_headmaster_id']
                === (int) $employee->id
        ) {
            return back()
                ->withErrors(
                    [
                        'new_headmaster_id' =>
                            'Сотрудник не может передать должность самому себе.',
                    ],
                    'employee'
                )
                ->withInput();
        }

        DB::transaction(
            function () use (
                $data,
                $employee,
                $clinic,
                $headmasterTransferRequired
            ): void {
                $employee->profession =
                    $data['position'];

                /*
                 * В текущей БД отдельного поля specialties нет.
                 * Поэтому выбранные профили храним в doctors.status
                 * одной строкой через запятую.
                 */
                $status = $this->specialtiesToStatus(
                    $data['specialties'] ?? []
                );

                if (
                    $data['position']
                    === 'Медицинский регистратор'
                ) {
                    $employee->status = null;
                } elseif ($status !== '') {
                    $employee->status = $status;
                }

                $this->fillOptionalEmployeeFields(
                    $employee,
                    $data
                );

                if (
                    Schema::hasColumn(
                        'doctors',
                        'employment_status'
                    )
                ) {
                    $employee->employment_status =
                        $data['employment_status'];
                }

                if (
                    Schema::hasColumn(
                        'doctors',
                        'dismissed_at'
                    )
                ) {
                    $employee->dismissed_at =
                        $data['employment_status']
                            === 'dismissed'
                                ? ($data['dismissed_at'] ?? now())
                                : null;
                }

                if (
                    $data['employment_status']
                    === 'dismissed'
                ) {
                    /*
                     * В проекте непривязанный врач хранится
                     * с clinic_id = 0.
                     */
                    $employee->clinic_id = 0;
                }

                $employee->save();

                if ($headmasterTransferRequired) {
                    $clinic->headmaster_id =
                        (int) $data['new_headmaster_id'];
                    $clinic->save();
                } elseif (
                    $data['position'] === 'Главный врач'
                    && $data['employment_status'] !== 'dismissed'
                ) {
                    $clinic->headmaster_id =
                        $employee->id;
                    $clinic->save();
                }
            }
        );

        Cache::forget('clinic_' . $clinicId);

        return redirect()
            ->route('doctors.settings.index')
            ->with(
                'employee_success',
                'Данные сотрудника были успешно сохранены.'
            );
    }

    private function specialtiesToStatus(
        array $specialties
    ): string {
        return collect($specialties)
            ->flatMap(
                static function ($value): array {
                    return array_map(
                        'trim',
                        explode(
                            ',',
                            (string) $value
                        )
                    );
                }
            )
            ->filter()
            ->map(
                static function (string $specialty): string {
                    return self::SPECIALTY_MAP[$specialty]
                        ?? $specialty;
                }
            )
            ->unique()
            ->values()
            ->implode(', ');
    }

    private function fillOptionalEmployeeFields(
        Doctor $employee,
        array $data
    ): void {
        if (
            Schema::hasColumn('doctors', 'phone')
        ) {
            $employee->phone =
                $data['phone'] ?? null;
        }

        if (
            Schema::hasColumn('doctors', 'email')
        ) {
            $employee->email =
                $data['email'] ?? null;
        }
    }
}
