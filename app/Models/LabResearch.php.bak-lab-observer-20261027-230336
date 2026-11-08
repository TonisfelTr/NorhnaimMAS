<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class LabResearch extends Model
{
    protected $guarded = [];
    protected $table = 'lab_researches';
    protected $casts = [
        'planned_at' => 'datetime',
        'parameters' => 'array',
        'updated_at' => 'datetime:d.m.Y H:i',
        'research_date' => 'datetime'
    ];

    public function getResearchDateAttribute()
    {
        return Carbon::parse($this->attributes['research_date']);
    }

    public function scopeLastBlood(Builder $query): void
    {
        $query->where('sample_type', 'кровь')
            ->whereIn('created_at', function($query) {
                $query->select(DB::raw('MAX(created_at)'))
                        ->from('lab_researches')
                        ->where('sample_type', 'кровь')
                        ->groupBy('patient_id');
        });
    }

    public function scopeLastUrinal(Builder $query): void
    {
        $query->where('sample_type', 'моча')
            ->whereIn('created_at', function($query) {
                $query->select(DB::raw('MAX(created_at)'))
                        ->from('lab_researches')
                        ->where('sample_type', 'моча')
                        ->groupBy('patient_id');
        });
    }

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
