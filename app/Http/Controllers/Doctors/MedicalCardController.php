<?php

namespace App\Http\Controllers\Doctors;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdatePatientMedcardRequest;
use App\Models\LabParameter;
use App\Models\InstrumentalResearch;
use App\Models\LabResearchTemplate;
use App\Models\Patient;
use App\Models\HospitalDepartment;
use App\Support\HospitalPatientAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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

    private function buildNormalValueForPatient($param, Patient $patient): array
    {
        $normalValues = $param->normal_values ?? [];

        if (is_string($normalValues)) {
            $normalValues = json_decode($normalValues, true) ?: [];
        }

        if (!is_array($normalValues) || empty($normalValues)) {
            return [
                'min' => null,
                'max' => null,
                'ref' => '',
            ];
        }

        $patientSex = $this->getPatientSexForLab($patient);
        $patientAge = $patient->birth_at
            ? \Carbon\Carbon::parse($patient->birth_at)->age
            : null;

        $matched = collect($normalValues)->first(function ($row) use ($patientSex, $patientAge) {
            $rowSex = strtoupper((string) ($row['sex'] ?? ''));

            $sexOk = $rowSex === ''
                || $rowSex === 'ANY'
                || $rowSex === $patientSex;

            $ageMin = $row['age_min_y'] ?? null;
            $ageMax = $row['age_max_y'] ?? null;

            $ageOk = true;

            if ($patientAge !== null) {
                if ($ageMin !== null && $ageMin !== '' && $patientAge < (float) $ageMin) {
                    $ageOk = false;
                }

                if ($ageMax !== null && $ageMax !== '' && $patientAge > (float) $ageMax) {
                    $ageOk = false;
                }
            }

            return $sexOk && $ageOk;
        });

        if (!$matched) {
            $matched = collect($normalValues)->first();
        }

        $min = $matched['min_value'] ?? null;
        $max = $matched['max_value'] ?? null;

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
            $rawIds = json_last_error() === JSON_ERROR_NONE ? $decoded : [];
        }

        $paramIds = collect($rawIds ?: [])
            ->map(function ($item) {
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
            ->whereIn('id', $allParamIds)
            ->get()
            ->keyBy('id');

        $patient = $research->patient;

        $paramsPayload = $allParamIds->map(function ($id) use ($paramsById, $patient) {
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
                ];
            }

            $normal = $patient
                ? $this->buildNormalValueForPatient($param, $patient)
                : ['min' => null, 'max' => null, 'ref' => ''];

            return [
                'id' => $param->id,
                'name' => $param->name,
                'unit' => $param->unit ?? '',
                'group' => $param->group ?? $param->group_code ?? '',
                'min' => $normal['min'],
                'max' => $normal['max'],
                'ref' => $normal['ref'],
            ];
        })->values();

        $valuesMap = $resultRows->mapWithKeys(function ($row) {
            return [
                (string) $row->lab_parameter_id => [
                    'value' => $row->value,
                    'result_value' => $row->value,
                ],
            ];
        })->toArray();

        return [
            $paramsPayload,
            $valuesMap,
        ];
    }

    public function index(Request $request, Patient $patient, HospitalPatientAccess $access)
    {
        $user = $request->user();
        abort_unless($user && $access->canRead($user, $patient), 403);

        // Audit each successful authorization, including direct URL access.
        $isAdmin = $user->hasRole('admins');
        $access->auditRead($user, $patient, $isAdmin ? 'admin-readonly' : 'doctor');

        if ($isAdmin) {
            return $this->adminReadOnlyCard($patient, $user, $access);
        }

        $contacts = $patient->contacts()
            ->orderBy('created_at', 'desc')
            ->get();

        $anamneses = $patient->anamneses()
            ->with('doctor')
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($anamnesis) {
                $plainText = trim(
                    preg_replace(
                        '/\s+/u',
                        ' ',
                        strip_tags(
                            html_entity_decode(
                                (string) ($anamnesis->text ?? ''),
                                ENT_QUOTES | ENT_HTML5,
                                'UTF-8'
                            )
                        )
                    ) ?? ''
                );

                $anamnesis->setAttribute(
                    'doctor_name',
                    $anamnesis->doctor?->full_name
                    ?? $anamnesis->doctor?->fullname
                    ?? '—'
                );

                $anamnesis->setAttribute(
                    'preview',
                    $plainText !== ''
                        ? Str::limit($plainText, 260)
                        : '—'
                );

                return $anamnesis;
            });

        $documents = $patient->documents()
            ->orderBy('created_at', 'desc')
            ->paginate();

        $medicalFilesCount = $patient->documents()
            ->medicalFile()
            ->count();

        if ($researchID = request()->get('research')) {
            $patient->labResearches()
                ->where('id', $researchID)
                ->whereNotNull('result_showed_at')
                ->update([
                    'result_showed_at' => now(),
                ]);
        }

        $researches = $patient->labResearches()
            ->with('patient')
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($research) {
                [$paramsPayload, $valuesMap] =
                    $this->buildLabResearchPayloads($research);

                $research->setAttribute(
                    'params_payload',
                    $paramsPayload
                );

                $research->setAttribute(
                    'values_map',
                    $valuesMap
                );

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
                        ];
                    }

                    $normal = $this->buildNormalValueForPatient($param, $patient);

                    return [
                        'id' => $param->id,
                        'name' => $param->name,
                        'unit' => $param->unit ?? '',
                        'group' => $param->group ?? $param->group_code ?? '',
                        'min' => $normal['min'],
                        'max' => $normal['max'],
                        'ref' => $normal['ref'],
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

        return view('doctors.reception.medical_card', compact(
            'patient',
            'contacts',
            'anamneses',
            'documents',
            'medicalFilesCount',
            'labTemplates',
            'psyTests',
            'researches',
            'instrumentalResearches',
            'instrumentalResearchTypes',
            'instrumentalResearchStatuses',
            'instrumentalResearchPriorities',
            'templatesMap',
            'prescriptions',
        ));
    }

    /**
     * Read-only administrator presentation. Never reuse the clinical edit form:
     * it contains action buttons for medications, research and documentation.
     */
    private function adminReadOnlyCard(Patient $patient, $user, HospitalPatientAccess $access)
    {
        $clinicIds = $access->adminClinicIds($user);
        $departmentIds = HospitalDepartment::query()
            ->whereIn('clinic_id', $clinicIds)->where('enabled', true)
            ->pluck('id')->map(static fn ($id): int => (int) $id)->all();

        $placement = $access->activeAssignments($clinicIds, $departmentIds)
            ->where('patient_id', $patient->getKey())
            ->with(['bed.room.department', 'attendingDoctor'])
            ->firstOrFail();

        $fullName = trim(implode(' ', array_filter([
            $patient->surname, $patient->name, $patient->patronym,
        ])));
        $patientInfo = [
            'full_name' => $fullName,
            'birth_date' => $this->adminBirthDate($patient),
            'gender' => $patient->gender ?? $patient->sex ?? '—',
            'medcard_number' => $patient->medcard_number ?? $patient->medical_card_number ?? '—',
            'snils' => $patient->snils ?? '—',
            'oms' => $patient->oms ?? '—',
            'address' => $patient->address_registration ?? '—',
            'department' => $placement->bed?->room?->department?->name ?? '—',
            'room' => $placement->bed?->room?->number ?? '—',
            'bed' => $placement->bed?->number ?? '—',
            'admitted_at' => $placement->assigned_at?->format('d.m.Y H:i') ?? '—',
            'doctor' => $placement->attendingDoctor
                ? trim(implode(' ', array_filter([
                    $placement->attendingDoctor->surname,
                    $placement->attendingDoctor->name,
                    $placement->attendingDoctor->patronym,
                ]))) : 'Не назначен',
        ];

        $anamneses = $patient->anamneses()->orderByDesc('created_at')->limit(30)->get()
            ->map(fn ($item): array => [
                'date' => $this->adminDate($item->created_at),
                'text' => $this->adminPlainText($item->text ?? $item->description ?? null),
            ])->all();

        $epicrises = $patient->epicrises()->orderByDesc('created_at')->limit(30)->get()
            ->map(fn ($item): array => [
                'date' => $this->adminDate($item->created_at),
                'text' => $this->adminPlainText($item->text ?? $item->description ?? $item->conclusion ?? null),
            ])->all();

        $researches = $patient->labResearches()->with('patient')
            ->orderByDesc('created_at')->limit(20)->get()
            ->map(function ($research): array {
                [$paramsPayload, $valuesMap] = $this->buildLabResearchPayloads($research);
                $values = collect($paramsPayload)->map(function (array $parameter) use ($valuesMap): array {
                    $entry = $valuesMap[(string) $parameter['id']] ?? [];
                    return [
                        'parameter' => (string) ($parameter['name'] ?? 'Параметр'),
                        'value' => $this->adminPlainText($entry['value'] ?? null),
                        'unit' => (string) ($parameter['unit'] ?? ''),
                        'reference' => (string) ($parameter['ref'] ?? ''),
                    ];
                })->values()->all();

                return [
                    'date' => $this->adminDate($research->created_at),
                    'title' => $this->adminPlainText($research->name ?? $research->title ?? 'Лабораторное исследование'),
                    'values' => $values,
                ];
            })->all();

        $instrumentals = $patient->instrumentalResearches()->orderByDesc('created_at')
            ->limit(30)->get()->map(fn ($item): array => [
                'date' => $this->adminDate($item->created_at),
                'type' => $this->adminPlainText($item->type ?? $item->name ?? 'Инструментальное исследование'),
                'result' => $this->adminPlainText($item->result ?? $item->conclusion ?? $item->description ?? null),
            ])->all();

        $prescriptions = $patient->prescriptions()->orderByDesc('created_at')
            ->limit(30)->get()->map(fn ($item): array => [
                'date' => $this->adminDate($item->created_at),
                'drug' => $this->adminPlainText($item->generic_name ?? $item->name ?? null),
                'dose' => $this->adminPlainText($item->dosage ?? $item->dose ?? null),
            ])->all();

        $documents = $patient->documents()->orderByDesc('created_at')
            ->limit(30)->get()->map(fn ($item): array => [
                'date' => $this->adminDate($item->created_at),
                'name' => $this->adminPlainText($item->title ?? $item->name ?? $item->original_name ?? 'Документ'),
            ])->all();

        return view('doctors.admin-medical-card', compact(
            'patientInfo', 'anamneses', 'epicrises', 'researches', 'instrumentals',
            'prescriptions', 'documents'
        ));
    }

    private function adminPlainText(mixed $value): string
    {
        if (!is_scalar($value) || trim((string) $value) === '') {
            return '—';
        }

        $text = strip_tags(html_entity_decode((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        return trim((string) (preg_replace('/[ \t]+/u', ' ', $text) ?? $text));
    }

    private function adminBirthDate(Patient $patient): string
    {
        try {
            $raw = $patient->getRawOriginal('birth_at');
            return $raw ? Carbon::parse($raw)->format('d.m.Y') : '—';
        } catch (\Throwable) {
            return '—';
        }
    }

    private function adminDate(mixed $value): string
    {
        if (!$value) {
            return '—';
        }
        try {
            return Carbon::parse($value)->format('d.m.Y H:i');
        } catch (\Throwable) {
            return '—';
        }
    }

    public function update(UpdatePatientMedcardRequest $request, Patient $patient) {
        $patient->update($request->validated());

        return redirect()->back()->with([
            'status' => 'success',
            'message' => 'Данные пациента были успешно обновлены.'
        ]);
    }
}
