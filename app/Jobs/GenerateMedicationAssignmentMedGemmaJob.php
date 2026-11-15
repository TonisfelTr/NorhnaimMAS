<?php

namespace App\Jobs;

// PATIENT_TREATMENT_WIZARD_V12_RABBITMQ_CONTEXT

use App\Services\MedicationAssignmentClinicalContextService;
use App\Services\MedicationAssignmentMedGemmaService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Throwable;

class GenerateMedicationAssignmentMedGemmaJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 1;
    public int $timeout = 600;
    public bool $failOnTimeout = true;

    public function __construct(
        public string $taskId,
        public int $patientId,
        public int $userId,
        public array $context
    ) {
    }

    public static function cacheKey(
        string $taskId
    ): string {
        return
            'medgemma:medication-assignment:'
            . $taskId;
    }

    public function handle(
        MedicationAssignmentMedGemmaService $service,
        MedicationAssignmentClinicalContextService $clinicalContext
    ): void {
        $this->update(
            status: 'running',
            progress: 12,
            stage: 'worker',
            stageLabel:
                'Задание взято RabbitMQ worker-ом',
            message:
                'Начинаем подготовку назначения.'
        );

        $this->update(
            status: 'running',
            progress: 22,
            stage: 'clinical_context',
            stageLabel:
                'Читаем анамнезы и эпикризы',
            message:
                'Собираем клинически важный контекст пациента.'
        );

        $this->context[
            'clinical_context'
        ] = $clinicalContext->build(
            $this->patientId,
            $this->context
        );

        $anamnesesCount = count(
            $this->context[
                'clinical_context'
            ]['anamneses'] ?? []
        );

        $epicrisesCount = count(
            $this->context[
                'clinical_context'
            ]['epicrises'] ?? []
        );

        $this->update(
            status: 'running',
            progress: 35,
            stage: 'context_ready',
            stageLabel:
                'Контекст подготовлен',
            message:
                'Анамнезов: '
                . $anamnesesCount
                . ', эпикризов: '
                . $epicrisesCount
        );

        $this->update(
            status: 'running',
            progress: 45,
            stage: 'medgemma',
            stageLabel:
                'MedGemma запускает генерацию',
            message:
                'Передаём рецепт, анамнезы '
                . 'и эпикризы модели.'
        );

        $lastHeartbeatAt =
            microtime(true);

        $lastChunk = 0;

        $fields = $service->generate(
            $this->context,
            function (
                int $chunks,
                int $chars
            ) use (
                &$lastHeartbeatAt,
                &$lastChunk
            ): void {
                $now = microtime(true);

                if (
                    ($chunks - $lastChunk) < 4
                    && ($now - $lastHeartbeatAt)
                        < 1.0
                ) {
                    return;
                }

                $progress = min(
                    88,
                    45 + intdiv(
                        $chunks,
                        2
                    )
                );

                $this->update(
                    status: 'running',
                    progress: $progress,
                    stage:
                        'medgemma_stream',
                    stageLabel:
                        'MedGemma генерирует · '
                        . $chunks
                        . ' фрагм.',
                    message:
                        'Получено символов: '
                        . $chars
                );

                $lastHeartbeatAt = $now;
                $lastChunk = $chunks;
            }
        );

        $this->update(
            status: 'running',
            progress: 94,
            stage: 'parse',
            stageLabel:
                'Проверка ответа',
            message:
                'Проверяем структуру '
                . 'и поля инструкции.'
        );

        $this->write([
            'task_id' => $this->taskId,
            'status' => 'completed',
            'progress' => 100,
            'stage' => 'completed',
            'stage_label' => 'Готово',
            'message' =>
                'Черновик MedGemma готов.',
            'patient_id' =>
                $this->patientId,
            'user_id' =>
                $this->userId,
            'context_stats' => [
                'anamneses' =>
                    $anamnesesCount,
                'epicrises' =>
                    $epicrisesCount,
            ],
            'result' => [
                'fields' => $fields,
                'model' =>
                    $service->modelName(),
                'draft' => true,
            ],
            'updated_at' =>
                now()->toIso8601String(),
            'completed_at' =>
                now()->toIso8601String(),
        ]);
    }

    public function failed(
        ?Throwable $exception
    ): void {
        $this->write([
            'task_id' => $this->taskId,
            'status' => 'failed',
            'progress' => 100,
            'stage' => 'failed',
            'stage_label' => 'Ошибка',
            'message' =>
                'MedGemma не смогла '
                . 'сформировать черновик.',
            'error' =>
                $exception?->getMessage()
                ?: 'Неизвестная ошибка '
                . 'фонового задания.',
            'patient_id' =>
                $this->patientId,
            'user_id' =>
                $this->userId,
            'updated_at' =>
                now()->toIso8601String(),
            'failed_at' =>
                now()->toIso8601String(),
        ]);
    }

    private function update(
        string $status,
        int $progress,
        string $stage,
        string $stageLabel,
        string $message
    ): void {
        $current =
            $this->store()->get(
                self::cacheKey(
                    $this->taskId
                ),
                []
            );

        if (!is_array($current)) {
            $current = [];
        }

        $this->write(
            array_merge(
                $current,
                [
                    'task_id' =>
                        $this->taskId,
                    'status' =>
                        $status,
                    'progress' =>
                        $progress,
                    'stage' =>
                        $stage,
                    'stage_label' =>
                        $stageLabel,
                    'message' =>
                        $message,
                    'patient_id' =>
                        $this->patientId,
                    'user_id' =>
                        $this->userId,
                    'updated_at' =>
                        now()->toIso8601String(),
                ]
            )
        );
    }

    private function write(
        array $payload
    ): void {
        $this->store()->put(
            self::cacheKey(
                $this->taskId
            ),
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
