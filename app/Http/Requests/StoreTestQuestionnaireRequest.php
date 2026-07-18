<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTestQuestionnaireRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return is_authed() && $this->user()->can('test_create_with_answers');
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
            'code' => 'required|string|unique:tests,code',
            'status' => 'required|string|in:официально,часто',
            'estimated_minutes' => 'required|integer',
            'description' => 'required|string',
            'instructions' => 'required|string',
            'scoring_type' => 'required|string',
            'min_scoring' => 'required|integer',
            'max_scoring' => 'required|integer',
            'attention_score' => 'required|integer',
            'description_interpretation' => 'required|string',
            'resource_profile.title' => 'required|string',
            'resource_profile.about' => 'required|string',
            'resource_profile.bands.*.min' => 'required|integer',
            'resource_profile.bands.*.max' => 'required|integer',
            'resource_profile.bands.*.label' => 'required|string',
            'resource_profile.bands.*.text' => 'required|string',
            'resource_profile.questions' => 'required|array',
            'resource_profile.questions.*.title' => 'required|string',
            'resource_profile.questions.*.text' => 'required|string',
            'resource_profile.questions.*.scale' => 'required|string',
            'resource_profile.questions.*.answer_type' => 'required|string',
            'resource_profile.questions.*.reverse' => 'required|bool',
        ];
    }
}
