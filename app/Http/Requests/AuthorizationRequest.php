<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class AuthorizationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'g-recaptcha-response' => 'required',
            'email' => 'required|email',
            'password' => 'required',
            'remember_me' => 'nullable|boolean',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            redirect($this->getRedirectUrl())
                ->withInput($this->except([
                    'password',
                    'g-recaptcha-response',
                ]))
                ->withErrors($validator->errors(), $this->errorBag)
                ->with('open_modal', 'authorization-block')
                ->with('auth_mode', 'login')
        );
    }

    public function messages(): array
    {
        return [
            'g-recaptcha-response.required' => 'Не удалось отличить Вас от бота. Обратитесь к Администрации через форму обратной связи.',
            'email.required' => 'Вы не заполнили поле для электронной почты.',
            'email.email' => 'Укажите корректный адрес электронной почты.',
            'password.required' => 'Вы не заполнили поле для пароля.',
            'remember_me.boolean' => 'Некорректное значение переключателя «Запомнить меня».',
        ];
    }
}
