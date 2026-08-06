<?php

declare(strict_types=1);

namespace App\Services\Prescriptions;

use App\Models\Prescription;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

final class PrescriptionRepeatStatistics
{
    /**
     * Количество повторно оформленных рецептов текущего врача за сегодня.
     *
     * В подсчёт попадает только рецепт, у которого заполнено repeated_from_id.
     * По совпадению пациента, препарата или дозировки рецепт повторным не считается.
     */
    public function todayForDoctor(int $doctorId): int
    {
        $now = Carbon::now();

        return $this->forDoctorAndPeriod(
            $doctorId,
            $now->copy()->startOfDay(),
            $now->copy()->endOfDay()
        );
    }

    /**
     * Количество повторно оформленных рецептов врача за заданный период.
     */
    public function forDoctorAndPeriod(
        int $doctorId,
        CarbonInterface $from,
        CarbonInterface $to
    ): int {
        if ($doctorId <= 0) {
            return 0;
        }

        return Prescription::query()
            ->where('doctor_id', $doctorId)
            ->whereNotNull('repeated_from_id')
            ->whereBetween('created_at', [$from, $to])
            ->count();
    }
}
