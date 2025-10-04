<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LabResearchTemplate extends Model
{
    protected $guarded = [];
    protected $casts = [
        'lab_parameters' => 'array',
    ];
}
