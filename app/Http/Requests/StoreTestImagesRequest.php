<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTestImagesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return is_authed() && auth()->user()->can('test_create_with_images');
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
            'code' => 'required',
            'status' => 'required|string',
            'estimated_minutes' => 'required|integer',
            'description' => 'required|string',
            'instructions' => 'required|string',
            'cards' => 'required|array',
            'cards.*.image' => 'required|image|mimes:jpeg,png,jpg|max:8196',
            'cards.*.title' => 'required|string',
            'cards.*.question' => 'required|string',
            'interpretation_type' => 'required|in:manual,template,scale',
            'answers_type' => 'required|string',
            'display_mode' => 'required|string|in:SEQUENTIAL,ALL'
        ];
    }
}
