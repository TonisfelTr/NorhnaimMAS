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
                    $newLabParameter->notes = $lab['notes'];
                    $newLabParameter->save();

                    if (isset($lab['ref_ranges'])) {
                        foreach ($lab['ref_ranges'] as $range) {
                            $insertResult = DB::statement('insert into "lab_reference_ranges" (parameter_id, sex, age_min_y, age_max_y, min, max) values ( ?, ?, ?, ?, ?, ?)',
                                [
                                    $newLabParameter->id,
                                    $range['sex'],
                                    $range['age_min_y'],
                                    $range['age_max_y'],
                                    $range['min'],
                                    $range['max'],
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
