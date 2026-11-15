<?php

namespace App\Services;

// PATIENT_TREATMENT_WIZARD_V12_RABBITMQ_CONTEXT

use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class MedicationAssignmentMedGemmaService
{
    private const RESULT_FIELDS = [
        'doctor_instruction',
        'indication',
        'contraindications',
        'adverse_reactions',
        'missed_dose',
        'overdose_actions',
        'urgent_symptoms',
    ];

    private const ALIASES = [
        'doctor_instruction' => [
            'doctor_instruction',
            'patient_instruction',
            'instruction',
            'instructions',
            'индивидуальная_инструкция',
            'инструкция_врача',
            'инструкция',
        ],
        'indication' => [
            'indication',
            'indications',
            'reason',
            'why',
            'показание',
            'показания',
            'почему_назначен',
        ],
        'contraindications' => [
            'contraindications',
            'contraindication',
            'restrictions',
            'противопоказания',
            'ограничения',
        ],
        'adverse_reactions' => [
            'adverse_reactions',
            'adverse_effects',
            'side_effects',
            'побочные_эффекты',
            'нежелательные_реакции',
        ],
        'missed_dose' => [
            'missed_dose',
            'missed',
            'пропуск_дозы',
            'пропуск_приема',
            'пропуск_приёма',
        ],
        'overdose_actions' => [
            'overdose_actions',
            'overdose',
            'передозировка',
            'действия_при_передозировке',
        ],
        'urgent_symptoms' => [
            'urgent_symptoms',
            'red_flags',
            'emergency_symptoms',
            'срочные_симптомы',
            'красные_флаги',
        ],
    ];

    public function generate(
        array $context,
        ?callable $onStreamProgress = null
    ): array {
        $response = Http::acceptJson()
            ->asJson()
            ->connectTimeout(
                (int) config(
                    'medgemma.connect_timeout_seconds',
                    15
                )
            )
            ->timeout(
                (int) config(
                    'medgemma.http_timeout_seconds',
                    540
                )
            )
            ->withOptions([
                'stream' => true,
            ])
            ->post(
                rtrim($this->baseUrl(), '/')
                . '/api/chat',
                [
                    'model' => $this->modelName(),
                    'stream' => true,
                    'format' => 'json',
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' =>
                                $this->systemPrompt(),
                        ],
                        [
                            'role' => 'user',
                            'content' =>
                                $this->userPrompt(
                                    $context
                                ),
                        ],
                    ],
                    'options' => [
                        'temperature' => 0.0,
                        'num_ctx' => (int) config(
                            'llm.ollama.num_ctx',
                            4096
                        ),
                    ],
                ]
            );

        if (!$response->successful()) {
            throw new RuntimeException(
                'Ollama/MedGemma HTTP '
                . $response->status()
            );
        }

        $stream = $response
            ->toPsrResponse()
            ->getBody();

        $buffer = '';
        $content = '';
        $chunks = 0;
        $lastNotifyAt = microtime(true);
        $lastNotifiedChunk = 0;

        while (!$stream->eof()) {
            $piece = $stream->read(8192);

            if ($piece === '') {
                usleep(20_000);
                continue;
            }

            $buffer .= $piece;

            while (
                ($newline = strpos(
                    $buffer,
                    "\n"
                )) !== false
            ) {
                $line = trim(
                    substr(
                        $buffer,
                        0,
                        $newline
                    )
                );

                $buffer = substr(
                    $buffer,
                    $newline + 1
                );

                if ($line === '') {
                    continue;
                }

                $this->consumeStreamLine(
                    $line,
                    $content,
                    $chunks
                );

                $now = microtime(true);

                if (
                    $onStreamProgress
                    && (
                        ($chunks - $lastNotifiedChunk)
                            >= 4
                        || ($now - $lastNotifyAt)
                            >= 1.0
                    )
                ) {
                    $onStreamProgress(
                        $chunks,
                        mb_strlen($content)
                    );

                    $lastNotifyAt = $now;
                    $lastNotifiedChunk =
                        $chunks;
                }
            }
        }

        $tail = trim($buffer);

        if ($tail !== '') {
            $this->consumeStreamLine(
                $tail,
                $content,
                $chunks
            );
        }

        if ($onStreamProgress) {
            $onStreamProgress(
                $chunks,
                mb_strlen($content)
            );
        }

        $decoded = $this->decodeJson(
            trim($content)
        );

        if (
            isset($decoded['fields'])
            && is_array($decoded['fields'])
        ) {
            $decoded = $decoded['fields'];
        }

        $normalized = $this->normalize(
            $decoded
        );

        $filled = count(
            array_filter(
                $normalized,
                static fn ($value) =>
                    is_string($value)
                    && trim($value) !== ''
            )
        );

        if ($filled === 0) {
            throw new RuntimeException(
                'MedGemma вернула JSON, '
                . 'но поля инструкции не распознаны.'
            );
        }

        return $normalized;
    }

    public function modelName(): string
    {
        return (string) config(
            'medgemma.model',
            config(
                'llm.ollama.review_model',
                'medgemma1.5:4b'
            )
        );
    }

    private function baseUrl(): string
    {
        return (string) config(
            'llm.ollama.base_url',
            config(
                'services.ollama.url',
                env(
                    'OLLAMA_URL',
                    'http://ollama:11434'
                )
            )
        );
    }

    private function systemPrompt(): string
    {
        return <<<'PROMPT'
Ты MedGemma в медицинской информационной системе.
Подготовь ЧЕРНОВИК полной инструкции пациенту для УЖЕ ВЫБРАННОГО врачом препарата.

Тебе передаются:
- препарат;
- зафиксированные врачом дозировка и режим;
- диагноз;
- справочные данные препарата;
- последние анамнезы пациента;
- последние эпикризы пациента.

ОЧЕНЬ ВАЖНО:
1. Не меняй препарат.
2. Не меняй дозировку.
3. Не меняй режим приёма.
4. Не предлагай альтернативные препараты.
5. Не добавляй новые назначения.
6. Анамнезы и эпикризы используй как клинический контекст:
   ищи в них важные сопутствующие состояния, прошлые нежелательные реакции,
   противопоказания, аллергии, особенности течения болезни, беременность/лактацию,
   суицидальный риск, нарушения функции органов, предыдущую переносимость лечения
   и другие сведения, которые могут менять инструкцию или меры предосторожности.
7. Не считай любой текст анамнеза или эпикриза абсолютной истиной:
   при противоречии формулируй осторожно и не выдумывай отсутствующие факты.
8. Если контекст указывает на потенциально важный риск,
   отрази его в противопоказаниях/ограничениях либо urgent_symptoms.
9. При передозировке дай понятный и безопасный порядок действий.
10. Текст предназначен для проверки врачом.
11. Ответ — только JSON, без Markdown.

Верни ровно 7 полей:
{
  "doctor_instruction": "...",
  "indication": "...",
  "contraindications": "...",
  "adverse_reactions": "...",
  "missed_dose": "...",
  "overdose_actions": "...",
  "urgent_symptoms": "..."
}
PROMPT;
    }

    private function userPrompt(
        array $context
    ): string {
        return
            "Сформируй полный черновик инструкции.\n"
            . "Препарат, дозировка и режим "
            . "зафиксированы врачом.\n"
            . "Особое внимание удели клинически важным "
            . "сведениям из анамнезов и эпикризов.\n\n"
            . json_encode(
                [
                    'drug' => [
                        'id' =>
                            $context['drug_id']
                            ?? null,
                        'name' =>
                            $context['drug_name']
                            ?? null,
                    ],
                    'prescription' => [
                        'dosage' =>
                            $context['dosage']
                            ?? null,
                        'regimen' =>
                            $context['regimen']
                            ?? null,
                    ],
                    'patient' =>
                        $context['clinical_context']
                            ['patient']
                        ?? [
                            'birth_at' =>
                                $context['birth_at']
                                ?? null,
                            'diagnosis_code' =>
                                $context[
                                    'diagnosis_code'
                                ] ?? null,
                        ],
                    'reference' =>
                        $context['reference']
                        ?? [],
                    'anamneses' =>
                        $context['clinical_context']
                            ['anamneses']
                        ?? [],
                    'epicrises' =>
                        $context['clinical_context']
                            ['epicrises']
                        ?? [],
                ],
                JSON_UNESCAPED_UNICODE
                | JSON_UNESCAPED_SLASHES
                | JSON_PRETTY_PRINT
            );
    }

    private function consumeStreamLine(
        string $line,
        string &$content,
        int &$chunks
    ): void {
        try {
            $payload = json_decode(
                $line,
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (Throwable $e) {
            throw new RuntimeException(
                'Ollama вернула некорректный '
                . 'NDJSON-фрагмент.',
                0,
                $e
            );
        }

        $part = data_get(
            $payload,
            'message.content'
        );

        if (
            is_string($part)
            && $part !== ''
        ) {
            $content .= $part;
            $chunks++;
        }

        if (
            isset($payload['error'])
            && is_string($payload['error'])
            && trim($payload['error']) !== ''
        ) {
            throw new RuntimeException(
                'Ollama: '
                . trim($payload['error'])
            );
        }
    }

    private function decodeJson(
        string $content
    ): array {
        if ($content === '') {
            throw new RuntimeException(
                'MedGemma вернула пустой поток.'
            );
        }

        if (
            str_starts_with(
                $content,
                '```'
            )
        ) {
            $content = preg_replace(
                '/^```(?:json)?\s*/iu',
                '',
                $content
            );

            $content = preg_replace(
                '/\s*```$/u',
                '',
                (string) $content
            );
        }

        try {
            $decoded = json_decode(
                $content,
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (Throwable $e) {
            throw new RuntimeException(
                'MedGemma вернула '
                . 'некорректный JSON.',
                0,
                $e
            );
        }

        if (!is_array($decoded)) {
            throw new RuntimeException(
                'Некорректная структура '
                . 'ответа MedGemma.'
            );
        }

        return $decoded;
    }

    private function normalize(
        array $payload
    ): array {
        $indexed = [];

        foreach (
            $payload
            as $key => $value
        ) {
            $indexed[
                $this->normalizeKey(
                    (string) $key
                )
            ] = $value;
        }

        $result = [];

        foreach (
            self::RESULT_FIELDS
            as $field
        ) {
            $value = '';

            foreach (
                self::ALIASES[$field]
                as $alias
            ) {
                $normalizedAlias =
                    $this->normalizeKey(
                        $alias
                    );

                if (
                    array_key_exists(
                        $normalizedAlias,
                        $indexed
                    )
                ) {
                    $value =
                        $indexed[
                            $normalizedAlias
                        ];
                    break;
                }
            }

            if (is_array($value)) {
                $value = implode(
                    "\n",
                    array_map(
                        static fn ($item) =>
                            is_scalar($item)
                                ? (string) $item
                                : json_encode(
                                    $item,
                                    JSON_UNESCAPED_UNICODE
                                    | JSON_UNESCAPED_SLASHES
                                ),
                        $value
                    )
                );
            }

            if (!is_scalar($value)) {
                $value = '';
            }

            $result[$field] =
                mb_substr(
                    trim(
                        (string) $value
                    ),
                    0,
                    5000
                );
        }

        return $result;
    }

    private function normalizeKey(
        string $key
    ): string {
        $key = mb_strtolower(
            trim($key)
        );

        return preg_replace(
            '/[^\p{L}\p{N}]+/u',
            '_',
            $key
        ) ?: $key;
    }
}
