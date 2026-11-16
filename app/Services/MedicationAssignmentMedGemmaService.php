<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class MedicationAssignmentMedGemmaService
{
    private const FIELD_CONFIG = [
        'doctor_instruction' => [
            'label' => 'Инструкция пациенту',
            'min' => 40,
            'max_tokens' => 160,
            'prompt' => 'Напиши ТОЛЬКО практическую инструкцию пациенту по приёму уже назначенного препарата. Начни с «Принимайте ...» или «Принимать ...». Обязательно используй название препарата, дозировку с единицей и режим приёма из контекста. НЕ перечисляй жалобы, симптомы, анамнез и диагнозы. НЕ объясняй причину назначения. Не меняй препарат, дозировку и режим. Не пиши название поля, JSON, Markdown, кавычки или текст задания.',
        ],
        'indication' => [
            'label' => 'Показание',
            'min' => 30,
            'max_tokens' => 180,
            'prompt' => 'Напиши только 1–2 предложения, почему именно этот препарат назначен именно этому пациенту. Используй только документированное обоснование из диагноза, анамнезов и эпикризов. Не придумывай показание на основании общих знаний о препарате. Если в записях нет явного обоснования, прямо напиши: «В доступных медицинских записях не найдено документированного обоснования назначения; требуется уточнение врача». Не перечисляй все общеизвестные показания препарата. Не пиши название поля, JSON или текст задания.',
        ],
        'contraindications' => [
            'label' => 'Противопоказания и ограничения',
            'min' => 35,
            'max_tokens' => 240,
            'prompt' => 'Напиши только противопоказания и ограничения, важные для данного пациента и препарата. 3–6 коротких неповторяющихся пунктов. Сначала учти анамнезы и эпикризы, затем критические ограничения препарата. Не повторяй одну мысль разными словами.',
        ],
        'adverse_reactions' => [
            'label' => 'Нежелательные реакции',
            'min' => 25,
            'max_tokens' => 200,
            'prompt' => 'Напиши только 3–6 наиболее важных возможных нежелательных реакций простым языком. Не перечисляй десятки редких реакций и не повторяйся.',
        ],
        'missed_dose' => [
            'label' => 'Пропуск дозы',
            'min' => 25,
            'max_tokens' => 140,
            'prompt' => 'Напиши только короткую однозначную инструкцию при пропуске дозы. 1–3 предложения. Не советуй удваивать дозу без прямого основания в контексте.',
        ],
        'overdose_actions' => [
            'label' => 'Действия при передозировке',
            'min' => 50,
            'max_tokens' => 260,
            'prompt' => 'Напиши только 3–5 последовательных уникальных ДЕЙСТВИЙ при подозрении на передозировку, лучше нумерованным списком. Пиши что прекратить, кому позвонить, когда вызвать 112, что сообщить врачу. Не перечисляй симптомы и не повторяй одно действие.',
        ],
        'urgent_symptoms' => [
            'label' => 'Срочные симптомы',
            'min' => 35,
            'max_tokens' => 200,
            'prompt' => 'Напиши только 3–6 симптомов или ситуаций, при которых нужна срочная медицинская помощь. Не повторяй алгоритм действий при передозировке.',
        ],
    ];

    public function generate(
        array $context,
        ?callable $onFieldProgress = null
    ): array {
        // Do not ask a small language model to rewrite structured reference data.
        // Render verified local drug reference rows deterministically and leave
        // leaflet-only fields blank until an official leaflet source is connected.
        $result = [];
        $manualReviewFields = [];
        $total = count(self::FIELD_CONFIG);
        $index = 0;
        $reference = is_array($context['reference'] ?? null)
            ? $context['reference']
            : [];

        foreach (self::FIELD_CONFIG as $field => $cfg) {
            $index++;

            if ($onFieldProgress) {
                $onFieldProgress(
                    $field,
                    $cfg['label'],
                    $index,
                    $total,
                    'start'
                );
            }

            $value = '';
            $source = 'missing_reference';
            $manualReview = false;
            $itemsCount = 0;

            switch ($field) {
                case 'doctor_instruction':
                    // Prescription details are authoritative; no model rewrite.
                    $value = $this->buildDoctorInstructionFallbackV24($context);
                    $hasCorePrescription =
                        trim((string) ($context['drug_name'] ?? '')) !== ''
                        && trim((string) ($context['dosage'] ?? '')) !== ''
                        && trim((string) ($context['dosage_unit'] ?? '')) !== ''
                        && trim((string) ($context['regimen'] ?? '')) !== '';
                    $manualReview = !$hasCorePrescription;
                    $source = 'prescription_snapshot';
                    break;

                case 'indication':
                    $explicit = $this->explicitIndicationV27($context);
                    if ($explicit !== null) {
                        $value = $explicit;
                        $source = 'prescriber_documented_rationale';
                    } else {
                        $value = $this->buildIndicationCandidateV29($context, $reference);
                        $manualReview = true;
                        $source = $value !== ''
                            ? 'registered_indication_candidate'
                            : 'no_documented_rationale';
                    }
                    break;

                case 'contraindications':
                    $items = is_array($reference['contraindications'] ?? null)
                        ? $reference['contraindications']
                        : [];
                    $itemsCount = count($items);
                    $value = $this->formatDrugReferenceItemsV29(
                        $items,
                        'Противопоказания и ограничения'
                    );
                    $manualReview = true;
                    $source = 'drug_reference';
                    break;

                case 'adverse_reactions':
                    $items = is_array($reference['adverse_reactions'] ?? null)
                        ? $reference['adverse_reactions']
                        : [];
                    $itemsCount = count($items);
                    $value = $this->formatDrugReferenceItemsV29(
                        $items,
                        'Возможные нежелательные реакции'
                    );
                    $manualReview = true;
                    $source = 'drug_reference';
                    break;

                case 'missed_dose':
                    $value = $this->formatLeafletFieldV29(
                        $reference['patient_leaflet']['missed_dose'] ?? null
                    );
                    $manualReview = $value === '';
                    $source = $value !== '' ? 'official_leaflet_payload' : 'official_leaflet_missing';
                    break;

                case 'overdose_actions':
                    $value = $this->formatLeafletFieldV29(
                        $reference['patient_leaflet']['overdose_actions'] ?? null
                    );
                    $manualReview = $value === '';
                    $source = $value !== '' ? 'official_leaflet_payload' : 'official_leaflet_missing';
                    break;

                case 'urgent_symptoms':
                    $value = $this->formatLeafletFieldV29(
                        $reference['patient_leaflet']['urgent_symptoms'] ?? null
                    );
                    $manualReview = $value === '';
                    $source = $value !== '' ? 'official_leaflet_payload' : 'official_leaflet_missing';
                    break;
            }

            $value = trim((string) $value);
            $result[$field] = mb_substr($value, 0, 5000);

            if ($manualReview) {
                $manualReviewFields[] = $field;
            }

            Log::channel(
                (string) config('medgemma.debug_log_channel', 'medgemma_debug')
            )->info(
                'Medication assignment field assembled',
                [
                    'field' => $field,
                    'source' => $source,
                    'filled' => $value !== '',
                    'reference_items_count' => $itemsCount,
                    'requires_physician_review' => $manualReview,
                ]
            );

            if ($onFieldProgress) {
                $onFieldProgress(
                    $field,
                    $cfg['label'],
                    $index,
                    $total,
                    $manualReview ? 'manual_review' : 'done'
                );
            }
        }

        return $result;
    }

    private function formatDrugReferenceItemsV29(
        array $items,
        string $heading
    ): string {
        $lines = [];
        $seen = [];

        foreach ($items as $item) {
            if (is_object($item)) {
                $item = (array) $item;
            }

            if (!is_array($item)) {
                continue;
            }

            $name = trim((string) ($item['name'] ?? ''));
            $description = trim((string) ($item['description'] ?? ''));

            if ($name === '') {
                continue;
            }

            $normalized = mb_strtolower(
                preg_replace('/\s+/u', ' ', $name) ?? $name
            );

            if (isset($seen[$normalized])) {
                continue;
            }

            $seen[$normalized] = true;
            $line = $name;

            if (
                $description !== ''
                && mb_strtolower($description) !== $normalized
            ) {
                $line .= ' — ' . $description;
            }

            $lines[] = $line;
        }

        if ($lines === []) {
            return '';
        }

        $lines = array_slice($lines, 0, 20);

        return $heading . ":\n• "
            . implode("\n• ", $lines);
    }

    private function formatLeafletFieldV29(mixed $value): string
    {
        if (is_string($value)) {
            return trim($value);
        }
        if (is_array($value)) {
            $text = $value['text'] ?? $value['content'] ?? $value['instruction'] ?? null;
            if (is_string($text) && trim($text) !== '') {
                return trim($text);
            }
            $items = [];
            foreach ($value as $item) {
                if (is_string($item) && trim($item) !== '') {
                    $items[] = trim($item);
                } elseif (is_array($item)) {
                    $label = trim((string) ($item['text'] ?? $item['name'] ?? $item['description'] ?? ''));
                    if ($label !== '') {
                        $items[] = $label;
                    }
                }
            }
            return implode("\n", array_values(array_unique($items)));
        }
        return '';
    }

    private function buildIndicationCandidateV29(
        array $context,
        array $reference
    ): string {
        $indications = is_array($reference['indications'] ?? null)
            ? $reference['indications']
            : [];

        if ($indications === []) {
            return 'Зарегистрированные показания не указаны.';
        }

        $diagnoses = data_get(
            $context,
            'clinical_context.diagnoses',
            []
        );

        $patientCodes = [];

        $selected = is_array($diagnoses['selected_encounter'] ?? null)
            ? $diagnoses['selected_encounter']
            : [];

        $selectedCode = trim(
            (string) ($selected['diagnosis_code'] ?? '')
        );

        if ($selectedCode !== '') {
            $patientCodes[] = $selectedCode;
        }

        foreach (($diagnoses['history'] ?? []) as $historyItem) {
            if (!is_array($historyItem)) {
                continue;
            }

            $code = trim(
                (string) ($historyItem['diagnosis_code'] ?? '')
            );

            if ($code !== '') {
                $patientCodes[] = $code;
            }
        }

        $patientCodes = array_values(array_unique($patientCodes));
        $matching = [];

        foreach ($indications as $item) {
            if (!is_array($item)) {
                continue;
            }

            $code = trim(
                (string) ($item['diagnosis_code'] ?? '')
            );

            if ($code !== '' && in_array($code, $patientCodes, true)) {
                $matching[] = $item;
            }
        }

        $candidate = $matching[0] ?? $indications[0];

        if (!is_array($candidate)) {
            return 'Зарегистрированные показания не указаны.';
        }

        $title = trim((string) ($candidate['diagnosis_title'] ?? ''));
        $code = trim((string) ($candidate['diagnosis_code'] ?? ''));

        if ($title === '' && $code === '') {
            return 'Зарегистрированные показания не указаны.';
        }

        $label = $title !== '' ? $title : $code;

        if ($title !== '' && $code !== '') {
            $label .= ' (' . $code . ')';
        }

        if ($matching !== []) {
            return 'Показание: ' . $label . '.';
        }

        return 'Зарегистрированное показание препарата: '
            . $label
            . '.';
    }

    private function explicitIndicationV27(array $context): ?string
    {
        $candidates = [
            $context['prescriber_indication'] ?? null,
            $context['indication_source'] ?? null,
            $context['prescription_reason'] ?? null,
            $context['reason_for_prescription'] ?? null,
            $context['rationale'] ?? null,
            data_get($context, 'clinical_context.prescriber_indication'),
            data_get($context, 'clinical_context.indication_source'),
            data_get($context, 'clinical_context.prescription_reason'),
        ];

        foreach ($candidates as $candidate) {
            if (
                is_string($candidate)
                && trim($candidate) !== ''
                && mb_strlen(trim($candidate)) >= 10
            ) {
                return trim($candidate);
            }
        }

        return null;
    }

    private function fieldSpecificQualityV27(
        string $field,
        string $value
    ): bool {
        $text = mb_strtolower(trim($value));

        if ($text === '') {
            return false;
        }

        if ($field === 'missed_dose') {
            return (bool) preg_match(
                '/пропуск|пропущенн|забыли|вспомнили|двойную доз|удваива|следующую доз/iu',
                $text
            );
        }

        if ($field === 'urgent_symptoms') {
            $anamnesisMarkers = [
                'концентрац',
                'мотивац',
                'продуктивност',
                'освоения нового',
                'усвоения нового',
                'планирован',
                'утомляемост',
                'интереса к',
                'эмоциональн',
                'нарушения сна',
                'однообразных задач',
            ];

            $anamnesisHits = 0;
            foreach ($anamnesisMarkers as $marker) {
                if (mb_stripos($text, $marker) !== false) {
                    $anamnesisHits++;
                }
            }

            if ($anamnesisHits >= 2) {
                return false;
            }

            $urgentMarkers = [
                'судорог',
                'потеря сознания',
                'обморок',
                'затрудненн',
                'удуш',
                'боль в груди',
                'отек лица',
                'отёк лица',
                'анафилак',
                'нарушение сердечного ритма',
                'спутанност',
                'галлюцинац',
                'резкое ухудшение',
                'суицидальн',
            ];

            foreach ($urgentMarkers as $marker) {
                if (mb_stripos($text, $marker) !== false) {
                    return true;
                }
            }

            return false;
        }

        if ($field === 'contraindications') {
            $invalidPhrases = [
                'сенсорная гиперчувствительность',
                'сенсорная гиперактивность',
                'двигательная гиперактивность',
                'двигательная гипотония',
                'двигательная гипокинезия',
            ];

            foreach ($invalidPhrases as $phrase) {
                if (mb_stripos($text, $phrase) !== false) {
                    return false;
                }
            }
        }

        if ($field === 'adverse_reactions') {
            $parts = preg_split(
                '/\R+|;\s*|(?<=\.)\s+/u',
                trim($value)
            ) ?: [];

            if (count($parts) > 8) {
                return false;
            }
        }

        if ($field === 'overdose_actions') {
            $actionMarkers = [
                'прекрат',
                'не приним',
                'позвон',
                'вызов',
                'обрат',
                'сообщ',
                'сохран',
            ];

            $found = 0;
            foreach ($actionMarkers as $marker) {
                if (mb_stripos($text, $marker) !== false) {
                    $found++;
                }
            }

            return $found >= 2;
        }

        return true;
    }

    public function modelName(): string
    {
        return (string) config(
            'medgemma.model',
            config('llm.ollama.review_model', 'medgemma1.5:4b')
        );
    }

    private function generateField(
        string $field,
        array $cfg,
        array $context,
        bool $retry
    ): string {
        $system = <<<'PROMPT'
Ты MedGemma — помощник врача, а не автор клинического рассуждения.
Верни ТОЛЬКО готовый текст одного поля формы на русском языке.
Запрещено выводить внутренние рассуждения, анализ задания, план ответа,
пересказ контекста, инструкции модели, английский текст или JSON.
Препарат, форма, дозировка, единица и режим уже выбраны врачом.
Никогда не изменяй их и не назначай другие препараты.
Используй анамнезы, эпикризы, диагноз и список active_medications.
active_medications содержит документированные текущие назначения пациента.
Не упоминай препараты, которых нет в active_medications.
Не придумывай аллергии, заболевания, противопоказания или взаимодействия.
Если данных недостаточно, прямо укажи, что врачу нужно уточнить информацию.
Пиши кратко, без повторов и без вводных фраз.
PROMPT;

        $user = $cfg['prompt']
            . ($retry
                ? "\nПредыдущий вариант отклонён проверкой качества. Сформулируй заново, конкретнее и без повторов."
                : '')
            . "\n\nКлинический контекст:\n"
            . json_encode(
                $this->contextPayload($field, $context),
                JSON_UNESCAPED_UNICODE
                | JSON_UNESCAPED_SLASHES
                | JSON_PRETTY_PRINT
            );

        $response = Http::acceptJson()
            ->asJson()
            ->connectTimeout((int) config('medgemma.connect_timeout_seconds', 15))
            ->timeout((int) config('medgemma.field_http_timeout_seconds', 120))
            ->post(
                rtrim($this->baseUrl(), '/') . '/api/chat',
                [
                    'model' => $this->modelName(),
                    'stream' => false,
                    // Ollama applies this only when the selected model supports it.
                    'think' => (bool) config('medgemma.think', false),
                    'keep_alive' => (string) config('medgemma.keep_alive', '10m'),
                    'messages' => [
                        ['role' => 'system', 'content' => $system],
                        ['role' => 'user', 'content' => $user],
                    ],
                    'options' => [
                        'temperature' => 0.0,
                        'num_ctx' => (int) config(
                            'medgemma.num_ctx',
                            config('llm.ollama.num_ctx', 4096)
                        ),
                        'repeat_penalty' => (float) config(
                            'medgemma.repeat_penalty',
                            1.18
                        ),
                        'repeat_last_n' => (int) config(
                            'medgemma.repeat_last_n',
                            128
                        ),
                        'num_predict' => max(
                            64,
                            (int) config(
                                'medgemma.field_num_predict.' . $field,
                                $cfg['max_tokens']
                                    ?? config('medgemma.num_predict', 220)
                            )
                        ),
                    ],
                ]
            );

        if (!$response->successful()) {
            throw new RuntimeException(
                'Ollama/MedGemma HTTP ' . $response->status()
                . ' при заполнении поля «' . $cfg['label'] . '».'
            );
        }

        $responseData = $response->json() ?? [];
        $message = data_get($responseData, 'message', []);

        $rawContent = data_get($message, 'content');
        $rawContent = is_string($rawContent) ? $rawContent : '';

        $rawThinking = data_get($message, 'thinking');
        $rawThinking = is_string($rawThinking) ? $rawThinking : '';

        $doneReason = (string) data_get($responseData, 'done_reason', '');
        $debug = Log::channel(
            (string) config('medgemma.debug_log_channel', 'medgemma_debug')
        );

        if ((bool) config('medgemma.debug_log_responses', true)) {
            $debug->info(
                'MedGemma raw field response',
                [
                    'field' => $field,
                    'field_label' => $cfg['label'] ?? $field,
                    'retry' => $retry,
                    'model' => $this->modelName(),
                    'response_length' => mb_strlen($rawContent),
                    'thinking_length' => mb_strlen($rawThinking),
                    'done_reason' => $doneReason,
                    'prompt_eval_count' => data_get($responseData, 'prompt_eval_count'),
                    'eval_count' => data_get($responseData, 'eval_count'),
                    'total_duration_ms' => $this->durationMsV26(
                        data_get($responseData, 'total_duration')
                    ),
                    'response_preview' => $this->debugPreviewV23($rawContent),
                    'thinking_preview' => $this->debugPreviewV23($rawThinking),
                ]
            );
        }

        // A truncated response must never be saved as a patient instruction.
        if ($doneReason === 'length') {
            $debug->warning(
                'MedGemma response reached num_predict limit',
                [
                    'field' => $field,
                    'field_label' => $cfg['label'] ?? $field,
                    'retry' => $retry,
                    'eval_count' => data_get($responseData, 'eval_count'),
                ]
            );

            return '';
        }

        // The reasoning channel is diagnostic only; never use it as field content.
        $finalContent = $this->extractFinalAnswerV26($rawContent);

        if (
            $finalContent === ''
            || $this->looksLikeReasoningV26($finalContent)
        ) {
            if ((bool) config('medgemma.debug_log_responses', true)) {
                $debug->warning(
                    'MedGemma returned no usable final answer',
                    [
                        'field' => $field,
                        'field_label' => $cfg['label'] ?? $field,
                        'retry' => $retry,
                        'content_length' => mb_strlen($rawContent),
                        'thinking_length' => mb_strlen($rawThinking),
                    ]
                );
            }

            return '';
        }

        if ((bool) config('medgemma.debug_log_responses', true)) {
            $debug->info(
                'MedGemma cleaned field response',
                [
                    'field' => $field,
                    'field_label' => $cfg['label'] ?? $field,
                    'retry' => $retry,
                    'model' => $this->modelName(),
                    'response_length' => mb_strlen($finalContent),
                    'response' => mb_substr(
                        $finalContent,
                        0,
                        (int) config('medgemma.debug_log_clean_max_chars', 3000)
                    ),
                ]
            );
        }

        return $finalContent;
    }

    private function contextPayload(string $field, array $context): array
    {
        $clinical = $context['clinical_context'] ?? [];

        $payload = [
            'drug' => [
                'id' => $context['drug_id'] ?? null,
                'name' => $context['drug_name'] ?? null,
                'form' => $context['drug_form'] ?? null,
            ],
            'prescription' => [
                'dosage' => $context['dosage'] ?? null,
                'dosage_unit' => $context['dosage_unit'] ?? null,
                'regimen' => $context['regimen'] ?? null,
            ],
            'patient' => $clinical['patient'] ?? [
                'birth_at' => $context['birth_at'] ?? null,
                'diagnosis_code' => $context['diagnosis_code'] ?? null,
            ],
            'diagnoses' => $clinical['diagnoses'] ?? [],
            'anamneses' => $clinical['anamneses'] ?? [],
            'epicrises' => $clinical['epicrises'] ?? [],

            'active_medications' =>
                $this->compactActiveMedicationsV26(
                    $clinical['active_medications'] ?? []
                ),
        ];

        if (
            in_array(
                $field,
                [
                    'contraindications',
                    'adverse_reactions',
                    'missed_dose',
                    'overdose_actions',
                    'urgent_symptoms',
                ],
                true
            )
            && !empty($context['reference'])
        ) {
            $payload['drug_reference'] = $context['reference'];
        }

        return $payload;
    }

    private function passes(string $field, string $value, int $min): bool
    {
        $value = trim($value);

        if (mb_strlen($value) < $min) {
            return false;
        }

        if (
            $this->looksLikePromptLeak($value)
            || $this->looksLikeReasoningV26($value)
            || $this->hasExcessiveRepetitionV26($value)
            || $this->duplicateCount($value) > 0
        ) {
            return false;
        }

        if (
            preg_match('/["\']?[a-z_]{3,}["\']?\s*:\s*/iu', $value)
            || preg_match('/^\s*[\{\[]/u', $value)
        ) {
            return false;
        }

        return true;
    }

    private function cleanField(string $field, string $value): string
    {
        $value = html_entity_decode(
            strip_tags($value),
            ENT_QUOTES | ENT_HTML5,
            'UTF-8'
        );

        $value = str_replace(['```text', '```json', '```'], '', $value);
        $value = preg_replace(
            '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u',
            '',
            $value
        ) ?? $value;

        $value = trim($value, " \t\n\r\0\x0B\"'`,");

        $value = preg_replace(
            '/^(?:ответ|готовый текст|текст поля|вариант|инструкция|показание|'
            . 'противопоказания(?: и ограничения)?|нежелательные реакции|'
            . 'если пропущен при[её]м|действия при .*?передозировк[еи]|'
            . 'срочные симптомы)\s*[:\-–—]\s*/iu',
            '',
            $value
        ) ?? $value;

        $lines = preg_split('/\R+/u', $value) ?: [$value];
        $lines = array_values(
            array_filter(
                $lines,
                fn ($line) => !$this->looksLikePromptLeak((string) $line)
            )
        );

        $value = trim(implode("\n", $lines));

        if (
            in_array(
                $field,
                [
                    'contraindications',
                    'adverse_reactions',
                    'overdose_actions',
                    'urgent_symptoms',
                ],
                true
            )
        ) {
            return $this->dedupeSegments($value, $field);
        }

        return $this->dedupeSentences($value);
    }

    private function dedupeSentences(string $text): string
    {
        $parts = preg_split(
            '/(?<=[.!?])\s+|\R+/u',
            trim($text)
        ) ?: [trim($text)];

        return $this->uniqueParts($parts, ' ');
    }

    private function dedupeSegments(string $text, string $field): string
    {
        $pattern = $field === 'overdose_actions'
            ? '/\R+|(?<=\.)\s+|(?<=;)\s+|(?=\d{1,2}[.)]\s*)/u'
            : '/\R+|(?<=\.)\s+|;\s*|,\s+(?=(?:при|если|не|следует|в случае|срочно|немедленно)\b)/iu';

        $parts = preg_split($pattern, trim($text)) ?: [trim($text)];

        return $this->uniqueParts(
            $parts,
            $field === 'overdose_actions' ? "\n" : '; '
        );
    }

    private function uniqueParts(array $parts, string $glue): string
    {
        $seen = [];
        $result = [];

        foreach ($parts as $part) {
            $part = trim((string) $part);

            if ($part === '') {
                continue;
            }

            $key = $this->normalizeForDedup($part);

            if ($key === '') {
                continue;
            }

            $duplicate = false;

            foreach ($seen as $existing) {
                if (
                    $key === $existing
                    || (
                        mb_strlen($key) > 24
                        && (
                            str_contains($existing, $key)
                            || str_contains($key, $existing)
                        )
                    )
                ) {
                    $duplicate = true;
                    break;
                }
            }

            if ($duplicate) {
                continue;
            }

            $seen[] = $key;
            $result[] = $part;
        }

        return implode($glue, $result);
    }

    private function duplicateCount(string $text): int
    {
        $parts = preg_split(
            '/\R+|(?<=\.)\s+|(?<=;)\s+|(?=\d{1,2}[.)]\s*)/u',
            trim($text)
        ) ?: [];

        $seen = [];
        $duplicates = 0;

        foreach ($parts as $part) {
            $key = $this->normalizeForDedup((string) $part);

            if ($key === '') {
                continue;
            }

            if (in_array($key, $seen, true)) {
                $duplicates++;
            } else {
                $seen[] = $key;
            }
        }

        return $duplicates;
    }

    private function normalizeForDedup(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $value = preg_replace('/^\d+[.)]\s*/u', '', $value) ?? $value;

        return preg_replace('/[^\p{L}\p{N}]+/u', '', $value) ?? $value;
    }

    private function looksLikePromptLeak(string $value): bool
    {
        $value = mb_strtolower($value);

        foreach (
            [
                'подготовь черновик',
                'для уже выбранного врачом препарата',
                'ты medgemma',
                'ты заполняешь одно поле',
                'критические правила',
                'тебе передаются',
                'не используй json',
                'верни только',
                'клинический контекст:',
                'сформируй пациентский черновик',
            ]
            as $needle
        ) {
            if (mb_stripos($value, $needle) !== false) {
                return true;
            }
        }

        return false;
    }

    private function extractFinalAnswerV26(string $raw): string
    {
        $text = trim($raw);

        if ($text === '') {
            return '';
        }

        // If Ollama/model exposes an explicit final marker, take only its tail.
        $markers = [
            '<unused95>',
            '<|final|>',
            '</think>',
        ];

        $lastPosition = -1;
        $lastMarker = '';

        foreach ($markers as $marker) {
            $position = strripos($text, $marker);

            if ($position !== false && $position > $lastPosition) {
                $lastPosition = $position;
                $lastMarker = $marker;
            }
        }

        if ($lastPosition >= 0) {
            $text = substr(
                $text,
                $lastPosition + strlen($lastMarker)
            );
        } elseif (
            preg_match('/(?:^|\R)\s*(?:final answer|ответ)\s*[:\-–—]\s*(.+)$/isu', $text, $match)
        ) {
            $text = $match[1];
        } elseif (
            preg_match('/^\s*<unused\d+>\s*(?:thought|analysis)\b/iu', $text)
            || $this->looksLikeReasoningV26($text)
        ) {
            // No final-answer boundary: do not leak chain-of-thought into the chart.
            return '';
        }

        $text = preg_replace('/<think>.*?<\/think>/isu', '', $text) ?? $text;
        $text = preg_replace('/<unused\d+>(?:thought|analysis)?/iu', '', $text) ?? $text;
        $text = preg_replace('/<\|(?:assistant|analysis|final|end)\|>/iu', '', $text) ?? $text;
        $text = preg_replace('/^\s*(?:final answer|ответ)\s*[:\-–—]\s*/iu', '', $text) ?? $text;
        $text = preg_replace('/^\s*[*-]\s+/mu', '', $text) ?? $text;

        $text = trim($text, " \t\n\r\0\x0B\"'`");

        if ($this->looksLikeReasoningV26($text)) {
            return '';
        }

        return $text;
    }

    private function looksLikeReasoningV26(string $value): bool
    {
        $text = mb_strtolower(trim($value));

        if ($text === '') {
            return false;
        }

        if (preg_match('/^(?:thought|analysis|reasoning)\b/u', $text)) {
            return true;
        }

        $markers = [
            'the user wants me',
            'i need to provide',
            'i need to fill',
            'i should provide',
            'identify the drug',
            'identify the patient',
            'identify the context',
            'look for justification',
            'the anamneses mention',
            'the diagnosis code is',
            'based only on the provided information',
            'prescription details are already fixed',
            'if there is no clear justification',
            'the field is',
            'the patient has a diagnosis',
            'step 1.',
            '1. identify',
        ];

        foreach ($markers as $marker) {
            if (mb_stripos($text, $marker) !== false) {
                return true;
            }
        }

        return (bool) preg_match(
            '/\R\s*(?:the user|i need to|i should|we need to|identify the|look for)\b/iu',
            $text
        );
    }

    private function hasExcessiveRepetitionV26(string $value): bool
    {
        $words = preg_split(
            '/[^\p{L}\p{N}]+/u',
            mb_strtolower($value),
            -1,
            PREG_SPLIT_NO_EMPTY
        ) ?: [];

        if (count($words) < 18) {
            return false;
        }

        $counts = [];
        $window = 6;

        for ($i = 0; $i <= count($words) - $window; $i++) {
            $phrase = implode(' ', array_slice($words, $i, $window));

            if (mb_strlen($phrase) < 24) {
                continue;
            }

            $counts[$phrase] = ($counts[$phrase] ?? 0) + 1;

            if ($counts[$phrase] >= 3) {
                return true;
            }
        }

        return false;
    }

    private function durationMsV26(mixed $nanoseconds): ?int
    {
        if (!is_numeric($nanoseconds)) {
            return null;
        }

        return (int) round(((float) $nanoseconds) / 1000000);
    }

    private function debugPreviewV23(
        string $value
    ): string {
        $headLength = max(
            0,
            (int) config(
                'medgemma.debug_log_preview_head',
                6000
            )
        );

        $tailLength = max(
            0,
            (int) config(
                'medgemma.debug_log_preview_tail',
                6000
            )
        );

        $length = mb_strlen($value);

        if (
            $length <= ($headLength + $tailLength)
            || ($headLength + $tailLength) <= 0
        ) {
            return $value;
        }

        $head = $headLength > 0
            ? mb_substr(
                $value,
                0,
                $headLength
            )
            : '';

        $tail = $tailLength > 0
            ? mb_substr(
                $value,
                -$tailLength
            )
            : '';

        return
            $head
            . "\n\n"
            . '--- [TRUNCATED '
            . ($length - $headLength - $tailLength)
            . ' chars] ---'
            . "\n\n"
            . $tail;
    }

    private function doctorInstructionPassesV24(
        string $value,
        array $context
    ): bool {
        $text = mb_strtolower(trim($value));

        if ($text === '') {
            return false;
        }

        $hasAction = (bool) preg_match(
            '/\b(принимайте|принимать|применяйте|применять|используйте|использовать)\b/ui',
            $text
        );

        if (!$hasAction) {
            return false;
        }

        $dose = trim((string) ($context['dosage'] ?? ''));
        $unit = trim((string) ($context['dosage_unit'] ?? ''));

        if (
            $dose !== ''
            && mb_stripos($text, mb_strtolower($dose)) === false
        ) {
            return false;
        }

        if (
            $unit !== ''
            && mb_stripos($text, mb_strtolower($unit)) === false
        ) {
            return false;
        }

        $symptomMarkers = [
            'утомляемост',
            'концентрац',
            'мотивац',
            'продуктивност',
            'нарушения сна',
            'эмоциональн',
            'настроени',
            'усвоения нового',
            'планирован',
            'интереса к',
        ];

        $hits = 0;

        foreach ($symptomMarkers as $marker) {
            if (mb_stripos($text, $marker) !== false) {
                $hits++;
            }
        }

        return $hits < 3;
    }

    private function buildDoctorInstructionFallbackV24(
        array $context
    ): string {
        $drug = trim(
            (string) (
                $context['drug_name']
                ?? 'назначенный препарат'
            )
        );

        $dose = trim((string) ($context['dosage'] ?? ''));
        $unit = trim((string) ($context['dosage_unit'] ?? ''));
        $regimen = trim((string) ($context['regimen'] ?? ''));

        $doseText = trim(
            implode(
                ' ',
                array_filter([
                    $dose,
                    $unit,
                ])
            )
        );

        if ($regimen !== '') {
            $first = 'Принимайте '
                . $drug
                . ($doseText !== '' ? ' ' . $doseText : '')
                . ' согласно назначенной схеме: '
                . rtrim($regimen, ".; ")
                . '.';
        } else {
            $first = 'Принимайте '
                . $drug
                . ($doseText !== '' ? ' в дозировке ' . $doseText : '')
                . ' согласно схеме, указанной врачом.';
        }

        return $first
            . ' Не изменяйте дозу и режим приёма самостоятельно. '
            . 'При появлении необычных или выраженных реакций сообщите лечащему врачу.';
    }

    private function compactActiveMedicationsV26(array $items): array
    {
        $limit = max(
            1,
            (int) config('medgemma.context.active_medications_limit', 30)
        );

        $keys = [
            'drug_name',
            'drug_form',
            'dosage',
            'dosage_unit',
            'regimen',
            'starts_at',
            'ends_at',
            'clinic_name',
        ];

        $result = [];

        foreach (array_slice($items, 0, $limit) as $item) {
            if (!is_array($item)) {
                continue;
            }

            $compact = [];

            foreach ($keys as $key) {
                $value = $item[$key] ?? null;

                if (is_scalar($value) && trim((string) $value) !== '') {
                    $compact[$key] = trim((string) $value);
                }
            }

            if (!empty($compact['drug_name'])) {
                $result[] = $compact;
            }
        }

        return $result;
    }

    private function baseUrl(): string
    {
        return (string) config(
            'llm.ollama.base_url',
            config(
                'services.ollama.url',
                env('OLLAMA_URL', 'http://ollama:11434')
            )
        );
    }

}
