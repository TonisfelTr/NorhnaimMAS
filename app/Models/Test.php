<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Test extends Model
{
    protected $guarded = [];

    public function sections(): HasMany
    {
        return $this->hasMany(TestSection::class);
    }

    public function ownerDoctor()
    {
        return $this->belongsTo(\App\Models\Doctor::class, 'owner_doctor_id');
    }
}
