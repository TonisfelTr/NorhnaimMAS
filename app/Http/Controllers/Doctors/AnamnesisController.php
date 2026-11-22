<?php

namespace App\Http\Controllers\Doctors;

use App\Http\Controllers\Controller;
use App\Http\Requests\AnamnesesStoreRequest;
use App\Models\Anamnesis;
use App\Models\Diagnose;
use App\Models\Patient;
use App\Models\PatientSymptom;
use App\Models\PatientDiagnosisHistory;
use App\Services\PatientClinicDiagnosisService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Models\Symptom;
use Illuminate\Http\Request;

class AnamnesisController extends Controller
{
    public function show(Request $request, Patient $patient, Anamnesis $anamnesis)
    {
        abort_if($anamnesis->patient_id !== $patient->id, 403);
        $symptoms = $anamnesis->symptoms()->get();

        return view('doctors.reception.anamnesis', compact('anamnesis', 'patient', 'symptoms'));
    }

    public function store(AnamnesesStoreRequest $request, Patient $patient)
    {
        $user = $request->user();
        $doctor = $user?->doctor()->first();
        $clinicId = (int) ($doctor?->clinic_id ?? 0);

        // Do not allow a diagnosis to be assigned to an arbitrary patient or clinic.
        abort_unless($doctor && $clinicId > 0 && $patient->clinicMemberships()
            ->where('clinic_id', $clinicId)
            ->where('is_active', true)
            ->exists(), 403);

        $payload = $request->payload();
        $diagnosisCode = trim((string) (
            $request->input('diagnosis_code') ?: ($payload['diagnosis_code'] ?? '')
        ));
        $diagnose = $diagnosisCode !== ''
            ? Diagnose::query()->where('code', $diagnosisCode)->first()
            : null;

        $setAsCurrent = $request->boolean('set_as_current')
            || $request->boolean('assign_current');
        if ($setAsCurrent && !$diagnose) {
            throw ValidationException::withMessages([
                'diagnosis_code' => 'Для установки основного диагноза выберите действующий код МКБ-10.',
            ])->errorBag('anamnesis');
        }
        if ($setAsCurrent && $diagnose
            && !PatientClinicDiagnosisService::isAllowedCode((string) $diagnose->code)) {
            throw ValidationException::withMessages([
                'diagnosis_code' => 'Основным диагнозом может быть только код из групп F или G МКБ-10.',
            ])->errorBag('anamnesis');
        }

        DB::transaction(function () use ($request, $patient, $doctor, $clinicId, $diagnose, $setAsCurrent): void {
            if ($setAsCurrent) {
                // Clear flags only for this patient AND this doctor's clinic.
                Anamnesis::query()
                    ->where('patient_id', (int) $patient->getKey())
                    ->where('is_current', true)
                    ->whereHas('doctor', static fn ($query) => $query->where('clinic_id', $clinicId))
                    ->update(['is_current' => false]);
            }

            $anamnesis = new Anamnesis();
            $anamnesis->patient_id = (int) $patient->getKey();
            $anamnesis->doctor_id = (int) $doctor->getKey();
            $anamnesis->text = $request->input('text');
            $anamnesis->category = $request->input('category');
            $anamnesis->title = $request->input('title');
            $anamnesis->source = $request->input('diag_source');
            $anamnesis->diagnose_id = $diagnose?->getKey();
            $anamnesis->is_current = $setAsCurrent;
            $anamnesis->save();

            if ($setAsCurrent) {
                PatientDiagnosisHistory::query()->create([
                    'patient_id' => (int) $patient->getKey(),
                    'diagnose_id' => (int) $diagnose->getKey(),
                    'doctor_id' => (int) $doctor->getKey(),
                    'clinic_id' => $clinicId,
                    'user_id' => (int) $request->user()->getKey(),
                    'doctor_name_snapshot' => trim(implode(' ', array_filter([
                        $doctor->surname, $doctor->name, $doctor->patronym,
                    ]))),
                    'clinic_name_snapshot' => $doctor->clinic?->name,
                    'source' => 'clinic_anamnesis_current',
                    'set_at' => now(),
                ]);
                // NEVER update patients.diagnose_id: it is shared across clinics.
            }

            $symptoms = json_decode((string) $request->input('symptoms_json', '[]'), true);
            if (is_array($symptoms)) {
                $names = array_values(array_filter($symptoms, 'is_string'));
                $ids = $names !== []
                    ? Symptom::query()->whereIn('title', $names)->pluck('id')
                    : collect();
                foreach ($ids as $id) {
                    PatientSymptom::firstOrCreate([
                        'patient_id' => (int) $patient->getKey(),
                        'symptom_id' => (int) $id,
                    ], ['anamnesis_id' => (int) $anamnesis->getKey()]);
                }
            }
        }, 3);

        return redirect()->route('doctors.patients.medical_card', ['patient' => $patient->id])
            ->with('success', 'Анамнез сохранён. Текущий диагноз изменяется только в клинике врача.');
    }
}
