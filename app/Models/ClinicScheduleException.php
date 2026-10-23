<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClinicScheduleException extends Model
{
    protected $fillable = [
        'clinic_id',
        'type',
        'title',
        'date_from',
        'date_to',
        'all_day',
        'time_from',
        'time_to',
        'mode',
        'scope',
        'target_ids',
        'comment',
    ];

    protected $casts = [
        'clinic_id' => 'integer',
        'date_from' => 'date',
        'date_to' => 'date',
        'all_day' => 'boolean',
        'target_ids' => 'array',
    ];

    public function clinic()
    {
        return $this->belongsTo(
            Clinic::class
        );
    }
}
