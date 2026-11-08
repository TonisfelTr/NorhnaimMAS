<?php

declare(strict_types=1);

namespace App\Http\Controllers\Doctors;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\User;
use App\Services\PatientDoctorTransferService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class PatientDoctorTransferController extends Controller
{
    public function candidates(
        Request $request,
        Patient $patient,
        PatientDoctorTransferService $transfer
    ): JsonResponse {
        /** @var User|null $user */
        $user = $request->user();
        abort_unless($user && $transfer->canTransfer($user, $patient), 403);

        $data = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'selected_id' => ['nullable', 'integer', 'min:1'],
        ]);

        $doctors = $transfer->searchDoctors(
            $patient,
            (string) ($data['q'] ?? ''),
            isset($data['selected_id']) ? (int) $data['selected_id'] : null
        );

        return response()->json(['doctors' => $doctors]);
    }

    public function store(
        Request $request,
        Patient $patient,
        PatientDoctorTransferService $transfer
    ): RedirectResponse {
        /** @var User|null $user */
        $user = $request->user();
        abort_unless($user && $transfer->canTransfer($user, $patient), 403);

        $data = $request->validateWithBag('patientTransfer', [
            'doctor_id' => ['required', 'integer', 'min:1'],
            'expected_doctor_id' => ['required', 'integer', 'min:1'],
            'reason' => ['required', 'string', 'min:5', 'max:1000'],
            'confirm_transfer' => ['accepted'],
        ]);

        // Lock защищает от одновременной передачи: до записи истории и нового
        // doctor_id повторно проверяются права и исходное назначение.
        DB::transaction(function () use ($patient, $user, $data, $transfer): void {
            $lockedPatient = Patient::query()
                ->whereKey($patient->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            abort_unless($transfer->canTransfer($user, $lockedPatient), 403);

            if ((int) $lockedPatient->doctor_id !== (int) $data['expected_doctor_id']) {
                throw ValidationException::withMessages([
                    'doctor_id' => 'Лечащий врач пациента уже изменился. Обновите медицинскую карту перед передачей.',
                ])->errorBag('patientTransfer');
            }

            $target = $transfer->eligibleDoctors($lockedPatient)
                ->where('doctors.id', (int) $data['doctor_id'])
                ->first();

            if (! $target) {
                throw ValidationException::withMessages([
                    'doctor_id' => 'Выберите другого врача той же клиники с учётной записью, имеющей роль doctors.',
                ])->errorBag('patientTransfer');
            }

            $previousDoctorId = (int) $lockedPatient->doctor_id;
            $lockedPatient->doctor_id = $target->getKey();
            $lockedPatient->save();

            // Сохраняем историю в той же транзакции, что и назначение врача.
            DB::table('patient_doctor_transfers')->insert([
                'patient_id' => $lockedPatient->getKey(),
                'from_doctor_id' => $previousDoctorId,
                'to_doctor_id' => $target->getKey(),
                'performed_by_user_id' => $user->getKey(),
                'reason' => trim($data['reason']),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }, 3);

        return redirect()
            ->route('doctors.patients.medical_card', ['patient' => $patient->getKey()])
            ->with('patient_transfer_success', 'Пациент передан другому лечащему врачу. Изменение записано в историю.');
    }
}
