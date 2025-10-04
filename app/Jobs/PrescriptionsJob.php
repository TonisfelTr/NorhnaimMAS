<?php

namespace App\Jobs;

use App\Models\MedicalPrescription;
use App\Models\Patient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PrescriptionsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private array $data
    )
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info('PrescriptionsJob: Starting job', ['data' => $this->data]);

            $prescription = MedicalPrescription::create($this->data);

            Log::info('PrescriptionsJob: Successfully created prescription', ['id' => $prescription->id]);
        } catch (\Exception $e) {
            Log::error('PrescriptionsJob: Error occurred', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e; // Переброс ошибки для обработки системой очередей
        }
    }

}
