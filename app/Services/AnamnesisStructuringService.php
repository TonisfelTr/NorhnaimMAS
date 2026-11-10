<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Anamnesis;
use App\Models\Patient;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class AnamnesisStructuringService
{
    private const CATEGORIES = [
        'morbi',
        'vitae',
        'social',
        'family',
        'drug',
        'allergic',
        'epidemiological',
        'psychiatric',
        'other',
    ];

    public function structure(Patient $patient, array $document): array
    {
        $existing = Anamnesis::query()
            ->where('patient_id', $patient->getKey())
            ->orderByDesc('created_at')
            ->limit(30)
            ->get(['id', 'title', 'category', 'text', 'created_at'])
            ->map(static fn (Anamnesis $row): array => [
                'id' => (int) $row->id,
                'title' => trim((string) $row->title),
                'category' => trim((string) $row->category),
                'text' => mb_substr(trim((string) $row->text), 0, 7000),
                'created_at' => optional($row->created_at)->toIso8601String(),
            ])
            ->filter(static fn (array $row): bool => $row['text'] !== '')
            ->values()
            ->all();

        $draft = is_array($document['draft'] ?? null)
            ? $document['draft']
            : [];

        $payload = [
            'document_draft' => $this->compactDraft($draft),
            'existing_anamneses' => $existing,
        ];

        $llm = $this->askOllama($payload);

        if (is_array($llm)) {
            $normalized = $this->normalize($llm);
            if ($normalized['anamneses'] !== [] || $this->hasEpicrisisPatch($normalized['epicrisis'])) {
                return $normalized;
            }
        }

        return $this->fallback($existing, $draft);
    }

    private function compactDraft(array $draft): array
    {
        $keys = [
            'complaints',
            'anamnesis',
            'anamnesis_life',
            'social_anamnesis',
            'disability',
            'condition',
            'conclusion',
            'treatment',
            'therapy',
            'recommendations',
            'diagnosis',
        ];

        $out = [];

        foreach ($keys as $key) {
            $value = trim((string) ($draft[$key] ?? ''));
            if ($value !== '') {
                $out[$key] = mb_substr($value, 0, 12000);
            }
        }

        return $out;
    }

    private function askOllama(array $payload): ?array
    {
        if ((string) config('llm.provider', 'ollama') !== 'ollama') {
            return null;
        }

        $baseUrl = trim((string) config('llm.ollama.base_url'));
        $model = trim((string) config(
            'llm.ollama.primary_model',
            config('llm.ollama.model', 'qwen3:14b')
        ));

        if ($baseUrl === '' || $model === '') {
            return null;
        }

        $schema = [
            'type' => 'object',
            'properties' => [
                'epicrisis' => [
                    'type' => 'object',
                    'properties' => [
                        'complaints' => ['type' => 'string'],
                        'anamnesis' => ['type' => 'string'],
                        'anamnesis_life' => ['type' => 'string'],
                        'social_anamnesis' => ['type' => 'string'],
                        'disability' => ['type' => 'string'],
                    ],
                    'required' => [
                        'complaints',
                        'anamnesis',
                        'anamnesis_life',
                        'social_anamnesis',
                        'disability',
                    ],
                    'additionalProperties' => false,
                ],
                'anamneses' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'category' => [
                                'type' => 'string',
                                'enum' => self::CATEGORIES,
                            ],
                            'title' => ['type' => 'string'],
                            'text' => ['type' => 'string'],
                            'period' => ['type' => 'string'],
                            'confidence' => ['type' => 'number'],
                            'source_ids' => [
                                'type' => 'array',
                                'items' => ['type' => 'integer'],
                            ],
                        ],
                        'required' => [
                            'category',
                            'title',
                            'text',
                            'period',
                            'confidence',
                            'source_ids',
                        ],
                        'additionalProperties' => false,
                    ],
                ],
            ],
            'required' => ['epicrisis', 'anamneses'],
            'additionalProperties' => false,
        ];

        $system = <<<'PROMPT'
