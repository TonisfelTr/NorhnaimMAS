<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClinicBuildingWorkSchedule extends Model
{
    protected $fillable = [
        'clinic_id',
        'building_id',
        'day_number',
        'enabled',
        'start_time',
        'end_time',
    ];

    protected $casts = [
        'clinic_id' => 'integer',
        'building_id' => 'integer',
        'day_number' => 'integer',
        'enabled' => 'boolean',
    ];
}
