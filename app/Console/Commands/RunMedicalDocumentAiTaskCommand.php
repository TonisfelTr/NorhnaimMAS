<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\MedicalDocumentAiTaskRunner;
use Illuminate\Console\Command;

final class RunMedicalDocumentAiTaskCommand extends Command
{
    protected $signature =
        'ai:medical-document-run {taskId}';

    protected $description =
        'Выполнить одну AI-задачу медицинского документа';

    public function handle(
        MedicalDocumentAiTaskRunner $runner
    ): int {
        return $runner->run(
            (string) $this->argument('taskId')
        );
    }
}
