<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Patient;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;

final class ContinueTreatmentRefinementService
{
    public function refine(
        Patient $patient,
        array $document,
        AnamnesisAnalyzeService $analyzer
    ): array {
        $draft = is_array($document['draft'] ?? null)
            ? $document['draft']
            : [];

        $sourceAnamnesis = $this->anamnesisText(
            $patient,
            $draft
        );

        if ($sourceAnamnesis !== '') {
            $analysis = $analyzer->process(
                $sourceAnamnesis,
                (int) $patient->id
            );

            if (
                is_array($analysis)
                && !isset($analysis['error'])
            ) {
                $diagnosis = $this->primaryDiagnosis(
                    $analysis
                );

                if ($diagnosis !== '') {
                    $draft['diagnosis'] = $diagnosis;
                }
            }
        }

        if (
            trim((string) ($draft['condition'] ?? '')) === ''
        ) {
            $draft['condition'] =
                $this->latestCondition($patient);
        }

        if (
            trim((string) ($draft['therapy'] ?? '')) === ''
        ) {
            $draft['therapy'] =
                $this->currentTherapy($patient);
        }

        /*
         * effect намеренно не выдумываем:
         * если основной генератор/медкарта не дали данных,
         * поле остаётся пустым.
         */

        $recommendations = $this->generateRecommendations(
            $draft
        );

        if ($recommendations !== '') {
            $draft['recommendations'] = $recommendations;
        }

        $document['draft'] = $draft;

        return $document;
    }

    private function anamnesisText(
        Patient $patient,
        array $draft
    ): string {
        $parts = [];

        foreach ([
            'anamnesis',
            'condition',
            'effect',
        ] as $key) {
            $value = trim((string) ($draft[$key] ?? ''));

            if ($value !== '') {
                $parts[] = $value;
            }
        }

        $anamneses = $patient->anamneses()
            ->select(['id', 'text', 'created_at'])
            ->orderByDesc('created_at')
            ->limit(12)
            ->get()
            ->pluck('text')
            ->map(
                static fn ($value): string =>
                    trim((string) $value)
            )
            ->filter()
            ->all();

        $parts = array_merge(
            $parts,
            $anamneses
        );

        return mb_substr(
            implode("\n\n", array_unique($parts)),
            0,
            30000
        );
    }

    private function primaryDiagnosis(
        array $analysis
    ): string {
        $primary = $analysis['primary_diagnosis']
            ?? null;

        if (!$primary) {
            $scored = $analysis['scored'] ?? [];

            if (
                $scored
                instanceof \Illuminate\Support\Collection
            ) {
                $primary = $scored->first();
            } elseif (is_array($scored)) {
                $primary = $scored[0] ?? null;
            }
        }

        $code = trim(
            (string) data_get(
                $primary,
                'code',
                ''
            )
        );

        $title = trim(
            (string) data_get(
                $primary,
                'title',
                ''
            )
        );

        return implode(
            ' — ',
            array_values(
                array_filter(
                    [$code, $title],
                    static fn (string $value): bool =>
                        $value !== ''
                )
            )
        );
    }

    private function latestCondition(
        Patient $patient
    ): string {
        if (
            !Schema::hasTable('medical_encounters')
            || !Schema::hasTable('registry_records')
            || !Schema::hasColumn(
                'medical_encounters',
                'registry_record_id'
            )
            || !Schema::hasColumn(
                'medical_encounters',
                'patient_condition'
            )
        ) {
            return '';
        }

        $row = DB::table('medical_encounters as me')
            ->join(
                'registry_records as rr',
                'rr.id',
                '=',
                'me.registry_record_id'
            )
            ->where(
                'rr.patient_id',
                $patient->getKey()
            )
            ->whereNull('rr.deleted_at')
            ->whereNotNull('me.patient_condition')
            ->where(
                'me.patient_condition',
                '<>',
                ''
            )
            ->orderByDesc('rr.for_datetime')
            ->orderByDesc('rr.id')
            ->value('me.patient_condition');

        return mb_substr(
            trim((string) $row),
            0,
            5000
        );
    }

