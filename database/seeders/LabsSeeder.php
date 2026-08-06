<?php

namespace Database\Seeders;

use App\Models\LabParameter;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LabsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        LabParameter::truncate();
        DB::table('lab_reference_ranges')->truncate();
        DB::table('lab_parameters_critical_ranges')->truncate();

        $labs = json_decode(file_get_contents(storage_path('app/private/labs.json')), true)['parameters'];

        DB::transaction(function () use ($labs) {
            foreach ($labs as $lab) {
                try {
                    $newLabParameter = new LabParameter();
                    $newLabParameter->name = $lab['name'];
                    $newLabParameter->unit = $lab['unit'];
                    $newLabParameter->data_type = $lab['data_type'];
                    $newLabParameter->sample_type = $lab['sample_type'];
                    $newLabParameter->group = $lab['group'];
                    $newLabParameter->normal_values = json_encode($lab['normal_values'] ?? null);
                    $newLabParameter->allowed_values = json_encode($lab['allowed_values'] ?? null);
                    $newLabParameter->notes = $lab['notes'] ?? null;
                    $newLabParameter->save();

                    if (isset($lab['ref_ranges'])) {
                        foreach ($lab['ref_ranges'] as $range) {
                            $insertResult = DB::statement('insert into "lab_reference_ranges" (parameter_id, sex, age_min_y, age_max_y, min, max, created_at, updated_at) values ( ?, ?, ?, ?, ?, ?, ?, ?)',
                                [
                                    $newLabParameter->id,
                                    $range['sex'],
                                    $range['age_min_y'],
                                    $range['age_max_y'],
                                    $range['min'],
                                    $range['max'],
                                    now(),
                                    now()
                                ]);

                            if (!$insertResult) {
                                DB::rollback();
                                break;
                            }

                        }
                    }

                    if (isset($lab['critical_ranges'])) {
                        foreach ($lab['critical_ranges'] as $range) {
                            $insertResult = DB::statement('insert into "lab_parameters_critical_ranges" (parameter_id, sex, age_min_y, age_max_y, critical_low, critical_high, created_at, updated_at) values ( ?, ?, ?, ?, ?, ?, ?, ?)',
                            [
                                $newLabParameter->id,
                                $range['sex'],
                                $range['age_min_y'],
                                $range['age_max_y'],
                                $range['critical_low'],
                                $range['critical_high'],
                                now(),
                                now()
                            ]);

                            if (!$insertResult) {
                                DB::rollback();
                                break;
                            }
                        }
                    }
                } catch (\Exception $e) {
                    DB::rollBack();
                    throw $e;
                }
            }

            DB::commit();
        });
    }
}
