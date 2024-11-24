<?php

namespace App\Http\Requests;

use App\Http\Controllers\Adminpanel\PatientController;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class ClinicManipulationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if (Auth::user()->group->adminpanel_see) {
            if (Route::getCurrentRoute()->getName() == 'admin.clinic.new') {
                if (Auth::user()->group->clinic_add) {
                    return true;
                }
            } elseif (Route::getCurrentRoute()->getName() == 'admin.clinic.edit') {
                if (Auth::user()->group->clinic_remove) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            ''
        ];
    }
}
