<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClinicUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return is_authed() && auth()->user()->can('clinic_edit');
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'address' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'photos.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'cover_photo_index' => 'nullable|integer',
            'services' => 'nullable|array',
            'services.*' => 'required|string|max:255',
        ];
    }
}
