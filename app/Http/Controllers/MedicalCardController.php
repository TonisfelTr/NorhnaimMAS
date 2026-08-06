<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePatientMedcardRequest;
use App\Models\InstrumentalResearch;
use App\Models\LabParameter;
use App\Models\LabResearchTemplate;
use App\Models\Patient;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class MedicalCardController extends Controller
{
    private function getPatientSexForLab(Patient $patient): string
    {
        $gender = strtoupper((string) ($patient->gender ?? $patient->sex ?? ''));

        return match ($gender) {
            'M', 'MALE', 'М', 'МУЖ', 'МУЖСКОЙ' => 'M',
            'F', 'FEMALE', 'Ж', 'ЖЕН', 'ЖЕНСКИЙ' => 'F',
            default => '',
        };
    }

    private function buildNormalValueForPatient(
        $param,
        Patient $patient,
        mixed $referenceDate = null
    ): array
    {
        $normalValues = $param->normal_values ?? [];

        if (is_string($normalValues)) {
            $normalValues = json_decode($normalValues, true) ?: [];
        }

        if (!is_array($normalValues) || $normalValues === []) {
            return [
                'min' => null,
                'max' => null,
                'ref' => '',
            ];
        }

        $patientSex = $this->getPatientSexForLab($patient);

        $patientAge = $patient->birth_at
            ? (int) Carbon::parse($patient->birth_at)->diffInYears(
                $referenceDate ? Carbon::parse($referenceDate) : now()
            )
            : null;

        $matchingRows = collect($normalValues)
            ->filter(function ($row) use ($patientSex, $patientAge): bool
            {
                if (!is_array($row)) {
                    return false;
                }

                $rowSex = strtoupper(
                    trim((string) ($row['sex'] ?? 'ANY'))
                );

                $sexMatches = $rowSex === ''
                    || $rowSex === 'ANY'
                    || (
                        $patientSex !== ''
                        && $rowSex === $patientSex
                    );

                $ageMin = $row['age_min_y'] ?? null;
                $ageMax = $row['age_max_y'] ?? null;

                $ageMinMatches = $ageMin === null
                    || $ageMin === ''
                    || $patientAge === null
                    || $patientAge >= (float) $ageMin;

                $ageMaxMatches = $ageMax === null
                    || $ageMax === ''
                    || $patientAge === null
                    || $patientAge <= (float) $ageMax;

                return $sexMatches
                    && $ageMinMatches
                    && $ageMaxMatches;
            });

        $matched = $matchingRows
            ->first(function ($row) use ($patientSex): bool
            {
                return $patientSex !== ''
                    && strtoupper(
                        trim((string) ($row['sex'] ?? ''))
                    ) === $patientSex;
            });

        $matched ??= $matchingRows
            ->first(function ($row): bool
            {
                $rowSex = strtoupper(
                    trim((string) ($row['sex'] ?? 'ANY'))
                );

                return $rowSex === ''
                    || $rowSex === 'ANY';
            });

        $matched ??= collect($normalValues)
            ->first(function ($row): bool
            {
                return is_array($row);
            });

        if (!is_array($matched)) {
            return [
                'min' => null,
                'max' => null,
                'ref' => '',
            ];
        }

        $min = $matched['min_value']
            ?? $matched['min']
            ?? null;

        $max = $matched['max_value']
            ?? $matched['max']
            ?? null;

        $text = $matched['value']
            ?? $matched['normal_value']
            ?? $matched['text']
            ?? $matched['ref']
            ?? null;

        if ($text !== null && $text !== '') {
            return [
                'min' => $min,
                'max' => $max,
                'ref' => (string) $text,
            ];
        }

        if ($min !== null && $min !== '' && $max !== null && $max !== '') {
            return [
                'min' => $min,
                'max' => $max,
                'ref' => $min . '–' . $max,
            ];
        }

        if ($min !== null && $min !== '') {
            return [
                'min' => $min,
                'max' => $max,
                'ref' => 'от ' . $min,
            ];
        }

        if ($max !== null && $max !== '') {
            return [
                'min' => $min,
                'max' => $max,
                'ref' => 'до ' . $max,
            ];
        }

        return [
            'min' => null,
            'max' => null,
            'ref' => '',
        ];
    }

    private function buildLabResearchPayloads($research): array
    {
        $rawIds = $research->parameters ?? [];

        if (is_string($rawIds)) {
            $decoded = json_decode($rawIds, true);
            $rawIds = json_last_error() === JSON_ERROR_NONE
                ? $decoded
                : [];
        }

        $paramIds = collect($rawIds ?: [])
            ->map(function ($item)
            {
                if (is_array($item)) {
                    return $item['id']
                        ?? $item['param_id']
                        ?? $item['parameter_id']
                        ?? $item['lab_parameter_id']
                        ?? null;
                }

                if (is_object($item)) {
                    return $item->id
                        ?? $item->param_id
                        ?? $item->parameter_id
                        ?? $item->lab_parameter_id
                        ?? null;
                }

                return $item;
            })
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $resultRows = $research->results()
            ->get()
            ->keyBy('lab_parameter_id');

        $resultParamIds = $resultRows
            ->keys()
            ->map(fn ($id) => (int) $id)
            ->values();

        $allParamIds = $paramIds
            ->merge($resultParamIds)
            ->unique()
            ->values();

        $paramsById = LabParameter::query()
            ->with('criticalRanges')
            ->whereIn('id', $allParamIds)
            ->get()
            ->keyBy('id');

        $patient = $research->patient;

        $referenceDate = $research->research_date
            ?? $research->collected_at
            ?? $research->planned_at
            ?? $research->created_at
            ?? now();

        $paramsPayload = $allParamIds
            ->map(function ($id) use (
                $paramsById,
                $patient,
                $referenceDate
            ): array
            {
                $param = $paramsById->get($id);

                if (!$param) {
                    return [
                        'id' => $id,
                        'name' => 'Параметр #' . $id,
                        'unit' => '',
                        'group' => '',
                        'min' => null,
                        'max' => null,
                        'ref' => '',
                        'critical_low' => null,
                        'critical_high' => null,
                        'critical_ref' => '',
                    ];
                }

                $normal = $patient
                    ? $this->buildNormalValueForPatient(
                        $param,
                        $patient,
                        $referenceDate
                    )
                    : [
                        'min' => null,
                        'max' => null,
                        'ref' => '',
                    ];

                $critical = $patient
                    ? $this->buildCriticalValueForPatient(
                        $param,
                        $patient,
                        $referenceDate
                    )
                    : [
                        'low' => null,
                        'high' => null,
                        'ref' => '',
                    ];

                return [
                    'id' => $param->id,
                    'name' => $param->name,
                    'unit' => $param->unit ?? '',
                    'group' => $param->group
                        ?? $param->group_code
                        ?? '',
                    'min' => $normal['min'],
                    'max' => $normal['max'],
                    'ref' => $normal['ref'],
                    'critical_low' => $critical['low'],
                    'critical_high' => $critical['high'],
                    'critical_ref' => $critical['ref'],
                ];
            })
            ->values();

        $valuesMap = $resultRows
            ->mapWithKeys(function ($row): array
            {
                return [
                    (string) $row->lab_parameter_id => [
                        'value' => $row->value,
                        'result_value' => $row->value,
                    ],
                ];
            })
            ->toArray();

        return [
            $paramsPayload,
            $valuesMap,
        ];
    }

    private function buildCriticalValueForPatient(
        $param,
        Patient $patient,
        mixed $referenceDate = null
    ): array
    {
        $patientSex = $this->getPatientSexForLab($patient);

        $patientAge = $patient->birth_at
            ? (int) Carbon::parse($patient->birth_at)->diffInYears(
                $referenceDate ? Carbon::parse($referenceDate) : now()
            )
            : null;

        $matchingRanges = collect($param->criticalRanges ?? [])
            ->filter(function ($range) use ($patientSex, $patientAge): bool
            {
                $rangeSex = strtoupper(
                    trim((string) $range->sex)
                );

                $sexMatches = $rangeSex === ''
                    || $rangeSex === 'ANY'
                    || (
                        $patientSex !== ''
                        && $rangeSex === $patientSex
                    );

                $ageMinMatches = $range->age_min_y === null
                    || $patientAge === null
                    || $patientAge >= (int) $range->age_min_y;

                $ageMaxMatches = $range->age_max_y === null
                    || $patientAge === null
                    || $patientAge <= (int) $range->age_max_y;

                return $sexMatches
                    && $ageMinMatches
                    && $ageMaxMatches;
            });

        $criticalRange = $matchingRanges
            ->first(function ($range) use ($patientSex): bool
            {
                return $patientSex !== ''
                    && strtoupper(
                        trim((string) $range->sex)
                    ) === $patientSex;
            });

        $criticalRange ??= $matchingRanges
            ->first(function ($range): bool
            {
                $rangeSex = strtoupper(
                    trim((string) $range->sex)
                );

                return $rangeSex === ''
                    || $rangeSex === 'ANY';
            });

        if (!$criticalRange) {
            return [
                'low' => null,
                'high' => null,
                'ref' => '',
            ];
        }

        $reference = [];

        if ($criticalRange->critical_low !== null) {
            $reference[] = '≤ ' . $criticalRange->critical_low;
        }

        if ($criticalRange->critical_high !== null) {
            $reference[] = '≥ ' . $criticalRange->critical_high;
        }

        return [
            'low' => $criticalRange->critical_low,
            'high' => $criticalRange->critical_high,
            'ref' => implode(' или ', $reference),
        ];
    }

    public function index(Patient $patient)
    {
        $contacts = $patient->contacts()
            ->orderBy('created_at', 'desc')
            ->get();

        $anamneses = $patient->anamneses()
            ->orderBy('created_at', 'desc')
            ->get();

        $documents = $patient->documents()
            ->orderBy('created_at', 'desc')
            ->paginate();

        $medicalFilesCount = $patient->documents()
            ->medicalFile()
            ->count();

        $researches = $patient->labResearches()
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($research) {
                [$paramsPayload, $valuesMap] = $this->buildLabResearchPayloads($research);

                $research->setAttribute('params_payload', $paramsPayload);
                $research->setAttribute('values_map', $valuesMap);

                return $research;
            });

        $psyTests = $patient->testSessions()
            ->orderBy('created_at', 'desc')
            ->get();

        $prescriptionCounts = $patient->prescriptions()
            ->selectRaw('DATE(created_at) as prescription_date, generic_name, COUNT(*) as prescriptions_count')
            ->whereNotNull('generic_name')
            ->groupBy(DB::raw('DATE(created_at)'), 'generic_name')
            ->get()
            ->keyBy(function ($row) {
                return $row->prescription_date . '|' . $row->generic_name;
            });

        $latestPrescriptionIds = $patient->prescriptions()
            ->selectRaw('MAX(id) as id')
            ->whereNotNull('generic_name')
            ->groupBy(DB::raw('DATE(created_at)'), 'generic_name');

        $prescriptions = $patient->prescriptions()
            ->with('drug')
            ->whereIn('id', $latestPrescriptionIds)
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($prescription) use ($prescriptionCounts) {
                $date = Carbon::parse($prescription->created_at)->format('Y-m-d');
                $key = $date . '|' . $prescription->generic_name;
                $count = (int) optional($prescriptionCounts->get($key))->prescriptions_count;

                $prescription->setAttribute(
                    'daily_generic_prescriptions_count',
                    $count > 0 ? $count : 1
                );

                return $prescription;
            })
            ->groupBy(function ($prescription) {
                return Carbon::parse($prescription->created_at)->format('Y-m-d');
            });

        if (config('app.debug')) {
            $labTemplates = LabResearchTemplate::orderBy('name', 'desc')->get();
        } else {
            $labTemplates = LabResearchTemplate::whereIn('doctor_id', [
                0,
                auth()->user()->doctor()->first()?->id
            ])
                ->orderBy('name', 'desc')
                ->get();
        }

        $templateParamIds = $labTemplates
            ->flatMap(function ($template) {
                $ids = $template->lab_parameters;

                if (is_string($ids)) {
                    $ids = json_decode($ids, true);
                }

                return collect($ids ?: []);
            })
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $labParamsById = LabParameter::query()
            ->with('criticalRanges')
            ->whereIn('id', $templateParamIds)
            ->get()
            ->keyBy('id');

        $templatesMap = $labTemplates->mapWithKeys(function ($template) use ($labParamsById, $patient) {
            $ids = $template->lab_parameters;

            if (is_string($ids)) {
                $ids = json_decode($ids, true);
            }

            $ids = collect($ids ?: [])
                ->filter()
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values();

            return [
                $template->id => $ids->map(function ($id) use ($labParamsById, $patient) {
                    $param = $labParamsById->get($id);

                    if (!$param) {
                        return [
                            'id' => $id,
                            'name' => 'Параметр #' . $id,
                            'unit' => '',
                            'group' => '',
                            'min' => null,
                            'max' => null,
                            'ref' => '',
                            'critical_low' => null,
                            'critical_high' => null,
                            'critical_ref' => '',
                        ];
                    }

                    $normal = $this->buildNormalValueForPatient(
                        $param,
                        $patient
                    );

                    $critical = $this->buildCriticalValueForPatient(
                        $param,
                        $patient
                    );

                    return [
                        'id' => $param->id,
                        'name' => $param->name,
                        'unit' => $param->unit ?? '',
                        'group' => $param->group
                            ?? $param->group_code
                            ?? '',
                        'min' => $normal['min'],
                        'max' => $normal['max'],
                        'ref' => $normal['ref'],
                        'critical_low' => $critical['low'],
                        'critical_high' => $critical['high'],
                        'critical_ref' => $critical['ref'],
                    ];
                })->values(),
            ];
        });

        $instrumentalResearches = $patient
            ->instrumentalResearches()
            ->with([
                'doctor:id,name,surname,patronym',
                'media',
            ])
            ->orderByDesc('created_at')
            ->get();

        $instrumentalResearchTypes = InstrumentalResearch::TYPES;
        $instrumentalResearchStatuses = InstrumentalResearch::STATUSES;
        $instrumentalResearchPriorities = InstrumentalResearch::PRIORITIES;

        return view(
            'doctors.reception.medical_card',
            compact(
                'patient',
                'contacts',
                'anamneses',
                'labTemplates',
                'psyTests',
                'researches',
                'instrumentalResearches',
                'instrumentalResearchTypes',
                'instrumentalResearchStatuses',
                'instrumentalResearchPriorities',
                'templatesMap',
                'prescriptions',
                'documents',
                'medicalFilesCount'
            )
        );
    }

    public function update(UpdatePatientMedcardRequest $request, Patient $patient) {
        $patient->update($request->validated());

        return redirect()->back()->with([
            'status' => 'success',
            'message' => 'Данные пациента были успешно обновлены.'
        ]);
    }
}
