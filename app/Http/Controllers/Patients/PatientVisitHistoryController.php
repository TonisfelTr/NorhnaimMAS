<?php

declare(strict_types=1);

namespace App\Http\Controllers\Patients;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\RegistryRecord;
use App\Support\ClinicAppointmentTime;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Throwable;

final class PatientVisitHistoryController extends Controller
{
    private const PER_PAGE = 15;

    public function index(Request $request): View
    {
        $user = $request->user();
        abort_unless($user && $user->hasRole('patients'), 403);

        /** @var Patient $patient */
        $patient = $user->patient()->firstOrFail();

        $filters = $request->validate([
            'format' => ['nullable', 'in:all,in_person,telemedicine'],
            'period' => ['nullable', 'in:all,30,90,365'],
            'clinic_id' => ['nullable', 'integer', 'min:1'],
        ]);

        $format = (string) ($filters['format'] ?? 'all');
        $period = (string) ($filters['period'] ?? 'all');
        $clinicId = isset($filters['clinic_id']) ? (int) $filters['clinic_id'] : null;

        $query = RegistryRecord::withTrashed()
            ->with(['doctor'])
            ->where('patient_id', (int) $patient->getKey());

        if ($format !== 'all') {
            $query->where('appointment_format', $format);
        }

        if ($clinicId !== null && $clinicId > 0) {
            $query->where(static function (Builder $scope) use ($clinicId): void {
                $scope->where('clinic_id', $clinicId)
                    ->orWhere(static function (Builder $legacy) use ($clinicId): void {
                        $legacy->whereNull('clinic_id')
                            ->whereIn('doctor_id', Doctor::query()->select('id')->where('clinic_id', $clinicId));
                    });
            });
        }

        if ($period !== 'all') {
            $from = CarbonImmutable::now(config('app.timezone', 'UTC'))
                ->subDays((int) $period)->startOfDay();

            if (Schema::hasColumn('registry_records', 'appointment_starts_at_utc')) {
                $query->where(static function (Builder $scope) use ($from): void {
                    $scope->where('appointment_starts_at_utc', '>=', $from->utc())
                        ->orWhere(static function (Builder $legacy) use ($from): void {
                            $legacy->whereNull('appointment_starts_at_utc')
                                ->where('for_datetime', '>=', $from);
                        });
                });
            } else {
                $query->where('for_datetime', '>=', $from);
            }
        }

        if (Schema::hasColumn('registry_records', 'appointment_starts_at_utc')) {
            $query->orderByRaw('COALESCE(appointment_starts_at_utc, for_datetime) DESC');
        } else {
            $query->orderByDesc('for_datetime');
        }

        $visits = $query->paginate(self::PER_PAGE)->withQueryString();
        $recordIds = $visits->getCollection()->pluck('id')->map(static fn ($id): int => (int) $id)->all();

        $encounters = $this->loadEncounters($recordIds);
        $confirmations = $this->loadConfirmations($recordIds);

        $resolvedClinicIds = $visits->getCollection()
            ->map(static fn (RegistryRecord $record): int =>
                (int) ($record->getRawOriginal('clinic_id') ?: $record->doctor?->clinic_id ?: 0))
            ->filter(static fn (int $id): bool => $id > 0)
            ->unique()->values();

        $clinicNames = $resolvedClinicIds->isNotEmpty()
            ? Clinic::query()->whereIn('id', $resolvedClinicIds->all())->pluck('name', 'id')
            : collect();

        $visits->setCollection($visits->getCollection()->map(function (RegistryRecord $record) use (
            $encounters, $confirmations, $clinicNames
        ): array {
            $id = (int) $record->getKey();
            $encounter = $encounters->get($id, collect())->first();
            $confirmationRows = $confirmations->get($id, collect());

            $attendance = (string) ($encounter->attendance_status ?? 'unknown');
            $consultation = (string) ($encounter->consultation_status ?? 'not_started');
            $latestConfirmation = $confirmationRows->first();
            $notHeldConfirmation = (string) ($latestConfirmation->outcome ?? '') === 'not_held';
            $heldConfirmation = (string) ($latestConfirmation->outcome ?? '') === 'held';

            $isNotHeld = $attendance === 'absent'
                || $consultation === 'not_held'
                || $notHeldConfirmation;
            $isHeld = $consultation === 'completed' || $heldConfirmation;
            $arrivalConfirmed = filter_var($record->getRawOriginal('appointment'), FILTER_VALIDATE_BOOLEAN);
            $isArrived = !$isNotHeld && !$isHeld && ($attendance === 'present' || $arrivalConfirmed);

            $startsAt = ClinicAppointmentTime::startInstant($record);
            $statusMoment = $startsAt;
            $dateLabel = null;
            try {
                $description = ClinicAppointmentTime::describe($record);
                if ($statusMoment === null) {
                    $statusMoment = ClinicAppointmentTime::legacyLocal($record);
                }
                if (!empty($description['date']) && !empty($description['time'])) {
                    $dateLabel = $description['date'] . ' · ' . $description['time'];
                }
            } catch (Throwable) {
                try {
                    $legacy = ClinicAppointmentTime::legacyLocal($record);
                    $dateLabel = $legacy?->format('d.m.Y · H:i');
                } catch (Throwable) {
                    // Keep the explicit unavailable-date label for malformed legacy rows.
                }
            }

            $isPast = $statusMoment !== null && $statusMoment->getTimestamp() <= now()->timestamp;
            $cancelled = $record->getRawOriginal('deleted_at') !== null;

            if ($cancelled) {
                [$status, $statusClass] = ['Запись отменена', 'is-muted'];
            } elseif ($isNotHeld) {
                [$status, $statusClass] = [
                    $attendance === 'absent' ? 'Не состоялся: пациент не явился' : 'Приём не состоялся',
                    'is-danger',
                ];
            } elseif ($isHeld) {
                [$status, $statusClass] = ['Консультация состоялась', 'is-success'];
            } elseif ($isArrived) {
                [$status, $statusClass] = ['Явка подтверждена, результат не указан', 'is-info'];
            } elseif ($isPast) {
                [$status, $statusClass] = ['Ожидает подтверждения врача', 'is-warning'];
            } else {
                [$status, $statusClass] = ['Запланирован', 'is-primary'];
            }

            $doctor = $record->doctor;
            $doctorName = $doctor
                ? trim(implode(' ', array_filter([$doctor->surname, $doctor->name, $doctor->patronym])))
                : 'Врач не указан';
            $resolvedClinicId = (int) ($record->getRawOriginal('clinic_id') ?: $doctor?->clinic_id ?: 0);
            $telemedicine = (string) ($record->getRawOriginal('appointment_format') ?: 'in_person') === 'telemedicine';

            return [
                'id' => $id,
                'date' => $dateLabel ?: 'Дата не определена',
                'doctor_name' => $doctorName !== '' ? $doctorName : 'Врач не указан',
                'doctor_speciality' => trim((string) ($doctor?->status ?: $doctor?->profession ?: '')),
                'clinic_name' => (string) ($clinicNames->get($resolvedClinicId)
                    ?: ($resolvedClinicId > 0 ? 'Клиника #' . $resolvedClinicId : 'Клиника не указана')),
                'format' => $telemedicine ? 'Телемедицина' : 'Очный приём',
                'is_telemedicine' => $telemedicine,
                'duration' => max(1, (int) ($record->appointment_duration_minutes ?: 30)),
                'status' => $status,
                'status_class' => $statusClass,
                // Only an actually completed visit can reveal its diagnosis.
                'diagnosis_code' => (!$cancelled && $attendance === 'present' && $consultation === 'completed')
                    ? trim((string) ($encounter->diagnosis_code ?? '')) : '',
                'diagnosis_title' => (!$cancelled && $attendance === 'present' && $consultation === 'completed')
                    ? trim((string) ($encounter->diagnosis_title ?? '')) : '',
            ];
        }));

        $allRecords = RegistryRecord::withTrashed()
            ->where('patient_id', (int) $patient->getKey())
            ->get(['clinic_id', 'doctor_id']);

        $clinicIds = $allRecords->pluck('clinic_id')->filter()->map(static fn ($id): int => (int) $id);
        $legacyDoctorIds = $allRecords
            ->filter(static fn ($row): bool => $row->clinic_id === null && $row->doctor_id !== null)
            ->pluck('doctor_id')->map(static fn ($id): int => (int) $id)->unique();

        if ($legacyDoctorIds->isNotEmpty()) {
            $clinicIds = $clinicIds->merge(
                Doctor::query()->whereIn('id', $legacyDoctorIds->all())->pluck('clinic_id')
            );
        }

        $clinicIds = $clinicIds->filter()->map(static fn ($id): int => (int) $id)->unique()->values();
        $clinicOptions = $clinicIds->isNotEmpty()
            ? Clinic::query()->whereIn('id', $clinicIds->all())->orderBy('name')->get(['id', 'name'])
            : collect();

        return view('patients.history.index', [
            'patient' => $patient,
            'visits' => $visits,
            'clinicOptions' => $clinicOptions,
            'filters' => ['format' => $format, 'period' => $period, 'clinic_id' => $clinicId],
        ]);
    }

    private function loadEncounters(array $ids): Collection
    {
        if ($ids === [] || !Schema::hasTable('medical_encounters')) {
            return collect();
        }

        return DB::table('medical_encounters')->whereIn('registry_record_id', $ids)
            ->orderByDesc('id')
            ->get(['registry_record_id', 'attendance_status', 'consultation_status', 'diagnosis_code', 'diagnosis_title'])
            ->groupBy('registry_record_id');
    }

    private function loadConfirmations(array $ids): Collection
    {
        if ($ids === [] || !Schema::hasTable('medical_visit_confirmations')) {
            return collect();
        }

        return DB::table('medical_visit_confirmations')->whereIn('registry_record_id', $ids)
            ->orderByDesc('id')->get(['registry_record_id', 'outcome'])
            ->groupBy('registry_record_id');
    }
}
