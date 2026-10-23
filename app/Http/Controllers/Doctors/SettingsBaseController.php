<?php

namespace App\Http\Controllers\Doctors;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

abstract class SettingsBaseController extends Controller
{
    protected function settingsLayoutData(): array
    {
        $doctor = auth()->user()?->doctor;
        $isPrivatePractice = (bool) ($doctor && !$doctor->clinic_id);
        $clinic = $doctor?->clinic;
        $clinicType = (string) ($clinic?->clinic_type ?: 'outpatient');
        $isInpatient = !$isPrivatePractice && $clinicType === 'inpatient';

        if ($isPrivatePractice) {
            $contextLabel = 'Частная практика';
            $contextIcon = 'bi-person-badge';
        } elseif ($isInpatient) {
            $contextLabel = 'Стационар';
            $contextIcon = 'bi-hospital';
        } else {
            $contextLabel = 'Амбулатория';
            $contextIcon = 'bi-hospital';
        }

        return [
            'settingsDoctor' => $doctor,
            'settingsIsPrivatePractice' => $isPrivatePractice,
            'settingsClinicType' => $clinicType,
            'settingsIsInpatient' => $isInpatient,
            'settingsContextLabel' => $contextLabel,
            'settingsContextIcon' => $contextIcon,
        ];
    }

    protected function settingsView(
        string $view,
        array $data = []
    ): View {
        return view(
            $view,
            array_merge(
                $this->settingsLayoutData(),
                $data
            )
        );
    }
}
