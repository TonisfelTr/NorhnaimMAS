<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LawyerUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return is_authed() && auth()->user()->can('lawyer_edit');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string',
            'surname' => 'required|string',
            'profession' => 'required|string',
            'skills' => 'required|array',
            'base_price' => 'numeric',
            'experience' => 'numeric',
            'phone' => 'required',
        ];
    }
}
