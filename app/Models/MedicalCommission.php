<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MedicalCommission extends Model
{
    public const TYPE_PLANNED = 'planned';
    public const TYPE_UNSCHEDULED = 'unscheduled';

    public const STATUS_DRAFT = 'draft';
    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_HELD = 'held';
    public const STATUS_PROTOCOL_READY = 'protocol_ready';
    public const STATUS_CANCELLED = 'cancelled';

    protected $guarded = [];

    protected $casts = [
        'meeting_at' => 'datetime',
        'protocol_ready_at' => 'datetime',
        'medical_record_entered_at' => 'datetime',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'created_by_doctor_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(MedicalCommissionMember::class)
            ->orderByRaw("CASE role WHEN 'chair' THEN 1 WHEN 'deputy' THEN 2 ELSE 3 END")
            ->orderBy('id');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'Черновик',
            self::STATUS_SCHEDULED => 'Назначено',
            self::STATUS_HELD => 'Заседание проведено',
            self::STATUS_PROTOCOL_READY => 'Протокол оформлен',
            self::STATUS_CANCELLED => 'Отменено',
            default => 'Неизвестно',
        };
    }

    public function getMeetingTypeLabelAttribute(): string
    {
        return match ($this->meeting_type) {
            self::TYPE_UNSCHEDULED => 'Внеплановое',
            default => 'Плановое',
        };
    }

    public function getCategoryLabelAttribute(): string
    {
        return self::categoryOptions()[$this->category] ?? 'Иной вопрос';
    }

    public static function categoryOptions(): array
    {
        return [
            'complex_case' => 'Сложный / конфликтный клинический случай',
            'treatment' => 'Диагностика, лечение или медицинская реабилитация',
            'quality' => 'Оценка качества и обоснованности лечебно-диагностических мероприятий',
            'medication' => 'Назначение / коррекция лекарственной терапии',
            'sick_leave' => 'Временная нетрудоспособность',
            'mse' => 'Направление на МСЭ',
            'psych_exam' => 'Обязательное психиатрическое освидетельствование',
            'professional_fitness' => 'Профессиональная пригодность',
            'other' => 'Иной вопрос компетенции ВК',
        ];
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_DRAFT => 'Черновик',
            self::STATUS_SCHEDULED => 'Назначено',
            self::STATUS_HELD => 'Заседание проведено',
            self::STATUS_PROTOCOL_READY => 'Протокол оформлен',
            self::STATUS_CANCELLED => 'Отменено',
        ];
    }

    public function totalMembersCount(): int
    {
        return $this->members->count();
    }

    public function presentMembersCount(): int
    {
        return $this->members->where('present', true)->count();
    }

    public function quorumRequiredCount(): int
    {
        $total = $this->totalMembersCount();

        return $total > 0 ? (int) ceil(($total * 2) / 3) : 0;
    }

    public function hasQuorum(): bool
    {
        $required = $this->quorumRequiredCount();

        return $required > 0 && $this->presentMembersCount() >= $required;
    }

    public function votesForCount(): int
    {
        return $this->members
            ->where('present', true)
            ->where('vote', MedicalCommissionMember::VOTE_FOR)
            ->count();
    }

    public function decisionRequiredVotes(): int
    {
        $present = $this->presentMembersCount();

        return $present > 0 ? (int) ceil(($present * 2) / 3) : 0;
    }

    public function isDecisionAdopted(): bool
    {
        if (!$this->hasQuorum()) {
            return false;
        }

        $required = $this->decisionRequiredVotes();

        return $required > 0 && $this->votesForCount() >= $required;
    }
}
