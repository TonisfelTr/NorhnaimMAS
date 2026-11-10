<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Http\Controllers\Doctors\NeuralNetworkController;
use App\Models\AiMedicalDocumentTask;
use App\Models\Patient;
use App\Models\User;
use App\Services\AnamnesisStructuringService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

final class GenerateMedicalDocumentAiTask implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;
    public int $timeout = 900;
    public bool $failOnTimeout = true;

    public function __construct(public string $taskId)
    {
        $this->onConnection('rabbitmq');
        $this->onQueue((string) config('ai_documents.queue', 'ai-documents'));
    }

    public function handle(): void
    {
        $task = AiMedicalDocumentTask::query()
            ->find($this->taskId);

        if (
            !$task
            || $task->cancellationRequested()
        ) {
            return;
        }

        /*
         * NORHNAIM_AI_DOCUMENT_CANCELLABLE_CHILD_V1
         *
         * Непосредственный AI-анализ выполняется в дочернем PHP-процессе.
         * Это позволяет действительно завершить процесс при cancel/F5,
         * а не ждать окончания блокирующего HTTP-запроса к Ollama.
         */
        $process = new \Symfony\Component\Process\Process(
            [
                PHP_BINARY,
                base_path('artisan'),
                'ai:medical-document-run',
                $this->taskId,
                '--no-interaction',
            ],
            base_path(),
            null,
            null,
            max(60, $this->timeout + 20)
        );

        $process->start();

        while ($process->isRunning()) {
            usleep(250000);

            $task->refresh();

            if ($task->cancellationRequested()) {
                /*
                 * SIGTERM -> короткая пауза -> SIGKILL через Process::stop().
                 * Закрытие PHP-процесса также закрывает его соединение
                 * с Ollama.
                 */
                $process->stop(0.8, 15);

                return;
            }
        }

        $task->refresh();

        if (
            !$process->isSuccessful()
            && !$task->cancellationRequested()
            && $task->status
                !== AiMedicalDocumentTask::STATUS_FAILED
        ) {
            $message = trim(
                $process->getErrorOutput()
                ?: $process->getOutput()
            );

            $task->forceFill([
                'status' =>
                    AiMedicalDocumentTask::STATUS_FAILED,
                'error_message' => mb_substr(
                    $message !== ''
                        ? $message
                        : 'Дочерний процесс AI завершился с ошибкой.',
                    0,
                    4000
                ),
                'finished_at' => now(),
            ])->save();
        }
    }


    private function failTask(
        AiMedicalDocumentTask $task,
        string $message
    ): void {
        $task->forceFill([
            'status' => AiMedicalDocumentTask::STATUS_FAILED,
            'error_message' => $message,
            'finished_at' => now(),
        ])->save();
    }
}
