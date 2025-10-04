<?php

namespace Database\Seeders;

use App\Models\Diagnose;
use App\Models\Drug;
use App\Models\DrugReceptor;
use App\Models\MedicineContraindication;
use App\Models\MedicineIndication;
use App\Models\MedicineSideEffect;
use App\Models\Receptor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DrugSeeder extends Seeder
{
    public function run(): void
    {
        $path = storage_path('app/private/combined_drugs_data.json');
        if (!is_file($path)) {
            logger()->error("Файл с препаратами не найден: {$path}");
            return;
        }

        $json = file_get_contents($path);
        $allDrugs = json_decode($json, true);
        if (!is_array($allDrugs)) {
            logger()->error("Не удалось декодировать JSON: {$path}");
            return;
        }

        DB::transaction(function () use ($allDrugs) {
            Drug::truncate();
            MedicineContraindication::truncate();
            MedicineIndication::truncate();
            MedicineSideEffect::truncate();
            DrugReceptor::truncate();

            foreach ($allDrugs as $category => $drugs) {
                if (!is_array($drugs)) {
                    continue;
                }

                foreach ($drugs as $item) {
                    // Безопасные извлечения
                    $name        = $item['name'] ?? null;
                    $latin       = $item['latin'] ?? null;
                    $group       = (int)($item['group'] ?? 1);
                    $forms       = $item['forms'] ?? [];
                    $preferential= (bool)($item['preferential'] ?? false);
                    $strict      = (bool)($item['strict'] ?? false);
                    $pregnancy   = (bool)($item['pregnancy'] ?? false);
                    $lactation   = (bool)($item['lactation'] ?? false);

                    $half        = $item['half_output_time'] ?? null;
                    $htFrom      = is_array($half) && array_key_exists(0, $half) ? $half[0] : null;
                    $htTo        = is_array($half) && array_key_exists(1, $half) ? $half[1] : null;

                    $organs      = $item['metabolism']['organs'] ?? [];
                    $liver       = isset($organs['liver']) ? 1 : 0;
                    $kidneys     = isset($organs['kidneys']) ? 1 : 0;

                    $cyt         = $item['metabolism']['cytochromes'] ?? [];
                    $description = $item['description'] ?? null;

                    $drug = new Drug();
                    $drug->name           = $name;
                    $drug->latin_name     = $latin;
                    $drug->group          = $group;
                    $drug->ht_output_from = $htFrom;
                    $drug->ht_output_to   = $htTo;
                    // Если в модели нет casts ['forms' => 'array'], тогда замените на json_encode($forms)
                    $drug->forms          = is_array($forms) ? $forms : [];
                    $drug->preferential   = $preferential;
                    $drug->strict         = $strict;
                    $drug->pregnancy      = $pregnancy;
                    $drug->lactation      = $lactation;

                    $drug->liver          = $liver;
                    $drug->kidneys        = $kidneys;

                    $drug->cytochromes    = json_encode($cyt, JSON_UNESCAPED_UNICODE);
                    $drug->description    = $description;

                    $drug->created_at     = now();
                    $drug->updated_at     = now();
                    $drug->save();

                    // Противопоказания
                    foreach (($item['contraindications'] ?? []) as $contraindication) {
                        if ($contraindication === null || $contraindication === '') {
                            continue;
                        }
                        MedicineContraindication::firstOrCreate([
                            'drug_id'             => $drug->id,
                            'contraindication_id' => $contraindication,
                            'type'                => 1,
                        ]);
                    }

                    foreach (($item['indications'] ?? []) as $indication) {
                        if (!$indication) {
                            continue;
                        }

                        $diagnose = Diagnose::where('code', 'ilike', $indication)->first();

                        if (!$diagnose && str_contains($indication, '.')) {
                            $parentCode = explode('.', $indication)[0];
                            $diagnose = Diagnose::where('code', 'ilike', $parentCode)->first();
                        }

                        if (!$diagnose) {
                            $prefix = str_contains($indication, '.') ? explode('.', $indication)[0] : $indication;
                            $diagnose = Diagnose::where('code', 'ilike', "$prefix.%")->first();
                        }

                        if ($diagnose) {
                            MedicineIndication::firstOrCreate([
                                'diagnose_id' => $diagnose->id,
                                'medicine_id' => $drug->id,
                            ]);
                        } else {
                            logger()->warning("Диагноз не найден для кода: {$indication}");
                        }
                    }

                    foreach (($item['side_effects'] ?? []) as $side_effect) {
                        if ($side_effect === null || $side_effect === '') {
                            continue;
                        }
                        MedicineSideEffect::firstOrCreate([
                            'drug_id'        => $drug->id,
                            'side_effect_id' => $side_effect,
                        ]);
                    }

                    foreach (($item['receptors'] ?? []) as $receptorName) {
                        if (!$receptorName) {
                            continue;
                        }
                        $receptor = Receptor::where('name', $receptorName)->first();
                        if (!$receptor) {
                            logger()->warning("Рецептор не найден: {$receptorName} (препарат: {$drug->name})");
                            continue;
                        }
                        DrugReceptor::firstOrCreate([
                            'drug_id'     => $drug->id,
                            'receptor_id' => $receptor->id,
                        ]);
                    }
                }
            }
        });
    }
}
