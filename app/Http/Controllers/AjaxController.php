<?php

namespace App\Http\Controllers;

use App\Models\ContraindicationsType;
use App\Models\Doctor;
use App\Models\Drug;
use App\Models\LabParameter;
use App\Models\LabResearchTemplate;
use App\Models\Patient;
use App\Models\PatientCondition;
use App\Models\Test;
use \Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class AjaxController extends Controller
{
    public function medicineList(int $groupIdentificator): JsonResponse {
        return response()->json([
            "data" => Drug::whereGroup($groupIdentificator)->with('receptors')->get()
        ]);
    }

    public function getDoctors(): JsonResponse {
        return response()->json([
            'status' => 'success',
            'items' => Doctor::get(['id', 'name', 'surname', 'patronym'])->toArray()
            ]);
    }

    public function getPatient(): JsonResponse {
        return response()->json([
            'status' => 'success',
            'items' => Patient::get(['id', 'name', 'surname', 'patronym'])->toArray()
            ]);
    }

    public function getDiagnoses(Request $request): JsonResponse
    {
        $q   = trim((string) $request->get('q', ''));
        $by  = $request->get('by', 'any');        // any|code|title
        $page = max(1, (int) $request->get('page', 1));
        $perPage = min(max((int) $request->get('per_page', 20), 5), 50);
        $offset = ($page - 1) * $perPage;

        // Базовый запрос: id, code, title
        $builder = DB::table('diagnoses')->select('id', 'code', 'title');

        if ($q !== '') {
            // Для pgsql используем ILIKE и unaccent() для названий
            if ($by === 'code') {
                $builder->where('code', 'ILIKE', "%{$q}%");
            } elseif ($by === 'title') {
                $builder->whereRaw('unaccent(title) ILIKE unaccent(?)', ["%{$q}%"]);
            } else {
                $builder->where(function ($w) use ($q) {
                    $w->where('code', 'ILIKE', "%{$q}%")
                        ->orWhereRaw('unaccent(title) ILIKE unaccent(?)', ["%{$q}%"]);
                });
            }
        }

        // Приоритизация результатов: точный код -> начинается с кода -> в коде -> в названии
        $builder->orderByRaw(
        // CASE возвращает «вес»: меньше — выше в списке
            "CASE
            WHEN code = ? THEN 0
            WHEN code ILIKE ? THEN 1
            WHEN code ILIKE ? THEN 2
            WHEN unaccent(title) ILIKE unaccent(?) THEN 3
            ELSE 4
         END, code ASC",
            [
                $q,            // точное совпадение кода
                $q.'%',        // начинается с
                '%'.$q.'%',    // содержит в коде
                '%'.$q.'%',    // содержит в названии
            ]
        );

        $total = (clone $builder)->count();
        $rows  = $builder->offset($offset)->limit($perPage)->get();

        return response()->json([
            'results' => $rows->map(fn($r) => [
                'id'    => $r->id,
                'code'  => $r->code,
                'title' => $r->title,
                'text'  => "{$r->code} — {$r->title}",
            ]),
            'pagination' => ['more' => $page * $perPage < $total],
        ]);
    }

    public function searchPatient(Request $request)
    {
        $search = $request->query('q');

        $patients = Patient::where('surname', 'ilike', "%{$search}%")
            ->orWhere('name', 'ilike', "%{$search}%")
            ->orWhere('patronym', 'ilike', "%{$search}%")
            ->orWhereRaw("CONCAT(surname, ' ', LEFT(name, 1), '.', LEFT(patronym, 1), '.') ILIKE ?", ["%{$search}%"])
            ->limit(10)
            ->get()
            ->map(function ($patient) {
                return [
                    'id' => $patient->id,
                    'text' => "{$patient->surname} " . mb_substr($patient->name, 0, 1) . "." . mb_substr($patient->patronym, 0, 1) . ".",
                    'birth_at' => $patient->birth_at
                ];
            });

        return response()->json(['results' => $patients]);
    }

    public function searchDrugs(Request $request)
    {
        $search = trim((string) $request->query('q', ''));
        $patientId = $request->query('patient_id');
        $indicationSource = $request->query('indication_source');

        $drugs = Drug::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'ilike', "%{$search}%")
                        ->orWhere('latin_name', 'ilike', "%{$search}%");
                });
            });

        if ($patientId) {
            $patient = Patient::find($patientId);

            if (!$patient) {
                return response()->json(['results' => []]);
            }

            $diagnosisIds = [];

            if (!empty($patient->diagnose_id)) {
                $diagnosisIds[] = $patient->diagnose_id;
            }

            if (method_exists($patient, 'diagnoses')) {
                $relationIds = $patient->diagnoses()->pluck('diagnoses.id')->all();
                $diagnosisIds = array_values(array_unique(array_merge($diagnosisIds, $relationIds)));
            }

            if (empty($diagnosisIds)) {
                return response()->json(['results' => []]);
            }

            $drugs->whereHas('indications', function ($query) use ($diagnosisIds, $indicationSource) {
                $query->whereIn('diagnose_id', $diagnosisIds);

                if ($indicationSource === 'fda') {
                    $query->where('indicated_by_fda', true);
                } elseif ($indicationSource === 'russia') {
                    $query->where('indicated_in_russia', true);
                }
            });

            $patientConditionNames = PatientCondition::query()
                ->where('patient_id', $patientId)
                ->where('status', 'active')
                ->with('condition:id,name')
                ->get()
                ->pluck('condition.name')
                ->filter()
                ->unique()
                ->values()
                ->all();

            if (!empty($patientConditionNames)) {
                $contraindicationIds = ContraindicationsType::query()
                    ->whereIn('name', $patientConditionNames)
                    ->pluck('id')
                    ->all();

                if (!empty($contraindicationIds)) {
                    $drugs->whereDoesntHave('contraindications', function ($query) use ($contraindicationIds) {
                        $query->whereIn('contraindications_types.id', $contraindicationIds);
                    });
                }
            }

            $patientAge = Carbon::parse($patient->birth_at)->age;
            $drugs->whereDoesntHave('contraindications', function ($query) use ($patientAge) {
                $ageContraindications = false;
                ContraindicationsType::where('name', 'ILIKE', "возраст до %{$patientAge}%")
                    ->pluck('name', 'id')
                    ->filter(function ($record) use ($patientAge, &$ageContraindications) {
                        preg_match_all('/до\s(\d{1,2})/', $record['name'], $matches);
                        $lowAgeLimit = intval($matches[1][0]);

                        if ($lowAgeLimit < $patientAge && !$ageContraindications) {
                            $ageContraindications = $record['id'];

                            return true;
                        } else {
                            return false;
                        }
                    });

                $query->where('contraindications_types.id', $ageContraindications);
            });
        }

        $drugs = $drugs->orderBy('name')
            ->limit(10)
            ->get()
            ->map(function ($drug) {
                return [
                    'id' => $drug->id,
                    'text' => $drug->name,
                    'latin_name' => $drug->latin_name,
                ];
            });

        return response()->json([
            'results' => $drugs,
        ]);
    }

    public function getDrugForms($drugId)
    {
        $drug = Drug::where('latin_name', 'ilike', "%$drugId%")
            ->orWhere('name', $drugId)
            ->first();

        if (!$drug) {
            return response()->json([]);
        }

        $drugStrict = $drug->strict;

        $formTranslations = [
            'tablets' => 'Таблетки',
            'ampules' => 'Ампулы',
            'dragees' => 'Драже',
            'capsules' => 'Капсулы',
        ];

        $forms = collect($drug->forms)->flatMap(function ($formDetails, $formKey) use ($formTranslations, $drugStrict) {
            $formName = $formTranslations[$formKey] ?? ucfirst($formKey);

            return collect($formDetails)->flatMap(function ($value, $dose) use ($formName, $drugStrict) {
                if (is_array($value)) {
                    return collect($value)->map(function ($qty, $volume) use ($formName, $dose, $drugStrict) {
                        return [
                            'form'   => $formName,
                            'dose'   => $dose,
                            'volume' => $volume,
                            'count'  => $qty,
                            'strict' => $drugStrict
                        ];
                    });
                } else {
                    return [[
                        'form'   => $formName,
                        'dose'   => $dose,
                        'volume' => null,
                        'count'  => $value,
                        'strict' => $drugStrict
                    ]];
                }
            });
        })->values();

        return response()->json($forms);
    }

    public function searchPatientFor(int $id)
    {
        $patient = Patient::findOrFail($id);

        return response()->json([
            'id' => $patient->id,
            'surname' => $patient->surname,
            'name' => $patient->name,
            'patronym' => $patient->patronym,
            'birth_at' => $patient->birth_at ? Carbon::parse($patient->birth_at)->format('Y-m-d') : '',
            'address_registration' => $patient->address_registration,
            'address_residence' => $patient->address_residence,
            'serial' => $patient->serial,
            'number' => $patient->number,
            'department_code' => $patient->department_code,
            'issued_by' => $patient->issued_by,
            'issued_at' => $patient->issued_at ? Carbon::parse($patient->issued_at)->format('Y-m-d') : '',
            'birth_place' => $patient->birth_place,
            'snils' => $patient->snils,
            'oms' => $patient->oms,
        ]);
    }

    public function searchLabParameterGroup(Request $request): JsonResponse
    {
        $groups = LabParameter::where('sample_type', $request->material)
            ->distinct()
            ->pluck('group');

        return response()->json([
            'groups' => $groups
        ]);
    }

    public function searchLabParameter(Request $request): JsonResponse
    {
        $params = LabParameter::where('name', 'ilike', '%' . $request->get('q') . '%');

        if ($group = $request->get('group')) {
            $params = $params->where('group', $group);
        }

        if ($sampleType = $request->get('sample_type')) {
            $params = $params->where('sample_type', $sampleType);
        }

        $params = $params->get();

        return response()->json($params);
    }

    public function searchTestsForPatient(Request $request): JsonResponse
    {
        $doctor = $request->user()->doctor;

        $doctorId = $doctor->id;
        $clinicId = $doctor->clinic_id;

        $search = trim((string) $request->get('q', ''));

        $tests = Test::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where('name', 'ilike', '%' . $search . '%');
            })

            // Есть ли у теста вообще привязка к какой-либо клинике
            ->withExists([
                'clinics as has_any_clinic',
            ])

            // Привязан ли тест именно к клинике текущего врача
            ->when(!is_null($clinicId), function ($query) use ($clinicId) {
                $query->withExists([
                    'clinics as is_current_clinic_test' => function ($query) use ($clinicId) {
                        $query->where('clinics.id', $clinicId);
                    },
                ]);
            })

            ->where(function ($query) use ($doctorId, $clinicId) {
                // 1. Тесты, созданные самим врачом
                $query->where('owner_doctor_id', $doctorId)

                    // 2. Общие тесты:
                    // нет владельца-врача и нет привязки к клинике
                    ->orWhere(function ($query) {
                        $query->whereNull('owner_doctor_id')
                            ->whereDoesntHave('clinics');
                    });

                // 3. Тесты клиники текущего врача
                if (!is_null($clinicId)) {
                    $query->orWhereHas('clinics', function ($query) use ($clinicId) {
                        $query->where('clinics.id', $clinicId);
                    });
                }
            })

            ->orderBy('name')
            ->limit(30)
            ->get()
            ->map(function ($element) use ($doctorId) {
                $isMyTest = (int) $element->owner_doctor_id === (int) $doctorId;
                $hasAnyClinic = (bool) ($element->has_any_clinic ?? false);
                $isCurrentClinicTest = (bool) ($element->is_current_clinic_test ?? false);

                $isCommonTest = is_null($element->owner_doctor_id) && !$hasAnyClinic;

                if ($isCommonTest) {
                    $accessSourceType = 'system';
                    $accessLabel = 'Общий тест';
                    $accessHint = 'Общий тест, доступный всем врачам.';
                } elseif ($isMyTest && $isCurrentClinicTest) {
                    $accessSourceType = 'doctor_clinic';
                    $accessLabel = 'Мой тест клиники';
                    $accessHint = 'Тест создан вами и доступен врачам вашей клиники.';
                } elseif ($isMyTest) {
                    $accessSourceType = 'doctor';
                    $accessLabel = 'Мой тест';
                    $accessHint = 'Тест создан вами и доступен только вам.';
                } else {
                    $accessSourceType = 'clinic';
                    $accessLabel = 'Тест клиники';
                    $accessHint = 'Тест доступен врачам вашей клиники.';
                }

                return [
                    'id' => $element->id,
                    'text' => $element->name,
                    'description' => $element->description,
                    'code' => $element->code,
                    'type_label' => $element->type,

                    'access_source_type' => $accessSourceType,
                    'access_label' => $accessLabel,
                    'access_hint' => $accessHint,

                    'can_assign' => true,
                    'can_conduct' => true,
                    'can_view_results' => true,
                ];
            });

        return response()->json([
            'data' => $tests->toArray(),
        ]);
    }

    public function searchAddress(Request $request)
    {
        $query = trim((string) $request->get('query', ''));

        if (mb_strlen($query) < 2) {
            return response()->json([
                'suggestions' => [],
            ]);
        }

        $token = config('dadata.dadata_api');

        if (!$token) {
            return response()->json([
                'suggestions' => [],
                'message' => 'DaData API key is not configured',
            ], 500);
        }

        $response = Http::withHeaders([
            'Authorization' => 'Token ' . $token,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->timeout(8)->post(
            'https://suggestions.dadata.ru/suggestions/api/4_1/rs/suggest/address',
            [
                'query' => $query,
                'count' => 8,
                'division' => 'administrative',
            ]
        );

        if (!$response->successful()) {
            return response()->json([
                'suggestions' => [],
                'message' => 'DaData request failed',
            ], 502);
        }

        $suggestions = collect($response->json('suggestions', []))
            ->map(function ($item) {
                $data = $item['data'] ?? [];

                return [
                    'value' => $item['value'] ?? '',
                    'unrestricted_value' => $item['unrestricted_value'] ?? '',

                    'postal_code' => $data['postal_code'] ?? null,
                    'region' => $data['region_with_type'] ?? null,
                    'city' => $data['city_with_type'] ?? $data['settlement_with_type'] ?? null,
                    'street' => $data['street_with_type'] ?? null,
                    'house' => $data['house'] ?? null,
                    'flat' => $data['flat'] ?? null,

                    'fias_id' => $data['fias_id'] ?? null,
                    'fias_level' => $data['fias_level'] ?? null,
                    'geo_lat' => $data['geo_lat'] ?? null,
                    'geo_lon' => $data['geo_lon'] ?? null,
                ];
            })
            ->values();

        return response()->json([
            'suggestions' => $suggestions,
        ]);
    }

    public function searchInsuranceOrganizations(Request $request)
    {
        $query = trim($request->get('q', $request->get('query', '')));

        if (mb_strlen($query) < 2) {
            return response()->json([
                'suggestions' => [],
            ]);
        }

        $response = Http::withHeaders([
            'Authorization' => 'Token ' . config('dadata.token'),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->post('https://suggestions.dadata.ru/suggestions/api/4_1/rs/suggest/party', [
            'query' => $query,
            'count' => 10,
            'status' => ['ACTIVE'],
        ]);

        if (!$response->successful()) {
            return response()->json([
                'suggestions' => [],
            ]);
        }

        return response()->json($response->json());
    }
}
