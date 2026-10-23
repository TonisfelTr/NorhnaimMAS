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
use App\Support\PatientCareScope;
use Filament\Forms\Components\Builder;
use \Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class AjaxController extends Controller
{
    const PSYCHIATRISTIC_DOCTORS = [
        'Психолог',
        'Патопсихолог',
        'Детский психолог',
        'Детский психиатр',
        'Медицинский психолог',
        'Психотерапевт',
        'Детский психотерапевт',
        'Психиатр'
    ];

    public function medicineList(int $groupIdentificator): JsonResponse {
        return response()->json([
            "data" => Drug::whereGroup($groupIdentificator)->with('receptors')->get()
        ]);
    }

    public function getDoctors(): JsonResponse {
        return response()->json([
            'status' => 'success',
            'items' => Doctor::whereNotIn('status', self::PSYCHIATRISTIC_DOCTORS)
                    ->get(['id', 'name', 'surname', 'patronym', 'status'])
                    ->toArray()
            ]);
    }

    public function getPsyDoctors(): JsonResponse {
        return response()->json([
            'status' => 'success',
            'items' => Doctor::whereIn('status', self::PSYCHIATRISTIC_DOCTORS)
                        ->get(['id', 'name', 'surname', 'patronym', 'status'])
                        ->toArray()
        ]);
    }

    public function getPatient(?Request $request = null): JsonResponse {
        $scope = $this->patientSearchScope($request ?? request());

        return response()->json([
            'status' => 'success',
            'items' => PatientCareScope::forPatients(Patient::query(), $scope)
                ->get(['id', 'name', 'surname', 'patronym'])
                ->toArray()
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

    public function searchPatient(Request $request): JsonResponse
    {
        $scope = $this->patientSearchScope($request);
        $search = trim((string) $request->query('q', ''));

        if ($search === '' || mb_strlen($search, 'UTF-8') < 1) {
            return response()->json([
                'results' => [],
            ]);
        }

        $terms = preg_split('/\s+/u', $search, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        $patients = PatientCareScope::forPatients(Patient::query(), $scope)
            ->with('diagnose')
            ->where(function ($query) use ($terms): void {
                foreach ($terms as $term) {
                    $like = '%' . $term . '%';

                    $query->where(function ($part) use ($like): void {
                        $part
                            ->where('surname', 'ilike', $like)
                            ->orWhere('name', 'ilike', $like)
                            ->orWhere('patronym', 'ilike', $like)
                            ->orWhereRaw(
                                "CONCAT_WS(' ', surname, name, patronym) ILIKE ?",
                                [$like]
                            );
                    });
                }
            })
            ->orderBy('surname')
            ->orderBy('name')
            ->orderBy('patronym')
            ->limit(12)
            ->get()
            ->map(function (Patient $patient): array {
                $birthAt = $patient->birth_at
                    ? Carbon::parse($patient->birth_at)
                    : null;

                $fullName = collect([
                    $patient->surname,
                    $patient->name,
                    $patient->patronym,
                ])->filter()->implode(' ');

                $diagnosisCode = trim((string) data_get($patient, 'diagnose.code', ''));
                $diagnosisTitle = trim((string) (
                    data_get($patient, 'diagnose.title')
                    ?: data_get($patient, 'diagnose.name')
                    ?: ''
                ));

                return [
                    'id' => (int) $patient->id,
                    // text оставляем для обратной совместимости с Select2 в других местах.
                    'text' => $fullName,
                    'full_name' => $fullName,
                    'birth_at' => $birthAt?->format('Y-m-d'),
                    'birth_at_display' => $birthAt?->format('d.m.Y'),
                    'diagnosis' => [
                        'id' => $patient->diagnose_id,
                        'code' => $diagnosisCode !== '' ? $diagnosisCode : null,
                        'title' => $diagnosisTitle !== '' ? $diagnosisTitle : null,
                    ],
                    'diagnosis_label' => ($diagnosisCode !== '' || $diagnosisTitle !== '')
                        ? trim($diagnosisCode . ($diagnosisCode !== '' && $diagnosisTitle !== '' ? ' — ' : '') . $diagnosisTitle)
                        : 'не установлен',
                ];
            })
            ->values();

        return response()->json([
            'results' => $patients,
        ]);
    }

    public function searchDrugs(Request $request): JsonResponse
    {
        /*
         * Select2 передаёт поисковую строку в параметрах q и term.
         */
        $search = trim((string) (
        $request->query('q')
            ?: $request->query('term', '')
        ));

        $patientId = $request->integer('patient_id') ?: null;

        $indicationSource = mb_strtolower(
            trim((string) $request->query('indication_source', ''))
        );

        $page = max(1, $request->integer('page', 1));
        $perPage = 20;

        /*
         * Разрешённые режимы:
         *
         * пустое значение — показывать все доступные препараты;
         * russia — фильтровать по показаниям РФ;
         * fda — фильтровать по показаниям FDA.
         */
        if (!in_array($indicationSource, ['russia', 'fda'], true)) {
            $indicationSource = '';
        }

        $drugsQuery = Drug::query()
            ->select([
                'id',
                'name',
                'latin_name',
                'strict',
            ]);

        /*
         * Поиск одновременно по русскому названию и МНН.
         */
        if ($search !== '') {
            $searchValue = '%' . $search . '%';

            $drugsQuery->where(
                function ( $query) use ($searchValue): void {
                    $query
                        ->where('name', 'ilike', $searchValue)
                        ->orWhere('latin_name', 'ilike', $searchValue);
                }
            );

            /*
             * Сначала выводим:
             *
             * 1. точное совпадение русского названия;
             * 2. точное совпадение МНН;
             * 3. название, начинающееся с введённого текста;
             * 4. МНН, начинающееся с введённого текста;
             * 5. остальные частичные совпадения.
             */
            $drugsQuery->orderByRaw(
                '
                CASE
                    WHEN name ILIKE ? THEN 0
                    WHEN latin_name ILIKE ? THEN 1
                    WHEN name ILIKE ? THEN 2
                    WHEN latin_name ILIKE ? THEN 3
                    ELSE 4
                END
            ',
                [
                    $search,
                    $search,
                    $search . '%',
                    $search . '%',
                ]
            );
        }

        /*
         * Фильтры, зависящие от пациента.
         */
        if ($patientId !== null) {
            $patient = Patient::query()
                ->select([
                    'id',
                    'diagnose_id',
                    'birth_at',
                ])
                ->find($patientId);

            if (!$patient) {
                return response()->json([
                    'results' => [],
                    'pagination' => [
                        'more' => false,
                    ],
                    'message' => 'Пациент не найден.',
                ]);
            }

            /*
             * Фильтрация по диагнозу включается только при выборе
             * «Показан в РФ» или «Показан FDA».
             *
             * В режиме «Не указывать» диагноз не ограничивает поиск.
             */
            if ($indicationSource !== '') {
                $diagnosisIds = collect([
                    $patient->diagnose_id,
                ]);

                if (method_exists($patient, 'diagnoses')) {
                    $diagnosisIds = $diagnosisIds->merge(
                        $patient->diagnoses()
                            ->pluck('diagnoses.id')
                    );
                }

                $diagnosisIds = $diagnosisIds
                    ->filter()
                    ->map(
                        static fn ($diagnosisId): int =>
                        (int) $diagnosisId
                    )
                    ->unique()
                    ->values();

                if ($diagnosisIds->isEmpty()) {
                    return response()->json([
                        'results' => [],
                        'pagination' => [
                            'more' => false,
                        ],
                        'message' => 'У пациента не указан диагноз.',
                    ]);
                }

                $drugsQuery->whereHas(
                    'indications',
                    function ( $query) use (
                        $diagnosisIds,
                        $indicationSource
                    ): void {
                        $query->whereIn(
                            'diagnose_id',
                            $diagnosisIds->all()
                        );

                        if ($indicationSource === 'russia') {
                            $query->where(
                                'indicated_in_russia',
                                true
                            );
                        }

                        if ($indicationSource === 'fda') {
                            $query->where(
                                'indicated_by_fda',
                                true
                            );
                        }
                    }
                );
            }

            /*
             * Получаем активные состояния пациента, которые могут
             * являться противопоказаниями для препаратов.
             */
            $patientConditionNames = PatientCondition::query()
                ->where('patient_id', $patientId)
                ->where('status', 'active')
                ->with('condition:id,name')
                ->get()
                ->pluck('condition.name')
                ->filter()
                ->map(
                    static fn ($name): string =>
                    trim((string) $name)
                )
                ->filter(
                    static fn (string $name): bool =>
                        $name !== ''
                )
                ->unique()
                ->values();

            $contraindicationIds = collect();

            /*
             * Противопоказания по заболеваниям и состояниям пациента.
             */
            if ($patientConditionNames->isNotEmpty()) {
                $conditionContraindicationIds =
                    ContraindicationsType::query()
                        ->whereIn(
                            'name',
                            $patientConditionNames->all()
                        )
                        ->pluck('id');

                $contraindicationIds = $contraindicationIds
                    ->merge($conditionContraindicationIds);
            }

            /*
             * Возрастные противопоказания.
             *
             * Например, запись «Возраст до 18» применяется,
             * если пациенту меньше 18 лет.
             */
            if ($patient->birth_at) {
                $patientAge = Carbon::parse(
                    $patient->birth_at
                )->age;

                $ageContraindicationIds =
                    ContraindicationsType::query()
                        ->where(
                            'name',
                            'ilike',
                            '%возраст%до%'
                        )
                        ->get([
                            'id',
                            'name',
                        ])
                        ->filter(
                            static function (
                                ContraindicationsType $contraindication
                            ) use ($patientAge): bool {
                                $name = trim(
                                    (string) $contraindication->name
                                );

                                $matched = preg_match(
                                    '/возраст\s+до\s+(\d{1,3})/ui',
                                    $name,
                                    $matches
                                );

                                if ($matched !== 1) {
                                    return false;
                                }

                                $minimumAge = (int) $matches[1];

                                return $patientAge < $minimumAge;
                            }
                        )
                        ->pluck('id');

                $contraindicationIds = $contraindicationIds
                    ->merge($ageContraindicationIds);
            }

            $contraindicationIds = $contraindicationIds
                ->filter()
                ->map(
                    static fn ($contraindicationId): int =>
                    (int) $contraindicationId
                )
                ->unique()
                ->values();

            /*
             * Исключаем препараты, противопоказанные пациенту.
             */
            if ($contraindicationIds->isNotEmpty()) {
                $drugsQuery->whereDoesntHave(
                    'contraindications',
                    function ( $query) use (
                        $contraindicationIds
                    ): void {
                        $query->whereIn(
                            'contraindications_types.id',
                            $contraindicationIds->all()
                        );
                    }
                );
            }
        }

        /*
         * Запрашиваем на одну запись больше, чтобы определить,
         * существует ли следующая страница Select2.
         */
        $drugs = $drugsQuery
            ->orderBy('name')
            ->forPage($page, $perPage + 1)
            ->get();

        $hasMore = $drugs->count() > $perPage;

        $results = $drugs
            ->take($perPage)
            ->map(
                static function (Drug $drug): array {
                    return [
                        'id' => (int) $drug->id,

                        /*
                         * Поле text обязательно для Select2.
                         */
                        'text' => (string) $drug->name,

                        'name' => (string) $drug->name,
                        'latin_name' =>
                            (string) ($drug->latin_name ?? ''),

                        'strict' => (bool) $drug->strict,
                    ];
                }
            )
            ->values();

        return response()->json([
            'results' => $results,
            'pagination' => [
                'more' => $hasMore,
            ],
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

    public function searchPatientFor(int $id, ?Request $request = null): JsonResponse
    {
        $scope = $this->patientSearchScope($request ?? request());
        $patient = Patient::query()
            ->with([
                'doctor',
                'diagnose',
            ])
            ->findOrFail($id);

        if ($scope !== 'all') {
            $isInpatient = PatientCareScope::isCurrentlyInpatient((int) $patient->id);

            if (($scope === 'inpatient') !== $isInpatient) {
                return response()->json([
                    'message' => $scope === 'outpatient'
                        ? 'Пациент сейчас находится в стационаре и недоступен для выбора в амбулаторной записи.'
                        : 'Пациент сейчас не находится в стационаре.',
                ], 422);
            }
        }

        $birthAt = $patient->birth_at
            ? Carbon::parse($patient->birth_at)
            : null;

        $fullName = collect([
            $patient->surname,
            $patient->name,
            $patient->patronym,
        ])->filter()->implode(' ');

        $doctorName = collect([
            data_get($patient, 'doctor.surname'),
            data_get($patient, 'doctor.name'),
            data_get($patient, 'doctor.patronym'),
        ])->filter()->implode(' ');

        return response()->json([
            'id' => (int) $patient->id,
            'full_name' => $fullName,
            'surname' => $patient->surname,
            'name' => $patient->name,
            'patronym' => $patient->patronym,
            'birth_at' => $birthAt?->format('Y-m-d'),
            'birth_at_display' => $birthAt?->format('d.m.Y'),
            'age' => $birthAt?->age,
            'gender' => $patient->gender,
            'medcard_number' => $patient->medcard_number,
            'address_registration' => $patient->address_registration,
            'address_residence' => $patient->address_residence,
            'serial' => $patient->serial,
            'number' => $patient->number,
            'department_code' => $patient->department_code,
            'issued_by' => $patient->issued_by,
            'issued_at' => $patient->issued_at
                ? Carbon::parse($patient->issued_at)->format('Y-m-d')
                : null,
            'birth_place' => $patient->birth_place,
            'snils' => $patient->snils,
            'oms' => $patient->oms,
            'phone' => $patient->phone,
            'email' => $patient->email,
            'doctor' => [
                'id' => data_get($patient, 'doctor.id'),
                'name' => $doctorName,
            ],
            'diagnosis' => [
                'id' => $patient->diagnose_id,
                'code' => data_get($patient, 'diagnose.code'),
                'title' => data_get($patient, 'diagnose.title')
                    ?: data_get($patient, 'diagnose.name'),
            ],
        ]);
    }

    private function patientSearchScope(Request $request): string
    {
        $scope = $request->query->has('patient_scope')
            ? $request->query('patient_scope')
            : 'all';

        if (!is_string($scope) || !in_array($scope, ['all', 'inpatient', 'outpatient'], true)) {
            abort(422, 'Некорректная область поиска пациентов. Допустимые значения: all, inpatient, outpatient.');
        }

        return $scope;
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
        $query = trim(
            (string) $request->get(
                'query',
                ''
            )
        );

        if (mb_strlen($query) < 2) {
            return response()->json([
                'suggestions' => [],
            ]);
        }

        /*
         * Основной источник — DaData.
         *
         * Поддерживаем оба имени переменной окружения:
         * DADATA_API_KEY и DADATA_TOKEN.
         */
        $token = config('dadata.dadata_api')
            ?: config('dadata.token');

        if ($token) {
            try {
                $response = Http::withHeaders([
                    'Authorization' =>
                        'Token ' . $token,
                    'Content-Type' =>
                        'application/json',
                    'Accept' =>
                        'application/json',
                ])
                    ->timeout(8)
                    ->post(
                        'https://suggestions.dadata.ru/suggestions/api/4_1/rs/suggest/address',
                        [
                            'query' => $query,
                            'count' => 8,
                            'division' =>
                                'administrative',
                        ]
                    );

                if ($response->successful()) {
                    $suggestions = collect(
                        $response->json(
                            'suggestions',
                            []
                        )
                    )
                        ->map(
                            static function (
                                array $item
                            ): array {
                                $data =
                                    $item['data']
                                    ?? [];

                                return [
                                    'value' =>
                                        $item['value']
                                        ?? '',

                                    'unrestricted_value' =>
                                        $item[
                                            'unrestricted_value'
                                        ]
                                        ?? '',

                                    'postal_code' =>
                                        $data[
                                            'postal_code'
                                        ]
                                        ?? null,

                                    'region' =>
                                        $data[
                                            'region_with_type'
                                        ]
                                        ?? null,

                                    'city' =>
                                        $data[
                                            'city_with_type'
                                        ]
                                        ?? $data[
                                            'settlement_with_type'
                                        ]
                                        ?? null,

                                    'street' =>
                                        $data[
                                            'street_with_type'
                                        ]
                                        ?? null,

                                    'house' =>
                                        $data['house']
                                        ?? null,

                                    'flat' =>
                                        $data['flat']
                                        ?? null,

                                    'fias_id' =>
                                        $data['fias_id']
                                        ?? null,

                                    'fias_level' =>
                                        $data[
                                            'fias_level'
                                        ]
                                        ?? null,

                                    'geo_lat' =>
                                        $data['geo_lat']
                                        ?? null,

                                    'geo_lon' =>
                                        $data['geo_lon']
                                        ?? null,

                                    'source' =>
                                        'dadata',
                                ];
                            }
                        )
                        ->values();

                    return response()->json([
                        'suggestions' =>
                            $suggestions,
                        'source' =>
                            'dadata',
                    ]);
                }

                logger()->warning(
                    'DaData address request failed',
                    [
                        'status' =>
                            $response->status(),
                        'body' =>
                            mb_substr(
                                $response->body(),
                                0,
                                500
                            ),
                    ]
                );
            } catch (\Throwable $exception) {
                logger()->warning(
                    'DaData address request exception',
                    [
                        'message' =>
                            $exception->getMessage(),
                    ]
                );
            }
        } else {
            logger()->warning(
                'DaData token is not configured; using OSM fallback.'
            );
        }

        /*
         * Fallback без DaData.
         *
         * Нужен прежде всего для уже подтверждённого адреса:
         * карта сможет получить координаты даже если ключ DaData
         * отсутствует или внешний сервис временно недоступен.
         */
        try {
            $userAgent = sprintf(
                '%s/1.0 (%s)',
                (string) config(
                    'app.name',
                    'Laravel'
                ),
                (string) config(
                    'app.url',
                    'localhost'
                )
            );

            $response = Http::withHeaders([
                'Accept' =>
                    'application/json',
                'User-Agent' =>
                    $userAgent,
            ])
                ->timeout(8)
                ->get(
                    'https://nominatim.openstreetmap.org/search',
                    [
                        'q' => $query,
                        'format' =>
                            'jsonv2',
                        'addressdetails' =>
                            1,
                        'limit' =>
                            8,
                        'countrycodes' =>
                            'ru',
                        'accept-language' =>
                            'ru',
                    ]
                );

            if (!$response->successful()) {
                return response()->json(
                    [
                        'suggestions' => [],
                        'message' =>
                            'Сервис поиска адресов временно недоступен.',
                    ],
                    502
                );
            }

            $suggestions = collect(
                $response->json()
            )
                ->map(
                    static function (
                        array $item
                    ): array {
                        $address =
                            $item['address']
                            ?? [];

                        $city =
                            $address['city']
                            ?? $address['town']
                            ?? $address['village']
                            ?? $address[
                                'municipality'
                            ]
                            ?? null;

                        $region =
                            $address['state']
                            ?? $address['region']
                            ?? $address[
                                'state_district'
                            ]
                            ?? null;

                        $street =
                            $address['road']
                            ?? $address[
                                'pedestrian'
                            ]
                            ?? $address[
                                'residential'
                            ]
                            ?? null;

                        return [
                            'value' =>
                                $item[
                                    'display_name'
                                ]
                                ?? '',

                            'unrestricted_value' =>
                                $item[
                                    'display_name'
                                ]
                                ?? '',

                            'postal_code' =>
                                $address[
                                    'postcode'
                                ]
                                ?? null,

                            'region' =>
                                $region,

                            'city' =>
                                $city,

                            'street' =>
                                $street,

                            'house' =>
                                $address[
                                    'house_number'
                                ]
                                ?? null,

                            'flat' =>
                                null,

                            'fias_id' =>
                                null,

                            'fias_level' =>
                                null,

                            'geo_lat' =>
                                $item['lat']
                                ?? null,

                            'geo_lon' =>
                                $item['lon']
                                ?? null,

                            'source' =>
                                'osm',
                        ];
                    }
                )
                ->filter(
                    static fn (
                        array $item
                    ): bool =>
                        $item['value'] !== ''
                        && $item[
                            'geo_lat'
                        ] !== null
                        && $item[
                            'geo_lon'
                        ] !== null
                )
                ->values();

            return response()->json([
                'suggestions' =>
                    $suggestions,
                'source' =>
                    'osm',
            ]);
        } catch (\Throwable $exception) {
            logger()->error(
                'Address search fallback failed',
                [
                    'message' =>
                        $exception->getMessage(),
                ]
            );

            return response()->json(
                [
                    'suggestions' => [],
                    'message' =>
                        'Не удалось получить координаты адреса.',
                ],
                502
            );
        }
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
