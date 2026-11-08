<?php

namespace App\Http\Controllers\Doctors;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Support\HospitalPatientAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Evidence-backed, manually verified disability details for the existing medical card.
 * This DOES NOT query any state registers. All updates require patient-level authorization.
 */
class PatientDisabilityController extends Controller
{
    private const DOCUMENT_SOURCES = ['mse_certificate', 'electronic_mse'];

    public function save(Request $request, Patient $patient, HospitalPatientAccess $access): RedirectResponse
    {
        $this->authorizePatientEdit($request, $patient, $access);

        $validated = $request->validateWithBag('disability', [
            'status' => ['required', Rule::in(['unknown', 'present', 'absent'])],
            'disability_group' => [
                Rule::requiredIf($request->input('status') === 'present'),
                'nullable', Rule::in(['I', 'II', 'III', 'child']),
            ],
            'established_at' => ['nullable', 'date', 'before_or_equal:today'],
            'valid_until' => ['nullable', 'date', 'after_or_equal:established_at'],
            'indefinite' => ['sometimes', 'boolean'],
            'cause' => ['nullable', 'string', 'max:500'],
            'source_type' => ['nullable', Rule::in([
                'patient_statement', 'gosuslugi_screen',
                'mse_certificate', 'electronic_mse',
                'medical_document', 'other',
            ])],
            'document_name' => ['nullable', 'string', 'max:255'],
            'document_number' => ['nullable', 'string', 'max:100'],
            'document_issued_at' => ['nullable', 'date', 'before_or_equal:today'],
            'issued_by' => ['nullable', 'string', 'max:255'],
            'evidence_document_id' => ['nullable', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $documentId = !empty($validated['evidence_document_id'])
            ? (int) $validated['evidence_document_id'] : null;
        if ($documentId !== null && !$patient->documents()->whereKey($documentId)->exists()) {
            throw ValidationException::withMessages([
                'evidence_document_id' => 'Документ не найден в медицинской карте этого пациента.',
            ])->errorBag('disability');
        }

        $status = (string) $validated['status'];
        $isPresent = $status === 'present';
        $fields = [
            'status' => $status,
            'disability_group' => $isPresent ? ($validated['disability_group'] ?? null) : null,
            'established_at' => $isPresent ? ($validated['established_at'] ?? null) : null,
            'valid_until' => $isPresent && !$request->boolean('indefinite')
                ? ($validated['valid_until'] ?? null) : null,
            'indefinite' => $isPresent && $request->boolean('indefinite'),
            'cause' => $isPresent ? ($validated['cause'] ?? null) : null,
            'source_type' => $isPresent ? ($validated['source_type'] ?? 'patient_statement') : 'patient_statement',
            'document_name' => $isPresent ? ($validated['document_name'] ?? null) : null,
            'document_number' => $isPresent ? ($validated['document_number'] ?? null) : null,
            'document_issued_at' => $isPresent ? ($validated['document_issued_at'] ?? null) : null,
            'issued_by' => $isPresent ? ($validated['issued_by'] ?? null) : null,
            'evidence_document_id' => $isPresent ? $documentId : null,
            'notes' => $isPresent ? ($validated['notes'] ?? null) : null,
        ];

        DB::transaction(function () use ($patient, $request, $fields, $isPresent): void {
            // Lock the patient row to serialize first-insert and successive updates.
            $lockedPatient = Patient::query()->whereKey($patient->getKey())
                ->lockForUpdate()->firstOrFail();
            $existing = DB::table('patient_disability_records')
                ->where('patient_id', $lockedPatient->getKey())->lockForUpdate()->first();

            $changed = [];
            foreach ($fields as $key => $newValue) {
                $previous = $existing?->{$key};
                // SQL booleans may be returned as 0/1; normalize them explicitly.
                if ($key === 'indefinite') {
                    if ($this->boolFromDatabase($previous) !== (bool) $newValue) {
                        $changed[] = $key;
                    }
                } elseif (($previous === null ? null : (string) $previous)
                    !== ($newValue === null ? null : (string) $newValue)) {
                    $changed[] = $key;
                }
            }

            if ($existing === null) {
                $id = DB::table('patient_disability_records')->insertGetId(array_merge($fields, [
                    'patient_id' => $lockedPatient->getKey(),
                    'created_by_user_id' => $request->user()->getKey(),
                    'updated_by_user_id' => $request->user()->getKey(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            } else {
                $id = (int) $existing->id;
                if ($changed !== []) {
                    // Any substantive change requires a new manual document review.
                    DB::table('patient_disability_records')->where('id', $id)
                        ->update(array_merge($fields, [
                            'verified_at' => null,
                            'verified_by_user_id' => null,
                            'updated_by_user_id' => $request->user()->getKey(),
                            'updated_at' => now(),
                        ]));
                }
            }

            if ($changed !== [] || $existing === null) {
                DB::table('patient_disability_audits')->insert([
                    'patient_disability_record_id' => $id,
                    'user_id' => $request->user()->getKey(),
                    'action' => 'saved',
                    'changed_fields' => json_encode($changed, JSON_THROW_ON_ERROR),
                    'created_at' => now(),
                ]);
            }

            // Keep the legacy checkmark in sync without treating it as verified evidence.
            if ($this->boolFromDatabase($lockedPatient->disability) !== $isPresent) {
                $lockedPatient->disability = $isPresent;
                $lockedPatient->save();
            }
        });

        return redirect()->route('doctors.patients.medical_card', [
            'patient' => $patient->getKey(), 'tab' => 'patient-info',
        ])->with('success', 'Сведения об инвалидности сохранены. Документ необходимо подтвердить отдельно.');
    }

    public function verification(Request $request, Patient $patient, HospitalPatientAccess $access): RedirectResponse
    {
        $this->authorizePatientEdit($request, $patient, $access);

        $data = $request->validateWithBag('disability', [
            'action' => ['required', Rule::in(['confirm', 'revoke'])],
            'reviewed_document' => ['required_if:action,confirm', 'nullable', 'accepted'],
        ]);

        DB::transaction(function () use ($request, $patient, $data): void {
            $lockedPatient = Patient::query()->whereKey($patient->getKey())
                ->lockForUpdate()->firstOrFail();
            $record = DB::table('patient_disability_records')
                ->where('patient_id', $lockedPatient->getKey())->lockForUpdate()->first();
            abort_unless($record, 404, 'Сведения об инвалидности не внесены.');

            if ($data['action'] === 'confirm') {
                $validUntil = $record->valid_until;
                $expired = !$this->boolFromDatabase($record->indefinite)
                    && (!$validUntil || (string) $validUntil < today()->toDateString());
                $documentExists = $record->evidence_document_id
                    && $lockedPatient->documents()->whereKey($record->evidence_document_id)->exists();
                $hasPaperDetails = filled($record->document_number) && filled($record->issued_by);
                // A screen from Gosuslugi is NOT a signed electronic MSE certificate.
                // For electronic MSE evidence a file must be attached to this patient;
                // for a checked paper original, its identifying details are sufficient.
                $hasVerifiedSource = ($record->source_type === 'mse_certificate'
                        && ($documentExists || $hasPaperDetails))
                    || ($record->source_type === 'electronic_mse' && $documentExists);
                $canConfirm = $record->status === 'present'
                    && filled($record->disability_group)
                    && filled($record->established_at)
                    && in_array($record->source_type, self::DOCUMENT_SOURCES, true)
                    && !$expired
                    && $hasVerifiedSource;
                if (!$canConfirm) {
                    throw ValidationException::withMessages([
                        'verification' => 'Нужны группа, дата установления, действующий срок (или «Бессрочно»). Для бумажной справки МСЭ — реквизиты или файл; для электронной справки МСЭ — сам электронный документ во вкладке «Документы» и ручная проверка его подписи. Скриншот Госуслуг не подтверждает документ.',
                    ])->errorBag('disability');
                }

                DB::table('patient_disability_records')->where('id', $record->id)
                    ->update([
                        'verified_at' => now(),
                        'verified_by_user_id' => $request->user()->getKey(),
                        'updated_at' => now(),
                        'updated_by_user_id' => $request->user()->getKey(),
                    ]);
            } else {
                DB::table('patient_disability_records')->where('id', $record->id)
                    ->update([
                        'verified_at' => null,
                        'verified_by_user_id' => null,
                        'updated_at' => now(),
                        'updated_by_user_id' => $request->user()->getKey(),
                    ]);
            }

            DB::table('patient_disability_audits')->insert([
                'patient_disability_record_id' => $record->id,
                'user_id' => $request->user()->getKey(),
                'action' => $data['action'] === 'confirm' ? 'verified' : 'revoked',
                'changed_fields' => json_encode(['verified_at', 'verified_by_user_id'], JSON_THROW_ON_ERROR),
                'created_at' => now(),
            ]);
        });

        return redirect()->route('doctors.patients.medical_card', [
            'patient' => $patient->getKey(), 'tab' => 'patient-info',
        ])->with('success', $data['action'] === 'confirm'
            ? 'Проверка документа МСЭ зафиксирована сотрудником. Это не автоматическая проверка в госреестре.'
            : 'Отметка о проверке документа МСЭ отозвана.');
    }

    /** PostgreSQL/PDO may return booleans as native bool, 0/1 or t/f strings. */
    private function boolFromDatabase(mixed $value): bool
    {
        return in_array($value, [true, 1, '1', 't', 'true'], true);
    }

    private function authorizePatientEdit(Request $request, Patient $patient, HospitalPatientAccess $access): void
    {
        abort_unless($request->user() && $access->canRead($request->user(), $patient)
            && $access->canEdit($request->user(), $patient), 403);
        abort_unless(Schema::hasTable('patient_disability_records')
            && Schema::hasTable('patient_disability_audits'), 503,
            'Сначала выполните миграцию сведений об инвалидности.');
    }
}
