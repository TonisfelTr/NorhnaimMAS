<?php

namespace App\Services;

use App\Models\Patient;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Http;

class HospitalizationAnalyzeService
{
    private const MAX_CLINICAL_TEXT_CHARS = 24000;
    private const MAX_LLM_RESPONSE_BYTES = 131072;

    /**
     * Анализирует сведения для проекта направления на госпитализацию.
     *
     * Решение является поддержкой врача: сервис не отправляет направление,
     * не меняет тип госпитализации и не записывает диагноз автоматически.
     */
    public function analyze(
        string $type,
        Patient $patient,
        ?string $clinicalText = null,
        array $sources = []
    ): array {
        $startedAt = microtime(true);
        $warnings = [];

        $type = $type === 'emergency' ? 'emergency' : 'planned';
        $clinicalText = $this->cleanText($clinicalText ?? '');

        if (mb_strlen($clinicalText, 'UTF-8') > self::MAX_CLINICAL_TEXT_CHARS) {
            return [
                'error' => 'CLINICAL_TEXT_TOO_LARGE',
                'message' => 'Дополнительный клинический текст слишком большой.',
            ];
        }

        $context = $this->buildClinicalContext($patient, $sources, $clinicalText);

        if ($this->contextIsEmpty($context)) {
            return [
                'error' => 'CLINICAL_CONTEXT_EMPTY',
                'message' => 'Добавьте текст или выберите хотя бы один источник данных пациента.',
            ];
        }

        $primaryModel = $this->configuredModelName('primary');
        $reviewerModel = $this->configuredModelName('reviewer');

        $primaryStartedAt = microtime(true);
        $primaryContent = $this->chatJson(
            role: 'primary',
            systemPrompt: $this->primarySystemPrompt($type),
            userPayload: [
                'task' => 'hospitalization_decision_support',
                'requested_page' => $type,
                'clinical_context' => $context,
            ],
            schema: $this->primarySchema(),
            maxTokens: 1700
        );

        $primary = $this->decodeJsonSafe($primaryContent);
        $primaryUsed = is_array($primary);

        if (!$primaryUsed) {
            return [
                'error' => 'HOSPITALIZATION_AI_PRIMARY_FAILED',
                'message' => 'Основная модель не вернула корректный структурированный результат.',
                'models' => [
                    'primary' => [
                        'name' => $primaryModel,
                        'used' => false,
                    ],
                    'reviewer' => [
                        'name' => $reviewerModel,
                        'used' => false,
                        'enabled' => $this->reviewEnabled(),
                    ],
                ],
                'warnings' => ['HOSPITALIZATION_PRIMARY_INVALID_JSON'],
            ];
        }

        $primary = $this->normalizePrimary($primary, $type);
        $primaryFinishedAt = microtime(true);

        $review = null;
        $reviewUsed = false;
        $reviewStartedAt = microtime(true);

        if ($this->reviewEnabled() && $reviewerModel !== '') {
            $reviewContent = $this->chatJson(
                role: 'reviewer',
                systemPrompt: $this->reviewSystemPrompt(),
                userPayload: [
                    'task' => 'independent_medical_review_of_hospitalization_draft',
                    'requested_page' => $type,
                    'clinical_context' => $context,
                    'primary_analysis' => $primary,
                ],
                schema: $this->reviewSchema(),
                maxTokens: max(900, (int) config('llm.ollama.review_max_tokens', 1800))
            );

            $decodedReview = $this->decodeJsonSafe($reviewContent);

            if (is_array($decodedReview)) {
                $review = $this->normalizeReview($decodedReview);
                $reviewUsed = true;
            } else {
                $warnings[] = 'HOSPITALIZATION_MEDICAL_REVIEW_INVALID_JSON';
            }
        } elseif (!$this->reviewEnabled()) {
            $warnings[] = 'HOSPITALIZATION_MEDICAL_REVIEW_DISABLED';
        } else {
            $warnings[] = 'HOSPITALIZATION_MEDICAL_REVIEW_MODEL_NOT_CONFIGURED';
        }

        $finishedAt = microtime(true);

        return [
            'type' => $type,
            'primary' => $primary,
            'medical_review' => $review,
            'draft' => $this->buildDraft($primary, $review, $patient),
            'decision_support_only' => true,
            'models' => [
                'primary' => [
                    'name' => $primaryModel,
                    'role' => 'clinical_extraction_and_draft',
                    'used' => $primaryUsed,
                ],
                'reviewer' => [
                    'name' => $reviewerModel,
                    'role' => 'independent_medical_review',
                    'used' => $reviewUsed,
                    'enabled' => $this->reviewEnabled(),
                ],
            ],
            'warnings' => array_values(array_unique($warnings)),
            'timings_ms' => [
                'primary' => $this->milliseconds($primaryStartedAt, $primaryFinishedAt),
                'medical_review' => $this->milliseconds($reviewStartedAt, $finishedAt),
                'total' => $this->milliseconds($startedAt, $finishedAt),
            ],
        ];
    }

