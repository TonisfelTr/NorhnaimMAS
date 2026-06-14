<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TestSession extends Model
{
    protected $fillable = [
        'test_id',
        'clinician_id',
        'patient_id',
        'context',
        'status',
        'in_progress',
        'started_at',
        'completed_at',
        'locked_at',
        'locked_by',
    ];

    protected $casts = [
        'context'        => 'array',
        'result_payload' => 'array',
        'started_at'     => 'datetime',
        'completed_at'   => 'datetime',
        'locked_at'      => 'datetime',
    ];

    public function test(): BelongsTo
    {
        return $this->belongsTo(Test::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(TestResult::class, 'session_id');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(TestAnswer::class, 'session_id');
    }

    public function openResponses(): HasMany
    {
        return $this->hasMany(TestOpenResponse::class, 'session_id');
    }

    public function assignment()
    {
        return $this->hasOne(TestAssignment::class, 'session_id');
    }

    public function getIsLockedAttribute(): bool
    {
        return $this->in_progress && is_null($this->completed_at);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }
}
