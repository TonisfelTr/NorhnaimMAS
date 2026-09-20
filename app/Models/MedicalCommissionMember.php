<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicalCommissionMember extends Model
{
    public const ROLE_CHAIR = 'chair';
    public const ROLE_DEPUTY = 'deputy';
    public const ROLE_MEMBER = 'member';

    public const VOTE_FOR = 'for';
    public const VOTE_AGAINST = 'against';
    public const VOTE_ABSTAIN = 'abstain';

    protected $guarded = [];

    protected $casts = [
        'present' => 'boolean',
    ];

    public function commission(): BelongsTo
    {
        return $this->belongsTo(MedicalCommission::class, 'medical_commission_id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            self::ROLE_CHAIR => 'Председатель',
            self::ROLE_DEPUTY => 'Заместитель председателя',
            default => 'Член комиссии',
        };
    }

    public function getVoteLabelAttribute(): string
    {
        return match ($this->vote) {
            self::VOTE_FOR => 'За',
            self::VOTE_AGAINST => 'Против',
            self::VOTE_ABSTAIN => 'Воздержался',
            default => 'Не голосовал',
        };
    }
}