    private function buildClinicalContext(
        Patient $patient,
        array $sources,
        string $clinicalText
    ): array {
        $includeDiagnosis = (bool) ($sources['diagnosis'] ?? true);
        $includeAnamneses = (bool) ($sources['anamneses'] ?? true);
        $includePrescriptions = (bool) ($sources['prescriptions'] ?? false);

        $birthAt = null;
        try {
            $birthAt = $patient->birth_at
                ? CarbonImmutable::parse($patient->birth_at)
                : null;
        } catch (\Throwable) {
            $birthAt = null;
        }

        $context = [
            'patient' => array_filter([
                'age' => $birthAt?->age,
                'gender' => $this->normalizeGender($patient->gender ?? null),
            ], static fn (mixed $value): bool => $value !== null && $value !== ''),
            'additional_clinical_text' => $clinicalText !== '' ? $clinicalText : null,
        ];

        if ($includeDiagnosis) {
            try {
                $patient->loadMissing('diagnose');
                $context['current_diagnosis'] = array_filter([
                    'code' => data_get($patient, 'diagnose.code'),
                    'title' => data_get($patient, 'diagnose.title')
                        ?: data_get($patient, 'diagnose.name'),
                ], static fn (mixed $value): bool => $value !== null && $value !== '');
            } catch (\Throwable) {
                $context['current_diagnosis'] = [];
            }
        }

        if ($includeAnamneses && method_exists($patient, 'anamneses')) {
            try {
                $context['recent_anamneses'] = $patient->anamneses()
                    ->latest('created_at')
                    ->limit(5)
                    ->get(['id', 'title', 'text', 'created_at'])
                    ->map(function (mixed $row): array {
                        return [
                            'date' => optional($row->created_at)->format('Y-m-d H:i'),
                            'title' => $this->shortText((string) ($row->title ?? ''), 200),
                            'text' => $this->shortText(
                                $this->cleanText((string) ($row->text ?? '')),
                                5000
                            ),
                        ];
                    })
                    ->filter(static fn (array $row): bool => $row['text'] !== '')
                    ->values()
                    ->all();
            } catch (\Throwable) {
                $context['recent_anamneses'] = [];
            }
        }

        if ($includePrescriptions && method_exists($patient, 'prescriptions')) {
            try {
                $context['recent_prescriptions'] = $patient->prescriptions()
                    ->latest('created_at')
                    ->limit(12)
                    ->get([
                        'generic_name',
                        'usage_instructions',
                        'created_at',
                    ])
                    ->map(function (mixed $row): array {
                        return array_filter([
                            'date' => optional($row->created_at)->format('Y-m-d'),
                            'drug' => $this->shortText((string) ($row->generic_name ?? ''), 180),
                            'usage' => $this->shortText((string) ($row->usage_instructions ?? ''), 500),
                        ], static fn (mixed $value): bool => $value !== null && $value !== '');
                    })
                    ->values()
                    ->all();
            } catch (\Throwable) {
                $context['recent_prescriptions'] = [];
            }
        }

        return array_filter(
            $context,
            static fn (mixed $value): bool => $value !== null && $value !== [] && $value !== ''
        );
    }

