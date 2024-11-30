<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClinicPhoto extends Model
{
    protected $guarded = [];

    public function clinic() {
        return $this->belongsTo(Clinic::class);
    }
}
