<?php

namespace App\Http\Requests;

use App\Models\InstrumentalResearch;
use App\Models\Patient;
use Illuminate\Foundation\Http\FormRequest;

abstract class InstrumentalResearchPatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        $doctor = $this->user()?->doctor;
        $patient = $this->route('patient');
        $instrumentalResearch = $this->route('instrumentalResearch');

        if (
            !$doctor
            || !$patient instanceof Patient
            || !$instrumentalResearch instanceof InstrumentalResearch
        ) {
            return false;
        }

        return (int) $instrumentalResearch->patient_id === (int) $patient->id;
    }
}