    private function primarySystemPrompt(string $type): string
    {
        $pageLabel = $type === 'emergency'
            ? 'экстренная госпитализация'
            : 'плановая госпитализация';

        return <<<PROMPT
Ты — клинический ассистент врача. Подготовь структурированную поддержку решения для страницы «{$pageLabel}».

Правила:
1. Не выдавай окончательное распоряжение и не подменяй врача.
2. Не придумывай факты, диагнозы, витальные показатели или организацию.
3. Любой красный флаг должен опираться на короткий дословный evidence из clinical_context.
4. Отрицания учитывай как отрицания.
5. Если данных недостаточно, укажи это в missing_information.
6. Различай плановую и экстренную госпитализацию. При признаках непосредственной угрозы жизни или тяжёлого ухудшения отметь recommendation=consider_emergency либо supports_emergency.
7. Поле diagnosis трактуй только как ПРЕДВАРИТЕЛЬНЫЙ диагноз для текущего направления. Он не является установленным диагнозом пациента.
8. Если current_diagnosis отсутствует, сформируй предварительный диагноз только из анамнеза/clinical_context. Если оснований недостаточно — оставь code/title пустыми и укажи, что требуется уточнение, в missing_information.
9. Если current_diagnosis присутствует, используй его только как контекст и всё равно оцени, подтверждается ли он текущими данными.
10. draft_fields — только проект для последующего ручного редактирования врачом.
11. Не назначай лекарства и не указывай дозировки.
12. Не придумывай конкретную медицинскую организацию или её адрес. Эти поля заполняет врач.
13. Верни только JSON по схеме.
PROMPT;
    }

    private function reviewSystemPrompt(): string
    {
        return <<<'PROMPT'
Ты — независимый медицинский рецензент. Перепроверь проект решения о госпитализации, подготовленный другой моделью.

Правила:
1. Не меняй автоматически тип направления, предварительный диагноз или решение врача.
2. Проверь, подтверждаются ли рекомендация, предварительный диагноз и красные флаги исходными клиническими данными.
3. Evidence должен быть коротким дословным фрагментом из clinical_context.
4. Если срочность нельзя подтвердить, urgent=false.
5. Укажи недостающую информацию и сомнительные места.
6. Не назначай лечение и дозировки.
7. Поле confidence — твоя уверенность в собственной рецензии и поддерживаемой рекомендации на основании переданного clinical_context, от 0 до 1.
8. Верни только JSON по схеме.
PROMPT;
    }

