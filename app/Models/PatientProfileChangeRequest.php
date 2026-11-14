<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientProfileChangeRequest extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    public const TARGET_DOCTOR = 'doctor';
    public const TARGET_ADMINISTRATION = 'administration';

    protected $guarded = [];

    protected $casts = [
        'payload' => 'encrypted:array',
        'reason' => 'encrypted',
        'review_note' => 'encrypted',
        'changed_fields' => 'array',
        'reviewed_at' => 'datetime',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }

    public function reviewClinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class, 'review_clinic_id');
    }

    public function reviewDoctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'review_doctor_id');
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public static function targetOptions(): array
    {
        return [
            self::TARGET_DOCTOR => 'Лечащий врач',
            self::TARGET_ADMINISTRATION => 'Администрация клиники',
        ];
    }
}
