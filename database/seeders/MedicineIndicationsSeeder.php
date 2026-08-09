<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use JsonException;
use RuntimeException;

final class MedicineIndicationsSeeder extends Seeder
{
    public function run(): void
    {
        $path = storage_path('app/private/combined_drugs_data.json');

        if (!is_file($path) || !is_readable($path)) {
            throw new RuntimeException(
                "Файл не найден или недоступен для чтения: {$path}"
            );
        }

        try {
            $allDrugs = json_decode(
                file_get_contents($path),
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (JsonException $exception) {
            throw new RuntimeException(
                'Ошибка JSON: ' . $exception->getMessage(),
                previous: $exception
            );
        }

        if (!is_array($allDrugs)) {
            throw new RuntimeException(
                'Корневой элемент combined_drugs_data.json должен быть массивом/объектом.'
            );
        }

        $diagnosesCount = DB::table('diagnoses')->count();
        $drugsCount = DB::table('drugs')->count();

        if ($diagnosesCount === 0) {
            throw new RuntimeException(
                'Таблица diagnoses пуста. Сначала необходимо загрузить справочник диагнозов.'
            );
        }

        if ($drugsCount === 0) {
            throw new RuntimeException(
                'Таблица drugs пуста. Сначала необходимо загрузить препараты.'
            );
        }

        $stats = [
            'json_drugs' => 0,
            'matched_drugs' => 0,
            'missing_drugs' => 0,
            'json_indications' => 0,
            'diagnosis_matches' => 0,
            'saved_rows' => 0,
            'missing_diagnosis_codes' => 0,
        ];

        $missingDrugs = [];
        $missingDiagnosisCodes = [];

        DB::transaction(function () use (
            $allDrugs,
            &$stats,
            &$missingDrugs,
            &$missingDiagnosisCodes
        ): void {
            foreach ($allDrugs as $category => $drugRows) {
                if (!is_array($drugRows)) {
                    continue;
                }

                foreach ($drugRows as $item) {
                    if (!is_array($item)) {
                        continue;
                    }

                    $stats['json_drugs']++;

                    $name = trim((string) ($item['name'] ?? ''));
                    $latin = trim((string) ($item['latin'] ?? ''));

                    $medicineId = $this->findMedicineId(
                        $name,
                        $latin
                    );

                    if (!$medicineId) {
                        $stats['missing_drugs']++;

                        $missingDrugs[] = trim(
                            $name
                            . ($latin !== '' ? " ({$latin})" : '')
                            . " [{$category}]"
                        );

                        continue;
                    }

                    $stats['matched_drugs']++;

                    $indications = $item['indications'] ?? [];

                    if (!is_array($indications)) {
                        continue;
                    }

                    foreach ($indications as $indicationItem) {
                        if (!is_array($indicationItem)) {
                            continue;
                        }

                        $code = trim(
                            (string) ($indicationItem['code'] ?? '')
                        );

                        if ($code === '') {
                            continue;
                        }

                        $stats['json_indications']++;

                        $diagnoses = $this->findDiagnoses($code);

                        if ($diagnoses->isEmpty()) {
                            $stats['missing_diagnosis_codes']++;
                            $missingDiagnosisCodes[] = $code;

                            continue;
                        }

                        $stats['diagnosis_matches'] +=
                            $diagnoses->count();

                        foreach ($diagnoses as $diagnosis) {
                            DB::table('medicine_indications')
                                ->updateOrInsert(
                                    [
                                        'medicine_id' =>
                                            (int) $medicineId,
                                        'diagnose_id' =>
                                            (int) $diagnosis->id,
                                    ],
                                    [
                                        'indicated_in_russia' =>
                                            (bool) (
                                                $indicationItem[
                                                    'indicated_in_russia'
                                                ] ?? false
                                            ),
                                        'indicated_by_fda' =>
                                            (bool) (
                                                $indicationItem[
                                                    'indicated_by_fda'
                                                ] ?? false
                                            ),
                                        'deleted_at' => null,
                                        'updated_at' => now(),
                                        'created_at' => now(),
                                    ]
                                );

                            $stats['saved_rows']++;
                        }
                    }
                }
            }
        });

        $actualRows = DB::table('medicine_indications')->count();

        $this->command?->newLine();
        $this->command?->info(
            'Показания препаратов синхронизированы.'
        );

        $this->command?->table(
            ['Показатель', 'Количество'],
            [
                ['Препаратов в БД', $drugsCount],
                ['Диагнозов в БД', $diagnosesCount],
                ['Препаратов в JSON', $stats['json_drugs']],
                ['Препаратов сопоставлено', $stats['matched_drugs']],
                ['Препаратов не найдено', $stats['missing_drugs']],
                ['Показаний в JSON', $stats['json_indications']],
                ['Совпадений диагнозов', $stats['diagnosis_matches']],
                ['Создано/обновлено записей', $stats['saved_rows']],
                ['Строк в medicine_indications', $actualRows],
            ]
        );

        if ($missingDrugs !== []) {
            $this->command?->warn(
                'Не найдены препараты (первые 20):'
            );

            foreach (
                array_slice(
                    array_values(array_unique($missingDrugs)),
                    0,
                    20
                ) as $missingDrug
            ) {
                $this->command?->line('  - ' . $missingDrug);
            }
        }

        if ($missingDiagnosisCodes !== []) {
            $this->command?->warn(
                'Не найдены коды/маски диагнозов:'
            );

            $this->command?->line(
                '  '
                . implode(
                    ', ',
                    array_values(
                        array_unique($missingDiagnosisCodes)
                    )
                )
            );
        }
    }

    private function findMedicineId(
        string $name,
        string $latin
    ): ?int {
        $query = DB::table('drugs');

        if ($latin !== '') {
            $id = $query
                ->where('latin_name', 'ilike', $latin)
                ->value('id');

            if ($id) {
                return (int) $id;
            }
        }

        if ($name !== '') {
            $id = DB::table('drugs')
                ->where('name', 'ilike', $name)
                ->value('id');

            if ($id) {
                return (int) $id;
            }
        }

        return null;
    }

    /**
     * Возвращает все диагнозы, соответствующие коду из JSON.
     *
     * Примеры:
     * F25.2  -> точное совпадение F25.2
     * F22    -> F22, либо все F22.* если родителя нет
     * F1%.5  -> все совпадения SQL ILIKE F1%.5
     */
    private function findDiagnoses(string $code): Collection
    {
        if (
            str_contains($code, '%')
            || str_contains($code, '_')
        ) {
            return DB::table('diagnoses')
                ->where('code', 'ilike', $code)
                ->get([
                    'id',
                    'code',
                ]);
        }

        $exact = DB::table('diagnoses')
            ->where('code', 'ilike', $code)
            ->get([
                'id',
                'code',
            ]);

        if ($exact->isNotEmpty()) {
            return $exact;
        }

        /*
         * Если в JSON указан родительский код, а в diagnoses
         * есть только дочерние коды, связываем со всеми дочерними.
         */
        $children = DB::table('diagnoses')
            ->where('code', 'ilike', $code . '.%')
            ->get([
                'id',
                'code',
            ]);

        if ($children->isNotEmpty()) {
            return $children;
        }

        return collect();
    }
}
