<?php

namespace App\Services\MedicalDocuments;

use App\Models\LabParameter;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

final class PatientMedicalContextService
{
    public function build(Patient $patient): array
    {
        $patient->loadMissing(['doctor', 'diagnose']);

        return [
            'patient' => $this->patientBlock($patient),
            'current_diagnosis' => $this->diagnosisBlock($patient),
            'anamneses' => $this->anamneses($patient),
            'epicrises' => $this->epicrises($patient),
            'symptoms' => $this->symptoms($patient),
            'conditions' => $this->conditions($patient),
            'prescriptions' => $this->prescriptions($patient),
            'laboratory_researches' => $this->laboratoryResearches($patient),
            'instrumental_researches' => $this->instrumentalResearches($patient),
            'tests' => $this->tests($patient),
        ];
    }

    private function patientBlock(Patient $patient): array
    {
        $birth = $this->date($patient->getRawOriginal('birth_at') ?: $patient->birth_at);
        $age = null;

        try {
            $rawBirth = $patient->getRawOriginal('birth_at') ?: null;
            $age = $rawBirth ? Carbon::parse($rawBirth)->age : null;
        } catch (\Throwable) {
        }

        return [
            'id' => (int) $patient->id,
            'full_name' => trim(implode(' ', array_filter([
                $patient->surname ?? null,
                $patient->name ?? null,
                $patient->patronym ?? null,
            ]))),
            'birth_date' => $birth,
            'age' => $age,
            'gender' => $patient->gender ?? $patient->sex ?? null,
            'medical_card_number' => $patient->medcard_number ?? null,
            'observed_since' => $this->date($patient->getRawOriginal('created_at') ?: null),
            'treating_doctor' => trim(implode(' ', array_filter([
                $patient->doctor?->surname,
                $patient->doctor?->name,
                $patient->doctor?->patronym,
            ]))),
        ];
    }

    private function diagnosisBlock(Patient $patient): ?array
    {
        $diagnosis = $patient->diagnose;
        if (!$diagnosis) {
            return null;
        }

        return [
            'id' => $diagnosis->id ?? null,
            'code' => trim((string) ($diagnosis->code ?? '')),
            'title' => trim((string) ($diagnosis->title ?? $diagnosis->name ?? '')),
        ];
    }

    private function anamneses(Patient $patient): array
    {
        return $patient->anamneses()
            ->with('doctor')
            ->orderBy('created_at')
            ->get()
            ->map(fn ($row) => [
                'date' => $this->dateTime($row->created_at ?? null),
                'title' => trim((string) ($row->title ?? '')),
                'text' => $this->plain($row->text ?? ''),
                'source' => $row->source ?? null,
                'doctor' => $this->doctorName($row->doctor ?? null),
                'diagnosis_code' => $row->diagnosis_code ?? null,
                'diagnosis_title' => $row->diagnosis_title ?? null,
            ])
            ->filter(fn (array $row) => $row['text'] !== '' || $row['title'] !== '')
            ->values()
            ->all();
    }

    private function epicrises(Patient $patient): array
    {
        return $patient->epicrises()
            ->with('diagnose')
            ->orderBy('created_at')
            ->get()
            ->map(fn ($row) => [
                'date' => $this->dateTime($row->created_at ?? null),
                'type' => $row->type ?? $row->epicrisis_type ?? null,
                'mkb10' => $row->mkb10 ?? $row->diagnose?->code ?? null,
                'diagnosis' => $row->diagnosis_title ?? $row->diagnose?->title ?? $row->diagnose?->name ?? null,
                'text' => $this->plain($row->text ?? ''),
            ])
            ->filter(fn (array $row) => $row['text'] !== '' || $row['mkb10'])
            ->values()
            ->all();
    }

    private function symptoms(Patient $patient): array
    {
        try {
            return $patient->symptomsLinks()
                ->with('symptom')
                ->orderBy('created_at')
                ->get()
                ->map(function ($row) {
                    $symptom = $row->symptom;
                    return [
                        'date' => $this->dateTime($row->created_at ?? null),
                        'name' => trim((string) ($symptom->name ?? $symptom->title ?? '')),
                        'value' => $row->value ?? $row->status ?? null,
                    ];
                })
                ->filter(fn (array $row) => $row['name'] !== '')
                ->values()
                ->all();
        } catch (\Throwable) {
            return [];
        }
    }

    private function conditions(Patient $patient): array
    {
        try {
            return $patient->conditions()
                ->orderBy('created_at')
                ->get()
                ->map(fn ($row) => [
                    'date' => $this->dateTime($row->created_at ?? null),
                    'status' => $row->status ?? $row->condition ?? $row->name ?? null,
                    'description' => $this->plain($row->description ?? $row->text ?? $row->comment ?? ''),
                ])
                ->filter(fn (array $row) => $row['status'] || $row['description'] !== '')
                ->values()
                ->all();
        } catch (\Throwable) {
            return [];
        }
    }

