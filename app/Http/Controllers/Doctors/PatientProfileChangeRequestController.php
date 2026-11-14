<?php

namespace App\Http\Controllers\Doctors;

use App\Http\Controllers\Controller;
use App\Models\PatientProfileChangeRequest;
use App\Services\PatientProfileChangeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PatientProfileChangeRequestController extends Controller
{
    public function index(Request $request): View
    {
        $doctor = $request->user()?->doctor()->first();

        abort_unless($doctor, 403);

        $status = (string) $request->query('status', 'pending');

        if (! in_array($status, ['pending', 'approved', 'rejected'], true)) {
            $status = 'pending';
        }

        $requests = PatientProfileChangeRequest::query()
            ->with(['patient', 'user', 'reviewer', 'reviewClinic'])
            ->where('review_target', PatientProfileChangeRequest::TARGET_DOCTOR)
            ->where('review_doctor_id', (int) $doctor->id)
            ->where('status', $status)
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('doctors.patient-profile-change-requests.index', [
            'requests' => $requests,
            'status' => $status,
            'fieldLabels' => [
                'surname' => 'Фамилия',
                'name' => 'Имя',
                'patronym' => 'Отчество',
                'birth_at' => 'Дата рождения',
                'gender' => 'Пол',
                'phone' => 'Телефон',
                'email' => 'Email',
                'birth_place' => 'Место рождения',
                'address_registration' => 'Адрес регистрации',
                'address_residence' => 'Адрес проживания',
                'snils' => 'СНИЛС',
                'oms' => 'ОМС',
                'insurance_company' => 'Страховая компания',
                'document_type' => 'Тип документа',
                'document_series' => 'Серия',
                'document_number' => 'Номер',
                'document_issued_by' => 'Кем выдан',
                'document_issued_at' => 'Дата выдачи',
                'document_department_code' => 'Код подразделения',
            ],
        ]);
    }

    public function approve(
        Request $request,
        PatientProfileChangeRequest $changeRequest,
        PatientProfileChangeService $service
    ): RedirectResponse {
        $data = $request->validate([
            'review_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $service->approve($changeRequest, $request->user(), $data['review_note'] ?? null);

        return back()->with('success', 'Изменения пациента подтверждены.');
    }

    public function reject(
        Request $request,
        PatientProfileChangeRequest $changeRequest,
        PatientProfileChangeService $service
    ): RedirectResponse {
        $data = $request->validate([
            'review_note' => ['required', 'string', 'min:5', 'max:2000'],
        ], [
            'review_note.required' => 'Укажите причину отказа.',
            'review_note.min' => 'Причина должна содержать не менее 5 символов.',
        ]);

        $service->reject($changeRequest, $request->user(), $data['review_note']);

        return back()->with('success', 'Запрос отклонён.');
    }
}
