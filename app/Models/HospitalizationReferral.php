<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HospitalizationReferral extends Model
{
    use HasFactory;

    public const TYPE_PLANNED = 'planned';
    public const TYPE_EMERGENCY = 'emergency';

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PREPARED = 'prepared';
    public const STATUS_SENT = 'sent';
    public const STATUS_ADMITTED = 'admitted';
    public const STATUS_CANCELLED = 'cancelled';

    public const SOURCE_MANUAL = 'manual';
    public const SOURCE_AI = 'ai';
    public const SOURCE_COMBINED = 'combined';

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'type',
        'status',
        'source_mode',
        'destination_organization',
        'destination_address',
        'department',
        'diagnosis_code',
        'diagnosis_title',
        'clinical_summary',
        'reason',
        'planned_at',
        'urgency_level',
        'transport_type',
        'details',
        'ai_analysis',
        'ai_analyzed_at',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'planned_at' => 'datetime',
            'ai_analyzed_at' => 'datetime',
            'sent_at' => 'datetime',
            'details' => 'array',
            'ai_analysis' => 'array',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_EMERGENCY => 'Экстренная госпитализация',
            default => 'Плановая госпитализация',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PREPARED => 'Подготовлено',
            self::STATUS_SENT => 'Направлено',
            self::STATUS_ADMITTED => 'Госпитализирован',
            self::STATUS_CANCELLED => 'Отменено',
            default => 'Черновик',
        };
    }

    public function getSourceModeLabelAttribute(): string
    {
        return match ($this->source_mode) {
            self::SOURCE_AI => 'Сформировано ИИ',
            self::SOURCE_COMBINED => 'ИИ + врач',
            default => 'Заполнено вручную',
        };
    }
}
