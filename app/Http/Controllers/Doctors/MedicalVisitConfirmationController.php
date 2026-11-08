<?php

namespace App\Http\Controllers\Doctors;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Services\MedicalVisitAuditService;
use App\Support\HospitalPatientAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * A doctor explicitly attests the clinical outcome of a scheduled visit.
 * A WebRTC connection, passed time or patient booking NEVER confirms a visit.
 */
class MedicalVisitConfirmationController extends Controller
{
    public function store(
        Request $request,
        Patient $patient,
        int $registryRecord,
        HospitalPatientAccess $access,
        MedicalVisitAuditService $audit
    ): RedirectResponse {
        $user = $request->user();
        abort_unless($user && $access->canEdit($user, $patient), 403);

        $doctorId = $user->doctor()->first()?->getKey();
        // The person attesting must be the doctor scheduled for THIS appointment.
        abort_unless($doctorId, 403);

        $validator = Validator::make($request->all(), [
            'outcome' => ['required', Rule::in(['held', 'not_held'])],
            'reason' => [Rule::requiredIf($request->input('outcome') === 'not_held'), 'nullable', 'string', 'max:1200'],
            'attested' => [Rule::requiredIf($request->input('outcome') === 'held'), 'accepted'],
            'correction_reason' => ['nullable', 'string', 'max:600'],
        ], [
            'outcome.required' => 'Выберите результат приёма.',
            'reason.required' => 'Для несостоявшегося приёма укажите причину.',
            'attested.required' => 'Подтвердите факт проведения консультации.',
            'attested.accepted' => 'Подтвердите факт проведения консультации.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator, 'visitConfirmation')
                ->withInput()->with('active_tab', 'visits');
        }

        $data = $validator->validated();
        $outcome = $data['outcome'];
        $reason = $outcome === 'not_held' ? trim((string) ($data['reason'] ?? '')) : null;
        $correctionReason = trim((string) ($data['correction_reason'] ?? ''));
        if ($outcome === 'not_held' && $reason === '') {
            return back()->withErrors(['reason' => 'Укажите причину несостоявшегося приёма.'],
                'visitConfirmation')->withInput()->with('active_tab', 'visits');
        }

        // Lock the appointment as well as the optional existing confirmation:
        // simultaneous clicks cannot create two initial confirmations.
        DB::transaction(function () use (
            $audit, $access, $user, $patient, $registryRecord, $doctorId,
            $outcome, $reason, $correctionReason
        ): void {
            $appointment = $audit->visibleAppointments($user, $patient, $access)
                ->whereKey($registryRecord)
                ->whereNull('deleted_at')
                ->lockForUpdate()
                ->firstOrFail();

            abort_unless((int) $appointment->doctor_id === (int) $doctorId, 403);

// clinic-telemedicine-timezones:timezone-confirmation-start:v1:begin
$appointmentStart = \App\Support\ClinicAppointmentTime::displayDate($appointment);
            if (!$appointmentStart || Carbon::instance($appointmentStart)->isFuture()) {
// clinic-telemedicine-timezones:timezone-confirmation-start:v1:end
                $this->validationError('Нельзя подтверждать приём до его запланированного начала.');
            }

            $existing = DB::table('medical_visit_confirmations')
                ->where('registry_record_id', $appointment->getKey())
                ->lockForUpdate()
                ->first();

            $oldOutcome = $existing?->outcome;
            $oldReason = $existing?->reason;
            $changed = $existing !== null
                && ($oldOutcome !== $outcome || (string) $oldReason !== (string) $reason);

            if ($existing !== null && !$changed) {
                return; // Idempotent double-click: do not create a false second event.
            }

            if ($changed && mb_strlen($correctionReason) < 10) {
                $this->validationError('Для исправления ранее подтверждённого результата укажите причину (от 10 символов).', 'correction_reason');
            }

            $now = now();
            $attributes = [
                'patient_id' => $patient->getKey(),
                'doctor_id' => $doctorId,
                'outcome' => $outcome,
                'reason' => $reason,
                'confirmed_by_user_id' => $user->getKey(),
                'confirmed_at' => $now,
                'updated_at' => $now,
            ];

            if ($existing) {
                DB::table('medical_visit_confirmations')
                    ->where('id', $existing->id)->update($attributes);
            } else {
                DB::table('medical_visit_confirmations')->insert(array_merge($attributes, [
                    'registry_record_id' => $appointment->getKey(),
                    'created_at' => $now,
                ]));
            }

            // Append-only correction trail. Do not edit/delete old events.
            $audit->appendEvent(
                (int) $appointment->getKey(),
                (int) $patient->getKey(),
                $outcome === 'held' ? 'consultation_confirmed' : 'consultation_not_held',
                'clinician',
                'doctor',
                (int) $user->getKey(),
                null,
                [
                    'previous_outcome' => $oldOutcome,
                    'new_outcome' => $outcome,
                    'is_correction' => $changed,
                    'correction_reason' => $changed ? $correctionReason : null,
                ],
                $now
            );
        });

        return back()->with('visit_confirmation_success',
            $outcome === 'held' ? 'Врач подтвердил, что приём состоялся.' : 'Зафиксировано, что приём не состоялся.'
        )->with('active_tab', 'visits');
    }

    private function validationError(string $message, string $field = 'outcome'): never
    {
        session()->flash('active_tab', 'visits');
        throw ValidationException::withMessages([$field => $message])
            ->errorBag('visitConfirmation');
    }
}
