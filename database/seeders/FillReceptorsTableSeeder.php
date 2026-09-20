<?php

namespace Database\Seeders;

use App\Models\Receptor;
use Illuminate\Database\Seeder;

class FillReceptorsTableSeeder extends Seeder
{
    public function run(): void
    {
        Receptor::truncate();

        $receptors = [
            // Допаминовые рецепторы
            ['name' => 'D-1', 'full_name' => 'Дофаминовый рецептор D1'],
            ['name' => 'D-2', 'full_name' => 'Дофаминовый рецептор D2'],
            ['name' => 'D-3', 'full_name' => 'Дофаминовый рецептор D3'],
            ['name' => 'D-4', 'full_name' => 'Дофаминовый рецептор D4'],
            ['name' => 'D-5', 'full_name' => 'Дофаминовый рецептор D5'],

            // Серотониновые рецепторы (5-HT)
            ['name' => '5HT-1A', 'full_name' => 'Серотониновый рецептор 5-HT1A'],
            ['name' => '5HT-1B', 'full_name' => 'Серотониновый рецептор 5-HT1B'],
            ['name' => '5HT-1C', 'full_name' => 'Серотониновый рецептор 5-HT1C (историческое обозначение 5-HT2C)'],
            ['name' => '5HT-1D', 'full_name' => 'Серотониновый рецептор 5-HT1D'],
            ['name' => '5HT-1E', 'full_name' => 'Серотониновый рецептор 5-HT1E'],
            ['name' => '5HT-1F', 'full_name' => 'Серотониновый рецептор 5-HT1F'],
            ['name' => '5HT-2A', 'full_name' => 'Серотониновый рецептор 5-HT2A'],
            ['name' => '5HT-2B', 'full_name' => 'Серотониновый рецептор 5-HT2B'],
            ['name' => '5HT-2C', 'full_name' => 'Серотониновый рецептор 5-HT2C'],
            ['name' => '5HT-3', 'full_name' => 'Серотониновый рецептор 5-HT3'],
            ['name' => '5HT-4', 'full_name' => 'Серотониновый рецептор 5-HT4'],
            ['name' => '5HT-5A', 'full_name' => 'Серотониновый рецептор 5-HT5A'],
            ['name' => '5HT-5B', 'full_name' => 'Серотониновый рецептор 5-HT5B'],
            ['name' => '5HT-6', 'full_name' => 'Серотониновый рецептор 5-HT6'],
            ['name' => '5HT-7', 'full_name' => 'Серотониновый рецептор 5-HT7'],

            // Адренорецепторы
            ['name' => 'a-1A', 'full_name' => 'Альфа-1A-адренорецептор'],
            ['name' => 'a-1B', 'full_name' => 'Альфа-1B-адренорецептор'],
            ['name' => 'a-1D', 'full_name' => 'Альфа-1D-адренорецептор'],
            ['name' => 'a-2A', 'full_name' => 'Альфа-2A-адренорецептор'],
            ['name' => 'a-2B', 'full_name' => 'Альфа-2B-адренорецептор'],
            ['name' => 'a-2C', 'full_name' => 'Альфа-2C-адренорецептор'],
            ['name' => 'a-2D', 'full_name' => 'Альфа-2D-адренорецептор'],
            ['name' => 'b-1', 'full_name' => 'Бета-1-адренорецептор'],
            ['name' => 'b-2', 'full_name' => 'Бета-2-адренорецептор'],
            ['name' => 'b-3', 'full_name' => 'Бета-3-адренорецептор'],

            // Гистаминовые рецепторы
            ['name' => 'H-1', 'full_name' => 'Гистаминовый рецептор H1'],
            ['name' => 'H-2', 'full_name' => 'Гистаминовый рецептор H2'],
            ['name' => 'H-3', 'full_name' => 'Гистаминовый рецептор H3'],
            ['name' => 'H-4', 'full_name' => 'Гистаминовый рецептор H4'],

            // Ацетилхолиновые рецепторы
            ['name' => 'ACh-N', 'full_name' => 'Никотиновый ацетилхолиновый рецептор'],
            ['name' => 'ACh-M1', 'full_name' => 'Мускариновый ацетилхолиновый рецептор M1'],
            ['name' => 'ACh-M2', 'full_name' => 'Мускариновый ацетилхолиновый рецептор M2'],
            ['name' => 'ACh-M3', 'full_name' => 'Мускариновый ацетилхолиновый рецептор M3'],
            ['name' => 'ACh-M4', 'full_name' => 'Мускариновый ацетилхолиновый рецептор M4'],
            ['name' => 'ACh-M5', 'full_name' => 'Мускариновый ацетилхолиновый рецептор M5'],

            // Глутаматные рецепторы
            ['name' => 'NMDA', 'full_name' => 'NMDA-рецептор глутамата (N-метил-D-аспартатный рецептор)'],
            ['name' => 'AMPA', 'full_name' => 'AMPA-рецептор глутамата'],
            ['name' => 'Kainate', 'full_name' => 'Каинатный рецептор глутамата'],

            // ГАМК-рецепторы
            ['name' => 'GABA-A', 'full_name' => 'ГАМК-A-рецептор (рецептор γ-аминомасляной кислоты типа A)'],
            ['name' => 'GABA-B', 'full_name' => 'ГАМК-B-рецептор (рецептор γ-аминомасляной кислоты типа B)'],

            // Сигма-рецепторы
            ['name' => 'Sigma-1', 'full_name' => 'Сигма-1-рецептор'],
            ['name' => 'Sigma-2', 'full_name' => 'Сигма-2-рецептор (TMEM97)'],

            // Опиоидные рецепторы
            ['name' => 'Opioid-mu', 'full_name' => 'Мю-опиоидный рецептор (μ-opioid receptor, MOR)'],
            ['name' => 'Opioid-delta', 'full_name' => 'Дельта-опиоидный рецептор (δ-opioid receptor, DOR)'],
            ['name' => 'Opioid-kappa', 'full_name' => 'Каппа-опиоидный рецептор (κ-opioid receptor, KOR)'],

            // Пуринергические рецепторы
            ['name' => 'P2X', 'full_name' => 'P2X-пуринергический рецептор (ионотропный ATP-рецептор)'],
            ['name' => 'P2Y', 'full_name' => 'P2Y-пуринергический рецептор (метаботропный пуриновый рецептор)'],

            // Транспортёры
            ['name' => 'DAT', 'full_name' => 'Транспортёр дофамина (Dopamine Transporter, DAT, SLC6A3)'],
            ['name' => 'SERT', 'full_name' => 'Транспортёр серотонина (Serotonin Transporter, SERT, SLC6A4)'],
            ['name' => 'NET', 'full_name' => 'Транспортёр норадреналина (Norepinephrine Transporter, NET, SLC6A2)'],
            ['name' => 'GAT-1', 'full_name' => 'Транспортёр ГАМК типа 1 (GABA Transporter 1, GAT-1, SLC6A1)'],
            ['name' => 'GAT-2', 'full_name' => 'Транспортёр ГАМК типа 2 (GABA Transporter 2, GAT-2, SLC6A13)'],
            ['name' => 'GAT-3', 'full_name' => 'Транспортёр ГАМК типа 3 (GABA Transporter 3, GAT-3, SLC6A11)'],
            ['name' => 'GLT-1', 'full_name' => 'Глиальный транспортёр глутамата GLT-1 (EAAT2, SLC1A2)'],
            ['name' => 'EAAT1', 'full_name' => 'Транспортёр возбуждающих аминокислот типа 1 (EAAT1, SLC1A3)'],
            ['name' => 'EAAT2', 'full_name' => 'Транспортёр возбуждающих аминокислот типа 2 (EAAT2, SLC1A2)'],
        ];

        foreach ($receptors as $receptorData) {
            $receptor = new Receptor();
            $receptor->name = $receptorData['name'];
            $receptor->full_name = $receptorData['full_name'];
            $receptor->save();
        }
    }
}
