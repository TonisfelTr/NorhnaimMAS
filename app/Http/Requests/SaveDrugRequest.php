<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class SaveDrugRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::user()->group->adminpanel_see == true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $drugId = $this->route('drug');

        return [
            "name" => "required|string|unique:drugs,name,{$drugId},id",
            "latin_name" => "required|string|unique:drugs,latin_name,{$drugId},id",
            "group" => "required|numeric",
            "description" => "string|nullable",
            "ht_output_from" => "numeric|required",
            "ht_output_to" => "numeric|required",
        ];
    }
}
