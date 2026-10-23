<?php

namespace App\Support;

use App\Models\HospitalBed;
use App\Models\HospitalBedAssignment;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Завершает конкретное размещение, сохраняя его историю.
 * Проверка полномочий врача выполняется вызывающим контроллером.
 */
final class HospitalBedRelease
{
    public static function release(
        int $clinicId,
        int $bedId,
        ?int $expectedPatientId = null,
        ?int $expectedAssignmentId = null,
        bool $discharge = false
    ): void {
        if (($expectedPatientId === null) !== ($expectedAssignmentId === null)
            || ($discharge && $expectedPatientId === null)) {
            throw ValidationException::withMessages([
                'assignment_id' => 'Не удалось определить размещение пациента. Обновите страницу.',
            ]);
        }

        DB::transaction(function () use (
            $clinicId,
            $bedId,
            $expectedPatientId,
            $expectedAssignmentId,
            $discharge
        ): void {
            $bedQuery = HospitalBed::query()
                ->where('clinic_id', $clinicId)
                ->whereHas('room', fn (Builder $room): Builder => $room
                    ->where('clinic_id', $clinicId));

            // Не блокируем пациента до проверки, что койка доступна этой клинике.
            (clone $bedQuery)->findOrFail($bedId);

            if ($expectedPatientId !== null) {
                // Тот же порядок блокировок, что при оформлении: пациент, койка.
                $patient = Patient::query()
                    ->whereKey($expectedPatientId)
                    ->lockForUpdate()
                    ->first();

                if (!$patient) {
                    throw ValidationException::withMessages([
                        'patient_id' => 'Пациент не найден. Обновите страницу.',
                    ]);
                }
            }

            $bed = $bedQuery->lockForUpdate()->findOrFail($bedId);
            $assignment = HospitalBedAssignment::query()
                ->where('bed_id', $bed->id)
                ->whereNull('released_at')
                ->lockForUpdate()
                ->first();

            if (!$assignment) {
                throw ValidationException::withMessages([
                    'assignment_id' => 'Это размещение уже завершено. Обновите страницу.',
                ]);
            }

            if ($expectedPatientId !== null && (
                (int) $assignment->id !== $expectedAssignmentId
                || (int) $assignment->patient_id !== $expectedPatientId
            )) {
                throw ValidationException::withMessages([
                    'assignment_id' => 'Размещение изменилось. Обновите страницу и выберите пациента заново.',
                ]);
            }

            if ($discharge && HospitalBedAssignment::query()
                ->where('patient_id', $expectedPatientId)
                ->whereNull('released_at')
                ->whereKeyNot($assignment->id)
                ->exists()) {
                throw ValidationException::withMessages([
                    'assignment_id' => 'У пациента несколько активных размещений. Проверьте их перед выпиской.',
                ]);
            }

            $assignment->released_at = now();
            $assignment->save();
        }, 3);
    }
}
