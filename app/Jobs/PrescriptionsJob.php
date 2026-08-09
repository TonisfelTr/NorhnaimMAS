<?php

namespace App\Jobs;

use App\Models\MedicalPrescription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Throwable;

class PrescriptionsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $timeout = 60;

    public function __construct(
        private array $data
    ) {
    }

    public function handle(): void
    {
        try {
            $this->validateRequiredData();

            Log::info(
                'PrescriptionsJob: начало создания рецепта',
                [
                    'doctor_id' => $this->data['doctor_id'],
                    'patient_id' => $this->data['patient_id'],
                    'prescription_form' =>
                        $this->data['prescription_form'] ?? null,
                    'is_strict' =>
                        (bool) ($this->data['is_strict'] ?? false),
                ]
            );

            $prescription = new MedicalPrescription();

            $prescription->doctor_id =
                (int) $this->data['doctor_id'];

            $prescription->doctor_name =
                (string) $this->data['doctor_name'];

            $prescription->patient_id =
                (int) $this->data['patient_id'];

            $prescription->patient_name =
                (string) $this->data['patient_name'];

            $prescription->generic_name =
                (string) $this->data['generic_name'];

            $prescription->drug_form =
                (string) $this->data['drug_form'];

            $prescription->dosage =
                (string) $this->data['dosage'];

            $prescription->quantity =
                (int) $this->data['quantity'];

            $prescription->standards =
                (int) $this->data['standards'];

            $prescription->usage_instructions =
                (string) $this->data['usage_instructions'];

            $prescription->prescription_form =
                (string) $this->data['prescription_form'];

            $prescription->issued_at =
                $this->data['issued_at'];

            $prescription->validity_period =
                (string) $this->data['validity_period'];

            $prescription->series =
                $this->data['series'] ?? null;

            $prescription->number =
                $this->data['number'] ?? null;

            $prescription->birth_at =
                $this->data['birth_at'];

            $prescription->is_strict =
                (bool) ($this->data['is_strict'] ?? false);

            $prescription->save();

            Log::info(
                'PrescriptionsJob: рецепт создан',
                [
                    'prescription_id' => $prescription->id,
                    'doctor_id' => $prescription->doctor_id,
                    'patient_id' => $prescription->patient_id,
                    'is_strict' => $prescription->is_strict,
                    'prescription_form' =>
                        $prescription->prescription_form,
                ]
            );
        } catch (Throwable $exception) {
            Log::error(
                'PrescriptionsJob: ошибка создания рецепта',
                [
                    'doctor_id' =>
                        $this->data['doctor_id'] ?? null,

                    'patient_id' =>
                        $this->data['patient_id'] ?? null,

                    'exception' => $exception::class,
                    'message' => $exception->getMessage(),
                ]
            );

            throw $exception;
        }
    }

    private function validateRequiredData(): void
    {
        foreach (['doctor_id', 'patient_id'] as $field) {
            if (!isset($this->data[$field]) || !$this->data[$field]) {
                throw new InvalidArgumentException(
                    "Для создания рецепта не передано обязательное поле {$field}."
                );
            }
        }
    }
}
