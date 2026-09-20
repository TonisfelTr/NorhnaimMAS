<?php

namespace App\Support\CardDocuments;

use App\Models\Patient;
use App\Models\PatientClinicalDocument;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;

final class DocumentAccess
{
    public function doctor(): Model
    {
        $user = auth()->user();
        abort_unless($user && method_exists($user, 'doctor'), 403, 'Не найден профиль врача.');
        $doctor = $user->doctor()->first();
        abort_unless($doctor, 403, 'Для работы с документами нужен профиль врача.');

        return $doctor;
    }

    public function canView(Patient $patient): bool
    {
        $doctor = $this->doctor();
        $policy = Gate::getPolicyFor($patient);

        // Зарегистрированную политику не обходим проверкой doctor_id.
        if ($policy !== null) {
            return Gate::allows('view', $patient);
        }

        return (string) $patient->doctor_id === (string) $doctor->getKey();
    }

    public function patient(Patient $patient): void
    {
        abort_unless($this->canView($patient), 403, 'Нет доступа к медицинской карте пациента.');
    }

    public function document(PatientClinicalDocument $document, bool $write = false): void
    {
        $patient = $document->patient;
        abort_unless($patient, 404);
        $this->patient($patient);

        if ($write) {
            abort_unless((string) $document->author_id === (string) auth()->id(), 403,
                'Редактирование доступно только автору документа.');
            abort_unless($document->status === 'draft', 409,
                'Оформленный документ не изменяется. Создайте новый документ.');
        }
    }

    /** Поиск не раскрывает чужих пациентов; явный patient_id дополнительно проверяет policy. */
    public function searchablePatients(): Builder
    {
        return Patient::query()->where('doctor_id', $this->doctor()->getKey());
    }
}
