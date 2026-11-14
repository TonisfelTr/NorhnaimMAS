<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClinicalRecordOrigin extends Model
{
    protected $guarded = [];

    protected $casts = [
        'captured_at' => 'datetime',
    ];
}
