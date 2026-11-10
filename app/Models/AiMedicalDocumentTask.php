<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

final class AiMedicalDocumentTask extends Model
{
    public const STATUS_QUEUED = 'queued';
    public const STATUS_RUNNING = 'running';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_FAILED = 'failed';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'patient_id',
        'user_id',
        'mode',
        'status',
        'progress',
        'request_payload',
        'result_payload',
        'error_message',
        'cancel_requested_at',
        'started_at',
        'finished_at',
        'anamneses_saved_at',
    ];

    protected $casts = [
        'request_payload' => 'array',
        'result_payload' => 'array',
        'cancel_requested_at' => 'datetime',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'anamneses_saved_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(static function (self $task): void {
            if (!$task->getKey()) {
                $task->setAttribute('id', (string) Str::uuid());
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'id';
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cancellationRequested(): bool
    {
        return $this->status === self::STATUS_CANCELLED
            || $this->cancel_requested_at !== null;
    }
}
