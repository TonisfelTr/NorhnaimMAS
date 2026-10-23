<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class HospitalBed extends Model
{
    protected $guarded = [];

    protected $casts = [
        'enabled' => 'boolean',
        'x' => 'integer',
        'y' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
        'rotation' => 'float',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(HospitalRoom::class, 'room_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(HospitalBedAssignment::class, 'bed_id');
    }

    public function currentAssignment(): HasOne
    {
        return $this->hasOne(HospitalBedAssignment::class, 'bed_id')
            ->whereNull('released_at');
    }
}
