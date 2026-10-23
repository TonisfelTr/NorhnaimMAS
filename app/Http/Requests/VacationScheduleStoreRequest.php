<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VacationScheduleStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check()
            && auth()->user()->doctor
            && auth()->user()->doctor->clinic_id;
    }

    protected function prepareForValidation(): void
    {
        $employeeType = (string) $this->input('employee_type');

        $this->merge([
            'replace_doctor_id' => $employeeType === 'doctor'
                && $this->filled('replace_doctor_id')
                    ? $this->input('replace_doctor_id')
                    : null,
            'replace_nurse_id' => $employeeType === 'nurse'
                && $this->filled('replace_nurse_id')
                    ? $this->input('replace_nurse_id')
                    : null,
        ]);
    }

    public function rules(): array
    {
        $clinicId = (int) auth()
            ->user()
            ->doctor
            ->clinic_id;

        $employeeType = (string) $this->input('employee_type');
        $employeeTable = $employeeType === 'nurse'
            ? 'nurses'
            : 'doctors';


        return [
            'employee_key' => [
                'required',
                'integer',
                'min:1',
                Rule::exists(
                    $employeeTable,
                    'id'
                )->where(
                    static function ($query) use ($clinicId): void {
                        $query->where('clinic_id', $clinicId);
                    }
                ),
            ],

            'employee_type' => [
                'required',
                'string',
                Rule::in([
                    'doctor',
                    'nurse',
                ]),
            ],

            'replace_doctor_id' => [
                Rule::requiredIf(
                    $employeeType === 'doctor'
                ),
                'nullable',
                'integer',
                'min:1',
                'different:employee_key',
                Rule::exists(
                    'doctors',
                    'id'
                )->where(
                    static function ($query) use ($clinicId): void {
                        $query->where('clinic_id', $clinicId);
                    }
                ),
            ],

            'replace_nurse_id' => [
                Rule::requiredIf(
                    $employeeType === 'nurse'
                ),
                'nullable',
                'integer',
                'min:1',
                'different:employee_key',
                Rule::exists(
                    'nurses',
                    'id'
                )->where(
                    static function ($query) use ($clinicId): void {
                        $query->where('clinic_id', $clinicId);
                    }
                ),
            ],

            'vacation_type' => [
                'required',
                'string',
                Rule::in([
                    'annual',
                    'study',
                    'unpaid',
                    'maternity',
                    'other',
                ]),
            ],

            'date_from' => [
                'required',
                'date',
            ],

            'date_to' => [
                'required',
                'date',
                'after_or_equal:date_from',
            ],

            'status' => [
                'required',
                'string',
                Rule::in([
                    'planned',
                    'approved',
                ]),
            ],

            'comment' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'employee_key.required' =>
                'Выберите сотрудника.',
            'employee_key.integer' =>
                'Некорректный идентификатор сотрудника.',
            'employee_key.min' =>
                'Некорректный идентификатор сотрудника.',
            'employee_key.exists' =>
                'Сотрудник не найден в текущей клинике.',

            'employee_type.required' =>
                'Не удалось определить тип сотрудника.',
            'employee_type.in' =>
                'Недопустимый тип сотрудника.',

            'replace_doctor_id.required' =>
                'Укажите исполняющего обязанности врача.',
            'replace_doctor_id.integer' =>
                'Некорректный идентификатор исполняющего обязанности врача.',
            'replace_doctor_id.min' =>
                'Некорректный идентификатор исполняющего обязанности врача.',
            'replace_doctor_id.different' =>
                'Врач не может быть назначен исполняющим обязанности самого себя.',
            'replace_doctor_id.exists' =>
                'Исполняющий обязанности врач не найден в текущей клинике.',

            'replace_nurse_id.required' =>
                'Укажите исполняющую обязанности медсестры.',
            'replace_nurse_id.integer' =>
                'Некорректный идентификатор исполняющей обязанности медсестры.',
            'replace_nurse_id.min' =>
                'Некорректный идентификатор исполняющей обязанности медсестры.',
            'replace_nurse_id.different' =>
                'Медсестра не может быть назначена исполняющей обязанности самой себя.',
            'replace_nurse_id.exists' =>
                'Исполняющая обязанности медсестра не найдена в текущей клинике.',

            'vacation_type.required' =>
                'Выберите тип отпуска.',
            'vacation_type.in' =>
                'Выбран недопустимый тип отпуска.',

            'date_from.required' =>
                'Укажите дату начала отпуска.',
            'date_from.date' =>
                'Некорректная дата начала отпуска.',
            'date_to.required' =>
                'Укажите дату окончания отпуска.',
            'date_to.date' =>
                'Некорректная дата окончания отпуска.',
            'date_to.after_or_equal' =>
                'Дата окончания не может быть раньше даты начала.',

            'status.required' =>
                'Выберите статус отпуска.',
            'status.in' =>
                'Выбран недопустимый статус отпуска.',

            'comment.max' =>
                'Комментарий не должен превышать 2000 символов.',
        ];
    }

    public function attributes(): array
    {
        return [
            'employee_key' => 'сотрудник',
            'employee_type' => 'тип сотрудника',
            'replace_doctor_id' => 'исполняющий обязанности врача',
            'replace_nurse_id' => 'исполняющая обязанности медсестры',
            'vacation_type' => 'тип отпуска',
            'date_from' => 'начало отпуска',
            'date_to' => 'окончание отпуска',
            'status' => 'статус',
            'comment' => 'комментарий',
        ];
    }
}
