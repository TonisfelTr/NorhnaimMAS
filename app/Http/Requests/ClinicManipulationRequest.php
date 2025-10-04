<?php

namespace App\Http\Requests;

use App\Http\Controllers\Adminpanel\PatientController;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class ClinicManipulationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return is_authed() && (auth()->user()->can('clinic_add') || auth()->user()->can('clinic_edit'));
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'description' => 'nullable|string',
            'photos.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'cover_photo_index' => 'nullable|integer',
            'services' => 'nullable|array',
            'services.*' => 'required|string|max:255',
        ];
    }
}
