<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClinicBuilding extends Model
{
    protected $fillable = [
        'clinic_id',
        'number',
        'name',
        'address',
        'enabled',
    ];

    protected $casts = [
        'enabled' => 'boolean',
    ];

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(
            Clinic::class,
            'clinic_id',
            'id'
        );
    }

    public function cabinets(): HasMany
    {
        return $this->hasMany(
            ClinicCabinet::class,
            'building_id',
            'id'
        );
    }
}
