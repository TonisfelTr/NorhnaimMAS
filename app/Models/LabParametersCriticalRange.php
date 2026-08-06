<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LabParametersCriticalRange extends Model
{
    protected $guarded = [];

    protected $casts = [
        'critical_low' => 'float',
        'critical_high' => 'float',
        'age_min_y' => 'integer',
        'age_max_y' => 'integer',
    ];

    public function parameter(): BelongsTo
    {
        return $this->belongsTo(LabParameter::class,'parameter_id');
    }
}
