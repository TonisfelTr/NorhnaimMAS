<?php

declare(strict_types=1);

namespace App\View\Components;

use App\Services\Prescriptions\PrescriptionRepeatStatistics;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

final class PrescriptionRepeatStatCard extends Component
{
    public int $count;

    public function __construct(?int $doctorId = null)
    {
        $resolvedDoctorId = $doctorId ?? $this->resolveCurrentDoctorId();

        $this->count = app(PrescriptionRepeatStatistics::class)
            ->todayForDoctor($resolvedDoctorId);
    }

    public function render(): View
    {
        return view('components.prescription-repeat-stat-card');
    }

    private function resolveCurrentDoctorId(): int
    {
        $user = request()->user();

        if (!$user || !method_exists($user, 'doctor')) {
            return 0;
        }

        return (int)$user->doctor()->value('id');
    }
}
