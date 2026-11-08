<?php

namespace App\Http\Controllers\Doctors;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\MedicalEncounter;
use App\Models\Patient;
use App\Models\RegistryRecord;
use App\Support\HospitalPatientAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class MedicalEncounterController extends Controller
{
    /**
     * Mark actual attendance and the clinical outcome independently.
     * Never infer either from the appointment date, the old appointment flag,
     * or an unverified WebRTC connection.
     */
    public function save(
        Request $request,
        Patient $patient,
        RegistryRecord $registryRecord,
        HospitalPatientAccess $access
    ): RedirectResponse {
        $this->authorizeForRecord($request, $patient, $registryRecord, $access, true);

        // A booking is not a performed consultation. Nothing (including a
        // no-show or clinical note) can be recorded before its scheduled start.
        // Enforce this on every POST; hiding the form in Blade is not security.
        $scheduledRaw = $registryRecord->getRawOriginal('for_datetime');
        abort_if(blank($scheduledRaw), 409, 'У записи нет даты и времени приёма. Исправьте запись в регистратуре.');

        try {
            // Registry timestamps are clinic/application-local (no UTC suffix).
            $scheduledAt = Carbon::parse($scheduledRaw, config('app.timezone'));
        } catch (\Throwable) {
            abort(409, 'Некорректная дата приёма. Исправьте запись в регистратуре.');
        }

        abort_if(
            $scheduledAt->isFuture(),
            409,
            'До назначенного времени приёма (' . $scheduledAt->format('d.m.Y H:i') . ') '
            . 'нельзя отмечать явку, результат консультации и заполнять протокол.'
        );

        abort_unless(Schema::hasTable('medical_encounters'), 503, 'Выполните миграции журнала приёмов.');

        $data = $request->validateWithBag('encounter', [
            'attendance_status' => ['required', Rule::in(['unknown', 'present', 'absent'])],
            'consultation_status' => ['required', Rule::in(['not_started', 'in_progress', 'completed', 'not_held'])],
            'visit_type' => ['nullable', Rule::in(['initial', 'repeat'])],
            'noncompletion_reason' => ['nullable', 'string', 'max:1000'],
            'patient_condition' => ['nullable', 'string', 'max:255'],
            'disability_group' => ['nullable', 'string', 'max:50'],
            'disability_details' => ['nullable', 'string', 'max:500'],
            'complaints' => ['nullable', 'string', 'max:8000'],
            'disease_history' => ['nullable', 'string', 'max:16000'],
            'life_history' => ['nullable', 'string', 'max:16000'],
            'social_history' => ['nullable', 'string', 'max:8000'],
            'mental_status' => ['nullable', 'string', 'max:16000'],
            'diagnosis_code' => ['nullable', 'string', 'max:24'],
            'diagnosis_title' => ['nullable', 'string', 'max:255'],
            'conclusion' => ['nullable', 'string', 'max:16000'],
            'recommendations' => ['nullable', 'string', 'max:16000'],
            'service_code' => ['nullable', 'string', 'max:32'],
            'service_name' => ['nullable', 'string', 'max:255'],
        ]);

        // A no-show is a failed consultation, never an examination.
        // Client-side controls mirror this rule, but the server is authoritative.
        if ($data['attendance_status'] === 'absent'
            && $data['consultation_status'] !== 'not_held') {
            throw ValidationException::withMessages([
                'consultation_status' => 'Если пациент не явился или не подключился, укажите «Не состоялась».',
            ])->errorBag('encounter');
        }

        $isClinicalVisit = $data['attendance_status'] === 'present'
            && in_array($data['consultation_status'], ['in_progress', 'completed'], true);

        if (in_array($data['consultation_status'], ['in_progress', 'completed'], true)
            && $data['attendance_status'] !== 'present') {
            throw ValidationException::withMessages([
                'attendance_status' => 'Для проведения консультации необходимо подтвердить явку или подключение пациента.',
            ])->errorBag('encounter');
        }

        // When the visit never took place, a reason is enough. Do not request
        // examination findings, diagnoses or services for a no-show.
        if ($data['consultation_status'] === 'not_held'
            && $data['attendance_status'] !== 'absent'
            && blank($data['noncompletion_reason'] ?? null)) {
            throw ValidationException::withMessages([
                'noncompletion_reason' => 'Укажите причину несостоявшейся консультации.',
            ])->errorBag('encounter');
        }

        if ($data['consultation_status'] === 'completed') {
            $errors = [];
            if ($data['attendance_status'] !== 'present') {
                $errors['attendance_status'] = 'Сначала подтвердите присутствие пациента (для видео — подключение).';
            }
            if (blank($data['visit_type'] ?? null)) {
                $errors['visit_type'] = 'Укажите первичный или повторный приём.';
            }
            if (blank($data['conclusion'] ?? null)) {
                $errors['conclusion'] = 'Для состоявшейся консультации необходимо заполнить заключение.';
            }
            if ($errors !== []) {
                throw ValidationException::withMessages($errors)->errorBag('encounter');
            }
        }

        // No examination can be created from a missed visit. Ignore clinical
        // fields even if they are submitted manually in a forged request.
        // Already saved clinical text is never silently erased: changing an
        // erroneous status can be corrected later, with every change audited.
        $clinicalFields = [
            'visit_type', 'patient_condition', 'disability_group', 'disability_details',
            'complaints', 'disease_history', 'life_history', 'social_history',
            'mental_status', 'diagnosis_code', 'diagnosis_title', 'conclusion',
            'recommendations', 'service_code', 'service_name',
        ];

        if (!$isClinicalVisit) {
            foreach ($clinicalFields as $field) {
                unset($data[$field]);
            }
        }

        if ($data['consultation_status'] !== 'not_held') {
            $data['noncompletion_reason'] = null;
        }

        $currentUserId = (int) $request->user()->getKey();

        DB::transaction(static function () use ($registryRecord, $data, $currentUserId): void {
            $encounter = MedicalEncounter::query()->firstOrNew([
                'registry_record_id' => $registryRecord->getKey(),
            ]);

            $encounter->fill($data);
            $encounter->updated_by_user_id = $currentUserId;

            if (!$encounter->exists) {
                $encounter->created_by_user_id = $currentUserId;
            }

            $encounter->completed_at = $data['consultation_status'] === 'completed'
                ? ($encounter->completed_at ?? now())
                : null;

            $changedFields = array_values(array_diff(
                array_keys($encounter->getDirty()),
                ['created_at', 'updated_at']
            ));

            if ($changedFields === []) {
                return;
            }

            $encounter->save();

            // Audit who changed which fields without leaking a second copy of PHI.
            DB::table('medical_encounter_audits')->insert([
                'medical_encounter_id' => $encounter->getKey(),
                'user_id' => $currentUserId,
                'changed_fields' => json_encode($changedFields, JSON_THROW_ON_ERROR),
                'created_at' => now(),
            ]);
        }, 3);

        $notice = match ($data['consultation_status']) {
            'not_held' => $data['attendance_status'] === 'absent'
                ? 'Неявка пациента зафиксирована. Протокол осмотра не требуется.'
                : 'Причина несостоявшейся консультации сохранена. Протокол не требуется.',
            'completed' => 'Консультация и протокол сохранены.',
            'in_progress' => 'Статус консультации и черновик протокола сохранены.',
            default => 'Статус записи сохранён. Протокол не создавался.',
        };

        return redirect()->route('doctors.patients.medical_card', [
            'patient' => $patient->getKey(),
            'tab' => 'visits',
            'visit_open' => $registryRecord->getKey(),
        ])->with('active_tab', 'visits')
          ->with('visit_notice', $notice);
    }

    /** A printable draft only. Digital signature integration is separate. */
    public function printProtocol(
        Request $request,
        Patient $patient,
        RegistryRecord $registryRecord,
        HospitalPatientAccess $access
    ): View {
        $this->authorizeForRecord($request, $patient, $registryRecord, $access, false);

        $encounter = MedicalEncounter::query()
            ->where('registry_record_id', $registryRecord->getKey())
            ->firstOrFail();

        // A nonattendance record is not a medical consultation protocol.
        abort_unless(
            $encounter->attendance_status === 'present'
            && $encounter->consultation_status === 'completed'
            && filled($encounter->conclusion),
            404
        );

        $registryRecord->load('doctor');
        $clinic = $registryRecord->clinic_id
            ? Clinic::query()->find($registryRecord->clinic_id)
            : null;

        $doctor = $registryRecord->doctor;
        $doctorName = trim(implode(' ', array_filter([
            $doctor?->surname, $doctor?->name, $doctor?->patronym,
        ], static fn ($part): bool => filled($part))));
        $dateRaw = $registryRecord->getRawOriginal('for_datetime');
        $appointmentAt = $dateRaw ? Carbon::parse($dateRaw)->format('d.m.Y H:i') : '—';
        $format = Schema::hasColumn('registry_records', 'appointment_format')
            ? $registryRecord->getAttribute('appointment_format') : null;

        return view('doctors.reception.visit_protocol_print', [
            'patient' => $patient,
            'record' => $registryRecord,
            'encounter' => $encounter,
            'clinic' => $clinic,
            'doctorName' => $doctorName !== '' ? $doctorName : 'Не указан',
            'appointmentAt' => $appointmentAt,
            'appointmentFormatLabel' => $format === 'telemedicine'
                ? 'Телемедицина' : ($format === 'in_person' ? 'Очный приём' : 'Не указан'),
        ]);
    }

    private function authorizeForRecord(
        Request $request,
        Patient $patient,
        RegistryRecord $registryRecord,
        HospitalPatientAccess $access,
        bool $editing
    ): void {
        $user = $request->user();
        abort_unless($user !== null, 403);

        abort_unless((int) $registryRecord->patient_id === (int) $patient->getKey(), 404);
        if ($editing) {
            abort_if($registryRecord->trashed(), 409, 'Нельзя менять протокол удалённой записи.');
            abort_unless($access->canEdit($user, $patient), 403);
        } else {
            abort_unless($access->canRead($user, $patient), 403);
        }

        // The debug bootstrap admin has cross-clinic access only as already
        // authorized by HospitalPatientAccess; doctors remain clinic-scoped.
        if (!$access->isDebugAdmin($user)) {
            $doctor = $user->doctor()->first();
            abort_unless($doctor !== null, 403);

            $sameClinic = $doctor->clinic_id !== null
                && (int) $registryRecord->clinic_id === (int) $doctor->clinic_id;
            $privateAppointment = $doctor->clinic_id === null
                && (int) $registryRecord->doctor_id === (int) $doctor->getKey();

            abort_unless($sameClinic || $privateAppointment, 403);
        }
    }
}
