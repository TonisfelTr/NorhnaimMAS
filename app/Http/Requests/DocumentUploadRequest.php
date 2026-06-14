<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;


class DocumentUploadRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->can('doctor_document_upload');
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
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:doctors,id',
            'description' => 'string|nullable',
            'document_upload' => 'required|file|mimes:doc,docx,pdf,txt',
            'medical_file' => 'string'
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Укажите название документа.',
            'name.string' => 'Название документа должно быть строкой.',

            'patient_id.required' => 'Не указан пациент.',
            'patient_id.exists' => 'Указанный пациент не найден.',

            'doctor_id.required' => 'Не указан врач.',
            'doctor_id.exists' => 'Указанный врач не найден.',

            'document_upload.required' => 'Выберите файл для загрузки.',
            'document_upload.file' => 'Загружаемый документ должен быть файлом.',
            'document_upload.mimes' => 'Документ должен быть в одном из форматов: DOC, DOCX, PDF или TXT.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'название документа',
            'patient_id' => 'пациент',
            'doctor_id' => 'врач',
            'document_upload' => 'документ',
        ];
    }
}
