<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HospitalBuilding extends Model
{
    protected $table = 'hospital_buildings';

    protected $guarded = [];

    protected $casts = [
        'enabled' => 'boolean',
    ];

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(
            Clinic::class,
            'clinic_id'
        );
    }

    public function departments(): HasMany
    {
        return $this->hasMany(
            HospitalDepartment::class,
            'building_id'
        );
    }
}
