<?php

namespace Database\Seeders;

use App\Models\Drug;
use App\Models\DrugReceptor;
use App\Models\Receptor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DrugReceptorsSeeder extends Seeder
{
    public function run(): void
    {
        $path = storage_path('app/private/combined_drugs_data.json');

        if (!is_file($path)) {
            throw new RuntimeException("Файл с препаратами не найден: {$path}");
        }

        $allDrugs = json_decode(file_get_contents($path), true);

        if (!is_array($allDrugs)) {
            throw new RuntimeException("Не удалось декодировать JSON: {$path}");
        }

        $catalog = Receptor::query()->pluck('id', 'name');
        $unknownTargets = [];
        $missingDrugs = [];

        // Валидируем всё ДО любых DELETE/INSERT.
        foreach ($allDrugs as $category => $items) {
            if (!is_array($items)) {
                continue;
            }

            foreach ($items as $item) {
                $name = trim((string)($item['name'] ?? ''));
                $latin = trim((string)($item['latin'] ?? ''));

                foreach (array_values(array_unique($item['receptors'] ?? [])) as $target) {
                    $target = trim((string)$target);

                    if ($target !== '' && !$catalog->has($target)) {
                        $unknownTargets[$target][] = $name ?: $latin;
                    }
                }

                $drug = Drug::query()
                    ->when($latin !== '', fn($q) => $q->where('latin_name', $latin))
                    ->when($latin === '' && $name !== '', fn($q) => $q->where('name', $name))
                    ->first();

                if (!$drug) {
                    $missingDrugs[] = $latin ?: $name;
                }
            }
        }

        if ($unknownTargets) {
            $messages = [];
            foreach ($unknownTargets as $target => $drugs) {
                $messages[] = "{$target}: " . implode(', ', array_unique($drugs));
            }

            throw new RuntimeException(
                "В JSON есть мишени, отсутствующие в receptors:\n" . implode("\n", $messages)
            );
        }

        if ($missingDrugs) {
            throw new RuntimeException(
                "В БД не найдены препараты из JSON: " . implode(', ', array_unique($missingDrugs))
            );
        }

        DB::transaction(function () use ($allDrugs, $catalog) {
            foreach ($allDrugs as $items) {
                if (!is_array($items)) {
                    continue;
                }

                foreach ($items as $item) {
                    $name = trim((string)($item['name'] ?? ''));
                    $latin = trim((string)($item['latin'] ?? ''));

                    $drug = Drug::query()
                        ->when($latin !== '', fn($q) => $q->where('latin_name', $latin))
                        ->when($latin === '' && $name !== '', fn($q) => $q->where('name', $name))
                        ->firstOrFail();

                    $targetNames = collect($item['receptors'] ?? [])
                        ->map(fn($v) => trim((string)$v))
                        ->filter()
                        ->unique()
                        ->values();

                    DrugReceptor::query()->where('drug_id', $drug->id)->delete();

                    foreach ($targetNames as $targetName) {
                        DrugReceptor::create([
                            'drug_id' => $drug->id,
                            'receptor_id' => $catalog[$targetName],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        });
    }
}
