<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class RegistryRecord extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function patient(): BelongsTo {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo {
        return $this->belongsTo(Doctor::class);
    }

    public function getForDatetimeAttribute(): string {
        return Carbon::from($this->for_datetime)->format("d.m.Y H:i");
    }
}
