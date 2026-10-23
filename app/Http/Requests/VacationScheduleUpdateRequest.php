<?php

namespace App\Http\Requests;

use App\Models\EmployeeVacation;

class VacationScheduleUpdateRequest extends VacationScheduleStoreRequest
{
    public function authorize(): bool
    {
        if (!parent::authorize()) {
            return false;
        }

        $vacation = $this->route('vacation');

        if (!$vacation instanceof EmployeeVacation) {
            return false;
        }

        return (int) $vacation->clinic_id
            === (int) auth()->user()->doctor->clinic_id;
    }

    protected function failedAuthorization(): void
    {
        abort(404);
    }
}
