<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BannedStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->can('banning_user');
    }

    public function rules(): array
    {
        return [
            'login' => 'required|string',
            'rule_id' => [
                'required',
                function ($attribute, $value, $fail) {
                    if (\App\Models\Rule::where('id', $value)->count() !== 1) {
                        $fail("Некоторые значения {$attribute} отсутствуют в таблице правил.");
                    }
                },
            ],
            'to' => [
                'required',
                'date',
                function ($attribute, $value, $fail) {
                    if (\Carbon\Carbon::parse($value)->isToday()) {
                        $fail("Поле {$attribute} не может быть сегодняшней датой.");
                    }
                },
            ],
        ];
    }
}
