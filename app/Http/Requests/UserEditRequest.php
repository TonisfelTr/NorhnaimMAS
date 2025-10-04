<?php

namespace App\Http\Requests;

use App\Models\Doctor;
use app\Models\Patient;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UserEditRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return is_authed() && auth()->user()->can('user_edit');
    }

    public function messages(): array {
        return [
            'email.required' => 'Вы не ввели адрес электронной почты',
            'email.email' => 'Вы ввели некорректный адрес электронной почты',
            'login.required' => 'Вы не ввели логин пользователя',
            'group.required' => 'Вы не указали группу',
            'group.numeric' => 'Вы указали неверный ID группы',
            'email_verified_at.required' => 'Вы не ввели дату подтверждения аккаунта',
            'userable_id.required' => 'Вы не ввели ID прилегающей сущности',
            'userable_type.required' => 'Вы не выбрали тип прилегающей сущности',
            'userable_type.in' => 'Вы выбрали некорректный тип прилегающей сущности'
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => 'required|email',
            'login' => 'required',
            'group_id' => 'required|numeric',
            'email_verified_at' => 'required',
            'userable_id' => [
                Rule::requiredIf(fn () => $this->input('userable_type') != 'administrators'),
                function ($attribute, $value, $fail) {
                $model = null;

                switch($this->input('userable_type')) {
                    case Doctor::class:
                        $model = 'doctors';
                        break;
                    case Patient::class:
                        $model = 'patients';
                        break;
                    default:
                        $model = 'administrators';
                        break;
                }

                if ($model != 'administrators') {
                    if ($model && !DB::table($model)->where('id', $value)->exists()) {
                        $fail('Неправильное определение записи ' . $model == 'doctors'
                                  ? 'доктора'
                                  : 'пациента'
                        );
                    }
                }
            }],
            'userable_type' => 'required|in:' . implode(',', ['\App\Models\Doctor', '\App\Models\Patient', 'administrators', '']),
            'balance' => 'numeric'
        ];
    }
}
