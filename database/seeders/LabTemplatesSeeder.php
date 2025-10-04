<?php

namespace Database\Seeders;

use App\Models\LabResearchTemplate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LabTemplatesSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $getIds = function (array $groups = [], array $extraIds = []): array {
            $base = DB::table('lab_parameters')
                ->when(!empty($groups), fn ($q) => $q->whereIn('group', $groups))
                ->pluck('id')
                ->map(fn ($v) => (int)$v)
                ->all();

            $ids = array_unique(array_merge($base, $extraIds));
            sort($ids);
            return $ids;
        };

        $soeId = DB::table('lab_parameters')->where('name', 'СОЭ')->value('id');
        $oakIds = $getIds(['cbc', 'cbc_indices', 'diff'], $soeId ? [(int)$soeId] : []);

        $oamIds = $getIds(['urinalysis']);

        $bioIds = $getIds(['metabolic', 'lipids', 'liver', 'renal', 'electrolytes']);

        $rows = [
            [
                'name'           => 'ОАК',
                'lab_parameters' => json_encode($oakIds, JSON_UNESCAPED_UNICODE),
                'doctor_id'      => 0,
                'sample_type'    => 'кровь',
                'created_at'     => $now,
                'updated_at'     => $now,
            ],
            [
                'name'           => 'ОАМ',
                'lab_parameters' => json_encode($oamIds, JSON_UNESCAPED_UNICODE),
                'sample_type'    => 'моча',
                'doctor_id'      => 0,
                'created_at'     => $now,
                'updated_at'     => $now,
            ],
            [
                'name'           => 'Биохимия',
                'lab_parameters' => json_encode($bioIds, JSON_UNESCAPED_UNICODE),
                'doctor_id'      => 0,
                'sample_type'    => 'кровь',
                'created_at'     => $now,
                'updated_at'     => $now,
            ],
        ];

        foreach ($rows as $row) {
            LabResearchTemplate::updateOrInsert(
                [
                    'name' => $row['name'],
                    'doctor_id' => 0
                ],
                [
                    'lab_parameters' => $row['lab_parameters'],
                    'sample_type' => $row['sample_type']
                ]
            );
        }
    }
}
