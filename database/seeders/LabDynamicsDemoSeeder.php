<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\LabResearch;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class LabDynamicsDemoSeeder extends Seeder
{
    /**
     * Вставьте сюда ID врача из поля «Врач».
     * В консоли браузера его можно получить командой:
     * $('#dynamicsDoctorSelect').val()
     */
    private const DOCTOR_ID = 1;

    /**
     * Вставьте сюда ID существующего пациента.
     * Список пациентов можно посмотреть через Tinker:
     * Patient::query()->select('id', 'surname', 'name', 'patronym')->limit(20)->get();
     */
    private const PATIENT_ID = 17;

    public function run(): void
    {
        if (self::DOCTOR_ID <= 0 || self::PATIENT_ID <= 0) {
            throw new RuntimeException(
                'Сначала заполните DOCTOR_ID и PATIENT_ID в LabDynamicsDemoSeeder.php.'
            );
        }

        $doctor = Doctor::query()->findOrFail(self::DOCTOR_ID);
        $patient = Patient::query()->findOrFail(self::PATIENT_ID);

        DB::transaction(function () use ($doctor, $patient): void {
            $researchPrototype = new LabResearch();
            $resultPrototype = $researchPrototype->results()->getRelated();

            if (!method_exists($resultPrototype, 'parameter')) {
                throw new RuntimeException(
                    'У модели результата нет связи parameter(). Проверьте модель, которую возвращает LabResearch::results().'
                );
            }

            $parameterPrototype = $resultPrototype->parameter()->getRelated();

            $parameter = $parameterPrototype->newQuery()
                ->where('name', 'Гемоглобин')
                ->first();

            if (!$parameter) {
                $parameter = $parameterPrototype->newInstance();

                $normalValues = [
                    [
                        'sex' => 'ANY',
                        'age_min_y' => 0,
                        'age_max_y' => 150,
                        'min_value' => 120,
                        'max_value' => 160,
                    ],
                ];

                $this->fillExistingColumns($parameter, [
                    'name' => 'Гемоглобин',
                    'code' => 'HGB_DEMO',
                    'unit' => 'г/л',
                    'data_type' => 'numeric',
                    'group' => 'cbc',
                    'normal_values' => $this->prepareJsonValue(
                        $parameter,
                        'normal_values',
                        $normalValues
                    ),
                ]);

                $parameter->save();
            }

            $points = [
                [now()->copy()->subMonths(2)->setTime(9, 0), '128'],
                [now()->copy()->subMonth()->setTime(9, 0), '134'],
                [now()->copy()->setTime(9, 0), '131'],
            ];

            foreach ($points as [$date, $value]) {
                $research = new LabResearch();

                $this->fillExistingColumns($research, [
                    'doctor_id' => (int) $doctor->id,
                    'patient_id' => (int) $patient->id,
                    'status' => 'ready',
                    'priority' => 'normal',
                    'laboratory' => 'Демонстрационная лаборатория',
                    'sample_type' => 'кровь',
                    'research_date' => $date,
                    'planned_at' => $date,
                    'collected_at' => $date,
                    'result_at' => $date,
                    'comment' => 'Демо-данные для страницы динамики показателей',
                ]);

                $this->assertRequiredResearchColumnsExist($research);
                $research->save();

                $result = $resultPrototype->newInstance();

                $this->fillExistingColumns($result, [
                    'patient_id' => (int) $patient->id,
                    'doctor_id' => (int) $doctor->id,
                    'lab_parameter_id' => (int) $parameter->getKey(),
                    'value' => $value,
                ]);

                $this->assertRequiredResultColumnsExist($result);

                // Связь results() сама заполнит внешний ключ lab_research_id.
                $research->results()->save($result);
            }

            $this->command?->info(
                sprintf(
                    'Создано 3 результата динамики: doctor_id=%d, patient_id=%d, parameter_id=%d.',
                    $doctor->id,
                    $patient->id,
                    $parameter->getKey()
                )
            );
        });
    }

    private function fillExistingColumns(Model $model, array $attributes): void
    {
        $table = $model->getTable();
        $connection = $model->getConnectionName();
        $schema = Schema::connection($connection);

        $existingAttributes = [];

        foreach ($attributes as $column => $value) {
            if ($schema->hasColumn($table, $column)) {
                $existingAttributes[$column] = $value;
            }
        }

        $model->forceFill($existingAttributes);
    }

    private function prepareJsonValue(
        Model $model,
        string $column,
        array $value
    ): array|string {
        $cast = $model->getCasts()[$column] ?? null;

        if ($cast !== null) {
            return $value;
        }

        return json_encode(
            $value,
            JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
        );
    }

    private function assertRequiredResearchColumnsExist(Model $research): void
    {
        $schema = Schema::connection($research->getConnectionName());
        $table = $research->getTable();

        foreach (['doctor_id', 'patient_id', 'status'] as $column) {
            if (!$schema->hasColumn($table, $column)) {
                throw new RuntimeException(
                    "В таблице {$table} отсутствует обязательная колонка {$column}."
                );
            }
        }
    }

    private function assertRequiredResultColumnsExist(Model $result): void
    {
        $schema = Schema::connection($result->getConnectionName());
        $table = $result->getTable();

        foreach (['lab_parameter_id', 'value'] as $column) {
            if (!$schema->hasColumn($table, $column)) {
                throw new RuntimeException(
                    "В таблице {$table} отсутствует обязательная колонка {$column}."
                );
            }
        }
    }
}