    private function primarySchema(): array
    {
        return [
            'type' => 'object',
            'additionalProperties' => false,
            'required' => [
                'recommendation',
                'confidence',
                'reasoning_summary',
                'diagnosis',
                'red_flags',
                'missing_information',
                'draft_fields',
            ],
            'properties' => [
                'recommendation' => [
                    'type' => 'string',
                    'enum' => [
                        'supports_planned',
                        'supports_emergency',
                        'consider_planned',
                        'consider_emergency',
                        'outpatient_or_observation',
                        'insufficient_data',
                    ],
                ],
                'confidence' => ['type' => 'number', 'minimum' => 0, 'maximum' => 1],
                'reasoning_summary' => ['type' => 'string'],
                'diagnosis' => [
                    'type' => 'object',
                    'additionalProperties' => false,
                    'required' => ['code', 'title'],
                    'properties' => [
                        'code' => ['type' => 'string'],
                        'title' => ['type' => 'string'],
                    ],
                ],
                'red_flags' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'additionalProperties' => false,
                        'required' => ['label', 'severity', 'evidence'],
                        'properties' => [
                            'label' => ['type' => 'string'],
                            'severity' => [
                                'type' => 'string',
                                'enum' => ['critical', 'high', 'moderate', 'low'],
                            ],
                            'evidence' => ['type' => 'string'],
                        ],
                    ],
                ],
                'missing_information' => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                ],
                'draft_fields' => [
                    'type' => 'object',
                    'additionalProperties' => false,
                    'required' => [
                        'clinical_summary',
                        'reason',
                        'destination_profile',
                        'urgency_level',
                        'transport_type',
                        'treatment_goal',
                        'stabilization_measures',
                    ],
                    'properties' => [
                        'clinical_summary' => ['type' => 'string'],
                        'reason' => ['type' => 'string'],
                        'destination_profile' => ['type' => 'string'],
                        'urgency_level' => [
                            'type' => 'string',
                            'enum' => ['routine', 'within_24h', 'within_2h', 'immediate', 'unknown'],
                        ],
                        'transport_type' => [
                            'type' => 'string',
                            'enum' => [
                                'ambulance',
                                'resuscitation',
                                'medical_transport',
                                'self',
                                'accompanied',
                                'unknown',
                            ],
                        ],
                        'treatment_goal' => ['type' => 'string'],
                        'stabilization_measures' => ['type' => 'string'],
                    ],
                ],
            ],
        ];
    }

    private function reviewSchema(): array
    {
        return [
            'type' => 'object',
            'additionalProperties' => false,
            'required' => [
                'verdict',
                'confidence',
                'urgent',
                'supported_recommendation',
                'confirmed_red_flags',
                'questionable_points',
                'missing_information',
                'summary',
            ],
            'properties' => [
                'verdict' => [
                    'type' => 'string',
                    'enum' => [
                        'consistent',
                        'partially_consistent',
                        'inconsistent',
                        'insufficient_data',
                    ],
                ],
                'confidence' => [
                    'type' => 'number',
                    'minimum' => 0,
                    'maximum' => 1,
                ],
                'urgent' => ['type' => 'boolean'],
                'supported_recommendation' => [
                    'type' => 'string',
                    'enum' => [
                        'supports_planned',
                        'supports_emergency',
                        'consider_planned',
                        'consider_emergency',
                        'outpatient_or_observation',
                        'insufficient_data',
                    ],
                ],
                'confirmed_red_flags' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'additionalProperties' => false,
                        'required' => ['label', 'evidence'],
                        'properties' => [
                            'label' => ['type' => 'string'],
                            'evidence' => ['type' => 'string'],
                        ],
                    ],
                ],
                'questionable_points' => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                ],
                'missing_information' => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                ],
                'summary' => ['type' => 'string'],
            ],
        ];
    }

    private function chatJson(
        string $role,
        string $systemPrompt,
        array $userPayload,
        array $schema,
        int $maxTokens
    ): ?string {
        $model = $this->configuredModelName($role);
        $baseUrl = trim((string) config('llm.ollama.base_url', 'http://ollama:11434'));

        if ($model === '' || $baseUrl === '') {
            return null;
        }

        $isReviewer = $role === 'reviewer';
        $timeout = (int) config(
            $isReviewer ? 'llm.ollama.review_timeout' : 'llm.ollama.timeout',
            300
        );
        $numCtx = (int) config(
            $isReviewer ? 'llm.ollama.review_num_ctx' : 'llm.ollama.num_ctx',
            $isReviewer ? 8192 : 8192
        );
        $temperature = (float) config(
            $isReviewer ? 'llm.ollama.review_temperature' : 'llm.ollama.temperature',
            0.0
        );
        $think = (bool) config(
            $isReviewer ? 'llm.ollama.review_think' : 'llm.ollama.think',
            false
        );
        $keepAlive = config(
            $isReviewer ? 'llm.ollama.review_keep_alive' : 'llm.ollama.keep_alive',
            $isReviewer ? 0 : '5m'
        );

        $encodedPayload = json_encode(
            $userPayload,
            JSON_UNESCAPED_UNICODE
            | JSON_UNESCAPED_SLASHES
            | JSON_INVALID_UTF8_SUBSTITUTE
        );

        if (!is_string($encodedPayload)) {
            return null;
        }

        try {
            $response = Http::connectTimeout(
                (int) config('llm.ollama.connect_timeout', 10)
            )
                ->timeout(max(30, $timeout))
                ->acceptJson()
                ->asJson()
                ->post(rtrim($baseUrl, '/') . '/api/chat', [
                    'model' => $model,
                    'stream' => false,
                    'think' => $think,
                    'keep_alive' => $keepAlive,
                    'format' => $schema,
                    'options' => [
                        'temperature' => $temperature,
                        'num_ctx' => max(4096, $numCtx),
                        'num_predict' => max(256, $maxTokens),
                    ],
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => $encodedPayload],
                    ],
                ]);

            if (!$response->successful()) {
                \Log::warning('Hospitalization Ollama request failed', [
                    'role' => $role,
                    'model' => $model,
                    'status' => $response->status(),
                ]);

                return null;
            }

            $content = data_get($response->json(), 'message.content');

            if (!is_string($content) || strlen($content) > self::MAX_LLM_RESPONSE_BYTES) {
                return null;
            }

            return $content;
        } catch (\Throwable $exception) {
            \Log::warning('Hospitalization LLM exception', [
                'role' => $role,
                'model' => $model,
                'exception' => get_class($exception),
            ]);

            return null;
        }
    }

    private function normalizePrimary(array $response, string $type): array
    {
        $allowedRecommendations = [
            'supports_planned',
            'supports_emergency',
            'consider_planned',
            'consider_emergency',
            'outpatient_or_observation',
            'insufficient_data',
        ];

        $recommendation = trim((string) ($response['recommendation'] ?? ''));
        if (!in_array($recommendation, $allowedRecommendations, true)) {
            $recommendation = 'insufficient_data';
        }

        $diagnosis = is_array($response['diagnosis'] ?? null)
            ? $response['diagnosis']
            : [];
        $draft = is_array($response['draft_fields'] ?? null)
            ? $response['draft_fields']
            : [];

        return [
            'requested_page' => $type,
            'recommendation' => $recommendation,
            'confidence' => round(max(0.0, min(1.0, (float) ($response['confidence'] ?? 0.0))), 3),
            'reasoning_summary' => $this->shortText(
                (string) ($response['reasoning_summary'] ?? ''),
                3000
            ),
            'diagnosis' => [
                'code' => $this->shortText((string) ($diagnosis['code'] ?? ''), 32),
                'title' => $this->shortText((string) ($diagnosis['title'] ?? ''), 255),
            ],
            // Явный alias для UI: это не установленный диагноз пациента.
            'preliminary_diagnosis' => [
                'code' => $this->shortText((string) ($diagnosis['code'] ?? ''), 32),
                'title' => $this->shortText((string) ($diagnosis['title'] ?? ''), 255),
            ],
            'red_flags' => $this->normalizeRedFlags($response['red_flags'] ?? []),
            'missing_information' => $this->stringList($response['missing_information'] ?? [], 20, 500),
            'draft_fields' => [
                'clinical_summary' => $this->shortText((string) ($draft['clinical_summary'] ?? ''), 8000),
                'reason' => $this->shortText((string) ($draft['reason'] ?? ''), 5000),
                'destination_profile' => $this->shortText((string) ($draft['destination_profile'] ?? ''), 255),
                'urgency_level' => $this->allowedValue(
                    (string) ($draft['urgency_level'] ?? 'unknown'),
                    ['routine', 'within_24h', 'within_2h', 'immediate', 'unknown'],
                    'unknown'
                ),
                'transport_type' => $this->allowedValue(
                    (string) ($draft['transport_type'] ?? 'unknown'),
                    ['ambulance', 'resuscitation', 'medical_transport', 'self', 'accompanied', 'unknown'],
                    'unknown'
                ),
                'treatment_goal' => $this->shortText((string) ($draft['treatment_goal'] ?? ''), 3000),
                'stabilization_measures' => $this->shortText(
                    (string) ($draft['stabilization_measures'] ?? ''),
                    3000
                ),
            ],
        ];
    }

    private function normalizeReview(array $response): array
    {
        return [
            'model' => $this->configuredModelName('reviewer'),
            'confidence' => round(
                max(0.0, min(1.0, (float) ($response['confidence'] ?? 0.0))),
                3
            ),
            'verdict' => $this->allowedValue(
                (string) ($response['verdict'] ?? ''),
                ['consistent', 'partially_consistent', 'inconsistent', 'insufficient_data'],
                'insufficient_data'
            ),
            'urgent' => (bool) ($response['urgent'] ?? false),
            'supported_recommendation' => $this->allowedValue(
                (string) ($response['supported_recommendation'] ?? ''),
                [
                    'supports_planned',
                    'supports_emergency',
                    'consider_planned',
                    'consider_emergency',
                    'outpatient_or_observation',
                    'insufficient_data',
                ],
                'insufficient_data'
            ),
            'confirmed_red_flags' => $this->normalizeConfirmedRedFlags(
                $response['confirmed_red_flags'] ?? []
            ),
            'questionable_points' => $this->stringList($response['questionable_points'] ?? [], 20, 500),
            'missing_information' => $this->stringList($response['missing_information'] ?? [], 20, 500),
            'summary' => $this->shortText((string) ($response['summary'] ?? ''), 2500),
            'changes_decision_automatically' => false,
        ];
    }

    private function buildDraft(array $primary, ?array $review, Patient $patient): array
    {
        $draft = (array) ($primary['draft_fields'] ?? []);
        $diagnosis = (array) (
            $primary['preliminary_diagnosis']
            ?? $primary['diagnosis']
            ?? []
        );

        // Не подставляем установленный диагноз пациента автоматически.
        // Если Qwen не смог сформировать предварительный диагноз, поле остаётся пустым.
        return [
            'preliminary_diagnosis_code' => (string) ($diagnosis['code'] ?? ''),
            'preliminary_diagnosis_title' => (string) ($diagnosis['title'] ?? ''),
            // Старые ключи оставлены для совместимости с уже собранным фронтендом.
            'diagnosis_code' => (string) ($diagnosis['code'] ?? ''),
            'diagnosis_title' => (string) ($diagnosis['title'] ?? ''),
            'clinical_summary' => (string) ($draft['clinical_summary'] ?? ''),
            'reason' => (string) ($draft['reason'] ?? ''),
            'department' => (string) ($draft['destination_profile'] ?? ''),
            'urgency_level' => (string) ($draft['urgency_level'] ?? 'unknown'),
            'transport_type' => (string) ($draft['transport_type'] ?? 'unknown'),
            'treatment_goal' => (string) ($draft['treatment_goal'] ?? ''),
            'stabilization_measures' => (string) ($draft['stabilization_measures'] ?? ''),
            'red_flags' => collect($primary['red_flags'] ?? [])
                ->map(static fn (array $flag): string => trim(
                    ($flag['label'] ?? '')
                    . (($flag['evidence'] ?? '') !== '' ? ': ' . $flag['evidence'] : '')
                ))
                ->filter()
                ->implode("\n"),
            'review_warning' => $review && ($review['urgent'] ?? false)
                ? 'Медицинский рецензент отметил возможную срочность. Требуется немедленная оценка врача.'
                : '',
        ];
    }

    private function normalizeRedFlags(mixed $rows): array
    {
        if (!is_array($rows)) {
            return [];
        }

        return collect($rows)
            ->filter(static fn (mixed $row): bool => is_array($row))
            ->take(20)
            ->map(function (array $row): array {
                return [
                    'label' => $this->shortText((string) ($row['label'] ?? ''), 300),
                    'severity' => $this->allowedValue(
                        (string) ($row['severity'] ?? ''),
                        ['critical', 'high', 'moderate', 'low'],
                        'low'
                    ),
                    'evidence' => $this->shortText((string) ($row['evidence'] ?? ''), 700),
                ];
            })
            ->filter(static fn (array $row): bool => $row['label'] !== '')
            ->values()
            ->all();
    }

    private function normalizeConfirmedRedFlags(mixed $rows): array
    {
        if (!is_array($rows)) {
            return [];
        }

        return collect($rows)
            ->filter(static fn (mixed $row): bool => is_array($row))
            ->take(20)
            ->map(function (array $row): array {
                return [
                    'label' => $this->shortText((string) ($row['label'] ?? ''), 300),
                    'evidence' => $this->shortText((string) ($row['evidence'] ?? ''), 700),
                ];
            })
            ->filter(static fn (array $row): bool => $row['label'] !== '')
            ->values()
            ->all();
    }

    private function stringList(mixed $items, int $limit, int $maxLength): array
    {
        if (!is_array($items)) {
            return [];
        }

        return collect($items)
            ->filter(static fn (mixed $item): bool => is_scalar($item))
            ->map(fn (mixed $item): string => $this->shortText((string) $item, $maxLength))
            ->filter()
            ->unique()
            ->take($limit)
            ->values()
            ->all();
    }

    private function decodeJsonSafe(?string $content): ?array
    {
        if (!is_string($content)) {
            return null;
        }

        $content = trim($content);
        if ($content === '') {
            return null;
        }

        $content = preg_replace('/^```(?:json)?\s*/iu', '', $content) ?? $content;
        $content = preg_replace('/\s*```$/u', '', $content) ?? $content;

        $decoded = json_decode($content, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        $first = strpos($content, '{');
        $last = strrpos($content, '}');
        if ($first !== false && $last !== false && $last > $first) {
            $decoded = json_decode(substr($content, $first, $last - $first + 1), true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return null;
    }

    private function configuredModelName(string $role): string
    {
        if ($role === 'reviewer') {
            return trim((string) config('llm.ollama.review_model', 'medgemma1.5:4b'));
        }

        return trim((string) config(
            'llm.ollama.primary_model',
            config('llm.ollama.model', 'qwen3:14b')
        ));
    }

    private function reviewEnabled(): bool
    {
        return (bool) config('llm.ollama.review_enabled', true);
    }

    private function cleanText(string $value): string
    {
        $value = strip_tags(html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', ' ', $value) ?? $value;
        $value = preg_replace('/\s+/u', ' ', $value) ?? $value;

        return trim($value);
    }

    private function shortText(string $value, int $maxLength): string
    {
        $value = trim(preg_replace('/\s+/u', ' ', $value) ?? $value);

        return $value === ''
            ? ''
            : mb_substr($value, 0, max(1, $maxLength), 'UTF-8');
    }

    private function normalizeGender(mixed $value): ?string
    {
        $value = mb_strtolower(trim((string) $value), 'UTF-8');

        return match ($value) {
            'm', 'male', 'м', 'муж', 'мужской' => 'male',
            'f', 'female', 'ж', 'жен', 'женский' => 'female',
            default => null,
        };
    }

    private function allowedValue(string $value, array $allowed, string $fallback): string
    {
        $value = trim($value);

        return in_array($value, $allowed, true) ? $value : $fallback;
    }

    private function contextIsEmpty(array $context): bool
    {
        $copy = $context;
        unset($copy['patient']);

        return collect($copy)->every(static function (mixed $value): bool {
            return $value === null || $value === '' || $value === [];
        });
    }

    private function milliseconds(float $from, float $to): int
    {
        return (int) round(max(0, $to - $from) * 1000);
    }
}
