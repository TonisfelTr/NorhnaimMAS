<?php

namespace App\Jobs;

use App\Services\MedicationAssignmentClinicalContextService;
use App\Services\MedicationAssignmentMedGemmaService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

class GenerateMedicationAssignmentMedGemmaJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 1;
    public int $timeout = 1200;
    public bool $failOnTimeout = true;

    public function __construct(
        public string $taskId,
        public int $patientId,
        public int $userId,
        public array $context
    ) {
    }

    public static function cacheKey(string $taskId): string
    {
        return 'medgemma:medication-assignment:' . $taskId;
    }

    public function handle(
        MedicationAssignmentMedGemmaService $service,
        MedicationAssignmentClinicalContextService $clinicalContext
    ): void {
        $this->update(
            'running',
            10,
            'worker',
            'Задание принято в обработку',
            'Подготавливаем клинический контекст.'
        );

        $this->context['clinical_context'] =
            $clinicalContext->build(
                $this->patientId,
                $this->context
            );

        // V27 checks the top-level reference key before generating reference-based fields.
        $this->context['reference'] =
            $this->context['clinical_context']['drug_reference']
            ?? [];

        $anamnesesCount = count(
            $this->context['clinical_context']['anamneses'] ?? []
        );

        $epicrisesCount = count(
            $this->context['clinical_context']['epicrises'] ?? []
        );

        $activeMedicationsCount = count(
            $this->context['clinical_context']['active_medications']
            ?? []
        );

        $this->update(
            'running',
            18,
            'context_ready',
            'Контекст подготовлен',
            'Активных препаратов: '
                . $activeMedicationsCount
                . ', анамнезов: '
                . $anamnesesCount
                . ', эпикризов: '
                . $epicrisesCount
        );

        try {
        $manualReviewFields = [];

        $fields = $service->generate(
            $this->context,
            function (
                string $field,
                string $label,
                int $index,
                int $total,
                string $stage
            ) use (&$manualReviewFields): void {
                if ($stage === 'manual_review') {
                    $manualReviewFields[] = $field;
                }

                $completed = in_array(
                    $stage,
                    ['done', 'manual_review'],
                    true
                )
                    ? $index
                    : max(0, $index - 1);

                $progress = min(
                    92,
                    20 + (int) floor(
                        ($completed / $total) * 72
                    )
                );

                $stageLabel = $stage === 'retry'
                    ? 'Уточняем: ' . $label
                    : ($stage === 'manual_review'
                        ? 'Продолжаем'
                        : ($stage === 'done'
                            ? 'Готово: ' . $label
                            : 'Заполняем: ' . $label));

                $message = $stage === 'retry'
                    ? 'Уточняем формулировку.'
                    : ($stage === 'manual_review'
                        ? 'Продолжаем формирование инструкции.'
                        : ($stage === 'done'
                            ? 'Поле заполнено.'
                            : 'Генерируем поле '
                                . $index . ' из ' . $total . '.'));

                $this->update(
                    'running',
                    $progress,
                    'field_' . $field,
                    $stageLabel,
                    $message
                );
            }
        );

        $manualReviewFields = array_values(
            array_unique($manualReviewFields)
        );

        } catch (Throwable $exception) {
            $error = mb_substr(
                (string) $exception->getMessage(),
                0,
                1500
            );

            Log::channel(
                (string) config('medgemma.debug_log_channel', 'medgemma_debug')
            )->error(
                'MedGemma generation failed',
                [
                    'task_id' => $this->taskId,
                    'patient_id' => $this->patientId,
                    'user_id' => $this->userId,
                    'error' => $error,
                ]
            );

            $this->write([
                'task_id' => $this->taskId,
                'status' => 'failed',
                'progress' => 100,
                'stage' => 'failed',
                'stage_label' => 'Ошибка',
                'message' => 'Не удалось сформировать инструкцию.',
                'error' => $error,
                'patient_id' => $this->patientId,
                'user_id' => $this->userId,
                'updated_at' => now()->toIso8601String(),
                'failed_at' => now()->toIso8601String(),
            ]);

            return;
        }

        $this->update(
            'running',
            96,
            'final_check',
            'Финальная проверка',
            'Проверяем заполненные поля.'
        );

        if ((bool) config('medgemma.debug_log_responses', true)) {
            Log::channel(
                (string) config(
                    'medgemma.debug_log_channel',
                    'medgemma_debug'
                )
            )->info(
                'MedGemma final assignment response',
                [
                    'task_id' => $this->taskId,
                    'patient_id' => $this->patientId,
                    'user_id' => $this->userId,
                    'model' => $service->modelName(),
                    'manual_review_fields' => $manualReviewFields,
                    'fields' => array_map(
                        static fn ($value) => [
                            'length' => mb_strlen((string) $value),
                            'preview' => mb_substr((string) $value, 0, 800),
                        ],
                        $fields
                    ),
                ]
            );
        }

        $this->write([
            'task_id' => $this->taskId,
            'status' => 'completed',
            'progress' => 100,
            'stage' => 'completed',
            'stage_label' => 'Готово',
            'message' => 'Инструкция сформирована. Проверьте заполненные поля перед сохранением.',
            'patient_id' => $this->patientId,
            'user_id' => $this->userId,
            'context_stats' => [
                'active_medications_count' =>
                    $activeMedicationsCount,
                'anamneses' =>
                    $anamnesesCount,
                'epicrises' =>
                    $epicrisesCount,
            ],
            'result' => [
                'fields' => $fields,
                'manual_review_fields' => $manualReviewFields,
                'model' => $service->modelName(),
                'draft' => true,
            ],
            'updated_at' => now()->toIso8601String(),
            'completed_at' => now()->toIso8601String(),
        ]);
    }

    public function failed(?Throwable $exception): void
    {
        Log::channel(
            (string) config('medgemma.debug_log_channel', 'medgemma_debug')
        )->error(
            'MedGemma assignment job failed',
            [
                'task_id' => $this->taskId,
                'patient_id' => $this->patientId,
                'user_id' => $this->userId,
                'error' => mb_substr(
                    (string) ($exception?->getMessage() ?? 'Unknown error'),
                    0,
                    2000
                ),
            ]
        );

        $this->write([
            'task_id' => $this->taskId,
            'status' => 'failed',
            'progress' => 100,
            'stage' => 'failed',
            'stage_label' => 'Ошибка',
            'message' => 'Не удалось сформировать инструкцию.',
            'error' => $exception?->getMessage()
                ?: 'Неизвестная ошибка фонового задания.',
            'patient_id' => $this->patientId,
            'user_id' => $this->userId,
            'updated_at' => now()->toIso8601String(),
            'failed_at' => now()->toIso8601String(),
        ]);
    }

    private function update(
        string $status,
        int $progress,
        string $stage,
        string $stageLabel,
        string $message
    ): void {
        $current = $this->store()->get(
            self::cacheKey($this->taskId),
            []
        );

        if (!is_array($current)) {
            $current = [];
        }

        $this->write(
            array_merge(
                $current,
                [
                    'task_id' => $this->taskId,
                    'status' => $status,
                    'progress' => $progress,
                    'stage' => $stage,
                    'stage_label' => $stageLabel,
                    'message' => $message,
                    'patient_id' => $this->patientId,
                    'user_id' => $this->userId,
                    'updated_at' => now()->toIso8601String(),
                ]
            )
        );
    }

    private function write(array $payload): void
    {
        $this->store()->put(
            self::cacheKey($this->taskId),
            $payload,
            now()->addMinutes(
                (int) config(
                    'medgemma.status_ttl_minutes',
                    30
                )
            )
        );
    }

    private function store()
    {
        return Cache::store(
            (string) config(
                'medgemma.status_store',
                'redis'
            )
        );
    }
}
