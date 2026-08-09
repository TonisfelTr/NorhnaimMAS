<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class LabOverviewDemoSeeder extends Seeder
{
    private const MARK = '[LAB_OVERVIEW_DEMO]';

    public function run(): void
    {
        $doctorId = (int) env('LAB_DEMO_DOCTOR_ID', 0);

        if ($doctorId <= 0) {
            $doctorId = (int) DB::table('doctors')
                ->orderBy('id')
                ->value('id');

            if ($doctorId > 0) {
                $this->command?->warn(
                    'LAB_DEMO_DOCTOR_ID не задан. '
                    . "Для демо выбран первый врач: doctor_id={$doctorId}."
                );
            }
        }

        if (
            $doctorId <= 0
            || !DB::table('doctors')->where('id', $doctorId)->exists()
        ) {
            throw new RuntimeException(
                'Не найден врач для демо-данных. '
                . 'Запусти seeder с LAB_DEMO_DOCTOR_ID=<id врача>.'
            );
        }

        /** @var Collection<int, object> $patients */
        $patients = DB::table('patients')
            ->orderBy('id')
            ->limit(8)
            ->get([
                'id',
                'surname',
                'name',
                'patronym',
                'birth_at',
                'gender',
            ]);

        if ($patients->isEmpty()) {
            throw new RuntimeException(
                'В таблице patients нет пациентов. '
                . 'Сначала создай хотя бы одного пациента.'
            );
        }

        DB::transaction(function () use ($doctorId, $patients): void {
            $this->removePreviousDemoData($doctorId);

            $hemoglobinId = $this->ensureNumericParameter(
                name: '[DEMO] Гемоглобин',
                unit: 'г/л',
                sampleType: 'кровь',
                group: 'demo_cbc',
                normalMin: 120,
                normalMax: 160,
                criticalLow: 70,
                criticalHigh: 220,
            );

            $glucoseId = $this->ensureNumericParameter(
                name: '[DEMO] Глюкоза',
                unit: 'ммоль/л',
                sampleType: 'сыворотка',
                group: 'demo_metabolic',
                normalMin: 3.9,
                normalMax: 5.5,
                criticalLow: 2.5,
                criticalHigh: 20,
            );

            /*
             * 6 НОВЫХ ГОТОВЫХ РЕЗУЛЬТАТОВ:
             * - 2 критических;
             * - 2 с отклонениями;
             * - 2 нормальных.
             *
             * Все result_showed_at = NULL, поэтому должны попасть в
             * "Новые результаты" и "Последние результаты".
             */
            $readyCases = [
                [
                    'title' => '[DEMO] ОАК — критический гемоглобин',
                    'parameter_id' => $hemoglobinId,
                    'sample_type' => 'кровь',
                    'value' => '245',
                    'priority' => 'urgent',
                    'days_ago' => 0,
                ],
                [
                    'title' => '[DEMO] Глюкоза — критическое значение',
                    'parameter_id' => $glucoseId,
                    'sample_type' => 'сыворотка',
                    'value' => '24.6',
                    'priority' => 'urgent',
                    'days_ago' => 0,
                ],
                [
                    'title' => '[DEMO] ОАК — гемоглобин выше нормы',
                    'parameter_id' => $hemoglobinId,
                    'sample_type' => 'кровь',
                    'value' => '178',
                    'priority' => 'normal',
                    'days_ago' => 1,
                ],
                [
                    'title' => '[DEMO] Глюкоза — умеренно повышена',
                    'parameter_id' => $glucoseId,
                    'sample_type' => 'сыворотка',
                    'value' => '7.4',
                    'priority' => 'normal',
                    'days_ago' => 1,
                ],
                [
                    'title' => '[DEMO] ОАК — нормальный результат',
                    'parameter_id' => $hemoglobinId,
                    'sample_type' => 'кровь',
                    'value' => '138',
                    'priority' => 'normal',
                    'days_ago' => 2,
                ],
                [
                    'title' => '[DEMO] Глюкоза — нормальный результат',
                    'parameter_id' => $glucoseId,
                    'sample_type' => 'сыворотка',
                    'value' => '4.9',
                    'priority' => 'normal',
                    'days_ago' => 2,
                ],
            ];

            foreach ($readyCases as $index => $case) {
                $patient = $patients[$index % $patients->count()];
                $date = now()->copy()
                    ->subDays((int) $case['days_ago']);

                $researchId = $this->insertResearch(
                    doctorId: $doctorId,
                    patientId: (int) $patient->id,
                    status: 'ready',
                    priority: (string) $case['priority'],
                    title: (string) $case['title'],
                    sampleType: (string) $case['sample_type'],
                    parameterIds: [(int) $case['parameter_id']],
                    plannedAt: $date,
                    researchDate: $date,
                    resultShowedAt: null,
                    suffix: "READY-{$index}",
                );

                $this->insertResult(
                    researchId: $researchId,
                    patientId: (int) $patient->id,
                    parameterId: (int) $case['parameter_id'],
                    value: (string) $case['value'],
                );
            }

            /*
             * 3 исследования "В РАБОТЕ".
             * Одно из них одновременно просрочено.
             */
            $processingCases = [
                [
                    'title' => '[DEMO] Биохимия — в работе',
                    'planned_at' => now()->copy()->addDay(),
                    'parameter_id' => $glucoseId,
                    'sample_type' => 'сыворотка',
                ],
                [
                    'title' => '[DEMO] ОАК — в работе',
                    'planned_at' => now()->copy()->addDays(2),
                    'parameter_id' => $hemoglobinId,
                    'sample_type' => 'кровь',
                ],
                [
                    'title' => '[DEMO] ОАК — просрочено в работе',
                    'planned_at' => now()->copy()->subDays(2),
                    'parameter_id' => $hemoglobinId,
                    'sample_type' => 'кровь',
                ],
            ];

            foreach ($processingCases as $index => $case) {
                $patient = $patients[
                    ($index + count($readyCases)) % $patients->count()
                ];

                $this->insertResearch(
                    doctorId: $doctorId,
                    patientId: (int) $patient->id,
                    status: 'processing',
                    priority: $index === 2 ? 'urgent' : 'normal',
                    title: (string) $case['title'],
                    sampleType: (string) $case['sample_type'],
                    parameterIds: [(int) $case['parameter_id']],
                    plannedAt: $case['planned_at'],
                    researchDate: null,
                    resultShowedAt: null,
                    suffix: "PROCESSING-{$index}",
                );
            }

            /*
             * Ещё одно ПРОСРОЧЕННОЕ направление со статусом ordered.
             * Итого "Просрочено" должно быть 2:
             * 1 processing + 1 ordered.
             */
            $patient = $patients[0];

            $this->insertResearch(
                doctorId: $doctorId,
                patientId: (int) $patient->id,
                status: 'ordered',
                priority: 'urgent',
                title: '[DEMO] Анализ — просроченное направление',
                sampleType: 'сыворотка',
                parameterIds: [$glucoseId],
                plannedAt: now()->copy()->subDays(5),
                researchDate: null,
                resultShowedAt: null,
                suffix: 'ORDERED-OVERDUE',
            );

            /*
             * Будущее направление — полезно для проверки журнала/назначений,
             * но оно не меняет четыре счётчика обзора.
             */
            $this->insertResearch(
                doctorId: $doctorId,
                patientId: (int) $patients[
                    min(1, $patients->count() - 1)
                ]->id,
                status: 'ordered',
                priority: 'normal',
                title: '[DEMO] Плановое направление',
                sampleType: 'кровь',
                parameterIds: [$hemoglobinId],
                plannedAt: now()->copy()->addDays(4),
                researchDate: null,
                resultShowedAt: null,
                suffix: 'ORDERED-FUTURE',
            );

            /*
             * Уже просмотренный готовый результат.
             * Он будет полезен в журнале, но НЕ должен попадать
             * в "Новые результаты".
             */
            $viewedPatient = $patients[
                min(2, $patients->count() - 1)
            ];

            $viewedResearchId = $this->insertResearch(
                doctorId: $doctorId,
                patientId: (int) $viewedPatient->id,
                status: 'ready',
                priority: 'normal',
                title: '[DEMO] Уже просмотренный результат',
                sampleType: 'кровь',
                parameterIds: [$hemoglobinId],
                plannedAt: now()->copy()->subDays(3),
                researchDate: now()->copy()->subDays(3),
                resultShowedAt: now()->copy()->subDays(2),
                suffix: 'READY-VIEWED',
            );

            $this->insertResult(
                researchId: $viewedResearchId,
                patientId: (int) $viewedPatient->id,
                parameterId: $hemoglobinId,
                value: '142',
            );
        });

        $this->command?->newLine();
        $this->command?->info(
            "Демо-данные лаборатории созданы для doctor_id={$doctorId}."
        );
        $this->command?->line('Ожидаемо на странице обзора:');
        $this->command?->line('  Новые результаты:      6');
        $this->command?->line('  Критические значения:  2');
        $this->command?->line('  В работе:               3');
        $this->command?->line('  Просрочено:             2');
        $this->command?->line(
            '  Требуют внимания:      4 (2 критических + 2 отклонения)'
        );
        $this->command?->newLine();
        $this->command?->comment(
            'Повторный запуск безопасен: старые записи этого DEMO для врача '
            . 'сначала удаляются.'
        );
    }

    private function removePreviousDemoData(int $doctorId): void
    {
        $researchIds = DB::table('lab_researches')
            ->where('doctor_id', $doctorId)
            ->where('comment', 'like', self::MARK . '%')
            ->pluck('id');

        if ($researchIds->isEmpty()) {
            return;
        }

        DB::table('lab_research_results')
            ->whereIn('lab_research_id', $researchIds)
            ->delete();

        DB::table('lab_researches')
            ->whereIn('id', $researchIds)
            ->delete();
    }

    private function ensureNumericParameter(
        string $name,
        string $unit,
        string $sampleType,
        string $group,
        float $normalMin,
        float $normalMax,
        float $criticalLow,
        float $criticalHigh,
    ): int {
        $now = now();

        $normalValues = json_encode(
            [
                [
                    'sex' => 'any',
                    'age_min_y' => 0,
                    'age_max_y' => 150,
                    'min_value' => $normalMin,
                    'max_value' => $normalMax,
                ],
            ],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );

        $parameterId = DB::table('lab_parameters')
            ->where('name', $name)
            ->value('id');

        $attributes = [
            'unit' => $unit,
            'data_type' => 'numeric',
            'sample_type' => $sampleType,
            'allowed_values' => null,
            'normal_values' => $normalValues,
            'notes' => self::MARK . ' Тестовый показатель для UI.',
            'group' => $group,
            'updated_at' => $now,
        ];

        if ($parameterId) {
            DB::table('lab_parameters')
                ->where('id', $parameterId)
                ->update($attributes);
        } else {
            $parameterId = DB::table('lab_parameters')
                ->insertGetId([
                    'name' => $name,
                    ...$attributes,
                    'created_at' => $now,
                ]);
        }

        /*
         * Удаляем только DEMO-диапазоны нашего DEMO-параметра
         * и создаём один универсальный диапазон 0–150 лет.
         */
        DB::table('lab_parameters_critical_ranges')
            ->where('parameter_id', $parameterId)
            ->delete();

        DB::table('lab_parameters_critical_ranges')->insert([
            'parameter_id' => $parameterId,
            'sex' => 'any',
            'age_min_y' => 0,
            'age_max_y' => 150,
            'critical_low' => $criticalLow,
            'critical_high' => $criticalHigh,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return (int) $parameterId;
    }

    private function insertResearch(
        int $doctorId,
        int $patientId,
        string $status,
        string $priority,
        string $title,
        string $sampleType,
        array $parameterIds,
        Carbon $plannedAt,
        ?Carbon $researchDate,
        ?Carbon $resultShowedAt,
        string $suffix,
    ): int {
        return (int) DB::table('lab_researches')->insertGetId([
            'patient_id' => $patientId,
            'doctor_id' => $doctorId,
            'research_date' => $researchDate?->toDateString(),
            'laboratory' => $title,
            'planned_at' => $plannedAt->toDateString(),
            'status' => $status,
            'priority' => $priority,
            'comment' => self::MARK . ' ' . $suffix,
            'parameters' => json_encode(
                array_values($parameterIds),
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            ),
            'sample_type' => $sampleType,
            'result_showed_at' => $resultShowedAt,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function insertResult(
        int $researchId,
        int $patientId,
        int $parameterId,
        string $value,
    ): void {
        DB::table('lab_research_results')->insert([
            'lab_research_id' => $researchId,
            'patient_id' => $patientId,
            'lab_parameter_id' => $parameterId,
            'value' => $value,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
