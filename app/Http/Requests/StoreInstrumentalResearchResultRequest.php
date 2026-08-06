<?php

namespace App\Http\Requests;

class StoreInstrumentalResearchResultRequest extends InstrumentalResearchPatientRequest
{
    public function rules(): array
    {
        return [
            'performed_at' => 'required|date',
            'result_at' => 'nullable|date|after_or_equal:performed_at',
            'organization' => 'nullable|string|max:255',
            'specialist_name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:30000',
            'conclusion' => 'required|string|max:30000',
            'recommendations' => 'nullable|string|max:10000',

            'result_files' => 'nullable|array|max:10',
            'result_files.*' => 'file|max:102400|mimes:pdf,jpg,jpeg,png,webp,zip',
        ];
    }

    public function validatedData(): array
    {
        $data = $this->validated();

        unset($data['result_files']);

        $data['status'] = 'ready';
        $data['result_at'] = $data['result_at'] ?? now();
        $data['result_showed_at'] = null;

        return $data;
    }

    public function attributes(): array
    {
        return [
            'performed_at' => 'дата выполнения',
            'result_at' => 'дата формирования результата',
            'organization' => 'медицинская организация',
            'specialist_name' => 'специалист',
            'description' => 'описание исследования',
            'conclusion' => 'заключение',
            'recommendations' => 'рекомендации',
        ];
    }
}