    private function currentTherapy(
        Patient $patient
    ): string {
        $rows = $patient->prescriptions()
            ->select([
                'generic_name',
                'dosage',
                'drug_form',
                'usage_instructions',
                'issued_at',
                'created_at',
            ])
            ->orderByDesc('issued_at')
            ->orderByDesc('id')
            ->limit(12)
            ->get();

        return $rows
            ->map(
                static function ($row): string {
                    $name = trim(
                        (string) $row->generic_name
                    );
                    $dosage = trim(
                        (string) $row->dosage
                    );
                    $form = trim(
                        (string) $row->drug_form
                    );
                    $usage = trim(
                        (string) $row->usage_instructions
                    );

                    $head = trim(
                        implode(
                            ' ',
                            array_filter([
                                $name,
                                $dosage,
                                $form,
                            ])
                        )
                    );

                    if ($usage !== '') {
                        return $head !== ''
                            ? $head . ' — ' . $usage
                            : $usage;
                    }

                    return $head;
                }
            )
            ->filter()
            ->unique()
            ->take(8)
            ->implode("\n");
    }

    private function generateRecommendations(
        array $draft
    ): string {
        $payload = [
            'diagnosis' => trim(
                (string) ($draft['diagnosis'] ?? '')
            ),
            'condition' => trim(
                (string) ($draft['condition'] ?? '')
            ),
            'therapy' => trim(
                (string) ($draft['therapy'] ?? '')
            ),
            'effect' => trim(
                (string) ($draft['effect'] ?? '')
            ),
            'researches' => trim(
                (string) ($draft['researches'] ?? '')
            ),
        ];

        if (
            implode('', $payload) === ''
        ) {
            return '';
        }

        if (
            (string) config('llm.provider', 'ollama')
                !== 'ollama'
        ) {
            return '';
        }

        $baseUrl = trim(
            (string) config(
                'llm.ollama.base_url'
            )
        );

        $model = trim(
            (string) config(
                'llm.ollama.primary_model',
                config(
                    'llm.ollama.model',
                    'qwen3:14b'
                )
            )
        );

        if ($baseUrl === '' || $model === '') {
            return '';
        }

        $schema = [
            'type' => 'object',
            'properties' => [
                'recommendations' => [
                    'type' => 'string',
                ],
            ],
            'required' => ['recommendations'],
            'additionalProperties' => false,
        ];

        $system = <<<'PROMPT'
Ты формируешь черновик раздела «Рекомендации и план лечения» для передачи пациента другому врачу.

Используй только предоставленные данные.
Не придумывай новые диагнозы, препараты, дозировки, результаты исследований или побочные эффекты.
Не отменяй и не меняй дозы препаратов, если такое решение прямо не следует из входных данных.
Можно:
- рекомендовать продолжить уже указанную терапию;
- указать необходимость наблюдения врача;
- предложить контроль уже упомянутых клинически значимых показателей;
- рекомендовать оценить эффективность и переносимость текущего лечения;
- рекомендовать уточнение лечения принимающим врачом при наличии оснований.

Текст должен быть профессиональным, компактным, 3–7 пунктов или предложений.
Это черновик для проверки врачом перед печатью.
PROMPT;

        try {
            $response = Http::connectTimeout(
                (int) config(
                    'llm.ollama.connect_timeout',
                    10
                )
            )
                ->timeout(
                    max(
                        45,
                        (int) config(
                            'llm.ollama.timeout',
                            300
                        )
                    )
                )
                ->acceptJson()
                ->asJson()
                ->post(
                    rtrim($baseUrl, '/')
                    . '/api/chat',
                    [
                        'model' => $model,
                        'stream' => false,
                        'think' => false,
                        'keep_alive' =>
                            config(
                                'llm.ollama.keep_alive',
                                '5m'
                            ),
                        'format' => $schema,
                        'options' => [
                            'temperature' => 0.0,
                            'num_ctx' => max(
                                4096,
                                (int) config(
                                    'llm.ollama.num_ctx',
                                    12288
                                )
                            ),
                            'num_predict' => 700,
                        ],
                        'messages' => [
                            [
                                'role' => 'system',
                                'content' => $system,
                            ],
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
                    ]
                );

            if (!$response->successful()) {
                return '';
            }

            $content = data_get(
                $response->json(),
                'message.content'
            );

            if (
                !is_string($content)
                || trim($content) === ''
            ) {
                return '';
            }

            $decoded = json_decode(
                $content,
                true
            );

            if (!is_array($decoded)) {
                return '';
            }

            return mb_substr(
                trim(
                    (string) (
                        $decoded['recommendations']
                        ?? ''
                    )
                ),
                0,
                6000
            );
        } catch (\Throwable) {
            return '';
        }
    }
}
