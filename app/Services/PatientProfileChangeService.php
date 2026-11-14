<?php

namespace App\Services;

use App\Models\Patient;
use App\Models\PatientClinic;
use App\Models\PatientIdentityDocument;
use App\Models\PatientProfileChangeRequest;
use App\Models\PatientProfileEvent;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class PatientProfileChangeService
{
    private const PATIENT_LABELS = [
        'surname' => 'Фамилия',
        'name' => 'Имя',
        'patronym' => 'Отчество',
        'birth_at' => 'Дата рождения',
        'gender' => 'Пол',
        'phone' => 'Телефон',
        'email' => 'Контактный email',
        'birth_place' => 'Место рождения',
        'address_registration' => 'Адрес регистрации',
        'address_residence' => 'Адрес проживания',
        'snils' => 'СНИЛС',
        'oms' => 'Полис ОМС',
        'insurance_company' => 'Страховая компания',
    ];

    private const DOCUMENT_LABELS = [
        'document_type' => 'Тип документа',
        'document_series' => 'Серия документа',
        'document_number' => 'Номер документа',
        'document_issued_by' => 'Кем выдан документ',
        'document_issued_at' => 'Дата выдачи документа',
        'document_department_code' => 'Код подразделения',
    ];

    public function submit(User $user, Patient $patient, array $data): PatientProfileChangeRequest
    {
        return DB::transaction(function () use ($user, $patient, $data): PatientProfileChangeRequest {
            $lockedPatient = Patient::query()
                ->whereKey($patient->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ((int) $lockedPatient->user_id !== (int) $user->id) {
                throw new AccessDeniedHttpException();
            }

            $reviewMembership = PatientClinic::query()
                ->where('patient_id', (int) $lockedPatient->id)
                ->where('clinic_id', (int) $data['review_clinic_id'])
                ->where('is_active', true)
                ->lockForUpdate()
                ->first();

            if (!$reviewMembership) {
                throw ValidationException::withMessages([
                    'review_route' => 'Выбранная клиника не связана с медицинской картой пациента.',
                ]);
            }

            $reviewDoctorId = null;

            if (
                $data['review_target'] === PatientProfileChangeRequest::TARGET_DOCTOR
            ) {
                if (!$reviewMembership->doctor_id) {
                    throw ValidationException::withMessages([
                        'review_route' => 'В выбранной клинике лечащий врач не назначен. Направьте запрос администрации клиники.',
                    ]);
                }

                $reviewDoctorId = (int) $reviewMembership->doctor_id;
            }

            $pendingExists = PatientProfileChangeRequest::query()
                ->where('patient_id', $lockedPatient->id)
                ->where('status', PatientProfileChangeRequest::STATUS_PENDING)
                ->exists();

            if ($pendingExists) {
                throw ValidationException::withMessages([
                    'request' => 'У вас уже есть запрос, который ожидает рассмотрения.',
                ]);
            }

            $payload = [
                'patient' => array_intersect_key(
                    $data,
                    array_flip(array_keys(self::PATIENT_LABELS))
                ),
                'document' => [
                    'document_type' => $data['document_type'] ?? null,
                    'series' => $data['document_series'] ?? null,
                    'number' => $data['document_number'] ?? null,
                    'issued_by' => $data['document_issued_by'] ?? null,
                    'issued_at' => $data['document_issued_at'] ?? null,
                    'department_code' => $data['document_department_code'] ?? null,
                ],
            ];

            $changedFields = $this->changedFields($lockedPatient, $payload);

            if ($changedFields === []) {
                throw ValidationException::withMessages([
                    'request' => 'Вы не указали изменений. Проверьте данные перед отправкой.',
                ]);
            }

            $changeRequest = PatientProfileChangeRequest::query()->create([
                'patient_id' => $lockedPatient->id,
                'user_id' => $user->id,
                'review_target' => $data['review_target'],
                'review_clinic_id' => (int) $reviewMembership->clinic_id,
                'review_doctor_id' => $reviewDoctorId,
                'status' => PatientProfileChangeRequest::STATUS_PENDING,
                'payload' => $payload,
                'changed_fields' => $changedFields,
                'reason' => trim((string) $data['reason']),
            ]);

            $this->recordEvent(
                $lockedPatient,
                $user,
                'profile_change_requested',
                'Отправлен запрос на изменение подтверждённых данных',
                $changedFields,
                [
                    'request_id' => $changeRequest->id,
                    'review_clinic_id' => (int) $reviewMembership->clinic_id,
                    'review_doctor_id' => $reviewDoctorId,
                ]
            );

            return $changeRequest;
        });
    }

    public function approve(
        PatientProfileChangeRequest $request,
        User $actor,
        ?string $reviewNote = null
    ): void {
        DB::transaction(function () use ($request, $actor, $reviewNote): void {
            $patient = Patient::query()
                ->whereKey($request->patient_id)
                ->lockForUpdate()
                ->firstOrFail();

            $lockedRequest = PatientProfileChangeRequest::query()
                ->whereKey($request->id)
                ->where('patient_id', $patient->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $lockedRequest->isPending()) {
                throw ValidationException::withMessages([
                    'request' => 'Запрос уже обработан.',
                ]);
            }

            $this->assertReviewer($lockedRequest, $patient, $actor);

            $payload = (array) $lockedRequest->payload;
            $patientData = (array) ($payload['patient'] ?? []);
            $documentData = (array) ($payload['document'] ?? []);

            $patient->forceFill(array_intersect_key(
                $patientData,
                array_flip(array_keys(self::PATIENT_LABELS))
            ))->save();

            if ($this->hasDocumentChanges($lockedRequest->changed_fields ?? [])) {
                $this->replacePrimaryDocument($patient, $documentData);
            }

            $lockedRequest->forceFill([
                'status' => PatientProfileChangeRequest::STATUS_APPROVED,
                'reviewed_by_user_id' => $actor->id,
                'reviewed_by_role' => $this->reviewerRole($actor),
                'reviewed_at' => now(),
                'review_note' => $reviewNote,
            ])->save();

            $this->recordEvent(
                $patient,
                $actor,
                'profile_change_approved',
                'Изменения подтверждённых данных одобрены',
                (array) $lockedRequest->changed_fields,
                ['request_id' => $lockedRequest->id]
            );
        });
    }

    public function reject(
        PatientProfileChangeRequest $request,
        User $actor,
        string $reviewNote
    ): void {
        DB::transaction(function () use ($request, $actor, $reviewNote): void {
            $patient = Patient::query()
                ->whereKey($request->patient_id)
                ->lockForUpdate()
                ->firstOrFail();

            $lockedRequest = PatientProfileChangeRequest::query()
                ->whereKey($request->id)
                ->where('patient_id', $patient->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $lockedRequest->isPending()) {
                throw ValidationException::withMessages([
                    'request' => 'Запрос уже обработан.',
                ]);
            }

            $this->assertReviewer($lockedRequest, $patient, $actor);

            $lockedRequest->forceFill([
                'status' => PatientProfileChangeRequest::STATUS_REJECTED,
                'reviewed_by_user_id' => $actor->id,
                'reviewed_by_role' => $this->reviewerRole($actor),
                'reviewed_at' => now(),
                'review_note' => trim($reviewNote),
            ])->save();

            $this->recordEvent(
                $patient,
                $actor,
                'profile_change_rejected',
                'Запрос на изменение данных отклонён',
                [],
                ['request_id' => $lockedRequest->id]
            );
        });
    }

    private function assertReviewer(
        PatientProfileChangeRequest $request,
        Patient $patient,
        User $actor
    ): void {
        if ($actor->can('adminpanel_see')) {
            return;
        }

        if (
            ! $actor->hasRole('doctors')
            || $request->review_target !== PatientProfileChangeRequest::TARGET_DOCTOR
        ) {
            throw new AccessDeniedHttpException();
        }

        $doctor = $actor->doctor()->first();

        if (
            !$doctor
            || !$request->review_doctor_id
            || (int) $doctor->id !== (int) $request->review_doctor_id
        ) {
            throw new AccessDeniedHttpException();
        }
    }

    private function changedFields(Patient $patient, array $payload): array
    {
        $current = $this->currentSnapshot($patient);
        $proposedPatient = (array) ($payload['patient'] ?? []);
        $proposedDocument = (array) ($payload['document'] ?? []);
        $changed = [];

        foreach (self::PATIENT_LABELS as $field => $label) {
            if (! array_key_exists($field, $proposedPatient)) {
                continue;
            }

            if (
                $this->normalize($field, $current['patient'][$field] ?? null)
                !== $this->normalize($field, $proposedPatient[$field] ?? null)
            ) {
                $changed[] = $field;
            }
        }

        $documentMap = [
            'document_type' => 'document_type',
            'document_series' => 'series',
            'document_number' => 'number',
            'document_issued_by' => 'issued_by',
            'document_issued_at' => 'issued_at',
            'document_department_code' => 'department_code',
        ];

        foreach ($documentMap as $field => $documentField) {
            if (
                $this->normalize($field, $current['document'][$documentField] ?? null)
                !== $this->normalize($field, $proposedDocument[$documentField] ?? null)
            ) {
                $changed[] = $field;
            }
        }

        return array_values(array_unique($changed));
    }

    private function currentSnapshot(Patient $patient): array
    {
        $document = $patient->primaryIdentityDocument()->first();

        if (! $document) {
            $legacyNumber = $patient->getRawOriginal('number');
            $legacySeries = $patient->getRawOriginal('serial');

            if ($legacyNumber || $legacySeries) {
                return [
                    'patient' => $this->patientValues($patient),
                    'document' => [
                        'document_type' => PatientIdentityDocument::TYPE_PASSPORT_RF,
                        'series' => $legacySeries,
                        'number' => $legacyNumber,
                        'issued_by' => $patient->getRawOriginal('issued_by'),
                        'issued_at' => $patient->getRawOriginal('issued_at'),
                        'department_code' => $patient->getRawOriginal('department_code'),
                    ],
                ];
            }
        }

        return [
            'patient' => $this->patientValues($patient),
            'document' => [
                'document_type' => $document?->document_type,
                'series' => $document?->series,
                'number' => $document?->number,
                'issued_by' => $document?->issued_by,
                'issued_at' => $document?->issued_at?->format('Y-m-d'),
                'department_code' => $document?->department_code,
            ],
        ];
    }

    private function patientValues(Patient $patient): array
    {
        $values = [];

        foreach (array_keys(self::PATIENT_LABELS) as $field) {
            $values[$field] = $patient->getRawOriginal($field);
        }

        return $values;
    }

    private function normalize(string $field, mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $value = trim((string) $value);

        if (in_array($field, ['birth_at', 'document_issued_at'], true)) {
            return substr($value, 0, 10);
        }

        if ($field === 'email') {
            return mb_strtolower($value);
        }

        if ($field === 'gender') {
            return strtoupper($value);
        }

        if ($field === 'snils') {
            return preg_replace('/\D+/', '', $value);
        }

        if (in_array($field, ['oms', 'document_series', 'document_number'], true)) {
            return strtoupper(preg_replace('/[^0-9A-Z]/i', '', $value));
        }

        return $value;
    }

    private function hasDocumentChanges(array $changedFields): bool
    {
        return count(array_intersect(
            $changedFields,
            array_keys(self::DOCUMENT_LABELS)
        )) > 0;
    }

    private function replacePrimaryDocument(Patient $patient, array $data): void
    {
        $type = trim((string) ($data['document_type'] ?? ''));
        $number = trim((string) ($data['number'] ?? ''));

        if ($type === '' || $number === '') {
            throw ValidationException::withMessages([
                'document_number' => 'Для изменения документа укажите его тип и номер.',
            ]);
        }

        $oldPrimary = $patient->primaryIdentityDocument()->first();

        if ($oldPrimary) {
            $oldPrimary->forceFill([
                'is_primary' => false,
                'is_active' => false,
            ])->save();
        }

        PatientIdentityDocument::query()->create([
            'patient_id' => $patient->id,
            'document_type' => $type,
            'document_name' => PatientIdentityDocument::typeOptions()[$type] ?? 'Иной документ',
            'series' => $data['series'] ?? null,
            'number' => $number,
            'issued_by' => $data['issued_by'] ?? null,
            'issued_at' => $data['issued_at'] ?? null,
            'department_code' => $data['department_code'] ?? null,
            'is_primary' => true,
            'is_active' => true,
        ]);

        if ($type === PatientIdentityDocument::TYPE_PASSPORT_RF) {
            $patient->forceFill([
                'serial' => $data['series'] ?? null,
                'number' => $number,
                'issued_by' => $data['issued_by'] ?? null,
                'issued_at' => $data['issued_at'] ?? null,
                'department_code' => $data['department_code'] ?? null,
            ])->save();
        } else {
            $patient->forceFill([
                'serial' => null,
                'number' => null,
                'issued_by' => null,
                'issued_at' => null,
                'department_code' => null,
            ])->save();
        }
    }

    private function reviewerRole(User $actor): string
    {
        if ($actor->can('adminpanel_see')) {
            return 'administration';
        }

        return $actor->getRoleNames()->first() ?: 'doctor';
    }

    private function recordEvent(
        Patient $patient,
        User $actor,
        string $eventType,
        string $title,
        array $fields,
        array $meta = []
    ): void {
        if (! Schema::hasTable('patient_profile_events')) {
            return;
        }

        $labels = [];

        foreach ($fields as $field) {
            $labels[] = self::PATIENT_LABELS[$field]
                ?? self::DOCUMENT_LABELS[$field]
                ?? $field;
        }

        PatientProfileEvent::query()->create([
            'patient_id' => $patient->id,
            'actor_user_id' => $actor->id,
            'event_type' => $eventType,
            'title' => $title,
            'changes' => $labels
                ? ['Изменённые поля' => ['old' => '—', 'new' => implode(', ', $labels)]]
                : null,
            'meta' => $meta,
            'occurred_at' => now(),
        ]);
    }
}
