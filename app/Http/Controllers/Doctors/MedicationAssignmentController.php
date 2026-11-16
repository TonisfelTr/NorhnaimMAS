<?php

namespace App\Http\Controllers\Doctors;

use App\Http\Controllers\Controller;
use App\Models\ContraindicationsType;
use App\Models\Drug;
use App\Models\Patient;
use App\Models\PatientCondition;
use App\Models\PatientMedicationAssignment;
use App\Support\HospitalPatientAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class MedicationAssignmentController extends Controller
{
    public function store(
        Request $request,
        Patient $patient,
        HospitalPatientAccess $access
    ): RedirectResponse {
        $doctor = $this->doctorFor($request, $patient, $access);

        $data = $request->validate([
            'drug_id' => ['required', 'integer', 'exists:drugs,id'],
            'starts_on' => ['required', 'date_format:Y-m-d'],
            'ends_on' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:starts_on'],
            'dosage' => ['required', 'string', 'max:255'],
            'drug_form' => ['nullable', 'string', 'max:255'],
            'dosage_unit' => ['nullable', 'string', 'max:50'],
            'schedule' => ['required', 'string', 'max:4000'],
            'doctor_instructions' => ['required', 'string', 'max:12000'],
            'indication_text' => ['required', 'string', 'max:8000'],
            'contraindications_text' => ['required', 'string', 'max:12000'],
            'side_effects_text' => ['nullable', 'string', 'max:12000'],
            'missed_dose_instructions' => ['nullable', 'string', 'max:8000'],
            'overdose_actions' => ['required', 'string', 'max:12000'],
            'warning_signs' => ['nullable', 'string', 'max:12000'],
        ], [
            'doctor_instructions.required' => 'Заполните индивидуальную инструкцию пациенту.',
            'indication_text.required' => 'Укажите, по какому показанию назначен препарат.',
            'contraindications_text.required' => 'Укажите противопоказания и ограничения, которые пациент должен учитывать.',
            'overdose_actions.required' => 'Обязательно укажите действия пациента при подозрении на передозировку.',
        ]);

        $drug = Drug::query()->findOrFail((int) $data['drug_id']);
        $clinic = $doctor->clinic;

        PatientMedicationAssignment::query()->create([
            'patient_id' => (int) $patient->id,
            'drug_id' => (int) $drug->id,
            'doctor_id' => (int) $doctor->id,
            'clinic_id' => $clinic?->id ? (int) $clinic->id : null,
            'created_by_user_id' => $request->user()?->id,
            'drug_name_snapshot' => (string) $drug->name,
            'generic_name_snapshot' => (string) ($drug->latin_name ?? ''),
            'doctor_name_snapshot' => $this->doctorName($doctor),
            'clinic_name_snapshot' => $clinic?->name,
            'status' => PatientMedicationAssignment::STATUS_ACTIVE,
            'starts_on' => $data['starts_on'],
            'ends_on' => $data['ends_on'] ?? null,
            'dosage' => trim((string) $data['dosage']),
            'drug_form' => $data['drug_form'] ?? $request->input('drug_form'),
            'dosage_unit' => $data['dosage_unit'] ?? $request->input('dosage_unit'),
            'schedule' => trim((string) $data['schedule']),
            'doctor_instructions' => trim((string) $data['doctor_instructions']),
            'indication_text' => trim((string) $data['indication_text']),
            'contraindications_text' => trim((string) $data['contraindications_text']),
            'side_effects_text' => $this->nullableText($data['side_effects_text'] ?? null),
            'missed_dose_instructions' => $this->nullableText($data['missed_dose_instructions'] ?? null),
            'overdose_actions' => trim((string) $data['overdose_actions']),
            'warning_signs' => $this->nullableText($data['warning_signs'] ?? null),
            'reference_snapshot' => $this->referenceSnapshot($patient, $drug),
        ]);

        return redirect()
            ->route('doctors.patients.medical_card', [
                'patient' => $patient->id,
                'tab' => 'presc',
            ])
            ->with('success', 'Назначение сохранено и доступно пациенту в личном кабинете.');
    }

    public function updateStatus(
        Request $request,
        Patient $patient,
        PatientMedicationAssignment $assignment,
        HospitalPatientAccess $access
    ): RedirectResponse {
        $doctor = $this->doctorFor($request, $patient, $access);

        abort_unless(
            (int) $assignment->patient_id === (int) $patient->id,
            404
        );

        // Only the authoring doctor or a doctor from the same captured clinic
        // may finish/cancel the assignment.
        abort_unless(
            (int) $assignment->doctor_id === (int) $doctor->id
            || (
                $assignment->clinic_id
                && (int) $assignment->clinic_id === (int) $doctor->clinic_id
            ),
            403
        );

        $data = $request->validate([
            'status' => [
                'required',
                Rule::in([
                    PatientMedicationAssignment::STATUS_COMPLETED,
                    PatientMedicationAssignment::STATUS_CANCELLED,
                ]),
            ],
        ]);

        $assignment->status = $data['status'];
        $assignment->completed_at = $data['status'] === PatientMedicationAssignment::STATUS_COMPLETED
            ? now()
            : null;
        $assignment->cancelled_at = $data['status'] === PatientMedicationAssignment::STATUS_CANCELLED
            ? now()
            : null;
        $assignment->save();

        return redirect()
            ->route('doctors.patients.medical_card', [
                'patient' => $patient->id,
                'tab' => 'presc',
            ])
            ->with(
                'success',
                $data['status'] === PatientMedicationAssignment::STATUS_COMPLETED
                    ? 'Назначение отмечено как завершённое.'
                    : 'Назначение отменено.'
            );
    }

    public function reference(
        Request $request,
        Patient $patient,
        Drug $drug,
        HospitalPatientAccess $access
    ): JsonResponse {
        $this->doctorFor($request, $patient, $access);

        return response()->json(
            $this->referenceSnapshot($patient, $drug)
        )->header('Cache-Control', 'private, no-store');
    }

    private function doctorFor(
        Request $request,
        Patient $patient,
        HospitalPatientAccess $access
    ) {
        $user = $request->user();

        abort_unless(
            $user
            && $user->hasRole('doctors')
            && $access->canRead($user, $patient),
            403
        );

        return $user->doctor()->with('clinic')->firstOrFail();
    }

    private function referenceSnapshot(
        Patient $patient,
        Drug $drug
    ): array {
        $indications = DB::table('medicine_indications')
            ->join(
                'diagnoses',
                'diagnoses.id',
                '=',
                'medicine_indications.diagnose_id'
            )
            ->where('medicine_indications.medicine_id', (int) $drug->id)
            ->orderBy('diagnoses.code')
            ->get([
                'diagnoses.id',
                'diagnoses.code',
                'diagnoses.title',
                'medicine_indications.indicated_in_russia',
                'medicine_indications.indicated_by_fda',
            ])
            ->map(static fn ($row): array => [
                'id' => (int) $row->id,
                'code' => (string) $row->code,
                'title' => (string) $row->title,
                'russia' => (bool) $row->indicated_in_russia,
                'fda' => (bool) $row->indicated_by_fda,
            ])
            ->values()
            ->all();

        $contraindications = DB::table('medicine_contraindications')
            ->join(
                'contraindications_types',
                'contraindications_types.id',
                '=',
                'medicine_contraindications.contraindication_id'
            )
            ->where('medicine_contraindications.drug_id', (int) $drug->id)
            ->orderBy('medicine_contraindications.type')
            ->orderBy('contraindications_types.name')
            ->get([
                'contraindications_types.id',
                'contraindications_types.name',
                'contraindications_types.description',
                'medicine_contraindications.type',
            ])
            ->map(static fn ($row): array => [
                'id' => (int) $row->id,
                'name' => (string) $row->name,
                'description' => (string) ($row->description ?? ''),
                'type' => (string) $row->type,
                'type_label' => (string) $row->type === '1'
                    ? 'Абсолютное'
                    : 'Относительное',
            ])
            ->values()
            ->all();

        $sideEffects = DB::table('medicine_side_effects')
            ->join(
                'side_effects',
                'side_effects.id',
                '=',
                'medicine_side_effects.side_effect_id'
            )
            ->where('medicine_side_effects.drug_id', (int) $drug->id)
            ->orderBy('side_effects.name')
            ->get([
                'side_effects.id',
                'side_effects.name',
                'side_effects.description',
            ])
            ->map(static fn ($row): array => [
                'id' => (int) $row->id,
                'name' => (string) $row->name,
                'description' => (string) ($row->description ?? ''),
            ])
            ->values()
            ->all();

        $patientWarnings = $this->patientContraindicationMatches(
            $patient,
            collect($contraindications)
        );

        return [
            'drug' => [
                'id' => (int) $drug->id,
                'name' => (string) $drug->name,
                'latin_name' => (string) ($drug->latin_name ?? ''),
                'group' => $drug->groupName(),
            ],
            'indications' => $indications,
            'contraindications' => $contraindications,
            'side_effects' => $sideEffects,
            'patient_warnings' => $patientWarnings,
            'pregnancy_allowed' => (bool) ($drug->pregnancy ?? false),
            'lactation_allowed' => (bool) ($drug->lactation ?? false),
            'generated_at' => now()->toIso8601String(),
        ];
    }

    private function patientContraindicationMatches(
        Patient $patient,
        $contraindications
    ): array {
        $warnings = [];

        $conditionNames = PatientCondition::query()
            ->where('patient_id', (int) $patient->id)
            ->where('status', 'active')
            ->with('condition:id,name')
            ->get()
            ->pluck('condition.name')
            ->filter()
            ->map(static fn ($name): string =>
                mb_strtolower(trim((string) $name))
            )
            ->filter()
            ->unique()
            ->values();

        foreach ($contraindications as $contraindication) {
            $name = mb_strtolower(
                trim((string) ($contraindication['name'] ?? ''))
            );

            if ($name !== '' && $conditionNames->contains($name)) {
                $warnings[] = [
                    'type' => 'condition',
                    'label' => (string) $contraindication['name'],
                    'message' => 'Совпадает с активным состоянием пациента.',
                ];
            }
        }

        if ($patient->birth_at) {
            $age = Carbon::parse($patient->birth_at)->age;

            foreach ($contraindications as $contraindication) {
                $name = (string) ($contraindication['name'] ?? '');

                if (
                    preg_match(
                        '/возраст\s+до\s+(\d{1,3})/ui',
                        $name,
                        $matches
                    ) === 1
                    && $age < (int) $matches[1]
                ) {
                    $warnings[] = [
                        'type' => 'age',
                        'label' => $name,
                        'message' => 'Возраст пациента: ' . $age . '.',
                    ];
                }
            }
        }

        return collect($warnings)
            ->unique(static fn (array $warning): string =>
                $warning['type'] . '|' . $warning['label']
            )
            ->values()
            ->all();
    }

    private function doctorName($doctor): string
    {
        return trim(implode(' ', array_filter([
            $doctor->surname,
            $doctor->name,
            $doctor->patronym,
        ]))) ?: 'Врач #' . $doctor->id;
    }

    private function nullableText(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value !== '' ? $value : null;
    }
}
