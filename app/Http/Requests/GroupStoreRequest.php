<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GroupStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Разрешить выполнение запроса
        return is_authed() && auth()->user()->can('group_add');
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:groups,slug',
            'adminpanel_see' => 'required|boolean',
            'user_edit' => 'required|boolean',
            'banning_user' => 'required|boolean',
            'user_change_role' => 'required|boolean',
            'user_remove' => 'required|boolean',
            'group_add' => 'required|boolean',
            'group_remove' => 'required|boolean',
            'group_edit' => 'required|boolean',
            'settings_main' => 'required|boolean',
            'settings_mail' => 'required|boolean',
            'clinic_add' => 'required|boolean',
            'clinic_edit' => 'required|boolean',
            'clinic_remove' => 'required|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Название группы обязательно для заполнения.',
            'slug.unique' => 'Техническое название группы уже занято.',
            'boolean' => 'Поле :attribute должно быть либо \"Разрешено\", либо \"Запрещено\".',
        ];
    }
}
