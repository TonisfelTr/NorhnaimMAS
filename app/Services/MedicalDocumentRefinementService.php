<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class MedicalDocumentRefinementService
{
    public function psychiatristComplaints(string $anamnesis): string
    {
        $anamnesis = trim($anamnesis);

        if ($anamnesis === '') {
            return '';
        }

        if ((string) config('llm.provider', 'ollama') !== 'ollama') {
            return '';
        }

        $baseUrl = trim((string) config('llm.ollama.base_url'));
        $model = trim((string) config(
            'llm.ollama.primary_model',
            config('llm.ollama.model', 'qwen3:14b')
        ));

        if ($baseUrl === '' || $model === '') {
            return '';
        }

        $schema = [
            'type' => 'object',
            'properties' => [
                'complaints' => ['type' => 'string'],
            ],
            'required' => ['complaints'],
            'additionalProperties' => false,
        ];

        $system = <<<'PROMPT'
Извлеки из предоставленного анамнеза ТОЛЬКО актуальные жалобы пациента для заключения психиатра.
Не добавляй фактов, которых нет в тексте.
Не включай диагнозы, анамнез жизни, прошлую терапию и социальные сведения.
Если явных актуальных жалоб нет, верни пустую строку.
Ответ краткий: обычно 1–4 предложения, без вводных слов.
PROMPT;

        try {
            $response = Http::connectTimeout(
                (int) config('llm.ollama.connect_timeout', 10)
            )
                ->timeout(max(30, (int) config('llm.ollama.timeout', 300)))
                ->acceptJson()
                ->asJson()
                ->post(rtrim($baseUrl, '/') . '/api/chat', [
                    'model' => $model,
                    'stream' => false,
                    'think' => false,
                    'keep_alive' => config('llm.ollama.keep_alive', '5m'),
                    'format' => $schema,
                    'options' => [
                        'temperature' => 0.0,
                        'num_ctx' => max(
                            4096,
                            (int) config('llm.ollama.num_ctx', 12288)
                        ),
                        'num_predict' => 350,
                    ],
                    'messages' => [
                        ['role' => 'system', 'content' => $system],
                        [
                            'role' => 'user',
                            'content' => mb_substr($anamnesis, 0, 16000),
                        ],
                    ],
                ]);

            if (!$response->successful()) {
                return '';
            }

            $content = data_get($response->json(), 'message.content');

            if (!is_string($content) || trim($content) === '') {
                return '';
            }

            $decoded = json_decode($content, true);

            return is_array($decoded)
                ? mb_substr(
                    trim((string) ($decoded['complaints'] ?? '')),
                    0,
                    2500
                )
                : '';
        } catch (\Throwable $e) {
            Log::warning('Psychiatrist complaints extraction failed', [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
            ]);

            return '';
        }
    }
}
