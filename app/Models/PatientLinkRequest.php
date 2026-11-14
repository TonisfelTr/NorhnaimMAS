<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientLinkRequest extends Model
{
    public const STATUS_PENDING_EMAIL = 'pending_email';
    public const STATUS_PENDING_REVIEW = 'pending_review';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    protected $guarded = [];

    protected $casts = [
        'payload' => 'encrypted:array',
        'verification_email' => 'encrypted',
        'verification_expires_at' => 'datetime',
        'verified_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }

    public function isPending(): bool
    {
        return in_array($this->status, [
            self::STATUS_PENDING_EMAIL,
            self::STATUS_PENDING_REVIEW,
        ], true);
    }
}
