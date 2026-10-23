<?php

namespace App\Http\Controllers\Doctors;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateRegistryRecordRequest;
use App\Models\Clinic;
use App\Models\ClinicBuildingWorkSchedule;
use App\Models\ClinicCabinet;
use App\Models\ClinicScheduleException;
use App\Models\ClinicWorkShedule;
use App\Models\Doctor;
use App\Models\HospitalBedAssignment;
use App\Models\Patient;
use App\Models\RegistryRecord;
use App\Support\HospitalDepartmentEligibility;
use App\Support\PatientCareScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ReceptionController extends Controller
{
    private const APPOINTMENT_FORMAT_IN_PERSON = 'in_person';
    private const APPOINTMENT_FORMAT_TELEMEDICINE = 'telemedicine';

    public function index(Request $request): View
    {
        $user = $request->user();
        $isDebug = app()->hasDebugModeEnabled();

        $doctor = $user && method_exists($user, 'doctor')
            ? $user->doctor()->with('clinic')->first()
            : null;

        $nurse = $user && method_exists($user, 'nurse')
            ? $user->nurse()->with('clinic')->first()
            : null;

        // Режим берём у фактической клиники пользователя, как в навигации.
        // Отладочный $hostClinic не определяет область поиска пациентов.
        $currentClinic = $doctor?->clinic ?? $nurse?->clinic;
        $clinicIsInpatient = $currentClinic?->clinic_type === Clinic::TYPE_INPATIENT;

        // Верхний поиск отправляет q, основная форма — fullname.
        if (!$request->filled('fullname') && is_string($request->query('q'))) {
            $request->merge(['fullname' => trim($request->query('q'))]);
        }

        // Удалённый переключатель больше не управляет режимом страницы.
        // Очищаем старый параметр, чтобы он не переносился в пагинацию.
        $request->query->remove('patient_scope');

        // Сохраняем существующую форму оформления из стационарного navbar.
        if ($clinicIsInpatient && $request->boolean('admit')
            && class_exists(InpatientAdmissionController::class)) {
            return app(InpatientAdmissionController::class)->index($request);
        }

        if ($clinicIsInpatient) {
            return $this->inpatientReceptionIndex(
                $request,
                $currentClinic,
                $doctor,
                $nurse,
                $isDebug
            );
        }

        $currentDoctorId = $doctor?->id
            ? (int) $doctor->id
            : null;

        $currentClinicId = $doctor?->clinic_id
            ? (int) $doctor->clinic_id
            : ($nurse?->clinic_id ? (int) $nurse->clinic_id : null);

        /*
         * В старой базе частная практика могла иметь clinic_id = 0.
         * В новой — clinic_id может быть NULL. Оба варианта считаем
         * частной практикой.
         */
        $isPrivateDoctor = $doctor !== null
            && ((int) ($doctor->clinic_id ?? 0) === 0);

        /*
         |--------------------------------------------------------------------------
         | Врачи для поиска по существующим записям
         |--------------------------------------------------------------------------
         |
         | Фильтр использует область доступа журнала. Наличие активного
         | кабинета проверяется отдельно при создании и изменении записи.
         */
        $doctors = $this->receptionFilterDoctors(
            $currentClinicId,
            $isPrivateDoctor ? $currentDoctorId : null,
            $isDebug
        );

        $selectedDoctorId = $request->filled('doctor_id')
            ? $request->integer('doctor_id')
            : null;

        $doctorSearchOptions = $doctors
            ->map(function (Doctor $filterDoctor) use ($selectedDoctorId): array {
                return array_merge(
                    $this->receptionDoctorDisplayData($filterDoctor),
                    ['selected' => (int) $filterDoctor->id === $selectedDoctorId]
                );
            })
            ->values()
            ->all();

        /*
         |--------------------------------------------------------------------------
         | Текущие записи регистратуры
         |--------------------------------------------------------------------------
         */
        $receptionsQuery = RegistryRecord::query()
            ->with(['patient', 'doctor'])
            ->orderByDesc('for_datetime');

        if (!$isDebug) {
            $receptionsQuery->whereBetween('for_datetime', [
                Carbon::now()->startOfDay(),
                Carbon::now()->endOfDay(),
            ]);
        }

        $this->scopeReceptionRecords(
            $receptionsQuery,
            $currentClinicId,
            $isPrivateDoctor ? $currentDoctorId : null,
            $isDebug
        );
        PatientCareScope::forRecords($receptionsQuery, 'outpatient');

        if ($request->filled('doctor_id')) {
            $receptionsQuery->where(
                'doctor_id',
                $request->integer('doctor_id')
            );
        }

        if (
            $request->filled('fullname')
            || $request->filled('birth_at')
            || $request->filled('address_residence')
        ) {
            $receptionsQuery->whereHas(
                'patient',
                function ($query) use ($request): void {
                    $this->applyPatientSearchFilters($query, $request);
                }
            );
        }

        $receptions = $receptionsQuery
            ->paginate(30, ['*'], 'receptions_page')
            ->withQueryString();

        $receptionRows = $receptions->getCollection()
            ->map(function (RegistryRecord $record) use (
                $isDebug,
                $isPrivateDoctor,
                $currentDoctorId,
                $currentClinicId
            ): array {
                $patient = $record->patient;
                $doctorData = $this->receptionDoctorDisplayData($record->doctor);
                $canModify = $isDebug || ($isPrivateDoctor
                    ? $currentDoctorId && (int) $record->doctor_id === $currentDoctorId
                    : $currentClinicId && (int) $record->clinic_id === $currentClinicId);

                return [
                    'id' => $record->id,
                    'patient_id' => $patient?->id,
                    'patient_name' => $patient?->fullname ?? 'Пациент не указан',
                    'patient_initials' => mb_substr($patient?->surname ?? 'П', 0, 1)
                        . mb_substr($patient?->name ?? '', 0, 1),
                    'birth_date' => $patient?->birth_at
                        ? Carbon::parse($patient->birth_at)->format('d.m.Y')
                        : null,
                    'doctor_name' => $doctorData['full_name'],
                    'doctor_profile' => $doctorData['profile'],
                    'appointment_format' => $record->appointment_format ?: self::APPOINTMENT_FORMAT_IN_PERSON,
                    'appointment_format_label' => $record->appointment_format === self::APPOINTMENT_FORMAT_TELEMEDICINE
                        ? 'Телемедицина'
                        : 'Очный приём',
                    'appointment_format_icon' => $record->appointment_format === self::APPOINTMENT_FORMAT_TELEMEDICINE
                        ? 'bi-camera-video'
                        : 'bi-person-check',
                    'appointment_format_class' => $record->appointment_format === self::APPOINTMENT_FORMAT_TELEMEDICINE
                        ? 'is-telemedicine'
                        : 'is-in-person',
                    'address_registration' => $patient?->address_registration ?: '—',
                    'address_residence' => $patient?->address_residence ?: '—',
                    'medical_card_url' => $patient
                        ? route('doctors.patients.medical_card', $patient->id)
                        : null,
                    'can_modify' => $canModify,
                    'edit_url' => $canModify
                        ? route('doctors.reception.edit', $record->id)
                        : null,
                ];
            })
            ->values()
            ->all();

        /*
         |--------------------------------------------------------------------------
         | Поиск непосредственно по patients
         |--------------------------------------------------------------------------
         |
         | Раньше поиск шёл только через registry_records. Поэтому пациент,
         | существующий в patients, но ещё не имеющий registry_records,
         | не мог быть найден.
         */
        $patientsQuery = $this->receptionVisiblePatients(
            $currentClinicId,
            $isPrivateDoctor ? $currentDoctorId : null,
            $isDebug
        )
            ->with(['doctor', 'diagnose']);
        PatientCareScope::forPatients($patientsQuery, 'outpatient');

        $this->applyPatientSearchFilters($patientsQuery, $request);

        if ($request->filled('doctor_id')) {
            $selectedDoctorId = $request->integer('doctor_id');

            $patientsQuery->where(
                function ($query) use ($selectedDoctorId): void {
                    $query
                        ->where('doctor_id', $selectedDoctorId)
                        ->orWhereIn(
                            'id',
                            RegistryRecord::query()
                                ->select('patient_id')
                                ->where('doctor_id', $selectedDoctorId)
                        );
                }
            );
        }

        $patients = $patientsQuery
            ->orderBy('surname')
            ->orderBy('name')
            ->orderBy('patronym')
            ->paginate(30, ['*'], 'patients_page')
            ->withQueryString();

        /*
         |--------------------------------------------------------------------------
         | Данные владельца регистратуры
         |--------------------------------------------------------------------------
         */
        if ($isDebug) {
            $hostClinic = Clinic::query()
                ->orderBy('id')
                ->first();

            $name = 'Администратор';
            $surname = 'Дебаггеров';
            $patronym = 'Тестович';
            $profession = 'дебаггер';
        } else {
            $hostClinic = $currentClinicId
                ? Clinic::query()->find($currentClinicId)
                : null;

            $name = $doctor?->name ?? $nurse?->name ?? '';
            $surname = $doctor?->surname ?? $nurse?->surname ?? '';
            $patronym = $doctor?->patronym ?? $nurse?->patronym ?? '';
            $profession = $nurse?->profession
                ?? $doctor?->profession
                ?? 'врач';
        }

        $futureReceptionsQuery = RegistryRecord::query()
            ->where(
                'for_datetime',
                '>',
                Carbon::now()->addDay()->startOfDay()
            );

        $this->scopeReceptionRecords(
            $futureReceptionsQuery,
            $currentClinicId,
            $isPrivateDoctor ? $currentDoctorId : null,
            $isDebug
        );
        PatientCareScope::forRecords($futureReceptionsQuery, 'outpatient');

        $futureReceptions = $futureReceptionsQuery->count();

        return view('doctors.reception.table', [
            'receptions' => $receptions,
            'receptionRows' => $receptionRows,
            'patients' => $patients,
            'hostClinic' => $hostClinic,
            'receptionClinicName' => $hostClinic?->name ?? 'Частная практика',
            'name' => $name,
            'surname' => $surname,
            'patronym' => $patronym,
            'profession' => $profession,
            'doctors' => $doctors,
            'doctorSearchOptions' => $doctorSearchOptions,
            'futureReceptions' => $futureReceptions,
            'isDebug' => $isDebug,
            'isPrivateDoctor' => $isPrivateDoctor,
            'receptionPage' => $this->receptionPageData(
                false,
                $receptions->total(),
                $futureReceptions,
                $receptions->total()
            ),
        ]);
    }

    /**
     * Поиск по пациентам с действующим размещением в стационаре.
     * Амбулаторные записи и текущий врач пациента не определяют госпитализацию.
     */
    private function inpatientReceptionIndex(
        Request $request,
        ?Clinic $clinic,
        $doctor,
        $nurse,
        bool $isDebug
    ): View {
        $clinicId = $clinic?->id ? (int) $clinic->id : null;
        $isPrivateDoctor = $doctor !== null
            && ((int) ($doctor->clinic_id ?? 0) === 0);
        $privateDoctorId = $isPrivateDoctor && $doctor?->id
            ? (int) $doctor->id
            : null;
        $currentAssignments = $this->activeInpatientAssignments();

        // Начинаем с patients: человек попадёт в выдачу даже без registry_records.
        // Подзапрос также не дублирует пациента при нескольких размещениях в данных.
        $currentPatients = PatientCareScope::forPatients(
            $this->receptionVisiblePatients($clinicId, $privateDoctorId, $isDebug),
            'inpatient'
        );
        $currentPatientsCount = (clone $currentPatients)->count();

        // В выборе врача — только врачи пациентов этого стационарного списка,
        // включая врача диспансера. Отбор выполняется до пагинации и поиска.
        $doctors = Doctor::query()
            ->whereIn(
                'id',
                (clone $currentPatients)
                    ->whereNotNull('doctor_id')
                    ->select('doctor_id')
            )
            ->orderBy('surname')
            ->orderBy('name')
            ->orderBy('patronym')
            ->get();

        $selectedDoctorId = $request->filled('doctor_id')
            ? $request->integer('doctor_id')
            : null;

        $doctorSearchOptions = $doctors
            ->map(function (Doctor $filterDoctor) use ($selectedDoctorId): array {
                return array_merge(
                    $this->receptionDoctorDisplayData($filterDoctor),
                    ['selected' => (int) $filterDoctor->id === $selectedDoctorId]
                );
            })
            ->values()
            ->all();

        $patientsQuery = (clone $currentPatients)->with('doctor');
        $this->applyPatientSearchFilters($patientsQuery, $request);

        if ($selectedDoctorId !== null) {
            $patientsQuery->where('doctor_id', $selectedDoctorId);
        }

        $patients = $patientsQuery
            ->orderBy('surname')
            ->orderBy('name')
            ->orderBy('patronym')
            ->orderBy('id')
            ->paginate(30, ['*'], 'patients_page')
            ->withQueryString();

        $pagePatientIds = $patients->getCollection()->pluck('id')->all();
        $placements = (clone $currentAssignments)
            ->whereIn('patient_id', $pagePatientIds)
            ->with('bed.room.department')
            ->orderByDesc('assigned_at')
            ->orderByDesc('id')
            ->get()
            ->unique('patient_id')
            ->keyBy('patient_id');

        $placementClinicNames = Clinic::query()
            ->whereIn(
                'id',
                $placements->pluck('bed.clinic_id')->filter()->unique()->values()->all()
            )
            ->pluck('name', 'id');

        // Просмотр может быть межклиническим; изменение размещения доступно врачу своего стационара.
        $dischargeClinicId = $doctor?->id
            && (int) ($doctor->clinic_id ?? 0) > 0
            && $doctor->clinic?->clinic_type === Clinic::TYPE_INPATIENT
                ? (int) $doctor->clinic_id
                : null;
        $dischargeRouteName = null;

        if ($dischargeClinicId !== null) {
            foreach (['doctors.inpatient.rooms.beds.release', 'doctors.settings.rooms.beds.release'] as $routeName) {
                if (Route::has($routeName)) {
                    $dischargeRouteName = $routeName;
                    break;
                }
            }
        }
        $placementEditRoutesAvailable = $dischargeClinicId !== null
            && Route::has('doctors.inpatient.admissions.options')
            && Route::has('doctors.inpatient.admissions.store');

        $receptionRows = $patients->getCollection()
            ->map(function (Patient $patient) use (
                $placements,
                $placementClinicNames,
                $dischargeClinicId,
                $dischargeRouteName,
                $placementEditRoutesAvailable
            ): array {
                $placement = $placements->get($patient->id);
                $bed = $placement?->bed;
                $room = $bed?->room;
                $doctorData = $this->receptionDoctorDisplayData($patient->doctor);
                $roomLabel = $room?->number !== null
                    ? 'Палата №' . $room->number
                    : 'Палата не указана';
                $bedLabel = $bed?->number !== null
                    ? 'Койка №' . $bed->number
                    : 'Койка не указана';
                $placementWarning = HospitalDepartmentEligibility::violation(
                    $patient,
                    $room?->department
                );
                $dischargeUnavailableReason = null;
                $placementEditUnavailableReason = null;
                if ($dischargeClinicId === null) {
                    $dischargeUnavailableReason = 'Выписка доступна врачу стационара.';
                    $placementEditUnavailableReason = 'Редактирование доступно врачу стационара.';
                } elseif (!$placement?->id
                    || (int) $placement->patient_id !== (int) $patient->id
                    || $placement->released_at !== null
                    || !$bed?->id
                    || !$room?->id) {
                    $dischargeUnavailableReason = 'Размещение пациента изменилось. Обновите страницу.';
                    $placementEditUnavailableReason = $dischargeUnavailableReason;
                } elseif ((int) $bed->clinic_id !== $dischargeClinicId
                    || (int) $room->clinic_id !== $dischargeClinicId) {
                    $dischargeUnavailableReason = 'Пациент находится в другой клинике. Выписку выполняет врач этой клиники.';
                    $placementEditUnavailableReason = 'Пациент находится в другой клинике. Запись изменяет врач этой клиники.';
                }
                if ($dischargeUnavailableReason === null && $dischargeRouteName === null) {
                    $dischargeUnavailableReason = 'Выписка временно недоступна. Обратитесь к администратору.';
                }
                if ($placementEditUnavailableReason === null && !$placementEditRoutesAvailable) {
                    $placementEditUnavailableReason = 'Редактирование временно недоступно. Обратитесь к администратору.';
                }
                $canDischarge = $dischargeUnavailableReason === null;
                $canEditPlacement = $placementEditUnavailableReason === null;

                try {
                    $birthDate = $patient->birth_at
                        ? Carbon::parse($patient->birth_at)->format('d.m.Y')
                        : null;
                } catch (\Throwable) {
                    $birthDate = 'Некорректная дата';
                }

                return [
                    'id' => $patient->id,
                    'patient_id' => $patient->id,
                    'patient_name' => $patient->fullname ?: 'Пациент не указан',
                    'patient_initials' => mb_substr($patient->surname ?? 'П', 0, 1)
                        . mb_substr($patient->name ?? '', 0, 1),
                    'birth_date' => $birthDate,
                    'doctor_name' => $doctorData['full_name'],
                    'doctor_profile' => $doctorData['profile'],
                    'address_registration' => $patient->address_registration ?: '—',
                    'address_residence' => $patient->address_residence ?: '—',
                    'medical_card_url' => route('doctors.patients.medical_card', $patient->id),
                    'edit_url' => null,
                    'assignment_id' => $placement?->id ? (int) $placement->id : null,
                    'can_discharge' => $canDischarge,
                    'discharge_unavailable_reason' => $dischargeUnavailableReason,
                    'discharge_url' => $canDischarge
                        ? route($dischargeRouteName, ['bed' => $bed->id])
                        : null,
                    'placement_bed_id' => $bed?->id ? (int) $bed->id : null,
                    'can_edit_placement' => $canEditPlacement,
                    'placement_edit_unavailable_reason' => $placementEditUnavailableReason,
                    'placement_edit_url' => $canEditPlacement
                        ? route('doctors.inpatient.admissions.store')
                        : null,
                    'placement_options_url' => $canEditPlacement
                        ? route('doctors.inpatient.admissions.options')
                        : null,
                    'location_clinic' => $placementClinicNames->get($bed?->clinic_id)
                        ?: 'Клиника стационара',
                    'location_department' => $room?->department?->name ?: 'Отделение не указано',
                    'location_room_bed' => $roomLabel . ' · ' . $bedLabel,
                    'assigned_at_display' => $placement?->assigned_at
                        ? Carbon::parse($placement->assigned_at)->format('d.m.Y H:i')
                        : null,
                    'placement_warning' => $placementWarning,
                ];
            })
            ->values()
            ->all();

        return view('doctors.reception.table', [
            'receptions' => $patients,
            'receptionRows' => $receptionRows,
            'patients' => $patients,
            'hostClinic' => $clinic,
            'receptionClinicName' => $clinic?->name
                ?: ($isPrivateDoctor ? 'Частная практика' : 'Клиника не определена'),
            'name' => $isDebug ? 'Администратор' : ($doctor?->name ?? $nurse?->name ?? ''),
            'surname' => $isDebug ? 'Дебаггеров' : ($doctor?->surname ?? $nurse?->surname ?? ''),
            'patronym' => $isDebug ? 'Тестович' : ($doctor?->patronym ?? $nurse?->patronym ?? ''),
            'profession' => $isDebug
                ? 'дебаггер'
                : ($nurse?->profession ?? $doctor?->profession ?? 'врач'),
            'doctors' => $doctors,
            'doctorSearchOptions' => $doctorSearchOptions,
            'futureReceptions' => 0,
            'isDebug' => $isDebug,
            'isPrivateDoctor' => $isPrivateDoctor,
            'receptionPage' => $this->receptionPageData(
                true,
                $currentPatientsCount,
                $patients->total(),
                $patients->total()
            ),
        ]);
    }

    /**
     * Текущее пребывание определяется размещением, независимо от клиники врача.
     * Даже в debug обязательны незавершённое размещение и корректная койка/палата.
     */
    private function activeInpatientAssignments(): Builder
    {
        return PatientCareScope::activeAssignments();
    }

    /**
     * История размещений с существующими согласованными койкой и палатой.
     */
    private function inpatientAssignments(): Builder
    {
        return HospitalBedAssignment::query()
            ->whereHas('bed', static function (Builder $bed): void {
                $bedClinicColumn = $bed->getModel()->qualifyColumn('clinic_id');

                $bed->whereHas('room', static function (Builder $room) use ($bedClinicColumn): void {
                    $room->whereColumn(
                        $room->getModel()->qualifyColumn('clinic_id'),
                        $bedClinicColumn
                    );
                });
            });
    }

    /**
     * Связь с собственной клиникой сохраняется и после завершения размещения.
     */
    private function inpatientAssignmentsForClinic(?int $clinicId): Builder
    {
        $query = $this->inpatientAssignments();

        if (!$clinicId || $clinicId <= 0) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereHas(
            'bed',
            static fn (Builder $bed): Builder => $bed->where('clinic_id', $clinicId)
        );
    }

    /**
     * Общая область пациентов для амбулаторного и стационарного просмотра.
     * Сохраняем связь через врача/запись регистратуры и добавляем историю
     * размещений в собственной клинике. clinic_type не является правом доступа.
     */
    private function receptionVisiblePatients(
        ?int $clinicId,
        ?int $privateDoctorId,
        bool $isDebug
    ): Builder {
        $query = Patient::query();

        if ($isDebug) {
            return $query;
        }

        if ($privateDoctorId) {
            return $query->where(static function (Builder $patient) use ($privateDoctorId): void {
                $patient->where('doctor_id', $privateDoctorId)
                    ->orWhereIn(
                        'id',
                        RegistryRecord::query()->select('patient_id')
                            ->where('doctor_id', $privateDoctorId)
                    );
            });
        }

        if (!$clinicId || $clinicId <= 0) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(function (Builder $patient) use ($clinicId): void {
            $patient->whereIn(
                'doctor_id',
                Doctor::query()->select('id')->where('clinic_id', $clinicId)
            )
                ->orWhereIn(
                    'id',
                    RegistryRecord::query()->select('patient_id')->where('clinic_id', $clinicId)
                )
                ->orWhereIn(
                    'id',
                    $this->inpatientAssignmentsForClinic($clinicId)->select('patient_id')
                );
        });
    }

    /**
     * В общей области доступны записи собственных и уже доступных пациентов,
     * в том числе запись диспансера для пациента своего стационара.
     */
    private function scopeReceptionRecords(
        Builder $query,
        ?int $clinicId,
        ?int $privateDoctorId,
        bool $isDebug
    ): Builder {
        if ($isDebug) {
            return $query;
        }

        if ($privateDoctorId) {
            return $query->where('doctor_id', $privateDoctorId);
        }

        if (!$clinicId || $clinicId <= 0) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(function (Builder $record) use ($clinicId): void {
            $record->where('clinic_id', $clinicId)
                ->orWhereIn(
                    'patient_id',
                    $this->receptionVisiblePatients($clinicId, null, false)->select('id')
                );
        });
    }

    /**
     * Готовые подписи и ссылки для обоих режимов; Blade только отображает данные.
     */
    private function receptionPageData(
        bool $isInpatient,
        int $firstCount,
        int $secondCount,
        int $resultCount
    ): array {
        return [
            'is_inpatient' => $isInpatient,
            'title' => $isInpatient ? 'Госпитализация' : 'Амбулаторная регистратура',
            'description' => $isInpatient
                ? 'Поиск пациентов, которые сейчас находятся в стационаре. Используйте фильтры по ФИО, врачу, дате рождения или адресу и переходите в медицинскую карту.'
                : 'Поиск пациентов на амбулаторном лечении, просмотр текущих записей и переход в медицинскую карту. Используйте фильтры по ФИО, врачу, дате рождения или адресу.',
            'badge' => $isInpatient ? 'Стационар' : 'Амбулаторный приём',
            'search_title' => $isInpatient ? 'Поиск пациента в стационаре' : 'Поиск амбулаторного пациента',
            'search_description' => $isInpatient
                ? 'Поиск только среди пациентов с текущим размещением в стационаре.'
                : 'Поиск среди доступных пациентов без текущего размещения в стационаре.',
            'search_button_label' => $isInpatient ? 'Найти в стационаре' : 'Найти пациента',
            'doctor_label' => $isInpatient ? 'Врач пациента' : 'Врач',
            'table_title' => $isInpatient ? 'Пациенты стационара' : 'Текущие амбулаторные записи',
            'count_label' => ($isInpatient ? 'Пациентов: ' : 'Записей: ') . $resultCount,
            'first_stat_label' => $isInpatient ? 'Сейчас в стационаре' : 'Найдено записей',
            'first_stat_value' => $firstCount,
            'second_stat_label' => $isInpatient ? 'Найдено пациентов' : 'В ожидании',
            'second_stat_value' => $secondCount,
            'empty_title' => $isInpatient
                ? ($firstCount > 0 ? 'Госпитализированные пациенты не найдены' : 'В стационаре пока нет пациентов')
                : 'Записи не найдены',
            'empty_description' => $isInpatient
                ? ($firstCount > 0
                    ? 'Измените или сбросьте фильтры. Поиск выполняется среди пациентов, которые сейчас находятся в стационаре.'
                    : 'Пациенты появятся здесь после оформления и размещения в стационаре.')
                : 'Измените или сбросьте фильтры. Здесь отображаются записи пациентов, которые сейчас не находятся в стационаре.',
            'form_action' => route('doctors.reception.index'),
            'reset_url' => route('doctors.reception.index'),
        ];
    }

    /**
     * Врачи доступных амбулаторных пациентов и записей, без проверки кабинета.
     */
    private function receptionFilterDoctors(
        ?int $clinicId,
        ?int $privateDoctorId,
        bool $isDebug
    ) {
        $patients = PatientCareScope::forPatients(
            $this->receptionVisiblePatients($clinicId, $privateDoctorId, $isDebug),
            'outpatient'
        );
        $records = PatientCareScope::forRecords(
            $this->scopeReceptionRecords(
                RegistryRecord::query(),
                $clinicId,
                $privateDoctorId,
                $isDebug
            ),
            'outpatient'
        );

        return Doctor::query()
            ->where(static function (Builder $doctor) use ($patients, $records): void {
                $doctor->whereIn(
                    'id',
                    $patients->whereNotNull('doctor_id')->select('doctor_id')
                )->orWhereIn(
                    'id',
                    $records->whereNotNull('doctor_id')->select('doctor_id')
                );
            })
            ->orderBy('surname')
            ->orderBy('name')
            ->orderBy('patronym')
            ->get();
    }

    /**
     * Единое представление врача для фильтра и строк журнала.
     */
    private function receptionDoctorDisplayData(?Doctor $doctor): array
    {
        $fullName = trim(implode(' ', array_filter([
            $doctor?->surname,
            $doctor?->name,
            $doctor?->patronym,
        ])));
        $fullName = preg_replace('/\s+/u', ' ', $fullName) ?? $fullName;
        $fullName = $fullName !== '' ? $fullName : 'Врач не указан';

        $profile = trim((string) ($doctor?->status ?? ''));
        $profession = trim((string) ($doctor?->profession ?? ''));

        if ($profile === '') {
            if ($profession !== '' && mb_strtolower($profession) !== 'врач') {
                $profile = mb_strtolower($profession) === 'психиатр'
                    ? 'Психиатр'
                    : $profession;
            } else {
                $profile = 'Профиль не указан';
            }
        }

        return [
            'id' => $doctor?->id,
            'full_name' => $fullName,
            'profile' => $profile,
            'label' => $fullName . ' — ' . $profile,
            'search_text' => $fullName . ' ' . $profile,
        ];
    }

    /**
     * Общие фильтры поиска пациента.
     */
    private function applyPatientSearchFilters(
        $query,
        Request $request
    ): void {
        if ($request->filled('fullname')) {
            $fullName = trim((string) $request->get('fullname'));

            $searchParts = preg_split(
                '/[\s,]+/u',
                $fullName,
                -1,
                PREG_SPLIT_NO_EMPTY
            ) ?: [];

            foreach ($searchParts as $part) {
                $searchValue = '%' . $part . '%';

                $query->where(
                    function ($patientQuery) use (
                        $searchValue,
                        $part
                    ): void {
                        $patientQuery
                            ->where('surname', 'ilike', $searchValue)
                            ->orWhere('name', 'ilike', $searchValue)
                            ->orWhere('patronym', 'ilike', $searchValue)
                            ->orWhereRaw(
                                "CONCAT(LEFT(name, 1), '.', LEFT(patronym, 1), '.') ILIKE ?",
                                ['%' . $part . '%']
                            );
                    }
                );
            }
        }

        if ($request->filled('birth_at')) {
            $query->whereDate(
                'birth_at',
                Carbon::parse($request->get('birth_at'))
            );
        }

        if ($request->filled('address_residence')) {
            $address = '%' . trim(
                (string) $request->get('address_residence')
            ) . '%';

            $query->where(
                function ($addressQuery) use ($address): void {
                    $addressQuery
                        ->where('address_registration', 'ilike', $address)
                        ->orWhere('address_residence', 'ilike', $address);
                }
            );
        }
    }

    public function create(Request $request): View
    {
        $currentClinicId = $this->currentReceptionClinicId($request);
        $activeClinicQuarantine =
            $this->activeClinicQuarantine($currentClinicId);

        $appointmentFormats = $this->clinicAppointmentFormats(
            $currentClinicId
        );
        $defaultAppointmentFormat = $this->defaultAppointmentFormat(
            $appointmentFormats
        );

        $doctors = $this->receptionDoctorsForClinic(
            $currentClinicId,
            $appointmentFormats
        );

        $doctorClinicIds = $doctors
            ->mapWithKeys(
                fn (Doctor $doctor): array => [
                    (string) $doctor->id => $doctor->clinic_id !== null
                        ? (int) $doctor->clinic_id
                        : null,
                ]
            )
            ->all();

        $clinicWorkSchedules = $this->clinicWorkSchedulesForDoctors($doctors);

        [
            $clinicScheduleExceptions,
            $doctorCabinetIds,
            $doctorCabinets,
        ] = $this->clinicScheduleExceptionDataForDoctors(
            $doctors
        );

        $doctorAppointmentFormats = $this->doctorAppointmentFormats(
            $doctors,
            $appointmentFormats,
            $doctorCabinetIds
        );

        return view('doctors.reception.create', compact(
            'doctors',
            'doctorClinicIds',
            'clinicWorkSchedules',
            'clinicScheduleExceptions',
            'doctorCabinetIds',
            'doctorCabinets',
            'doctorAppointmentFormats',
            'appointmentFormats',
            'defaultAppointmentFormat',
            'activeClinicQuarantine'
        ));
    }

    public function store(Request $request)
    {
        $currentClinicId = $this->currentReceptionClinicId($request);
        $activeClinicQuarantine =
            $this->activeClinicQuarantine($currentClinicId);

        /*
         * Создание новых записей во время действующего карантина
         * запрещаем именно на backend. Состояние кнопок и формы
         * в браузере не является защитой: прямой POST сюда также
         * должен быть отклонён.
         */
        if ($activeClinicQuarantine) {
            $until = Carbon::parse(
                $activeClinicQuarantine->date_to
            )->format('d.m.Y');

            throw ValidationException::withMessages([
                'appointment_datetime' => sprintf(
                    'В клинике действует карантин «%s». Создание новых записей через регистратуру отключено до %s включительно.',
                    $activeClinicQuarantine->title ?: 'Карантин',
                    $until
                ),
            ]);
        }

        $validator = Validator::make(
            $request->all(),
            [
                'patient_id' => 'nullable|integer|exists:patients,id',
                'surname' => 'required|string|max:255',
                'name' => 'required|string|max:255',
                'patronym' => 'nullable|string|max:255',
                'birth_at' => 'required|date|before_or_equal:today',
                'gender' => 'required|in:M,F',
                'appointment_datetime' => 'required|date|after_or_equal:now',
                'appointment_format' => 'required|in:in_person,telemedicine',
                'address_registration' => 'required|string',
                'address_residence' => 'required|string',
                'birth_place' => 'required|string',
                'snils' => 'required|string|max:14',
                'oms' => 'required|string|max:16',
                'doctor_id' => 'required|exists:doctors,id',

                'patient_document_type' => 'nullable|in:passport_rf,birth_certificate,foreign_passport,residence_permit,temporary_identity,foreign_identity,other',
                'patient_document_name' => 'nullable|string|max:255',
                'patient_document_series' => 'nullable|string|max:50',
                'patient_document_number' => 'nullable|string|max:100',
                'patient_document_department_code' => 'nullable|string|max:20',
                'patient_document_issued_by' => 'nullable|string',
                'patient_document_issued_at' => 'nullable|date|before_or_equal:today',

                'representative_type' => 'nullable|in:mother,father,adoptive_parent,guardian,trustee,authorized,other',
                'representative_surname' => 'nullable|string|max:255',
                'representative_name' => 'nullable|string|max:255',
                'representative_patronym' => 'nullable|string|max:255',
                'representative_birth_at' => 'nullable|date|before_or_equal:today',
                'representative_snils' => 'nullable|string|max:14',
                'representative_phone' => 'nullable|string|max:50',
                'representative_email' => 'nullable|email|max:255',

                'representative_identity_document_type' => 'nullable|in:passport_rf,foreign_passport,residence_permit,temporary_identity,foreign_identity,other',
                'representative_identity_document_name' => 'nullable|string|max:255',
                'representative_identity_series' => 'nullable|string|max:50',
                'representative_identity_number' => 'nullable|string|max:100',
                'representative_identity_department_code' => 'nullable|string|max:20',
                'representative_identity_issued_by' => 'nullable|string',
                'representative_identity_issued_at' => 'nullable|date|before_or_equal:today',

                'representative_authority_document_type' => 'nullable|in:guardianship_order,power_of_attorney,other',
                'representative_authority_document_name' => 'nullable|string|max:255',
                'representative_authority_series' => 'nullable|string|max:50',
                'representative_authority_number' => 'nullable|string|max:100',
                'representative_authority_issued_by' => 'nullable|string',
                'representative_authority_issued_at' => 'nullable|date|before_or_equal:today',
            ],
            [
                'required' => 'Поле ":attribute" обязательно для заполнения.',
                'string' => 'Поле ":attribute" должно быть строкой.',
                'integer' => 'Поле ":attribute" должно быть числом.',
                'max' => 'Поле ":attribute" не должно превышать :max символов.',
                'date' => 'Поле ":attribute" должно быть датой.',
                'email' => 'Поле ":attribute" должно содержать корректный e-mail.',
                'in' => 'Для поля ":attribute" выбрано недопустимое значение.',
                'before_or_equal' => 'Поле ":attribute" не может содержать будущую дату.',
                'after_or_equal' => 'Поле ":attribute" должно быть датой не раньше текущего момента.',
                'exists' => 'Значение поля ":attribute" не зарегистрировано в базе данных.',
            ],
            [
                'patient_id' => 'Пациент',
                'surname' => 'Фамилия',
                'name' => 'Имя',
                'patronym' => 'Отчество',
                'birth_at' => 'Дата рождения',
                'gender' => 'Пол',
                'appointment_datetime' => 'Дата приёма',
                'appointment_format' => 'Формат приёма',
                'address_registration' => 'Адрес регистрации',
                'address_residence' => 'Адрес проживания',
                'birth_place' => 'Место рождения',
                'snils' => 'СНИЛС',
                'oms' => 'ОМС',
                'doctor_id' => 'Лечащий врач',
                'patient_document_type' => 'Тип документа пациента',
                'patient_document_name' => 'Наименование документа пациента',
                'patient_document_number' => 'Номер документа пациента',
                'patient_document_department_code' => 'Код подразделения документа пациента',
                'patient_document_issued_by' => 'Кем выдан документ пациента',
                'patient_document_issued_at' => 'Дата выдачи документа пациента',
                'representative_type' => 'Тип представителя',
                'representative_surname' => 'Фамилия представителя',
                'representative_name' => 'Имя представителя',
                'representative_birth_at' => 'Дата рождения представителя',
                'representative_identity_document_type' => 'Тип документа представителя',
                'representative_identity_document_name' => 'Наименование документа представителя',
                'representative_identity_number' => 'Номер документа представителя',
                'representative_identity_department_code' => 'Код подразделения документа представителя',
                'representative_identity_issued_by' => 'Кем выдан документ представителя',
                'representative_identity_issued_at' => 'Дата выдачи документа представителя',
                'representative_authority_document_type' => 'Тип документа о полномочиях',
                'representative_authority_document_name' => 'Наименование документа о полномочиях',
                'representative_authority_number' => 'Номер документа о полномочиях',
                'representative_authority_issued_by' => 'Кем выдан документ о полномочиях',
                'representative_authority_issued_at' => 'Дата выдачи документа о полномочиях',
            ]
        );

        $validator->after(function ($validator) use ($request): void {
            /*
             * При выборе существующего пациента документы уже находятся
             * в patient_identity_documents / patient_representatives.
             * Повторно заставлять регистратуру вводить их не нужно.
             */
            if ($request->filled('patient_id')) {
                return;
            }

            foreach ([
                'patient_document_type' => 'Тип документа пациента',
                'patient_document_number' => 'Номер документа пациента',
                'patient_document_issued_by' => 'Кем выдан документ пациента',
                'patient_document_issued_at' => 'Дата выдачи документа пациента',
            ] as $field => $label) {
                if (!$request->filled($field)) {
                    $validator->errors()->add($field, "Поле \"{$label}\" обязательно для заполнения.");
                }
            }

            if (
                $request->input('patient_document_type') === 'passport_rf'
                && !$request->filled('patient_document_department_code')
            ) {
                $validator->errors()->add(
                    'patient_document_department_code',
                    'Для паспорта гражданина РФ необходимо указать код подразделения.'
                );
            }

            if (
                $request->input('patient_document_type') === 'other'
                && !$request->filled('patient_document_name')
            ) {
                $validator->errors()->add(
                    'patient_document_name',
                    'Для другого типа документа укажите его наименование.'
                );
            }

            if (!$request->filled('birth_at')) {
                return;
            }

            try {
                $age = Carbon::parse($request->input('birth_at'))->age;
            } catch (\Throwable) {
                return;
            }

            if ($age >= 18) {
                return;
            }

            /*
             * В психиатрическом сценарии законный представитель обязателен
             * для пациента младше 15 лет. В 15–17 лет блок допускается
             * оставить пустым, но если его начали заполнять — данные должны
             * быть согласованными и достаточными.
             */
            $representativeRequiredByAge = $age < 15;
            $representativeStarted = collect([
                'representative_surname',
                'representative_name',
                'representative_birth_at',
                'representative_phone',
                'representative_identity_number',
            ])->contains(fn (string $field): bool => $request->filled($field));

            if (!$representativeRequiredByAge && !$representativeStarted) {
                return;
            }

            foreach ([
                'representative_type' => 'Тип представителя',
                'representative_surname' => 'Фамилия представителя',
                'representative_name' => 'Имя представителя',
                'representative_birth_at' => 'Дата рождения представителя',
                'representative_phone' => 'Телефон представителя',
                'representative_identity_document_type' => 'Тип документа представителя',
                'representative_identity_number' => 'Номер документа представителя',
                'representative_identity_issued_by' => 'Кем выдан документ представителя',
                'representative_identity_issued_at' => 'Дата выдачи документа представителя',
            ] as $field => $label) {
                if (!$request->filled($field)) {
                    $message = $representativeRequiredByAge
                        ? "Поле \"{$label}\" обязательно для пациента младше 15 лет."
                        : "Если представитель указан, заполните поле \"{$label}\".";

                    $validator->errors()->add($field, $message);
                }
            }

            if (
                $request->input('representative_identity_document_type') === 'passport_rf'
                && !$request->filled('representative_identity_department_code')
            ) {
                $validator->errors()->add(
                    'representative_identity_department_code',
                    'Для паспорта представителя необходимо указать код подразделения.'
                );
            }

            if (
                $request->input('representative_identity_document_type') === 'other'
                && !$request->filled('representative_identity_document_name')
            ) {
                $validator->errors()->add(
                    'representative_identity_document_name',
                    'Для другого типа документа представителя укажите его наименование.'
                );
            }

            $needsAuthorityDocument = in_array(
                $request->input('representative_type'),
                ['guardian', 'trustee', 'authorized', 'other'],
                true
            );

            if (!$needsAuthorityDocument) {
                return;
            }

            foreach ([
                'representative_authority_document_type' => 'Тип документа о полномочиях',
                'representative_authority_number' => 'Номер документа о полномочиях',
                'representative_authority_issued_by' => 'Кем выдан документ о полномочиях',
                'representative_authority_issued_at' => 'Дата выдачи документа о полномочиях',
            ] as $field => $label) {
                if (!$request->filled($field)) {
                    $validator->errors()->add($field, "Поле \"{$label}\" обязательно для выбранного типа представителя.");
                }
            }

            if (
                $request->input('representative_authority_document_type') === 'other'
                && !$request->filled('representative_authority_document_name')
            ) {
                $validator->errors()->add(
                    'representative_authority_document_name',
                    'Укажите наименование документа, подтверждающего полномочия.'
                );
            }
        });

        $validatedData = $validator->validate();

        $clinicId = $this->currentReceptionClinicId($request);

        if (!$clinicId) {
            throw ValidationException::withMessages([
                'doctor_id' =>
                    'Не удалось определить текущую клинику пользователя.',
            ]);
        }

        $this->assertAppointmentFormatEnabled(
            $clinicId,
            $validatedData['appointment_format']
        );

        $selectedDoctor = Doctor::findOrFail(
            $validatedData['doctor_id']
        );

        $this->assertDoctorAvailableForReception(
            $selectedDoctor,
            $clinicId,
            $validatedData['appointment_format']
        );

        $doctorId = $selectedDoctor->id;

        $this->validateAppointmentWithinClinicSchedule(
            $selectedDoctor,
            Carbon::parse($validatedData['appointment_datetime']),
            $validatedData['appointment_format']
        );

        DB::transaction(function () use (
            $validatedData,
            $doctorId,
            $clinicId
        ): void {
            if (!empty($validatedData['patient_id'])) {
                $patient = Patient::query()
                    ->lockForUpdate()
                    ->findOrFail($validatedData['patient_id']);
            } else {
                /*
                 * Паспорт больше не является ключом пациента.
                 * Для нового ввода сначала пытаемся найти человека по СНИЛС.
                 */
                $patient = Patient::query()
                    ->where('snils', $validatedData['snils'])
                    ->lockForUpdate()
                    ->first() ?? new Patient();
            }

            // Проверяем повторно при сохранении: пациент мог быть госпитализирован
            // после открытия формы или найден по СНИЛС вместо выбранного ID.
            if ($patient->exists && PatientCareScope::isCurrentlyInpatient((int) $patient->id)) {
                throw ValidationException::withMessages([
                    'patient_id' => 'Пациент сейчас находится в стационаре. Новая амбулаторная запись доступна после завершения госпитализации.',
                ]);
            }

            $patient->fill([
                'surname' => $validatedData['surname'],
                'name' => $validatedData['name'],
                'patronym' => $validatedData['patronym'] ?? null,
                'birth_at' => $validatedData['birth_at'],
                'gender' => $validatedData['gender'],
                'address_registration' => $validatedData['address_registration'],
                'address_residence' => $validatedData['address_residence'],
                'birth_place' => $validatedData['birth_place'],
                'snils' => $validatedData['snils'],
                'oms' => $validatedData['oms'],
                'doctor_id' => $doctorId,
                'serial' => $validatedData['patient_document_series'],
                'number' => $validatedData['patient_document_number'],
            ]);
            $patient->save();

            if (empty($validatedData['patient_id'])) {
                $this->upsertPrimaryPatientIdentityDocument(
                    $patient->id,
                    $validatedData
                );

                $patientAge = Carbon::parse($validatedData['birth_at'])->age;
                $hasRepresentative = !empty($validatedData['representative_surname'])
                    && !empty($validatedData['representative_name']);

                if ($patientAge < 15 || ($patientAge < 18 && $hasRepresentative)) {
                    $representativeId = $this->upsertPrimaryPatientRepresentative(
                        $patient->id,
                        $validatedData
                    );

                    $this->upsertPrimaryRepresentativeIdentityDocument(
                        $representativeId,
                        $validatedData
                    );

                    if (in_array(
                        $validatedData['representative_type'],
                        ['guardian', 'trustee', 'authorized', 'other'],
                        true
                    )) {
                        $this->upsertPrimaryRepresentativeAuthorityDocument(
                            $representativeId,
                            $validatedData
                        );
                    }
                }
            }

            $record = new RegistryRecord();
            $record->patient_id = $patient->id;
            $record->doctor_id = $doctorId;
            $record->for_datetime = Carbon::parse(
                $validatedData['appointment_datetime']
            );
            $record->appointment_format =
                $validatedData['appointment_format'];
            $record->appointment = false;
            $record->clinic_id = $clinicId;
            $record->save();
        });

        return redirect()
            ->route('doctors.reception.index')
            ->with('success', 'Пациент успешно записан в регистратуру.');
    }

    private function upsertPrimaryPatientIdentityDocument(
        int $patientId,
        array $data
    ): void {
        DB::table('patient_identity_documents')
            ->where('patient_id', $patientId)
            ->where('is_primary', true)
            ->where('is_active', true)
            ->update([
                'is_primary' => false,
                'updated_at' => now(),
            ]);

        $documentName = $this->identityDocumentName(
            $data['patient_document_type'],
            $data['patient_document_name'] ?? null
        );

        $existingDocument = DB::table('patient_identity_documents')
            ->where('patient_id', $patientId)
            ->where('document_type', $data['patient_document_type'])
            ->where('number', $data['patient_document_number'])
            ->first();

        $values = [
            'document_name' => $documentName,
            'series' => $data['patient_document_series'] ?? null,
            'number' => $data['patient_document_number'],
            'issued_by' => $data['patient_document_issued_by'],
            'issued_at' => $data['patient_document_issued_at'],
            'department_code' => $data['patient_document_department_code'] ?? null,
            'is_primary' => true,
            'is_active' => true,
            'updated_at' => now(),
        ];

        if ($existingDocument) {
            DB::table('patient_identity_documents')
                ->where('id', $existingDocument->id)
                ->update($values);

            return;
        }

        DB::table('patient_identity_documents')->insert(array_merge(
            $values,
            [
                'patient_id' => $patientId,
                'document_type' => $data['patient_document_type'],
                'created_at' => now(),
            ]
        ));
    }

    private function upsertPrimaryPatientRepresentative(
        int $patientId,
        array $data
    ): int {
        DB::table('patient_representatives')
            ->where('patient_id', $patientId)
            ->where('is_primary', true)
            ->where('is_active', true)
            ->update([
                'is_primary' => false,
                'updated_at' => now(),
            ]);

        $representativeQuery = DB::table('patient_representatives')
            ->where('patient_id', $patientId)
            ->where('representative_type', $data['representative_type'])
            ->where('surname', $data['representative_surname'])
            ->where('name', $data['representative_name']);

        if (!empty($data['representative_birth_at'])) {
            $representativeQuery->whereDate(
                'birth_at',
                $data['representative_birth_at']
            );
        }

        $representative = $representativeQuery->first();

        $values = [
            'representative_type' => $data['representative_type'],
            'surname' => $data['representative_surname'],
            'name' => $data['representative_name'],
            'patronym' => $data['representative_patronym'] ?? null,
            'birth_at' => $data['representative_birth_at'] ?? null,
            'snils' => $data['representative_snils'] ?? null,
            'phone' => $data['representative_phone'] ?? null,
            'email' => $data['representative_email'] ?? null,
            'is_primary' => true,
            'is_active' => true,
            'updated_at' => now(),
        ];

        if ($representative) {
            DB::table('patient_representatives')
                ->where('id', $representative->id)
                ->update($values);

            return (int) $representative->id;
        }

        return (int) DB::table('patient_representatives')->insertGetId(
            array_merge(
                $values,
                [
                    'patient_id' => $patientId,
                    'created_at' => now(),
                ]
            )
        );
    }

    private function upsertPrimaryRepresentativeIdentityDocument(
        int $representativeId,
        array $data
    ): void {
        $this->upsertPrimaryRepresentativeDocument(
            $representativeId,
            'identity',
            [
                'document_type' => $data['representative_identity_document_type'],
                'document_name' => $this->identityDocumentName(
                    $data['representative_identity_document_type'],
                    $data['representative_identity_document_name'] ?? null
                ),
                'series' => $data['representative_identity_series'] ?? null,
                'number' => $data['representative_identity_number'],
                'issued_by' => $data['representative_identity_issued_by'],
                'issued_at' => $data['representative_identity_issued_at'],
                'department_code' => $data['representative_identity_department_code'] ?? null,
            ]
        );
    }

    private function upsertPrimaryRepresentativeAuthorityDocument(
        int $representativeId,
        array $data
    ): void {
        $documentName = match ($data['representative_authority_document_type']) {
            'guardianship_order' => 'Документ об установлении опеки/попечительства',
            'power_of_attorney' => 'Доверенность',
            default => $data['representative_authority_document_name'] ?? 'Другой документ',
        };

        $this->upsertPrimaryRepresentativeDocument(
            $representativeId,
            'authority',
            [
                'document_type' => $data['representative_authority_document_type'],
                'document_name' => $documentName,
                'series' => $data['representative_authority_series'] ?? null,
                'number' => $data['representative_authority_number'],
                'issued_by' => $data['representative_authority_issued_by'],
                'issued_at' => $data['representative_authority_issued_at'],
                'department_code' => null,
            ]
        );
    }

    private function upsertPrimaryRepresentativeDocument(
        int $representativeId,
        string $purpose,
        array $document
    ): void {
        DB::table('patient_representative_documents')
            ->where('representative_id', $representativeId)
            ->where('document_purpose', $purpose)
            ->where('is_primary', true)
            ->where('is_active', true)
            ->update([
                'is_primary' => false,
                'updated_at' => now(),
            ]);

        $existingDocument = DB::table('patient_representative_documents')
            ->where('representative_id', $representativeId)
            ->where('document_purpose', $purpose)
            ->where('document_type', $document['document_type'])
            ->where('number', $document['number'])
            ->first();

        $values = array_merge(
            $document,
            [
                'is_primary' => true,
                'is_active' => true,
                'updated_at' => now(),
            ]
        );

        if ($existingDocument) {
            DB::table('patient_representative_documents')
                ->where('id', $existingDocument->id)
                ->update($values);

            return;
        }

        DB::table('patient_representative_documents')->insert(array_merge(
            $values,
            [
                'representative_id' => $representativeId,
                'document_purpose' => $purpose,
                'created_at' => now(),
            ]
        ));
    }

    private function identityDocumentName(
        string $type,
        ?string $customName = null
    ): string {
        return match ($type) {
            'passport_rf' => 'Паспорт гражданина РФ',
            'birth_certificate' => 'Свидетельство о рождении',
            'foreign_passport' => 'Иностранный паспорт',
            'residence_permit' => 'Вид на жительство',
            'temporary_identity' => 'Временное удостоверение личности',
            'foreign_identity' => 'Иностранный документ, удостоверяющий личность',
            default => $customName ?: 'Другой документ',
        };
    }

    private function activeClinicQuarantine(
        ?int $clinicId
    ): ?ClinicScheduleException {
        if (
            !$clinicId
            || $clinicId <= 0
            || !Schema::hasTable('clinic_schedule_exceptions')
        ) {
            return null;
        }

        $today = Carbon::today()->toDateString();

        return ClinicScheduleException::query()
            ->where('clinic_id', $clinicId)
            ->where('type', 'quarantine')
            ->whereDate('date_from', '<=', $today)
            ->whereDate('date_to', '>=', $today)
            ->orderByDesc('date_from')
            ->orderByDesc('id')
            ->first();
    }

    private function currentReceptionClinicId(
        Request $request
    ): ?int {
        $user = $request->user();

        $doctor = $user && method_exists($user, 'doctor')
            ? $user->doctor()->first()
            : null;

        $nurse = $user && method_exists($user, 'nurse')
            ? $user->nurse()->first()
            : null;

        $clinicId = $doctor?->clinic_id
            ?? $nurse?->clinic_id;

        return $clinicId
            ? (int) $clinicId
            : null;
    }

    private function clinicAppointmentFormats(?int $clinicId): array
    {
        if (!$clinicId || $clinicId <= 0) {
            return [
                self::APPOINTMENT_FORMAT_IN_PERSON,
                self::APPOINTMENT_FORMAT_TELEMEDICINE,
            ];
        }

        $clinic = Clinic::query()->find($clinicId);

        if (!$clinic) {
            return [];
        }

        $services = $clinic->services;

        // Старые клиники без заполненного поля используют тот же fallback,
        // который уже применяется на странице настроек клиники.
        if ($services === null || $services === '') {
            return [
                self::APPOINTMENT_FORMAT_IN_PERSON,
                self::APPOINTMENT_FORMAT_TELEMEDICINE,
            ];
        }

        if (is_string($services)) {
            $decoded = json_decode($services, true);
            $services = is_array($decoded) ? $decoded : [];
        }

        if (!is_array($services)) {
            return [];
        }

        return collect($services)
            ->map(static fn ($format): string => (string) $format)
            ->filter(
                static fn (string $format): bool => in_array(
                    $format,
                    [
                        self::APPOINTMENT_FORMAT_IN_PERSON,
                        self::APPOINTMENT_FORMAT_TELEMEDICINE,
                    ],
                    true
                )
            )
            ->unique()
            ->values()
            ->all();
    }

    private function defaultAppointmentFormat(array $formats): string
    {
        if (in_array(self::APPOINTMENT_FORMAT_IN_PERSON, $formats, true)) {
            return self::APPOINTMENT_FORMAT_IN_PERSON;
        }

        return $formats[0] ?? self::APPOINTMENT_FORMAT_IN_PERSON;
    }

    private function assertAppointmentFormatEnabled(
        int $clinicId,
        string $format
    ): void {
        if (!in_array($format, $this->clinicAppointmentFormats($clinicId), true)) {
            throw ValidationException::withMessages([
                'appointment_format' =>
                    'Выбранный формат приёма отключён в настройках клиники.',
            ]);
        }
    }

    private function receptionDoctorsForClinic(
        ?int $clinicId,
        ?array $appointmentFormats = null
    ) {
        if (!$clinicId || $clinicId <= 0) {
            return collect();
        }

        $appointmentFormats ??= $this->clinicAppointmentFormats($clinicId);

        // Для телемедицины физический кабинет не требуется, поэтому
        // в список должны попасть все врачи текущей клиники.
        if (in_array(
            self::APPOINTMENT_FORMAT_TELEMEDICINE,
            $appointmentFormats,
            true
        )) {
            return Doctor::query()
                ->where('clinic_id', $clinicId)
                ->orderBy('surname')
                ->orderBy('name')
                ->orderBy('patronym')
                ->get();
        }

        $cabinetDoctorIds = ClinicCabinet::query()
            ->where('clinic_id', $clinicId)
            ->where('enabled', true)
            ->where(
                'cabinet_type',
                ClinicCabinet::TYPE_DOCTOR
            )
            ->get([
                'doctor_id',
                'replace_doctor_id',
            ])
            ->flatMap(
                static fn (
                    ClinicCabinet $cabinet
                ): array => [
                    $cabinet->doctor_id,
                    $cabinet->replace_doctor_id,
                ]
            )
            ->filter(
                static fn ($doctorId): bool =>
                    (int) $doctorId > 0
            )
            ->map(
                static fn ($doctorId): int =>
                    (int) $doctorId
            )
            ->unique()
            ->values();

        if ($cabinetDoctorIds->isEmpty()) {
            return collect();
        }

        return Doctor::query()
            ->where('clinic_id', $clinicId)
            ->whereIn('id', $cabinetDoctorIds)
            ->orderBy('surname')
            ->orderBy('name')
            ->orderBy('patronym')
            ->get();
    }

    private function doctorAppointmentFormats(
        $doctors,
        array $appointmentFormats,
        array $doctorCabinetIds
    ): array {
        return $doctors
            ->mapWithKeys(
                static function (Doctor $doctor) use (
                    $appointmentFormats,
                    $doctorCabinetIds
                ): array {
                    $formats = [];
                    $doctorId = (string) $doctor->id;

                    if (in_array(
                        self::APPOINTMENT_FORMAT_TELEMEDICINE,
                        $appointmentFormats,
                        true
                    )) {
                        $formats[] = self::APPOINTMENT_FORMAT_TELEMEDICINE;
                    }

                    if (
                        in_array(
                            self::APPOINTMENT_FORMAT_IN_PERSON,
                            $appointmentFormats,
                            true
                        )
                        && !empty($doctorCabinetIds[$doctorId] ?? [])
                    ) {
                        $formats[] = self::APPOINTMENT_FORMAT_IN_PERSON;
                    }

                    return [$doctorId => $formats];
                }
            )
            ->all();
    }

    private function assertDoctorAvailableForReception(
        Doctor $doctor,
        ?int $clinicId,
        string $appointmentFormat = self::APPOINTMENT_FORMAT_IN_PERSON
    ): void {
        if (
            !$clinicId
            || (int) $doctor->clinic_id !== (int) $clinicId
        ) {
            throw ValidationException::withMessages([
                'doctor_id' =>
                    'Выбранный врач не работает в текущей клинике.',
            ]);
        }

        if ($appointmentFormat === self::APPOINTMENT_FORMAT_TELEMEDICINE) {
            return;
        }

        $hasCabinet = ClinicCabinet::query()
            ->where('clinic_id', $clinicId)
            ->where('enabled', true)
            ->where(
                'cabinet_type',
                ClinicCabinet::TYPE_DOCTOR
            )
            ->where(
                function ($query) use ($doctor): void {
                    $query
                        ->where(
                            'doctor_id',
                            $doctor->id
                        )
                        ->orWhere(
                            'replace_doctor_id',
                            $doctor->id
                        );
                }
            )
            ->exists();

        if (!$hasCabinet) {
            throw ValidationException::withMessages([
                'doctor_id' =>
                    'Для очного приёма у выбранного врача должен быть активный кабинет в текущей клинике.',
            ]);
        }
    }

    private function clinicWorkSchedulesForDoctors($doctors): array
    {
        $clinicIds = $doctors
            ->pluck('clinic_id')
            ->filter(static fn ($clinicId): bool => (int) $clinicId > 0)
            ->map(static fn ($clinicId): int => (int) $clinicId)
            ->unique()
            ->values();

        $doctorIds = $doctors
            ->pluck('id')
            ->map(static fn ($doctorId): int => (int) $doctorId)
            ->unique()
            ->values();

        if ($clinicIds->isEmpty() || $doctorIds->isEmpty()) {
            return [];
        }

        $legacySchedules = ClinicWorkShedule::query()
            ->whereIn('clinic_id', $clinicIds)
            ->orderBy('day_number')
            ->get()
            ->groupBy('clinic_id')
            ->map(
                static fn ($rows) =>
                    $rows->keyBy('day_number')
            );

        $buildingSchedules = Schema::hasTable(
            'clinic_building_work_schedules'
        )
            ? ClinicBuildingWorkSchedule::query()
                ->whereIn('clinic_id', $clinicIds)
                ->orderBy('day_number')
                ->get()
                ->groupBy('building_id')
                ->map(
                    static fn ($rows) =>
                        $rows->keyBy('day_number')
                )
            : collect();

        $cabinetRows = ClinicCabinet::query()
            ->whereIn('clinic_id', $clinicIds)
            ->where('enabled', true)
            ->where(
                static function ($query) use ($doctorIds): void {
                    $query
                        ->whereIn('doctor_id', $doctorIds)
                        ->orWhereIn('replace_doctor_id', $doctorIds);
                }
            )
            ->orderBy('id')
            ->get([
                'id',
                'clinic_id',
                'building_id',
                'doctor_id',
                'replace_doctor_id',
            ]);

        $doctorBuildingIds = [];

        foreach ($cabinetRows as $cabinet) {
            $mainDoctorId = (int) $cabinet->doctor_id;

            if (
                $mainDoctorId > 0
                && $doctorIds->contains($mainDoctorId)
                && !isset($doctorBuildingIds[$mainDoctorId])
            ) {
                $doctorBuildingIds[$mainDoctorId] = $cabinet->building_id
                    ? (int) $cabinet->building_id
                    : null;
            }
        }

        foreach ($cabinetRows as $cabinet) {
            $replacementDoctorId = (int) $cabinet->replace_doctor_id;

            if (
                $replacementDoctorId > 0
                && $doctorIds->contains($replacementDoctorId)
                && !isset($doctorBuildingIds[$replacementDoctorId])
            ) {
                $doctorBuildingIds[$replacementDoctorId] = $cabinet->building_id
                    ? (int) $cabinet->building_id
                    : null;
            }
        }

        return $doctors
            ->mapWithKeys(
                function (Doctor $doctor) use (
                    $legacySchedules,
                    $buildingSchedules,
                    $doctorBuildingIds
                ): array {
                    $doctorId = (int) $doctor->id;
                    $clinicId = (int) ($doctor->clinic_id ?? 0);
                    $buildingId = $doctorBuildingIds[$doctorId] ?? null;

                    $rows = $buildingId
                        ? $buildingSchedules->get($buildingId, collect())
                        : collect();

                    if ($rows->isEmpty()) {
                        $rows = $legacySchedules->get($clinicId, collect());
                    }

                    if ($rows->isEmpty()) {
                        return [];
                    }

                    $schedule = $rows
                        ->mapWithKeys(
                            static function ($schedule): array {
                                return [
                                    (string) $schedule->day_number => [
                                        'enabled' => (bool) $schedule->enabled,
                                        'start_time' => $schedule->start_time
                                            ? Carbon::parse($schedule->start_time)->format('H:i')
                                            : null,
                                        'end_time' => $schedule->end_time
                                            ? Carbon::parse($schedule->end_time)->format('H:i')
                                            : null,
                                    ],
                                ];
                            }
                        )
                        ->all();

                    return [(string) $doctorId => $schedule];
                }
            )
            ->all();
    }


    private function clinicScheduleExceptionDataForDoctors(
        $doctors
    ): array {
        $clinicIds = $doctors
            ->pluck('clinic_id')
            ->filter(
                static fn ($clinicId): bool =>
                    (int) $clinicId > 0
            )
            ->map(
                static fn ($clinicId): int =>
                    (int) $clinicId
            )
            ->unique()
            ->values();

        $doctorIds = $doctors
            ->pluck('id')
            ->map(
                static fn ($doctorId): int =>
                    (int) $doctorId
            )
            ->unique()
            ->values();

        if (
            $clinicIds->isEmpty()
            || $doctorIds->isEmpty()
        ) {
            return [
                [],
                [],
                [],
            ];
        }

        $exceptions = Schema::hasTable(
            'clinic_schedule_exceptions'
        )
            ? ClinicScheduleException::query()
                ->whereIn(
                    'clinic_id',
                    $clinicIds
                )
                ->orderBy('date_from')
                ->orderBy('id')
                ->get()
                ->groupBy('clinic_id')
                ->map(
                    static function (
                        $rules
                    ): array {
                        return $rules
                            ->map(
                                static function (
                                    ClinicScheduleException $rule
                                ): array {
                                    $isQuarantine =
                                        (string) $rule->type
                                        === 'quarantine';

                                    return [
                                    'id' =>
                                        (int) $rule->id,
                                    'title' =>
                                        $rule->title,
                                    'date_from' =>
                                        Carbon::parse(
                                            $rule->date_from
                                        )
                                            ->format(
                                                'Y-m-d'
                                            ),
                                    'date_to' =>
                                        Carbon::parse(
                                            $rule->date_to
                                        )
                                            ->format(
                                                'Y-m-d'
                                            ),
                                    'all_day' =>
                                        $isQuarantine
                                            ? true
                                            : (bool) $rule->all_day,
                                    'time_from' =>
                                        $isQuarantine
                                            ? null
                                            : (
                                                $rule->time_from
                                                    ? substr(
                                                        (string) $rule->time_from,
                                                        0,
                                                        5
                                                    )
                                                    : null
                                            ),
                                    'time_to' =>
                                        $isQuarantine
                                            ? null
                                            : (
                                                $rule->time_to
                                                    ? substr(
                                                        (string) $rule->time_to,
                                                        0,
                                                        5
                                                    )
                                                    : null
                                            ),
                                    'mode' =>
                                        $isQuarantine
                                            ? 'closed'
                                            : match ($rule->mode) {
                                                'shortened' => 'override',
                                                'limited' => 'blocked',
                                                default => (string) $rule->mode,
                                            },
                                    'scope' =>
                                        $isQuarantine
                                            ? 'clinic'
                                            : $rule->scope,
                                    'target_ids' =>
                                        (
                                            $isQuarantine
                                                ? collect()
                                                : collect(
                                                    $rule->target_ids
                                                    ?? []
                                                )
                                        )
                                            ->map(
                                                static fn ($id): int =>
                                                    (int) $id
                                            )
                                            ->filter(
                                                static fn (int $id): bool =>
                                                    $id > 0
                                            )
                                            ->values()
                                            ->all(),
                                ];
                            }
                        )
                        ->values()
                        ->all();
                }
                )
                ->all()
            : [];

        $cabinetRows = ClinicCabinet::query()
            ->whereIn(
                'clinic_id',
                $clinicIds
            )
            ->where('enabled', true)
            ->where(
                'cabinet_type',
                ClinicCabinet::TYPE_DOCTOR
            )
            ->where(
                function ($query) use (
                    $doctorIds
                ): void {
                    $query
                        ->whereIn(
                            'doctor_id',
                            $doctorIds
                        )
                        ->orWhereIn(
                            'replace_doctor_id',
                            $doctorIds
                        );
                }
            )
            ->orderBy('cabinet')
            ->get([
                'id',
                'building_id',
                'doctor_id',
                'replace_doctor_id',
                'cabinet',
                'name',
            ]);

        $doctorCabinetIds = [];
        $doctorCabinets = [];

        foreach (
            $cabinetRows
            as $cabinet
        ) {
            $cabinetLabel = $cabinet->cabinet
                ? 'Кабинет №' . $cabinet->cabinet
                : 'Кабинет #' . $cabinet->id;

            if ($cabinet->name) {
                $cabinetLabel .= ' — ' . $cabinet->name;
            }

            foreach ([
                'main' => $cabinet->doctor_id,
                'replacement' => $cabinet->replace_doctor_id,
            ] as $role => $doctorId) {
                $doctorId = (int) $doctorId;

                if (
                    $doctorId <= 0
                    || !$doctorIds->contains(
                        $doctorId
                    )
                ) {
                    continue;
                }

                $key = (string) $doctorId;

                $doctorCabinetIds[$key][] =
                    (int) $cabinet->id;

                $doctorCabinets[$key][] = [
                    'id' => (int) $cabinet->id,
                    'building_id' => $cabinet->building_id
                        ? (int) $cabinet->building_id
                        : null,
                    'label' => $cabinetLabel,
                    'role' => $role,
                ];
            }
        }

        foreach (
            $doctorCabinetIds
            as $doctorId => $ids
        ) {
            $doctorCabinetIds[$doctorId] =
                array_values(
                    array_unique(
                        array_map(
                            'intval',
                            $ids
                        )
                    )
                );

            $doctorCabinets[$doctorId] =
                collect(
                    $doctorCabinets[$doctorId]
                    ?? []
                )
                    ->sortBy(
                        static fn (array $cabinet): int =>
                            $cabinet['role'] === 'main'
                                ? 0
                                : 1
                    )
                    ->unique('id')
                    ->values()
                    ->all();
        }

        return [
            $exceptions,
            $doctorCabinetIds,
            $doctorCabinets,
        ];
    }


    private function validateAppointmentWithinClinicSchedule(
        Doctor $doctor,
        Carbon $appointment,
        string $appointmentFormat = self::APPOINTMENT_FORMAT_IN_PERSON
    ): void {
        $clinicId = (int) ($doctor->clinic_id ?? 0);

        if ($clinicId <= 0) {
            return;
        }

        if (
            $this->validateAppointmentAgainstClinicExceptions(
                $doctor,
                $appointment,
                $appointmentFormat
            )
        ) {
            return;
        }

        $legacyHasSchedule = ClinicWorkShedule::query()
            ->where('clinic_id', $clinicId)
            ->exists();

        $schedule = null;
        $scheduleSubject = 'клиника';

        if ($appointmentFormat === self::APPOINTMENT_FORMAT_TELEMEDICINE) {
            if (!$legacyHasSchedule) {
                return;
            }

            $schedule = ClinicWorkShedule::query()
                ->where('clinic_id', $clinicId)
                ->where('day_number', $appointment->isoWeekday())
                ->first();
        } else {
            $cabinet = ClinicCabinet::query()
                ->where('clinic_id', $clinicId)
                ->where('enabled', true)
                ->where(
                    static function ($query) use ($doctor): void {
                        $query
                            ->where('doctor_id', $doctor->id)
                            ->orWhere('replace_doctor_id', $doctor->id);
                    }
                )
                ->orderByRaw(
                    'CASE WHEN doctor_id = ? THEN 0 ELSE 1 END',
                    [$doctor->id]
                )
                ->first([
                    'id',
                    'building_id',
                ]);

            $buildingId = $cabinet?->building_id
                ? (int) $cabinet->building_id
                : null;

            $buildingHasSchedule = $buildingId
                && Schema::hasTable('clinic_building_work_schedules')
                && ClinicBuildingWorkSchedule::query()
                    ->where('clinic_id', $clinicId)
                    ->where('building_id', $buildingId)
                    ->exists();

            if (!$buildingHasSchedule && !$legacyHasSchedule) {
                return;
            }

            if ($buildingHasSchedule) {
                $scheduleSubject = 'корпус';
                $schedule = ClinicBuildingWorkSchedule::query()
                    ->where('clinic_id', $clinicId)
                    ->where('building_id', $buildingId)
                    ->where('day_number', $appointment->isoWeekday())
                    ->first();
            }

            if (!$schedule && $legacyHasSchedule) {
                $scheduleSubject = 'клиника';
                $schedule = ClinicWorkShedule::query()
                    ->where('clinic_id', $clinicId)
                    ->where('day_number', $appointment->isoWeekday())
                    ->first();
            }
        }

        if (!$schedule || !$schedule->enabled) {
            throw ValidationException::withMessages([
                'appointment_datetime' =>
                    sprintf('В выбранный день %s не работает.', $scheduleSubject),
            ]);
        }

        if (!$schedule->start_time || !$schedule->end_time) {
            throw ValidationException::withMessages([
                'appointment_datetime' =>
                    sprintf('Для выбранного дня не задано рабочее время: %s.', $scheduleSubject),
            ]);
        }

        $startTime = Carbon::parse($schedule->start_time)->format('H:i:s');
        $endTime = Carbon::parse($schedule->end_time)->format('H:i:s');

        $workStartsAt = $appointment->copy()->setTimeFromTimeString($startTime);
        $workEndsAt = $appointment->copy()->setTimeFromTimeString($endTime);

        if ($workEndsAt->lt($workStartsAt)) {
            throw ValidationException::withMessages([
                'appointment_datetime' =>
                    sprintf('График работы (%s) на выбранный день заполнен некорректно.', $scheduleSubject),
            ]);
        }

        if ($appointment->lt($workStartsAt) || $appointment->gt($workEndsAt)) {
            throw ValidationException::withMessages([
                'appointment_datetime' => sprintf(
                    'В этот день %s работает с %s до %s.',
                    $scheduleSubject,
                    $workStartsAt->format('H:i'),
                    $workEndsAt->format('H:i')
                ),
            ]);
        }
    }


    /**
     * Проверяет особые периоды клиники.
     *
     * Возвращает true, если к записи применён отдельный рабочий
     * интервал (режим override/extra) и обычный недельный график
     * уже не нужно проверять.
     */
    private function validateAppointmentAgainstClinicExceptions(
        Doctor $doctor,
        Carbon $appointment,
        string $appointmentFormat = self::APPOINTMENT_FORMAT_IN_PERSON
    ): bool {
        $clinicId = (int) ($doctor->clinic_id ?? 0);

        if (
            $clinicId <= 0
            || !Schema::hasTable('clinic_schedule_exceptions')
        ) {
            return false;
        }

        $rules = ClinicScheduleException::query()
            ->where('clinic_id', $clinicId)
            ->whereDate(
                'date_from',
                '<=',
                $appointment->toDateString()
            )
            ->whereDate(
                'date_to',
                '>=',
                $appointment->toDateString()
            )
            ->orderBy('id')
            ->get();

        if ($rules->isEmpty()) {
            return false;
        }

        $cabinetRows = $appointmentFormat === self::APPOINTMENT_FORMAT_TELEMEDICINE
            ? collect()
            : ClinicCabinet::query()
                ->where('clinic_id', $clinicId)
                ->where('enabled', true)
                ->where(
                    'cabinet_type',
                    ClinicCabinet::TYPE_DOCTOR
                )
                ->where(
                    function ($query) use ($doctor): void {
                        $query
                            ->where('doctor_id', $doctor->id)
                            ->orWhere('replace_doctor_id', $doctor->id);
                    }
                )
                ->get([
                    'id',
                    'building_id',
                ]);

        $cabinetIds = $cabinetRows
            ->pluck('id')
            ->map(static fn ($id): int => (int) $id)
            ->all();

        $buildingIds = $cabinetRows
            ->pluck('building_id')
            ->filter()
            ->map(static fn ($id): int => (int) $id)
            ->unique()
            ->values()
            ->all();

        $applicable = $rules
            ->filter(
                fn (
                    ClinicScheduleException $rule
                ): bool =>
                    $this->clinicScheduleExceptionAppliesToDoctor(
                        $rule,
                        $doctor,
                        $cabinetIds,
                        $buildingIds
                    )
            )
            ->values();

        if ($applicable->isEmpty()) {
            return false;
        }

        $mode = static function (
            ClinicScheduleException $rule
        ): string {
            return match ($rule->mode) {
                'shortened' => 'override',
                'limited' => 'blocked',
                default => (string) $rule->mode,
            };
        };

        /*
         * Закрытие и приостановка имеют абсолютный приоритет.
         * Если выбранное время попало в такой интервал, ни особый,
         * ни дополнительный график его не открывают.
         */
        $blockingRule = $applicable
            ->filter(
                static fn (
                    ClinicScheduleException $rule
                ): bool =>
                    in_array(
                        $mode($rule),
                        [
                            'closed',
                            'blocked',
                        ],
                        true
                    )
            )
            ->first(
                fn (
                    ClinicScheduleException $rule
                ): bool =>
                    $this->clinicScheduleExceptionContainsTime(
                        $rule,
                        $appointment
                    )
            );

        if ($blockingRule) {
            $blockingMode = $mode(
                $blockingRule
            );

            $timeText = $blockingRule->all_day
                ? 'весь день'
                : sprintf(
                    'с %s до %s',
                    substr(
                        (string) $blockingRule->time_from,
                        0,
                        5
                    ),
                    substr(
                        (string) $blockingRule->time_to,
                        0,
                        5
                    )
                );

            $modeText = $blockingMode
                === 'blocked'
                    ? 'приём приостановлен'
                    : 'приём закрыт';

            throw ValidationException::withMessages([
                'appointment_datetime' => sprintf(
                    'Действует правило «%s»: %s %s. Выберите другое время.',
                    $blockingRule->title,
                    $modeText,
                    $timeText
                ),
            ]);
        }

        $scopePriority = static function (
            ClinicScheduleException $rule
        ): int {
            return match ($rule->scope) {
                'doctors' => 4,
                'offices' => 3,
                'buildings' => 2,
                default => 1,
            };
        };

        /*
         * Особый график полностью заменяет обычную рабочую неделю
         * на выбранную дату. При нескольких правилах выигрывает
         * наиболее конкретное: врач → кабинет → клиника.
         */
        $overrideRule = $applicable
            ->filter(
                static fn (
                    ClinicScheduleException $rule
                ): bool =>
                    $mode($rule) === 'override'
            )
            ->sortByDesc(
                $scopePriority
            )
            ->first();

        /*
         * Дополнительный приём не заменяет обычный график, а открывает
         * ещё один интервал. Поэтому может разрешить запись вечером
         * после закрытия клиники или даже в обычный выходной день.
         */
        $extraRule = $applicable
            ->filter(
                static fn (
                    ClinicScheduleException $rule
                ): bool =>
                    $mode($rule) === 'extra'
            )
            ->sortByDesc(
                $scopePriority
            )
            ->first(
                fn (
                    ClinicScheduleException $rule
                ): bool =>
                    $this->clinicScheduleExceptionContainsTime(
                        $rule,
                        $appointment
                    )
            );

        if ($overrideRule) {
            if (
                !$overrideRule->time_from
                || !$overrideRule->time_to
            ) {
                throw ValidationException::withMessages([
                    'appointment_datetime' =>
                        'Для особого графика не задан рабочий интервал.',
                ]);
            }

            if (
                $this->clinicScheduleExceptionContainsTime(
                    $overrideRule,
                    $appointment
                )
                || $extraRule
            ) {
                return true;
            }

            throw ValidationException::withMessages([
                'appointment_datetime' => sprintf(
                    'Действует особый график «%s»: приём возможен с %s до %s.',
                    $overrideRule->title,
                    substr(
                        (string) $overrideRule->time_from,
                        0,
                        5
                    ),
                    substr(
                        (string) $overrideRule->time_to,
                        0,
                        5
                    )
                ),
            ]);
        }

        if ($extraRule) {
            return true;
        }

        return false;
    }

    private function clinicScheduleExceptionAppliesToDoctor(
        ClinicScheduleException $rule,
        Doctor $doctor,
        array $cabinetIds,
        array $buildingIds
    ): bool {
        if (
            (string) $rule->type === 'quarantine'
            || $rule->scope === 'clinic'
        ) {
            return true;
        }

        $targetIds = collect($rule->target_ids ?? [])
            ->map(static fn ($id): int => (int) $id)
            ->filter(static fn (int $id): bool => $id > 0)
            ->all();

        if ($rule->scope === 'doctors') {
            return in_array((int) $doctor->id, $targetIds, true);
        }

        if ($rule->scope === 'offices') {
            return count(array_intersect($cabinetIds, $targetIds)) > 0;
        }

        if ($rule->scope === 'buildings') {
            return count(array_intersect($buildingIds, $targetIds)) > 0;
        }

        return false;
    }

    private function clinicScheduleExceptionContainsTime(
        ClinicScheduleException $rule,
        Carbon $appointment
    ): bool {
        if (
            (string) $rule->type === 'quarantine'
            || $rule->all_day
        ) {
            return true;
        }

        if (
            !$rule->time_from
            || !$rule->time_to
        ) {
            return false;
        }

        $startsAt = $appointment
            ->copy()
            ->setTimeFromTimeString(
                (string) $rule->time_from
            );

        $endsAt = $appointment
            ->copy()
            ->setTimeFromTimeString(
                (string) $rule->time_to
            );

        return $appointment->gte($startsAt)
            && $appointment->lt($endsAt);
    }


    public function archives(Request $request): View|JsonResponse
    {
        $now = Carbon::now();
        $user = $request->user();
        $isDebug = app()->hasDebugModeEnabled();

        $doctor = $user && method_exists($user, 'doctor')
            ? $user->doctor()->first()
            : null;

        $nurse = $user && method_exists($user, 'nurse')
            ? $user->nurse()->first()
            : null;

        $currentClinicId = $doctor?->clinic_id
            ? (int) $doctor->clinic_id
            : ($nurse?->clinic_id ? (int) $nurse->clinic_id : null);

        $hostClinic = $currentClinicId
            ? Clinic::query()->find($currentClinicId)
            : null;

        $name = $doctor?->name ?? $nurse?->name ?? '';
        $surname = $doctor?->surname ?? $nurse?->surname ?? '';
        $patronym = $doctor?->patronym ?? $nurse?->patronym ?? '';
        $profession = $doctor?->profession
            ?? $nurse?->profession
            ?? '';

        $recordsQuery = RegistryRecord::query()
            ->withTrashed()
            ->with([
                'patient',
                'doctor',
            ])
            ->where(
                function ($query) use ($now): void {
                    $query
                        ->where('appointment', true)
                        ->orWhere('for_datetime', '<=', $now);
                }
            );

        if (!$isDebug) {
            if ($currentClinicId) {
                $recordsQuery->where(
                    'clinic_id',
                    $currentClinicId
                );
            } else {
                $recordsQuery->whereRaw('1 = 0');
            }
        }

        /*
         * Поле «Полное имя» в архиве — поисковый селектор.
         * Используем этот же GET endpoint для AJAX-поиска, чтобы
         * не добавлять отдельный route только ради подсказок.
         */
        if ((string) $request->query('_lookup') === 'patient') {
            $search = trim(
                (string) $request->query('q', '')
            );

            if (mb_strlen($search) < 2) {
                return response()->json([
                    'results' => [],
                ]);
            }

            $patientIdsQuery = (clone $recordsQuery)
                ->reorder()
                ->select('patient_id')
                ->whereNotNull('patient_id');

            $terms = preg_split(
                '/\\s+/u',
                $search,
                -1,
                PREG_SPLIT_NO_EMPTY
            ) ?: [];

            $patientsQuery = Patient::query()
                ->whereIn('id', $patientIdsQuery);

            foreach ($terms as $term) {
                $searchValue = '%' . $term . '%';

                $patientsQuery->where(
                    function ($query) use ($searchValue): void {
                        $query
                            ->where('surname', 'ilike', $searchValue)
                            ->orWhere('name', 'ilike', $searchValue)
                            ->orWhere('patronym', 'ilike', $searchValue)
                            ->orWhereRaw(
                                "CONCAT_WS(' ', surname, name, patronym) ILIKE ?",
                                [$searchValue]
                            );
                    }
                );
            }

            $patients = $patientsQuery
                ->orderBy('surname')
                ->orderBy('name')
                ->orderBy('patronym')
                ->limit(20)
                ->get([
                    'id',
                    'surname',
                    'name',
                    'patronym',
                    'birth_at',
                    'address_registration',
                ])
                ->map(
                    static function (Patient $patient): array {
                        $fullName = trim(
                            implode(
                                ' ',
                                array_filter([
                                    $patient->surname,
                                    $patient->name,
                                    $patient->patronym,
                                ])
                            )
                        );

                        return [
                            'id' => (int) $patient->id,
                            'name' => $fullName !== ''
                                ? $fullName
                                : 'Пациент #' . $patient->id,
                            'birth_at' => $patient->birth_at
                                ? Carbon::parse($patient->birth_at)->format('d.m.Y')
                                : null,
                            'address_registration' => trim(
                                (string) ($patient->address_registration ?? '')
                            ),
                        ];
                    }
                )
                ->values();

            return response()->json([
                'results' => $patients,
            ]);
        }

        $doctorsQuery = Doctor::query()
            ->orderBy('surname')
            ->orderBy('name')
            ->orderBy('patronym');

        if (!$isDebug) {
            if ($currentClinicId) {
                $doctorsQuery->where(
                    'clinic_id',
                    $currentClinicId
                );
            } else {
                $doctorsQuery->whereRaw('1 = 0');
            }
        }

        $doctors = $doctorsQuery->get([
            'id',
            'name',
            'surname',
            'patronym',
            'status',
            'profession',
        ]);

        $selectedArchivePatient = null;

        if ($request->filled('patient_id')) {
            $patientId = $request->integer('patient_id');

            $patientExistsInArchive = (clone $recordsQuery)
                ->where('patient_id', $patientId)
                ->exists();

            if ($patientExistsInArchive) {
                $selectedArchivePatient = Patient::query()
                    ->find($patientId);
            }

            $recordsQuery->where(
                'patient_id',
                $patientId
            );
        }

        if ($request->filled('doctor_id')) {
            $recordsQuery->where(
                'doctor_id',
                $request->integer('doctor_id')
            );
        }

        if (
            $request->filled('birth_at')
            || $request->filled('address_registration')
        ) {
            $recordsQuery->whereHas(
                'patient',
                function ($query) use ($request): void {
                    if ($request->filled('birth_at')) {
                        $query->whereDate(
                            'birth_at',
                            (string) $request->input('birth_at')
                        );
                    }

                    if ($request->filled('address_registration')) {
                        $address = '%' . trim(
                            (string) $request->input('address_registration')
                        ) . '%';

                        $query->where(
                            'address_registration',
                            'ilike',
                            $address
                        );
                    }
                }
            );
        }

        $records = $recordsQuery
            ->orderByDesc('for_datetime')
            ->orderByDesc('id')
            ->paginate(30)
            ->withQueryString();

        return view(
            'doctors.reception.archives',
            compact(
                'records',
                'hostClinic',
                'name',
                'surname',
                'patronym',
                'profession',
                'doctors',
                'selectedArchivePatient'
            )
        );
    }

    public function edit(Patient $patient)
    {
        $record = RegistryRecord::with('patient')
            ->whereBelongsTo($patient)
            ->firstOrFail();

        $currentClinicId = $this->currentReceptionClinicId(
            request()
        );

        if (
            !$currentClinicId
            || (int) $record->clinic_id
                !== (int) $currentClinicId
        ) {
            abort(
                403,
                'Нельзя открыть запись другой клиники.'
            );
        }

        $appointmentFormats = $this->clinicAppointmentFormats(
            $currentClinicId
        );
        $recordAppointmentFormat = $record->appointment_format
            ?: self::APPOINTMENT_FORMAT_IN_PERSON;
        $defaultAppointmentFormat = in_array(
            $recordAppointmentFormat,
            $appointmentFormats,
            true
        )
            ? $recordAppointmentFormat
            : $this->defaultAppointmentFormat($appointmentFormats);

        $doctors = $this->receptionDoctorsForClinic(
            $currentClinicId,
            $appointmentFormats
        );

        $doctorClinicIds = $doctors
            ->mapWithKeys(
                fn (Doctor $doctor): array => [
                    (string) $doctor->id => $doctor->clinic_id !== null
                        ? (int) $doctor->clinic_id
                        : null,
                ]
            )
            ->all();

        $clinicWorkSchedules = $this->clinicWorkSchedulesForDoctors($doctors);

        [
            $clinicScheduleExceptions,
            $doctorCabinetIds,
            $doctorCabinets,
        ] = $this->clinicScheduleExceptionDataForDoctors(
            $doctors
        );

        $doctorAppointmentFormats = $this->doctorAppointmentFormats(
            $doctors,
            $appointmentFormats,
            $doctorCabinetIds
        );

        $patientDocument = DB::table('patient_identity_documents')
            ->where('patient_id', $patient->id)
            ->where('is_primary', true)
            ->where('is_active', true)
            ->first();

        $representative = DB::table('patient_representatives')
            ->where('patient_id', $patient->id)
            ->where('is_primary', true)
            ->where('is_active', true)
            ->first();

        $representativeIdentityDocument = $representative
            ? DB::table('patient_representative_documents')
                ->where('representative_id', $representative->id)
                ->where('document_purpose', 'identity')
                ->where('is_primary', true)
                ->where('is_active', true)
                ->first()
            : null;

        $representativeAuthorityDocument = $representative
            ? DB::table('patient_representative_documents')
                ->where('representative_id', $representative->id)
                ->where('document_purpose', 'authority')
                ->where('is_primary', true)
                ->where('is_active', true)
                ->first()
            : null;

        return view(
            'doctors.reception.edit',
            compact(
                'record',
                'doctors',
                'patient',
                'doctorClinicIds',
                'clinicWorkSchedules',
                'clinicScheduleExceptions',
                'doctorCabinetIds',
                'doctorCabinets',
                'doctorAppointmentFormats',
                'appointmentFormats',
                'defaultAppointmentFormat',
                'patientDocument',
                'representative',
                'representativeIdentityDocument',
                'representativeAuthorityDocument'
            )
        );
    }

    public function update(UpdateRegistryRecordRequest $request, $id)
    {
        $record = RegistryRecord::with('patient')->findOrFail($id);

        $currentClinicId = $this->currentReceptionClinicId(
            $request
        );

        if (!$currentClinicId) {
            abort(
                403,
                'Не удалось определить клинику пользователя.'
            );
        }

        if (
            (int) $record->clinic_id
            !== (int) $currentClinicId
        ) {
            abort(
                403,
                'Нельзя изменить запись другой клиники.'
            );
        }

        $appointmentFormat = (string) $request->appointment_format;

        $this->assertAppointmentFormatEnabled(
            $currentClinicId,
            $appointmentFormat
        );

        $selectedDoctor = Doctor::findOrFail(
            $request->doctor_id
        );

        $this->assertDoctorAvailableForReception(
            $selectedDoctor,
            $currentClinicId,
            $appointmentFormat
        );

        $this->validateAppointmentWithinClinicSchedule(
            $selectedDoctor,
            Carbon::parse($request->appointment_datetime),
            $appointmentFormat
        );

        $record->doctor_id = $selectedDoctor->id;
        $record->clinic_id = $selectedDoctor->clinic_id;
        $record->for_datetime = $request->appointment_datetime;
        $record->appointment_format = $appointmentFormat;
        $record->save();

        $patient = $record->patient;
        $patient->fill($request->only([
            'surname', 'name', 'patronym', 'birth_at', 'gender',
            'address_registration', 'address_residence',
            'serial', 'number', 'department_code',
            'issued_by', 'issued_at', 'birth_place',
            'snils', 'oms'
        ]));
        $patient->save();

        return redirect()
            ->route('doctors.reception.index')
            ->with('success', 'Запись успешно обновлена.');
    }

    public function destroy($id)
    {
        $record = RegistryRecord::findOrFail($id);
        $record->delete(); // Soft delete — удаляется через `deleted_at`

        return redirect()
            ->route('doctors.reception.index')
            ->with('success', 'Запись успешно перенесена в архив.');
    }
}
