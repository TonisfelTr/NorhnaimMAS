<?php

namespace App\Http\Requests;

use App\Models\EmployeeVacation;
use Illuminate\Foundation\Http\FormRequest;

class VacationScheduleDeleteRequest extends FormRequest
{
    public function authorize(): bool
    {
        $doctor = auth()->user()?->doctor;
        $vacation = $this->route('vacation');

        if (
            !$doctor
            || !$doctor->clinic_id
            || !$vacation instanceof EmployeeVacation
        ) {
            return false;
        }

        return (int) $vacation->clinic_id
            === (int) $doctor->clinic_id;
    }

    public function rules(): array
    {
        return [];
    }

    protected function failedAuthorization(): void
    {
        abort(404);
    }
}