Ты структурируешь медицинский анамнез для врача.
Используй ТОЛЬКО факты из входных данных. Ничего не выдумывай, не дополняй знаниями и не ставь новых диагнозов.

Задачи:
# NORHNAIM_EPICRISIS_COMPACT_PROMPT_V1
Эпикриз должен быть компактным медицинским документом, а не пересказом всей карты.
Не дублируй один факт между разделами.
Ориентиры по объёму:
- complaints: до 500 знаков;
- anamnesis: обычно 1200–2200 знаков, только значимая хронология заболевания;
- anamnesis_life: до 1000 знаков;
- social_anamnesis: до 700 знаков;
- disability: до 350 знаков.
Если факт уже вынесен в disability или social_anamnesis, не повторяй его в anamnesis без клинической необходимости.

1. Разложи сведения для эпикриза:
   - complaints: актуальные жалобы;
   - anamnesis: анамнез заболевания / Anamnesis morbi;
   - anamnesis_life: анамнез жизни / Anamnesis vitae;
   - social_anamnesis: образование, работа, семья, бытовое и социальное функционирование;
   - disability: только явно указанные сведения об инвалидности.
2. Предложи отдельные записи анамнеза. Если в анамнезе заболевания есть явно разные временные эпизоды (дебют, отдельная госпитализация, новое обострение), допустимо создать несколько morbi.
3. Не превращай в анамнез диагноз, назначения, результаты лаборатории, рекомендации или текущий осмотр, если это не является историческим фактом анамнеза.
4. Не дублируй один и тот же факт в нескольких предложенных записях без необходимости.
5. Сохраняй исходный смысл и хронологию. Текст можно очищать от повторов, но нельзя добавлять новые факты.
6. source_ids — ID исходных записей anamneses, на которых основан фрагмент. Если фрагмент только из document_draft, массив пустой.
7. confidence от 0 до 1 отражает уверенность именно в классификации, а не истинность медицинского факта.
PROMPT;

        try {
            $response = Http::connectTimeout(
                (int) config('llm.ollama.connect_timeout', 10)
            )
                ->timeout(max(60, (int) config('llm.ollama.timeout', 300)))
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
                        'num_ctx' => max(8192, (int) config('llm.ollama.num_ctx', 12288)),
                        'num_predict' => 3500,
                    ],
                    'messages' => [
                        ['role' => 'system', 'content' => $system],
                        [
                            'role' => 'user',
                            'content' => json_encode(
                                $payload,
                                JSON_UNESCAPED_UNICODE
                                | JSON_UNESCAPED_SLASHES
                                | JSON_INVALID_UTF8_SUBSTITUTE
                            ),
                        ],
                    ],
                ]);

            if (!$response->successful()) {
                Log::warning('Anamnesis structuring: Ollama non-success', [
                    'status' => $response->status(),
                ]);
                return null;
            }

            $content = data_get($response->json(), 'message.content');
            if (!is_string($content) || trim($content) === '') {
                return null;
            }

            $decoded = json_decode($content, true);

            return is_array($decoded) ? $decoded : null;
        } catch (\Throwable $e) {
            Log::warning('Anamnesis structuring failed', [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
            ]);
            return null;
        }
    }

    private function normalize(array $raw): array
    {
        $epicrisisRaw = is_array($raw['epicrisis'] ?? null)
            ? $raw['epicrisis']
            : [];

        $epicrisis = [];

        foreach ([
            'complaints',
            'anamnesis',
            'anamnesis_life',
            'social_anamnesis',
            'disability',
        ] as $key) {
            $epicrisis[$key] = mb_substr(
                trim((string) ($epicrisisRaw[$key] ?? '')),
                0,
                20000
            );
        }

        $items = collect($raw['anamneses'] ?? [])
            ->filter(static fn ($row): bool => is_array($row))
            ->map(function (array $row): ?array {
                $category = trim((string) ($row['category'] ?? 'other'));
                if (!in_array($category, self::CATEGORIES, true)) {
                    $category = 'other';
                }

                $text = mb_substr(trim((string) ($row['text'] ?? '')), 0, 20000);
                if (mb_strlen($text) < 3) {
                    return null;
                }

                $title = mb_substr(trim((string) ($row['title'] ?? '')), 0, 255);
                if ($title === '') {
                    $title = $this->defaultTitle($category);
                }

                $confidence = (float) ($row['confidence'] ?? 0.5);
                $confidence = max(0.0, min(1.0, $confidence));

                $sourceIds = collect($row['source_ids'] ?? [])
                    ->filter(static fn ($id): bool => is_numeric($id) && (int) $id > 0)
                    ->map(static fn ($id): int => (int) $id)
                    ->unique()
                    ->values()
                    ->all();

                return [
                    'category' => $category,
                    'title' => $title,
                    'text' => $text,
                    'period' => mb_substr(trim((string) ($row['period'] ?? '')), 0, 120),
                    'confidence' => round($confidence, 2),
                    'source_ids' => $sourceIds,
                ];
            })
            ->filter()
            ->take(max(1, (int) config('ai_documents.max_anamneses', 20)))
            ->values()
            ->all();

        return [
            'epicrisis' => $epicrisis,
            'anamneses' => $items,
        ];
    }

    private function fallback(array $existing, array $draft): array
    {
        $items = [];

        foreach ($existing as $row) {
            $category = $this->guessCategory(
                (string) ($row['category'] ?? ''),
                (string) ($row['title'] ?? '')
            );

            $items[] = [
                'category' => $category,
                'title' => trim((string) ($row['title'] ?? ''))
                    ?: $this->defaultTitle($category),
                'text' => trim((string) ($row['text'] ?? '')),
                'period' => '',
                'confidence' => 0.55,
                'source_ids' => [(int) $row['id']],
            ];
        }

        $byCategory = collect($items)->groupBy('category');

        return [
            'epicrisis' => [
                'complaints' => trim((string) ($draft['complaints'] ?? '')),
                'anamnesis' => $byCategory
                    ->get('morbi', collect())
                    ->pluck('text')
                    ->filter()
                    ->implode("\n\n"),
                'anamnesis_life' => $byCategory
                    ->get('vitae', collect())
                    ->pluck('text')
                    ->filter()
                    ->implode("\n\n"),
                'social_anamnesis' => $byCategory
                    ->get('social', collect())
                    ->pluck('text')
                    ->filter()
                    ->implode("\n\n"),
                'disability' => trim((string) ($draft['disability'] ?? '')),
            ],
            'anamneses' => array_slice(
                $items,
                0,
                max(1, (int) config('ai_documents.max_anamneses', 20))
            ),
        ];
    }

    private function guessCategory(string $category, string $title): string
    {
        $haystack = mb_strtolower(trim($category . ' ' . $title));

        return match (true) {
            str_contains($haystack, 'vitae'),
            str_contains($haystack, 'жизн') => 'vitae',

            str_contains($haystack, 'социал') => 'social',
            str_contains($haystack, 'семейн'),
            str_contains($haystack, 'наслед') => 'family',
            str_contains($haystack, 'лекар') => 'drug',
            str_contains($haystack, 'аллерг') => 'allergic',
            str_contains($haystack, 'эпид') => 'epidemiological',
            str_contains($haystack, 'психиатр') => 'psychiatric',

            default => 'morbi',
        };
    }

    private function defaultTitle(string $category): string
    {
        return match ($category) {
            'morbi' => 'Анамнез заболевания',
            'vitae' => 'Анамнез жизни',
            'social' => 'Социальный анамнез',
            'family' => 'Семейный и наследственный анамнез',
            'drug' => 'Лекарственный анамнез',
            'allergic' => 'Аллергологический анамнез',
            'epidemiological' => 'Эпидемиологический анамнез',
            'psychiatric' => 'Психиатрический анамнез',
            default => 'Дополнительный анамнез',
        };
    }

    private function hasEpicrisisPatch(array $epicrisis): bool
    {
        foreach ($epicrisis as $value) {
            if (trim((string) $value) !== '') {
                return true;
            }
        }

        return false;
    }
}
