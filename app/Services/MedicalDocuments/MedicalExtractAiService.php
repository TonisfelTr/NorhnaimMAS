<?php

namespace App\Services\MedicalDocuments;

use App\Models\Patient;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class MedicalExtractAiService
{
    public function __construct(
        private readonly PatientMedicalContextService $contextService,
    ) {
    }

    public function generate(
        Patient $patient,
        string $periodMode = 'all',
        ?string $dateFrom = null,
        ?string $dateTo = null,
    ): array {
        $context = $this->contextService->build($patient);
        $context = $this->filterContextByPeriod($context, $periodMode, $dateFrom, $dateTo);

        if (!$this->hasClinicalData($context)) {
            return [
                'error' => 'NO_CLINICAL_DATA',
                'message' => 'За выбранный период в медицинской карте недостаточно клинических данных для формирования выписки.',
            ];
        }

        $systemPrompt = <<<'PROMPT'
Ты — помощник врача по подготовке ЧЕРНОВИКА выписки из медицинской карты.
Тебе передаётся структурированная медицинская история одного пациента из медицинской информационной системы.

ЖЁСТКИЕ ПРАВИЛА:
1. Используй только факты, явно присутствующие во входных данных. Ничего не выдумывай.
2. Не устанавливай новый диагноз и не меняй формулировки диагноза без основания во входных данных.
3. Сохраняй клинически важную хронологию: обращения, динамику, обследования и лечение.
4. Не превращай предположение, жалобу пациента или рабочую гипотезу в подтверждённый факт.
5. В раздел researches включай значимые лабораторные и инструментальные исследования с датами и результатами, если они присутствуют.
6. В раздел treatment включай только реально назначенную/проведённую терапию. Не добавляй новые препараты, дозировки или схемы.
7. В condition опиши только последнее подтверждённое состояние пациента в выбранном периоде. Если данных недостаточно — пустая строка.
8. В recommendations включай только рекомендации, уже зафиксированные в карте. Если их нет — пустая строка.
9. Если для раздела нет подтверждённых данных — верни пустую строку.
10. Текст должен быть профессиональным, лаконичным, на русском языке и пригодным для проверки врачом.
11. Не включай markdown, служебные пояснения и дисклеймеры вне JSON.

Верни строго JSON-объект:
{
  "diagnosis": "",
  "anamnesis": "",
  "researches": "",
  "treatment": "",
  "condition": "",
  "recommendations": "",
  "used_sources": {
    "anamneses": 0,
    "epicrises": 0,
    "symptoms": 0,
    "conditions": 0,
    "prescriptions": 0,
    "laboratory_researches": 0,
    "instrumental_researches": 0,
    "tests": 0
  }
}
PROMPT;

        $payload = [
            'task' => 'Составь черновик выписки из медицинской карты за указанный период наблюдения.',
            'period' => [
                'mode' => $periodMode,
                'from' => $periodMode === 'range' ? $dateFrom : null,
                'to' => $periodMode === 'range' ? $dateTo : null,
            ],
            'medical_context' => $context,
        ];

        $raw = $this->askJson($systemPrompt, $payload);
        if ($raw === null) {
            return [
                'error' => 'LLM_UNAVAILABLE',
                'message' => 'ИИ-сервис не вернул ответ. Проверьте настройки LLM и повторите попытку.',
            ];
        }

        $result = $this->decodeJson($raw);
        if (!is_array($result)) {
            return [
                'error' => 'LLM_INVALID_JSON',
                'message' => 'ИИ вернул ответ в неожиданном формате.',
            ];
        }

        $fields = [
            'diagnosis',
            'anamnesis',
            'researches',
            'treatment',
            'condition',
            'recommendations',
        ];

        $normalized = [];
        foreach ($fields as $field) {
            $normalized[$field] = trim((string) ($result[$field] ?? ''));
        }

        // Не разрешаем модели подменить текущий зафиксированный диагноз пациента.
        $diagnosis = $context['current_diagnosis'] ?? null;
        if (is_array($diagnosis) && (($diagnosis['code'] ?? '') !== '' || ($diagnosis['title'] ?? '') !== '')) {
            $normalized['diagnosis'] = trim(implode(' — ', array_filter([
                $diagnosis['code'] ?? null,
                $diagnosis['title'] ?? null,
            ])));
        } else {
            $normalized['diagnosis'] = '';
        }

        $normalized['used_sources'] = is_array($result['used_sources'] ?? null)
            ? $result['used_sources']
            : [];

        $normalized['context_counts'] = $this->contextCounts($context);
        $normalized['period'] = [
            'mode' => $periodMode,
            'from' => $periodMode === 'range' ? $dateFrom : null,
            'to' => $periodMode === 'range' ? $dateTo : null,
        ];

        return $normalized;
    }

    private function filterContextByPeriod(
        array $context,
        string $periodMode,
        ?string $dateFrom,
        ?string $dateTo,
    ): array {
        if ($periodMode !== 'range' || !$dateFrom || !$dateTo) {
            return $context;
        }

        try {
            $from = Carbon::createFromFormat('Y-m-d', $dateFrom)->startOfDay();
            $to = Carbon::createFromFormat('Y-m-d', $dateTo)->endOfDay();
        } catch (\Throwable) {
            return $context;
        }

        foreach ([
            'anamneses',
            'epicrises',
            'symptoms',
            'conditions',
            'prescriptions',
            'laboratory_researches',
            'instrumental_researches',
            'tests',
        ] as $key) {
            $context[$key] = collect((array) ($context[$key] ?? []))
                ->filter(function ($row) use ($from, $to): bool {
                    if (!is_array($row) || empty($row['date'])) {
                        return false;
                    }

                    $date = $this->parseContextDate((string) $row['date']);
                    return $date !== null && $date->betweenIncluded($from, $to);
                })
                ->values()
                ->all();
        }

        return $context;
    }

    private function parseContextDate(string $value): ?Carbon
    {
        foreach (['d.m.Y H:i', 'd.m.Y'] as $format) {
            try {
                return Carbon::createFromFormat($format, $value);
            } catch (\Throwable) {
            }
        }

        return null;
    }

    private function hasClinicalData(array $context): bool
    {
        foreach ([
            'anamneses', 'epicrises', 'symptoms', 'conditions', 'prescriptions',
            'laboratory_researches', 'instrumental_researches', 'tests',
        ] as $key) {
            if (!empty($context[$key])) {
                return true;
            }
        }

        return !empty($context['current_diagnosis']);
    }

    private function contextCounts(array $context): array
    {
        $counts = [];
        foreach ([
            'anamneses', 'epicrises', 'symptoms', 'conditions', 'prescriptions',
            'laboratory_researches', 'instrumental_researches', 'tests',
        ] as $key) {
            $counts[$key] = count((array) ($context[$key] ?? []));
        }

        return $counts;
    }

    private function askJson(string $systemPrompt, array $payload): ?string
    {
        $provider = (string) config('llm.provider', 'ollama');
        $userText = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($userText === false) {
            return null;
        }

        try {
            if ($provider === 'ollama') {
                return $this->askOllama($systemPrompt, $userText);
            }

            return $this->askOpenAi($systemPrompt, $userText);
        } catch (\Throwable $e) {
            Log::warning('Medical extract LLM request failed', [
                'provider' => $provider,
                'exception' => get_class($e),
                'message' => $e->getMessage(),
            ]);
            return null;
        }
    }

    private function askOllama(string $systemPrompt, string $userText): ?string
    {
        $baseUrl = rtrim(trim((string) config('llm.ollama.base_url')), '/');
        $model = trim((string) config(
            'llm.ollama.primary_model',
            config('llm.ollama.model', 'qwen3:14b')
        ));
        if ($baseUrl === '' || $model === '') {
            return null;
        }

        $response = Http::timeout((int) config('llm.ollama.timeout', 300))
            ->acceptJson()
            ->post($baseUrl . '/api/chat', [
                'model' => $model,
                'stream' => false,
                'format' => 'json',
                'think' => (bool) config('llm.ollama.think', false),
                'keep_alive' => config('llm.ollama.keep_alive', '5m'),
                'options' => [
                    'temperature' => (float) config('llm.ollama.temperature', 0.0),
                    'num_ctx' => (int) config('llm.ollama.num_ctx', 16384),
                ],
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $userText],
                ],
            ]);

        if (!$response->successful()) {
            Log::warning('Medical extract Ollama HTTP error', ['status' => $response->status()]);
            return null;
        }

        return $response->json('message.content');
    }

    private function askOpenAi(string $systemPrompt, string $userText): ?string
    {
        $baseUrl = rtrim(trim((string) config('llm.openai.base_url', 'https://api.openai.com/v1')), '/');
        $apiKey = trim((string) config('llm.openai.api_key'));
        $model = trim((string) config('llm.openai.model'));
        if ($apiKey === '' || $model === '') {
            return null;
        }

        $response = Http::timeout((int) config('llm.openai.timeout', 120))
            ->withToken($apiKey)
            ->acceptJson()
            ->post($baseUrl . '/chat/completions', [
                'model' => $model,
                'temperature' => (float) config('llm.openai.temperature', 0.0),
                'response_format' => ['type' => 'json_object'],
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $userText],
                ],
            ]);

        if (!$response->successful()) {
            Log::warning('Medical extract OpenAI HTTP error', ['status' => $response->status()]);
            return null;
        }

        return $response->json('choices.0.message.content');
    }

    private function decodeJson(string $raw): ?array
    {
        $raw = trim($raw);
        $raw = preg_replace('/^```(?:json)?\s*/i', '', $raw) ?? $raw;
        $raw = preg_replace('/\s*```$/', '', $raw) ?? $raw;

        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        $start = strpos($raw, '{');
        $end = strrpos($raw, '}');
        if ($start === false || $end === false || $end <= $start) {
            return null;
        }

        $decoded = json_decode(substr($raw, $start, $end - $start + 1), true);
        return is_array($decoded) ? $decoded : null;
    }
}
