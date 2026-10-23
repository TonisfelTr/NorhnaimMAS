<?php

namespace App\Http\Controllers\Doctors;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Организационная принадлежность. НЕ выдаёт доступ к чужим медкартам.
 * «Стационар» — запись clinics с clinic_type = inpatient, отдельной модели нет.
 */
class ClinicAffiliationController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorizeManager($request);

        // Отображаем только текущую амбулаторию и ЕЁ стационары.
        // clinic_admin не должен видеть организации других клиентов платформы.
        $currentParentId = (int) $request->user()->doctor->clinic_id;
        $clinics = Clinic::query()
            ->whereNull('deleted_at')
            ->where(function ($query) use ($currentParentId): void {
                $query->where('id', $currentParentId)
                    ->orWhere(function ($children) use ($currentParentId): void {
                        $children->where('parent_clinic_id', $currentParentId)
                            ->where('clinic_type', Clinic::TYPE_INPATIENT);
                    });
            })
            ->orderBy('name')
            ->get([
                'id', 'name', 'clinic_type', 'parent_clinic_id',
                'address', 'city', 'description', 'inpatient_mode', 'phones',
            ]);

        $parentNames = $clinics->pluck('name', 'id');
        $hospitals = $clinics
            ->filter(static fn (Clinic $clinic): bool =>
                (string) $clinic->clinic_type === Clinic::TYPE_INPATIENT
            )
            ->values()
            ->map(static function (Clinic $hospital) use ($parentNames, $currentParentId): array {
                // Для формы редактирования передаём только данные самого стационара.
                // Принадлежность и тип учреждения не являются редактируемыми полями.
                $phones = $hospital->phones;
                if (!is_array($phones)) {
                    $phones = json_decode((string) ($phones ?? '[]'), true);
                }
                if (!is_array($phones)) {
                    $phones = [];
                }

                return [
                    'id' => (int) $hospital->id,
                    'name' => (string) $hospital->name,
                    'address' => (string) $hospital->address,
                    'city' => (string) ($hospital->city ?? ''),
                    'description' => (string) ($hospital->description ?? ''),
                    'inpatient_mode' => (string) ($hospital->inpatient_mode ?: 'round_the_clock'),
                    'phones' => array_values(array_filter($phones, 'is_array')),
                    'can_edit' => (int) $hospital->parent_clinic_id === $currentParentId,
                    'parent_id' => $hospital->parent_clinic_id
                        ? (int) $hospital->parent_clinic_id : null,
                    'parent_name' => $hospital->parent_clinic_id
                        ? ($parentNames[$hospital->parent_clinic_id] ?? null) : null,
                ];
            });

        return view('doctors.settings.clinic-affiliations', [
            'hospitals' => $hospitals,
            'potentialParents' => $clinics
                ->filter(static fn (Clinic $clinic): bool =>
                    (string) $clinic->clinic_type === Clinic::TYPE_OUTPATIENT
                )
                ->map(static fn (Clinic $clinic): array => [
                    'id' => (int) $clinic->id,
                    'name' => (string) $clinic->name,
                    'type' => (string) $clinic->clinic_type,
                ])->values(),
            'linkedCount' => $hospitals->filter(static fn (array $hospital): bool =>
                $hospital['parent_id'] !== null
            )->count(),
            'currentClinicName' => (string) $request->user()->doctor->clinic->name,
        ]);
    }


    /**
     * Создаёт стационар в рамках текущей амбулатории/диспансера.
     * parent_clinic_id и clinic_type вычисляются только на сервере.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorizeManager($request);

        $parentClinic = Clinic::query()
            ->whereKey((int) $request->user()->doctor->clinic_id)
            ->where('clinic_type', Clinic::TYPE_OUTPATIENT)
            ->whereNull('deleted_at')
            ->first();

        abort_unless($parentClinic, 403);

        // Связь с головной амбулаторией обязательна. Не создаём
        // «оторванный» стационар при неполной схеме БД.
        abort_unless(
            Schema::hasColumn('clinics', 'parent_clinic_id'),
            503,
            'Требуется миграция clinics.parent_clinic_id перед созданием стационара.'
        );

        $data = $request->validate([
            'hospital_name' => ['required', 'string', 'max:255'],
            'hospital_address' => ['required', 'string', 'max:500'],
            'hospital_city' => ['nullable', 'string', 'max:255'],
            'hospital_description' => ['nullable', 'string', 'max:2000'],
            'hospital_latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'hospital_longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ], [
            'hospital_name.required' => 'Укажите название стационара.',
            'hospital_address.required' => 'Укажите адрес стационара.',
        ]);

        $optional = static function (string $key) use ($data): ?string {
            $value = trim((string) ($data[$key] ?? ''));
            return $value !== '' ? $value : null;
        };

        $hospital = DB::transaction(static function () use ($data, $optional, $parentClinic): Clinic {
            $attributes = [
                'name' => trim((string) $data['hospital_name']),
                'address' => trim((string) $data['hospital_address']),
                'city' => $optional('hospital_city'),
                'description' => $optional('hospital_description'),
                'clinic_type' => Clinic::TYPE_INPATIENT,
                'parent_clinic_id' => (int) $parentClinic->id,
                // phone в DDL нет; phones/services — nullable JSONB.
                // Телефоны не вводятся при создании стационара и будут
                // настроены позднее. Не передаём PHP-массив в INSERT.
            ];

            // Если в clinics уже есть координаты, сохраняем их вместе с адресом.
            // На базах без этих столбцов создание стационара остаётся рабочим.
            if ($optional('hospital_latitude') !== null
                && $optional('hospital_longitude') !== null
                && Schema::hasColumn('clinics', 'latitude')
                && Schema::hasColumn('clinics', 'longitude')) {
                $attributes['latitude'] = (float) $data['hospital_latitude'];
                $attributes['longitude'] = (float) $data['hospital_longitude'];
            }

            return Clinic::query()->create($attributes);
        }, 3);

        Log::info('Clinic inpatient created', [
            'actor_user_id' => $request->user()->id,
            'clinic_id' => $hospital->id,
            'parent_clinic_id' => $parentClinic->id,
        ]);

        return redirect()
            ->route('doctors.settings.clinic-affiliations.index')
            ->with('success', 'Стационар «' . $hospital->name . '» создан и привязан к ' . $parentClinic->name . '.');
    }

    /**
     * Старый POST/PATCH-редактор отключён: данные стационара можно менять
     * только во временном рабочем окне с отдельной проверкой организации.
     * Удали также старый маршрут .../details из routes/web.php.
     */
    public function updateHospital(Request $request, int $hospital): RedirectResponse
    {
        abort(410, 'Используйте временный режим настройки стационара.');
    }

    public function update(Request $request): RedirectResponse
    {
        $this->authorizeManager($request);

        $data = $request->validate([
            'hospital_id' => ['required', 'integer', 'exists:clinics,id'],
            'parent_clinic_id' => ['present', 'nullable', 'integer', 'min:1', 'exists:clinics,id'],
        ]);

        $hospitalId = (int) $data['hospital_id'];
        $parentId = filled($data['parent_clinic_id'])
            ? (int) $data['parent_clinic_id'] : null;

        $ownParentId = (int) $request->user()->doctor->clinic_id;
        // Из амбулатории нельзя менять принадлежность чужих стационаров.
        if ($parentId !== $ownParentId) {
            throw ValidationException::withMessages([
                'parent_clinic_id' => 'Можно выбирать только свою амбулаторию. Перенос к другой организации выполняется администратором платформы.',
            ]);
        }

        $previousParentId = DB::transaction(function () use ($hospitalId, $ownParentId, $parentId): ?int {
            $hospital = Clinic::query()
                ->whereKey($hospitalId)
                ->where('clinic_type', Clinic::TYPE_INPATIENT)
                ->where('parent_clinic_id', $ownParentId)
                ->whereNull('deleted_at')
                ->lockForUpdate()
                ->firstOrFail();
            $previous = (int) $hospital->parent_clinic_id;
            $hospital->parent_clinic_id = $parentId;
            $hospital->save();
            return $previous;
        }, 3);

        Log::info('Clinic inpatient affiliation changed', [
            'actor_user_id' => $request->user()?->id,
            'hospital_clinic_id' => $hospitalId,
            'previous_parent_clinic_id' => $previousParentId,
            'parent_clinic_id' => $parentId,
        ]);

        return redirect()->route('doctors.settings.clinic-affiliations.index')
            ->with('success', 'Принадлежность стационара сохранена.');
    }

    private function authorizeManager(Request $request): void
    {
        // Управление принадлежностью стационаров выполняется из амбулатории.
        // Сравниваем режим клиники из профиля авторизованного пользователя,
        // а НЕ из параметра запроса: стационар не может открыть страницу
        // или сохранить изменение прямым GET/POST-запросом.
        // clinic_admin видит только свою клинику и её стационары.
        // Глобальный Filament-доступ не используется.
        // adminpanel_see оставляем только для Filament.
        $user = $request->user();
        abort_unless(
            \App\Http\Middleware\ClinicSettingsAccessMiddleware::allows($user)
                && $user?->doctor?->clinic?->clinic_type === Clinic::TYPE_OUTPATIENT,
            403
        );
    }
}
