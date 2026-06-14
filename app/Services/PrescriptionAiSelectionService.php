<?php

namespace App\Services;

use App\Helpers\DoctorPanel\DrugSelection;
use App\Models\Diagnose;
use App\Models\Drug;
use App\Models\Patient;
use App\Models\PatientCondition;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class PrescriptionAiSelectionService
{
    private const MAX_CANDIDATES_FOR_LLM = 12;
    private const MAX_RESULT_ITEMS = 5;

    public function process(array $data): array
    {
        $patientId = !empty($data['patient_id']) ? (int) $data['patient_id'] : null;
        $diagnosisCode = $data['diagnosis_code'] ?? null;
        $birthAt = $data['birth_at'] ?? null;
        $indicationSource = $data['indication_source'] ?? '';

        $patient = $patientId ? Patient::query()->find($patientId) : null;

        $diagnosis = $this->resolveDiagnosis($patient, $diagnosisCode);

        if (!$diagnosis) {
            return [
                'items' => [],
                'message' => 'Не удалось определить диагноз пациента для подбора препаратов.',
            ];
        }

        $birthAt = $birthAt
            ?: $patient?->birth_at
                ?: $patient?->birth_date
                    ?: null;

        $patientContext = $this->buildPatientContext($patient, $diagnosis, $birthAt);

        $candidates = $this->buildCandidateDrugs(
            patient: $patient,
            diagnosis: $diagnosis,
            birthAt: $birthAt,
            indicationSource: $indicationSource
        );

        if ($candidates->isEmpty()) {
            return [
                'items' => [],
                'message' => 'Не найдено подходящих препаратов после проверки показаний и ограничений.',
            ];
        }

        $llmItems = $this->askLLM(
            patientContext: $patientContext,
            candidates: $candidates->take(self::MAX_CANDIDATES_FOR_LLM)->values()
        );
        $items = $this->normalizeLlmItems($llmItems, $candidates, $diagnosis);

        if (empty($items)) {
            $items = $this->fallbackItems($candidates, $diagnosis);
        }

        return [
            'items' => array_slice($items, 0, self::MAX_RESULT_ITEMS),
            'meta' => [
                'diagnosis_code' => $diagnosis->code,
                'candidate_count' => $candidates->count(),
                'using_llm' => is_array($llmItems),
            ],
        ];
    }

    private function resolveDiagnosis(?Patient $patient, ?string $diagnosisCode): ?Diagnose
    {
        if ($diagnosisCode) {
            $diagnosis = Diagnose::query()
                ->where('code', $diagnosisCode)
                ->first();

            if ($diagnosis) {
                return $diagnosis;
            }
        }

        if ($patient && !empty($patient->diagnose_id)) {
            return Diagnose::query()->find($patient->diagnose_id);
        }

        return null;
    }

    private function buildPatientContext(?Patient $patient, Diagnose $diagnosis, ?string $birthAt): array
    {
        $age = null;

        if ($birthAt) {
            try {
                $age = Carbon::parse($birthAt)->age;
            } catch (\Throwable) {
                $age = null;
            }
        }

        $conditions = $patient
            ? PatientCondition::query()
                ->where('patient_id', $patient->id)
                ->where('status', 'active')
                ->with('condition:id,name')
                ->get()
                ->map(fn ($row) => $row->condition?->name)
                ->filter()
                ->unique()
                ->values()
                ->all()
            : [];

        $latestUrine = $this->getLatestLabResults($patient, 'моча');
        $latestSerum = $this->getLatestLabResults($patient, 'сыворотка');

        return [
            'patient_id' => $patient?->id,
            'gender' => $patient?->gender ?? $patient?->sex ?? null,
            'birth_at' => $birthAt,
            'age' => $age,
            'diagnosis' => [
                'id' => $diagnosis->id,
                'code' => $diagnosis->code,
                'title' => $diagnosis->title ?? $diagnosis->name ?? null,
            ],
            'active_conditions' => $conditions,
            'latest_labs' => [
                'urine' => $latestUrine,
                'serum' => $latestSerum,
            ],
        ];
    }

    private function buildCandidateDrugs(
        ?Patient $patient,
        Diagnose $diagnosis,
        ?string $birthAt,
        string $indicationSource = ''
    ): Collection {
        $drugSelection = new DrugSelection();

        $diagnosisIds = [$diagnosis->id];

        $query = Drug::query()
            ->with(['indications' => function ($q) use ($diagnosisIds) {
                $q->whereIn('diagnose_id', $diagnosisIds);
            }])
            ->whereHas('indications', function ($q) use ($diagnosisIds, $indicationSource) {
                $q->whereIn('diagnose_id', $diagnosisIds);

                if ($indicationSource === 'russia') {
                    $q->where('indicated_in_russia', true);
                } elseif ($indicationSource === 'fda') {
                    $q->where('indicated_by_fda', true);
                }
            });

        $blockedContraIds = array_values(array_unique(array_merge(
            $drugSelection->getPatientClinicalConditionIds($patient),
            $drugSelection->getAgeContraindicationIds($birthAt),
        )));

        if (!empty($blockedContraIds)) {
            $query->whereDoesntHave('contraindications', function ($q) use ($blockedContraIds) {
                $q->whereIn('contraindications_types.id', $blockedContraIds);
            });
        }

        $patientConditionNames = $this->getPatientConditionNames($patient);

        if (in_array('беременность', $patientConditionNames, true)) {
            $query->where('pregnancy', true);
        }

        if (in_array('лактация', $patientConditionNames, true)) {
            $query->where('lactation', true);
        }

        $drugs = $query
            ->orderBy('name')
            ->limit(30)
            ->get();

        $urineResults = collect($this->getLatestLabResults($patient, 'моча'));
        $serumResults = collect($this->getLatestLabResults($patient, 'сыворотка'));

        return $drugs
            ->filter(fn (Drug $drug) => $this->drugAllowedByLabs($drug, $urineResults, $serumResults))
            ->map(function (Drug $drug) use ($drugSelection, $diagnosis) {
                $drugKey = $drugSelection->getDrugPresetKey($drug);
                $preset = $drugSelection->resolvePrescriptionPreset($drugKey, $diagnosis->code);
                $forms = $drugSelection->extractDrugFormsForAutofill($drug);

                $warning = null;

                if (!$preset) {
                    $preset = $drugSelection->buildFallbackPrescriptionPreset($forms);
                    $warning = 'Preset для препарата не найден. Подставлен базовый вариант, врач должен проверить дозировку и схему.';
                }

                $totalUnits = ((int) ($preset['quantity'] ?? 0)) * ((int) ($preset['standard'] ?? 1));

                $usageInstructions = $this->buildUsageInstructions($preset, $drugSelection);

                $indication = $drug->indications->first();

                return [
                    'drug' => $drug,
                    'drug_id' => $drug->id,
                    'inn' => $drug->name,
                    'drug_name' => $drug->name,
                    'latin_name' => $drug->latin_name,
                    'trade_names' => $drug->latin_name,
                    'strict' => (bool) ($drug->strict ?? false),
                    'indicated_in_russia' => (bool) ($indication->indicated_in_russia ?? false),
                    'indicated_by_fda' => (bool) ($indication->indicated_by_fda ?? false),
                    'preset' => [
                        'drug_form' => $preset['drug_form'] ?? '',
                        'dosage' => $preset['dosage'] ?? '',
                        'quantity' => $preset['quantity'] ?? '',
                        'standard' => $preset['standard'] ?? 1,
                        'taking_drug' => $preset['taking_drug'] ?? 1,
                        'taking_count' => $preset['taking_count'] ?? 2,
                        'taking_time_meal' => $preset['taking_time_meal'] ?? 1,
                        'validity_period' => $preset['validity_period'] ?? 60,
                        'usage_instructions' => $usageInstructions,
                        'total_units' => $totalUnits,
                    ],
                    'forms' => $forms,
                    'warning' => $warning,
                ];
            })
            ->values();
    }

    private function getPatientConditionNames(?Patient $patient): array
    {
        if (!$patient) {
            return [];
        }

        return PatientCondition::query()
            ->where('patient_id', $patient->id)
            ->where('status', 'active')
            ->with('condition:id,name')
            ->get()
            ->map(fn ($row) => $row->condition?->name)
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function getLatestLabResults(?Patient $patient, string $sampleType): array
    {
        if (!$patient || !method_exists($patient, 'labResearches')) {
            return [];
        }

        $research = $patient->labResearches()
            ->where('sample_type', $sampleType)
            ->latest('created_at')
            ->first();

        if (!$research) {
            return [];
        }

        return collect($research->results ?? [])
            ->map(function ($row) {
                if (is_array($row)) {
                    return $row;
                }

                return [
                    'lab_parameter_id' => $row->lab_parameter_id ?? $row->parameter_id ?? null,
                    'value' => $row->value ?? null,
                ];
            })
            ->filter(fn ($row) => !empty($row['lab_parameter_id']))
            ->values()
            ->all();
    }

    private function drugAllowedByLabs(Drug $drug, Collection $urineResults, Collection $serumResults): bool
    {
        if ($urineResults->isNotEmpty()) {
            $proteinResult = $urineResults->firstWhere('lab_parameter_id', 63);

            if ($proteinResult) {
                $proteinValue = mb_strtolower(trim((string) ($proteinResult['value'] ?? '')));
                $proteinPositive = !in_array($proteinValue, ['', 'отрицательно', 'отсутствует'], true);

                if ($proteinPositive && array_key_exists('protein_allowed', $drug->getAttributes())) {
                    if (!(bool) $drug->getAttribute('protein_allowed')) {
                        return false;
                    }
                }
            }
        }

        if ($serumResults->isNotEmpty()) {
            $glucoseResult = $serumResults->firstWhere('lab_parameter_id', 19);

            if ($glucoseResult) {
                $glucoseValue = (float) str_replace(',', '.', (string) ($glucoseResult['value'] ?? 0));

                if ($glucoseValue > 5.5 && array_key_exists('glucose_allowed', $drug->getAttributes())) {
                    if (!(bool) $drug->getAttribute('glucose_allowed')) {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    private function buildUsageInstructions(array $preset, DrugSelection $drugSelection): string
    {
        return sprintf(
            'По %s %s %s раз(а) в день %s еды',
            $preset['taking_drug'] ?? 1,
            $drugSelection->getFormLabelForInstruction($preset['drug_form'] ?? ''),
            $preset['taking_count'] ?? 1,
            ((int) ($preset['taking_time_meal'] ?? 1) === 2 ? 'до' : 'после')
        );
    }

    private function askLLM(array $patientContext, Collection $candidates): ?array
    {
        $candidatePayload = $candidates
            ->map(function (array $row) {
                return [
                    'drug_id' => $row['drug_id'],
                    'inn' => $row['inn'],
                    'latin_name' => $row['latin_name'],
                    'strict' => $row['strict'],
                    'pregnancy' => $row['drug']->pregnancy,
                    'lactation' => $row['drug']->lactation,
                    'indicated_in_russia' => $row['indicated_in_russia'],
                    'indicated_by_fda' => $row['indicated_by_fda'],
                    'preset' => $row['preset'],
                    'warning' => $row['warning'],
                ];
            })
            ->values()
            ->all();

        $systemPrompt = <<<PROMPT
Ты — медицинский ассистент в психиатрическом модуле. Твоя задача — НЕ выписывать рецепт самостоятельно, а помочь врачу выбрать препарат из уже отфильтрованного списка.

Важные правила:
- Используй ТОЛЬКО препараты из списка candidates.
- Нельзя придумывать новые drug_id, МНН, дозировки, формы выпуска и схемы.
- Схему приёма бери из поля preset выбранного препарата.
- Учитывай диагноз, возраст, пол, активные состояния пациента, беременность/лактацию и данные анализов, если они есть.
- Если препарат требует особой осторожности, добавь это в safety_warnings.
- Верни только JSON без текста вне JSON.
- Максимум 5 вариантов.
- Не используй Markdown.
- Не пиши "важно" в описании своего выбора. Лучше пиши подробное описание. А тексты типа "Важно: Пациенту следует провести полное медицинское обследование перед началом приема препарата, включая проверку функции печени и почек" писать не надо.

Схема ответа:
{
  "items": [
    {
      "drug_id": 123,
      "priority": 1,
      "reason": "почему препарат подходит этому пациенту и побольше аргументов",
      "safety_warnings": ["что врачу проверить перед назначением"],
      "doctor_note": "краткий комментарий для врача",
      "lactation": "вставь true, если, судя по данным препаратов из таблицы drugs, он разрешён при лактации или false если нет",
      "pregnancy": "вставь true, если, судя по данным препаратов из таблицы drugs, он разрешён при беременности или false если нет",
      "hint": "в это поле впиши json со структурой соответствующего препарата из таблицы drugs."
    }
  ]
}
PROMPT;

        $userPayload = [
            'patient_context' => $patientContext,
            'candidates' => $candidatePayload,
        ];

        $provider = config('llm.provider', 'ollama');

        try {
            if ($provider === 'ollama') {
                $responseJson = $this->ollamaChatJson($systemPrompt, json_encode($userPayload, JSON_UNESCAPED_UNICODE));
            } else {
                $responseJson = $this->openaiChatJson($systemPrompt, json_encode($userPayload, JSON_UNESCAPED_UNICODE));
            }

            if (!is_string($responseJson) || trim($responseJson) === '') {
                return null;
            }

            $data = $this->decodeJsonSafe($responseJson);

            if (!is_array($data) || !isset($data['items']) || !is_array($data['items'])) {
                return null;
            }

            return $data['items'];
        } catch (\Throwable) {
            return null;
        }
    }

    private function normalizeLlmItems(?array $llmItems, Collection $candidates, Diagnose $diagnosis): array
    {
        if (!is_array($llmItems)) {
            return [];
        }

        $candidateMap = $candidates->keyBy('drug_id');
        $out = [];
        $used = [];

        foreach ($llmItems as $row) {
            if (!is_array($row) || empty($row['drug_id'])) {
                continue;
            }

            $drugId = (int) $row['drug_id'];

            if (isset($used[$drugId])) {
                continue;
            }

            if (!$candidateMap->has($drugId)) {
                continue;
            }

            $candidate = $candidateMap->get($drugId);
            $preset = $candidate['preset'];

            $warnings = $row['safety_warnings'] ?? [];
            if (!is_array($warnings)) {
                $warnings = [$warnings];
            }

            if (!empty($candidate['warning'])) {
                $warnings[] = $candidate['warning'];
            }

            $reason = trim((string) ($row['reason'] ?? ''));

            if ($reason === '') {
                $reason = 'Подходит по диагнозу и текущим фильтрам пациента.';
            }

            if (!empty($warnings)) {
                $reason .= ' Важно: ' . implode('; ', array_filter($warnings));
            }

            $out[] = [
                'drug_id' => $candidate['drug_id'],
                'inn' => $candidate['inn'],
                'drug_name' => $candidate['drug_name'],
                'latin_name' => $candidate['latin_name'],
                'trade_names' => $candidate['trade_names'],
                'strict' => $candidate['strict'],
                'indicated_in_russia' => $candidate['indicated_in_russia'],
                'indicated_by_fda' => $candidate['indicated_by_fda'],

                'reason' => $reason,
                'doctor_note' => $row['doctor_note'] ?? null,

                'drug_form' => $preset['drug_form'] ?? '',
                'dosage' => $preset['dosage'] ?? '',
                'quantity' => $preset['quantity'] ?? '',
                'standard' => $preset['standard'] ?? 1,
                'taking_drug' => $preset['taking_drug'] ?? 1,
                'taking_count' => $preset['taking_count'] ?? 2,
                'taking_time_meal' => $preset['taking_time_meal'] ?? 1,
                'validity_period' => $preset['validity_period'] ?? 60,
                'usage_instructions' => $preset['usage_instructions'] ?? '',
                'total_units' => $preset['total_units'] ?? 0,

                'warning' => !empty($warnings) ? implode('; ', array_filter($warnings)) : null,
            ];

            $used[$drugId] = true;
        }

        return $out;
    }

    private function fallbackItems(Collection $candidates, Diagnose $diagnosis): array
    {
        return $candidates
            ->take(self::MAX_RESULT_ITEMS)
            ->map(function (array $candidate) {
                $preset = $candidate['preset'];

                $reason = $candidate['warning']
                    ?: 'Подходит по диагнозу и текущим фильтрам. Проверьте клиническую целесообразность, дозировку и схему перед выпиской.';

                return [
                    'drug_id' => $candidate['drug_id'],
                    'inn' => $candidate['inn'],
                    'drug_name' => $candidate['drug_name'],
                    'latin_name' => $candidate['latin_name'],
                    'trade_names' => $candidate['trade_names'],
                    'strict' => $candidate['strict'],
                    'indicated_in_russia' => $candidate['indicated_in_russia'],
                    'indicated_by_fda' => $candidate['indicated_by_fda'],

                    'reason' => $reason,

                    'drug_form' => $preset['drug_form'] ?? '',
                    'dosage' => $preset['dosage'] ?? '',
                    'quantity' => $preset['quantity'] ?? '',
                    'standard' => $preset['standard'] ?? 1,
                    'taking_drug' => $preset['taking_drug'] ?? 1,
                    'taking_count' => $preset['taking_count'] ?? 2,
                    'taking_time_meal' => $preset['taking_time_meal'] ?? 1,
                    'validity_period' => $preset['validity_period'] ?? 60,
                    'usage_instructions' => $preset['usage_instructions'] ?? '',
                    'total_units' => $preset['total_units'] ?? 0,

                    'warning' => $candidate['warning'],
                ];
            })
            ->values()
            ->all();
    }

    private function ollamaChatJson(string $systemPrompt, string $userText): ?string
    {
        $payload = [
            'model' => config('llm.ollama.model'),
            'stream' => false,
            'format' => 'json',
            'options' => [
                'temperature' => config('llm.ollama.temperature', 0.0),
                'num_ctx' => config('llm.ollama.num_ctx', 4096),
            ],
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userText],
            ],
        ];

        $resp = Http::timeout(config('llm.ollama.timeout', 45))
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post(rtrim(config('llm.ollama.base_url'), '/') . '/api/chat', $payload);

        if (!$resp->successful()) {
            return null;
        }

        return data_get($resp->json(), 'message.content');
    }

    private function openaiChatJson(string $systemPrompt, string $userText): ?string
    {
        $payload = [
            'model' => config('llm.openai.model'),
            'temperature' => (float) config('llm.openai.temperature', 0.0),
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userText],
            ],
            'response_format' => ['type' => 'json_object'],
        ];

        $resp = Http::timeout(config('llm.openai.timeout', 45))
            ->withToken(config('llm.openai.api_key'))
            ->post(rtrim(config('llm.openai.base_url'), '/') . '/chat/completions', $payload);

        if (!$resp->successful()) {
            return null;
        }

        $content = data_get($resp->json(), 'choices.0.message.content');

        return is_string($content) ? $content : null;
    }

    private function decodeJsonSafe(string $s): ?array
    {
        $clean = preg_replace('/^\s*```(?:json)?\s*|\s*```\s*$/u', '', $s) ?? $s;
        $clean = preg_replace('/^\xEF\xBB\xBF|\p{C}+/u', '', $clean) ?? $clean;
        $clean = trim($clean);

        try {
            $decoded = json_decode($clean, true, 512, JSON_THROW_ON_ERROR);
            if (is_array($decoded)) {
                return $decoded;
            }
        } catch (\Throwable) {
            // ignore
        }

        try {
            $first = json_decode($clean, true, 512, JSON_THROW_ON_ERROR);
            if (is_string($first)) {
                $second = json_decode($first, true, 512, JSON_THROW_ON_ERROR);
                if (is_array($second)) {
                    return $second;
                }
            }
        } catch (\Throwable) {
            // ignore
        }

        if (preg_match('/\{.*\}\s*$/s', $clean, $m)) {
            try {
                $decoded = json_decode($m[0], true, 512, JSON_THROW_ON_ERROR);
                if (is_array($decoded)) {
                    return $decoded;
                }
            } catch (\Throwable) {
                // ignore
            }
        }

        return null;
    }
}