    private function prescriptions(Patient $patient): array
    {
        return $patient->prescriptions()
            ->with('drug')
            ->orderBy('created_at')
            ->get()
            ->map(fn ($row) => [
                'date' => $this->dateTime($row->created_at ?? null),
                'drug' => trim((string) ($row->drug?->name ?? $row->generic_name ?? '')),
                'generic_name' => $row->generic_name ?? null,
                'form' => $row->prescription_form ?? $row->drug_form ?? null,
                'dosage' => $row->dosage ?? null,
                'instructions' => $this->plain($row->usage_instructions ?? ''),
                'doctor' => $row->doctor_name ?? null,
            ])
            ->filter(fn (array $row) => $row['drug'] !== '' || $row['instructions'] !== '')
            ->values()
            ->all();
    }

    private function laboratoryResearches(Patient $patient): array
    {
        return $patient->labResearches()
            ->orderBy('created_at')
            ->get()
            ->map(function ($research) {
                $results = collect();
                try {
                    $results = $research->results()->get();
                } catch (\Throwable) {
                }

                $parameterIds = $results
                    ->pluck('lab_parameter_id')
                    ->filter()
                    ->map(fn ($id) => (int) $id)
                    ->unique()
                    ->values();

                $parameters = $parameterIds->isNotEmpty()
                    ? LabParameter::query()->whereIn('id', $parameterIds)->get()->keyBy('id')
                    : collect();

                return [
                    'date' => $this->dateTime(
                        $research->collected_at
                        ?? $research->performed_at
                        ?? $research->planned_at
                        ?? $research->created_at
                        ?? null
                    ),
                    'name' => trim((string) (
                        $research->name
                        ?? $research->laboratory
                        ?? $research->sample_type
                        ?? 'Лабораторное исследование'
                    )),
                    'status' => $research->status ?? null,
                    'comment' => $this->plain($research->comment ?? ''),
                    'results' => $results->map(function ($row) use ($parameters) {
                        $parameter = $parameters->get((int) ($row->lab_parameter_id ?? 0));
                        return [
                            'parameter' => trim((string) ($parameter->name ?? ('Параметр #' . ($row->lab_parameter_id ?? '')))),
                            'value' => $row->value ?? $row->result_value ?? null,
                            'unit' => $parameter->unit ?? null,
                        ];
                    })->values()->all(),
                ];
            })
            ->values()
            ->all();
    }

    private function instrumentalResearches(Patient $patient): array
    {
        return $patient->instrumentalResearches()
            ->orderBy('created_at')
            ->get()
            ->map(fn ($row) => [
                'date' => $this->dateTime(
                    $row->result_at
                    ?? $row->performed_at
                    ?? $row->planned_at
                    ?? $row->created_at
                    ?? null
                ),
                'type' => $row->study_type ?? null,
                'name' => $row->name ?? null,
                'body_area' => $row->body_area ?? null,
                'indication' => $this->plain($row->indication ?? ''),
                'description' => $this->plain($row->description ?? ''),
                'conclusion' => $this->plain($row->conclusion ?? ''),
                'recommendations' => $this->plain($row->recommendations ?? ''),
                'critical' => (bool) ($row->is_critical ?? false),
                'critical_reason' => $this->plain($row->critical_reason ?? ''),
            ])
            ->filter(fn (array $row) => $row['name'] || $row['conclusion'] !== '' || $row['description'] !== '')
            ->values()
            ->all();
    }

    private function tests(Patient $patient): array
    {
        try {
            return $patient->testSessions()
                ->with('test')
                ->orderBy('created_at')
                ->get()
                ->map(fn ($row) => [
                    'date' => $this->dateTime($row->finished_at ?? $row->created_at ?? null),
                    'test' => $row->test?->name ?? $row->test?->title ?? null,
                    'status' => $row->status ?? null,
                    'score' => $row->score ?? $row->total_score ?? null,
                    'result' => $this->plain($row->result ?? $row->interpretation ?? $row->conclusion ?? ''),
                ])
                ->filter(fn (array $row) => $row['test'] || $row['score'] !== null || $row['result'] !== '')
                ->values()
                ->all();
        } catch (\Throwable) {
            return [];
        }
    }

    private function doctorName(?Model $doctor): string
    {
        if (!$doctor) {
            return '';
        }

        return trim(implode(' ', array_filter([
            $doctor->surname ?? null,
            $doctor->name ?? null,
            $doctor->patronym ?? null,
        ])));
    }

    private function plain(mixed $value): string
    {
        $text = html_entity_decode(strip_tags((string) $value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/[\t ]+/u', ' ', $text) ?? $text;
        $text = preg_replace('/\R{3,}/u', "\n\n", $text) ?? $text;

        return trim(Str::limit($text, 30000, ''));
    }

    private function date(mixed $value): ?string
    {
        if (!$value) return null;
        try {
            return Carbon::parse($value)->format('d.m.Y');
        } catch (\Throwable) {
            return null;
        }
    }

    private function dateTime(mixed $value): ?string
    {
        if (!$value) return null;
        try {
            return Carbon::parse($value)->format('d.m.Y H:i');
        } catch (\Throwable) {
            return null;
        }
    }
}
