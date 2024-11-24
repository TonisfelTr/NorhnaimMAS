<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\ValidationException;

class AuthorizationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'g-recaptcha-response' => "required",
            'email' => 'required|email',
            'password' => 'required'
        ];
    }

    public function failedValidation(Validator $validator) {
        $errors = $validator->errors();

        throw new HttpResponseException(
            redirect($this->getRedirectUrl())
                ->withInput($this->input())
                ->withErrors($errors, $this->errorBag)
                // Добавляем в сессию наш кастомный ключ и значение
                ->with('open_modal', 'authorization-block')
        );
    }

    public function messages(): array {
        return [
            'g-recaptcha-response.required' => 'Не удалось отличить Вас от бота. Обратитесь к Администрации через форму обратной связи.',
            'email.required' => 'Вы не заполнили поле для электронной почты.',
            'password.required' => 'Вы не заполнили поле для пароля.'
        ];
    }
}
