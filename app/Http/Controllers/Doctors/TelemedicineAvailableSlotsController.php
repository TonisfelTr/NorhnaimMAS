<?php

namespace App\Http\Controllers\Doctors;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\Patient;
use App\Services\TelemedicineAvailableSlotService;
use App\Support\HospitalPatientAccess;
use App\Support\TelemedicineJournalAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TelemedicineAvailableSlotsController extends Controller
{
    public function __invoke(
        Request $request,
        Patient $patient,
        TelemedicineAvailableSlotService $slots,
        TelemedicineJournalAccess $telemedicineAccess,
        HospitalPatientAccess $patientAccess
    ): JsonResponse {
        $user = $request->user();
        abort_unless($user, 403);

        abort_unless(
            $telemedicineAccess->canManage($user, $patient)
                || $patientAccess->canEdit($user, $patient),
            403,
            'Нет права управлять записями этого пациента.'
        );

        $values = $request->validate([
            'doctor_id' => ['bail', 'required', 'integer', 'min:1'],
            'date' => ['bail', 'required', 'date_format:Y-m-d'],
            'telemed_duration_minutes' => [
                'bail',
                'required',
                'integer',
                'between:5,480',
                'multiple_of:5',
            ],
            'days' => ['nullable', 'integer', 'between:1,14'],
        ]);

        $context = $telemedicineAccess->patientContext($patient);

        if (!($context['valid'] ?? false)) {
            throw ValidationException::withMessages([
                'date' => (string) (
                    $context['reason']
                    ?? 'Не удалось определить клинику пациента.'
                ),
            ]);
        }

        $clinicId = (int) ($context['clinic_id'] ?? 0);

        if ($clinicId <= 0) {
            throw ValidationException::withMessages([
                'date' => 'Для подбора времени должна быть определена клиника пациента.',
            ]);
        }

        $doctor = Doctor::query()
            ->with('clinic')
            ->where('clinic_id', $clinicId)
            ->findOrFail((int) $values['doctor_id']);

        if (
            class_exists(\App\Services\ClinicTelemedicineSettings::class)
            && (
                !$doctor->clinic
                || !\App\Services\ClinicTelemedicineSettings::enabled($doctor->clinic)
            )
        ) {
            throw ValidationException::withMessages([
                'doctor_id' => 'Телемедицинский формат отключён для выбранной клиники.',
            ]);
        }

        return response()
            ->json(
                $slots->calendar(
                    $doctor,
                    $patient,
                    $clinicId,
                    (string) $values['date'],
                    (int) $values['telemed_duration_minutes'],
                    (int) ($values['days'] ?? 7)
                )
            )
            ->header('Cache-Control', 'private, no-store');
    }
}
