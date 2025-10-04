<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LabResearch extends Model
{
    protected $guarded = [];
    protected $table = 'lab_researches';
    protected $casts = [
        'planned_at' => 'datetime',
        'parameters' => 'array',
        'updated_at' => 'datetime:d.m.Y H:i'
    ];


    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(LabResearchResult::class);
    }
}
