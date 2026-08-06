<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\LabParameter;
use App\Models\LabResearch;
use App\Models\Patient;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class LabDynamicsSeeder extends Seeder
{
    private const LABORATORY = 'Тест динамики';
    private const COMMENT = 'Демо-данные для проверки страницы динамики';

    public function run(): void
    {
        [$doctor, $patient] = $this->resolveDoctorAndPatient();
        $parameter = $this->resolveParameter();

        $researchModel = new LabResearch();
        $researchTable = $researchModel->getTable();
        $resultModel = $researchModel->results()->getRelated();
        $resultTable = $resultModel->getTable();

        $baseDate = now()->startOfDay();
        $points = [
            ['date' => $baseDate->copy()->subMonthsNoOverflow(6), 'value' => '120'],
            ['date' => $baseDate->copy()->subMonthsNoOverflow(3), 'value' => '128'],
            ['date' => $baseDate->copy()->subDays(5), 'value' => '135'],
        ];

        DB::transaction(function () use (
            $doctor,
            $patient,
            $parameter,
            $points,
            $researchTable,
            $resultTable,
            $resultModel
        ): void {
            $this->deletePreviousDemoData(
                $doctor->id,
                $patient->id,
                $researchTable,
                $resultTable
            );

            foreach ($points as $point) {
                /** @var Carbon $date */
                $date = $point['date'];

                $researchId = DB::table($researchTable)->insertGetId([
                    'patient_id' => $patient->id,
                    'doctor_id' => $doctor->id,
                    'research_date' => $date->toDateString(),
                    'planned_at' => $date->toDateString(),
                    'laboratory' => self::LABORATORY,
                    'status' => 'ready',
                    'priority' => 'normal',
                    'comment' => self::COMMENT,
                    'parameters' => json_encode(
                        [(int) $parameter->id],
                        JSON_THROW_ON_ERROR
                    ),
                    'sample_type' => 'кровь',
                    'result_showed_at' => null,
                    'created_at' => $date,
                    'updated_at' => $date,
                ]);

                $resultData = [
                    'lab_research_id' => $researchId,
                    'patient_id' => $patient->id,
                    'lab_parameter_id' => $parameter->id,
                    'value' => $point['value'],
                ];

                $createdAtColumn = $resultModel->getCreatedAtColumn();
                $updatedAtColumn = $resultModel->getUpdatedAtColumn();

                if (Schema::hasColumn($resultTable, $createdAtColumn)) {
                    $resultData[$createdAtColumn] = $date;
                }

                if (Schema::hasColumn($resultTable, $updatedAtColumn)) {
                    $resultData[$updatedAtColumn] = $date;
                }

                DB::table($resultTable)->insert($resultData);
            }
        });

        $this->printSummary($doctor, $patient, $parameter);
    }

    /**
     * Выбирает уже связанную пару врач–пациент. Если такой пары нет,
     * привязывает первого пациента к первому врачу.
     *
     * @return array{0: Doctor, 1: Patient}
     */
    private function resolveDoctorAndPatient(): array
    {
        $patientTable = (new Patient())->getTable();
        $hasDoctorId = Schema::hasColumn($patientTable, 'doctor_id');

        if ($hasDoctorId) {
            $patient = Patient::query()
                ->whereNotNull('doctor_id')
                ->whereIn('doctor_id', Doctor::query()->select('id'))
                ->orderBy('id')
                ->first();

            if ($patient) {
                $doctor = Doctor::query()->find($patient->doctor_id);

                if ($doctor) {
                    return [$doctor, $patient];
                }
            }
        }

        $doctor = Doctor::query()->orderBy('id')->first();
        $patient = Patient::query()->orderBy('id')->first();

        if (!$doctor) {
            throw new RuntimeException('В таблице doctors нет врачей.');
        }

        if (!$patient) {
            throw new RuntimeException('В таблице patients нет пациентов.');
        }

        if ($hasDoctorId && (int) $patient->doctor_id !== (int) $doctor->id) {
            DB::table($patientTable)
                ->where('id', $patient->id)
                ->update(['doctor_id' => $doctor->id]);

            $patient->setAttribute('doctor_id', $doctor->id);
        }

        return [$doctor, $patient];
    }

    private function resolveParameter(): LabParameter
    {
        $parameter = LabParameter::query()
            ->whereIn('data_type', [
                'numeric',
                'number',
                'integer',
                'decimal',
                'float',
            ])
            ->orderBy('id')
            ->first();

        $parameter ??= LabParameter::query()
            ->whereNull('data_type')
            ->orderBy('id')
            ->first();

        $parameter ??= LabParameter::query()
            ->orderBy('id')
            ->first();

        if (!$parameter) {
            throw new RuntimeException(
                'В таблице lab_parameters нет показателей.'
            );
        }

        return $parameter;
    }

    private function deletePreviousDemoData(
        int $doctorId,
        int $patientId,
        string $researchTable,
        string $resultTable
    ): void {
        $researchIds = DB::table($researchTable)
            ->where('doctor_id', $doctorId)
            ->where('patient_id', $patientId)
            ->where('laboratory', self::LABORATORY)
            ->where('comment', self::COMMENT)
            ->pluck('id');

        if ($researchIds->isEmpty()) {
            return;
        }

        DB::table($resultTable)
            ->whereIn('lab_research_id', $researchIds)
            ->delete();

        DB::table($researchTable)
            ->whereIn('id', $researchIds)
            ->delete();
    }

    private function printSummary(
        Doctor $doctor,
        Patient $patient,
        LabParameter $parameter
    ): void {
        $patientName = collect([
            $patient->surname,
            $patient->name,
            $patient->patronym,
        ])->filter()->implode(' ');

        $this->command?->newLine();
        $this->command?->info('Тестовые данные динамики созданы.');
        $this->command?->line('Врач: ID ' . $doctor->id);
        $this->command?->line(
            'Пациент: ' . ($patientName ?: 'ID ' . $patient->id)
            . ' (ID ' . $patient->id . ')'
        );
        $this->command?->line(
            'Показатель: ' . $parameter->name
            . ' (ID ' . $parameter->id . ')'
        );
        $this->command?->line('Значения: 120 → 128 → 135');
    }
}

