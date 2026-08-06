<?php

namespace App\Http\Requests;

use App\Models\InstrumentalResearch;
use Illuminate\Validation\Rule;

class UpdateInstrumentalResearchRequest extends InstrumentalResearchPatientRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'with_contrast' => $this->boolean('with_contrast'),
        ]);
    }

    public function rules(): array
    {
        return [
            'study_type' => [
                'required',
                'string',
                Rule::in(array_keys(InstrumentalResearch::TYPES)),
            ],

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'body_area' => [
                'nullable',
                'string',
                'max:255',
            ],

            'priority' => [
                'required',
                'string',
                Rule::in(array_keys(InstrumentalResearch::PRIORITIES)),
            ],

            'status' => [
                'required',
                'string',
                Rule::in(array_keys(InstrumentalResearch::STATUSES)),
            ],

            'planned_at' => [
                'nullable',
                'date',
            ],

            'with_contrast' => [
                'required',
                'boolean',
            ],

            'organization' => [
                'nullable',
                'string',
                'max:255',
            ],

            'indication' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'study_type' => 'вид исследования',
            'name' => 'название исследования',
            'body_area' => 'область исследования',
            'priority' => 'приоритет',
            'status' => 'статус',
            'planned_at' => 'планируемая дата',
            'with_contrast' => 'использование контраста',
            'organization' => 'медицинская организация',
            'indication' => 'показания',
        ];
    }
}
