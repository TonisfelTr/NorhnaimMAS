<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PatientUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user() && auth()->user()->can('patient_edit');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'surname' => 'required|string',
            'name' => 'required|string',
            'patronym' => 'required|string',
            'birth_at' => 'required|date_format:Y-m-d',
            'married' => 'integer',
            'address_registration' => 'required|string',
            'address_job' => 'string',
            'address_residence' => 'required|string',
            'profession' => 'string',
        ];
    }
}
