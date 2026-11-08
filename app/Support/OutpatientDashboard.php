<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\RegistryRecord;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

/**
 * Read-only outpatient statistics for doctors.main.
 *
 * This does not query or change inpatient placements. Clinic scope is enforced
 * server-side; switching query-string parameters never widens a doctor's scope.
 */
final class OutpatientDashboard
{
    /**
     * @param Collection<int, Clinic> $outpatientClinics List already prepared by IndexController
     * @return array<string, mixed>
     */
    public function build(
        Request $request,
        bool $isDebugAdmin,
        ?Doctor $doctor,
        ?int $doctorClinicId,
        Collection $outpatientClinics
    ): array {
        $filters = $request->validate([
            'period' => ['sometimes', 'string', Rule::in(['today', 'week', 'month'])],
            'clinic_id' => ['sometimes', 'nullable', 'integer', 'min:1'],
        ]);

        $period = $filters['period'] ?? 'today';
        $requestedClinicId = isset($filters['clinic_id']) ? (int) $filters['clinic_id'] : null;

        if ($isDebugAdmin) {
            // The special debug account may inspect outpatient clinics, but
            // never accidentally include inpatient appointments in its totals.
            $allowedClinicIds = $outpatientClinics->pluck('id')->map(static fn ($id): int => (int) $id)->all();
            if ($requestedClinicId !== null && !in_array($requestedClinicId, $allowedClinicIds, true)) {
                abort(403, 'Нет доступа к выбранной амбулаторной клинике.');
            }
            $clinicOptions = $outpatientClinics;
            $selectedClinicId = $requestedClinicId;
            $visibleClinicIds = $selectedClinicId !== null ? [$selectedClinicId] : $allowedClinicIds;
        } else {
            abort_unless($doctor instanceof Doctor && $doctorClinicId !== null, 403);
            abort_unless($doctor->clinic !== null
                && $doctor->clinic->clinic_type === 'outpatient', 403);
            if ($requestedClinicId !== null && $requestedClinicId !== $doctorClinicId) {
                abort(403, 'Просмотр записей другой клиники запрещён.');
            }
            $visibleClinicIds = [$doctorClinicId];
            $selectedClinicId = $doctorClinicId;
            $clinicOptions = collect([$doctor->clinic]);
        }

        $now = Carbon::now();
        if ($period === 'week') {
            $from = $now->copy()->startOfWeek(Carbon::MONDAY);
            $through = $now->copy()->endOfWeek(Carbon::SUNDAY);
            $periodTitle = 'Эта неделя';
        } elseif ($period === 'month') {
            $from = $now->copy()->startOfMonth();
            $through = $now->copy()->endOfMonth();
            $periodTitle = 'Этот месяц';
        } else {
            $from = $now->copy()->startOfDay();
            $through = $now->copy()->endOfDay();
            $periodTitle = 'Сегодня';
        }

        $base = $this->visibleRecords($visibleClinicIds, $from, $through);
        $encountersAvailable = Schema::hasTable('medical_encounters');
        $formatAvailable = Schema::hasColumn('registry_records', 'appointment_format');

        $total = (clone $base)->count();
        $future = (clone $base)->where('for_datetime', '>=', $now)->count();
        $withoutDoctor = (clone $base)->whereNull('doctor_id')->count();
        $doctorsWithAppointments = (clone $base)->whereNotNull('doctor_id')
            ->distinct()->count('doctor_id');

        // Status comes from medical_encounters, not from the mere fact that
        // the scheduled appointment time has passed.
        $completed = null;
        $noShows = null;
        $notHeld = null;
        if ($encountersAvailable) {
            $completed = (clone $base)->whereIn('id', DB::table('medical_encounters')
                ->select('registry_record_id')->where('consultation_status', 'completed'))->count();
            $noShows = (clone $base)->whereIn('id', DB::table('medical_encounters')
                ->select('registry_record_id')->where('attendance_status', 'absent'))->count();
            $notHeld = (clone $base)->whereIn('id', DB::table('medical_encounters')
                ->select('registry_record_id')->where('consultation_status', 'not_held'))->count();
        }

        $telemedicine = $formatAvailable
            ? (clone $base)->where('appointment_format', 'telemedicine')->count()
            : null;

        // Show up to eight nearest future appointments within the selected
        // reporting period. PatientCareScope excludes current inpatients.
        $upcomingRecords = (clone $base)->where('for_datetime', '>=', $now)
            ->with(['patient', 'doctor'])
            ->orderBy('for_datetime')
            ->orderBy('id')
            ->limit(8)
            ->get();

        $encountersByRecord = $encountersAvailable && $upcomingRecords->isNotEmpty()
            ? DB::table('medical_encounters')
                ->whereIn('registry_record_id', $upcomingRecords->pluck('id'))
                ->get(['registry_record_id', 'consultation_status', 'attendance_status'])
                ->keyBy('registry_record_id')
            : collect();

        $clinicNames = $clinicOptions->pluck('name', 'id');
        $upcoming = $upcomingRecords->map(function (RegistryRecord $record) use (
            $formatAvailable, $encountersByRecord, $clinicNames
        ): array {
            $appointmentAt = Carbon::parse($record->getRawOriginal('for_datetime'));
            $encounter = $encountersByRecord->get($record->id);
            $doctor = $record->doctor;
            $patient = $record->patient;

            $patientName = $patient
                ? trim(implode(' ', array_filter([$patient->surname, $patient->name, $patient->patronym])))
                : '';
            $doctorName = $doctor
                ? trim(implode(' ', array_filter([$doctor->surname, $doctor->name, $doctor->patronym])))
                : '';

            $consultationStatus = $encounter?->consultation_status;
            $attendanceStatus = $encounter?->attendance_status;
            if ($attendanceStatus === 'absent') {
                $status = 'Неявка';
                $statusTone = 'warning';
            } elseif ($consultationStatus === 'completed') {
                $status = 'Завершён';
                $statusTone = 'success';
            } elseif ($consultationStatus === 'not_held') {
                $status = 'Не состоялся';
                $statusTone = 'warning';
            } elseif ($consultationStatus === 'in_progress') {
                $status = 'В процессе';
                $statusTone = 'info';
            } else {
                $status = 'Запланирован';
                $statusTone = 'neutral';
            }

            $format = $formatAvailable ? $record->getAttribute('appointment_format') : null;

            return [
                'id' => (int) $record->id,
                'date' => $appointmentAt->format('d.m'),
                'time' => $appointmentAt->format('H:i'),
                'patient' => $patientName !== '' ? $patientName : 'Пациент не указан',
                'doctor' => $doctorName !== '' ? $doctorName : 'Врач не назначен',
                'clinic' => (string) ($clinicNames->get((int) $record->clinic_id) ?? 'Учреждение не указано'),
                'format' => $format === 'telemedicine' ? 'Телемедицина'
                    : ($format === 'in_person' ? 'Очный' : 'Не указан'),
                'status' => $status,
                'status_tone' => $statusTone,
            ];
        })->all();

        $selectedClinicName = $selectedClinicId !== null
            ? (string) ($clinicNames->get($selectedClinicId) ?? 'Клиника')
            : 'Все амбулаторные учреждения';

        return [
            'period' => $period,
            'period_title' => $periodTitle,
            'period_dates' => $from->format('d.m.Y') . ($from->toDateString() === $through->toDateString()
                ? '' : ' — ' . $through->format('d.m.Y')),
            'clinic_options' => $clinicOptions,
            'selected_clinic_id' => $selectedClinicId,
            'selected_clinic_name' => $selectedClinicName,
            'is_admin' => $isDebugAdmin,
            'show_clinic_column' => $isDebugAdmin && $selectedClinicId === null,
            'appointments' => $total,
            'upcoming_count' => $future,
            'completed_count' => $completed,
            'no_show_count' => $noShows,
            'not_held_count' => $notHeld,
            'telemedicine_count' => $telemedicine,
            'doctors_with_appointments' => $doctorsWithAppointments,
            'without_doctor_count' => $withoutDoctor,
            'encounters_available' => $encountersAvailable,
            'format_available' => $formatAvailable,
            'upcoming' => $upcoming,
        ];
    }

    /**
     * @param list<int> $clinicIds
     * @return Builder<RegistryRecord>
     */
    private function visibleRecords(array $clinicIds, Carbon $from, Carbon $through): Builder
    {
        $query = RegistryRecord::query()
            ->whereIn('clinic_id', $clinicIds)
            ->whereBetween('for_datetime', [$from, $through])
            ->whereHas('patient');

        // Shared with the existing outpatient registration list: a patient
        // currently admitted to an inpatient facility must not be displayed.
        return PatientCareScope::forRecords($query, 'outpatient');
    }
}
