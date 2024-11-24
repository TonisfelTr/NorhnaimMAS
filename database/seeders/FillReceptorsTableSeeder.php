<?php

namespace Database\Seeders;

use App\Models\Receptor;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FillReceptorsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $receptors = [
            // Допаминовые рецепторы
            "D-1", "D-2", "D-3", "D-4", "D-5",

            // Серотониновые рецепторы (5HT)
            "5HT-1A", "5HT-1B", "5HT-1C", "5HT-1D", "5HT-1E", "5HT-1F",
            "5HT-2A", "5HT-2B", "5HT-2C", "5HT-3", "5HT-4",
            "5HT-5A", "5HT-5B", "5HT-6", "5HT-7",

            // Норадреналиновые рецепторы (альфа и бета)
            "a-1A", "a-1B", "a-1D",
            "a-2A", "a-2B", "a-2C", "a-2D",
            "b-1", "b-2", "b-3",

            // Гистаминовые рецепторы
            "H-1", "H-2", "H-3", "H-4",

            // Ацетилхолиновые рецепторы (никотиновые и мускариновые)
            "ACh-N", "ACh-M1", "ACh-M2", "ACh-M3", "ACh-M4", "ACh-M5",

            // Дополнительные рецепторы
            "NMDA", "AMPA", "Kainate", // Глутаматные рецепторы
            "GABA-A", "GABA-B", // ГАМК-рецепторы
            "Sigma-1", "Sigma-2", // Сигма-рецепторы
            "Opioid-mu", "Opioid-delta", "Opioid-kappa", // Опиоидные рецепторы
            "P2X", "P2Y", // Пуриновые рецепторы

            // Транспортёры
            "DAT", // Допаминовый транспортёр (Dopamine Transporter)
            "SERT", // Серотониновый транспортёр (Serotonin Transporter)
            "NET", // Норадреналиновый транспортёр (Norepinephrine Transporter)
            "GAT-1", "GAT-2", "GAT-3", // ГАМК-транспортёры (GABA Transporters)
            "GLT-1", "EAAT1", "EAAT2", // Глутаматные транспортёры (Excitatory Amino Acid Transporters)
        ];

        foreach ($receptors as $receptorName) {
            $receptor = new Receptor();
            $receptor->name = $receptorName;
            $receptor->save();
        }
    }
}
