<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PatientRepresentative extends Model
{
    /*
    |--------------------------------------------------------------------------
    | Типы представителей
    |--------------------------------------------------------------------------
    */

    public const TYPE_MOTHER = 'mother';

    public const TYPE_FATHER = 'father';

    public const TYPE_ADOPTIVE_PARENT = 'adoptive_parent';

    public const TYPE_GUARDIAN = 'guardian';

    public const TYPE_TRUSTEE = 'trustee';

    public const TYPE_AUTHORIZED = 'authorized';

    public const TYPE_OTHER = 'other';


    /*
    |--------------------------------------------------------------------------
    | Model
    |--------------------------------------------------------------------------
    */

    protected $guarded = [];

    protected $casts = [
        'birth_at' => 'date',
        'is_primary' => 'boolean',
        'is_active' => 'boolean',
    ];


    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function patient(): BelongsTo
    {
        return $this->belongsTo(
            Patient::class,
            'patient_id'
        );
    }

    public function documents(): HasMany
    {
        return $this->hasMany(
            PatientRepresentativeDocument::class,
            'representative_id'
        );
    }

    /**
     * Основной действующий документ,
     * удостоверяющий личность представителя.
     */
    public function identityDocument(): HasOne
    {
        return $this
            ->hasOne(
                PatientRepresentativeDocument::class,
                'representative_id'
            )
            ->where(
                'document_purpose',
                PatientRepresentativeDocument::PURPOSE_IDENTITY
            )
            ->where('is_primary', true)
            ->where('is_active', true);
    }

    /**
     * Основной действующий документ,
     * подтверждающий полномочия представителя.
     */
    public function authorityDocument(): HasOne
    {
        return $this
            ->hasOne(
                PatientRepresentativeDocument::class,
                'representative_id'
            )
            ->where(
                'document_purpose',
                PatientRepresentativeDocument::PURPOSE_AUTHORITY
            )
            ->where('is_primary', true)
            ->where('is_active', true);
    }


    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public static function typeOptions(): array
    {
        return [
            self::TYPE_MOTHER =>
                'Мать',

            self::TYPE_FATHER =>
                'Отец',

            self::TYPE_ADOPTIVE_PARENT =>
                'Усыновитель',

            self::TYPE_GUARDIAN =>
                'Опекун',

            self::TYPE_TRUSTEE =>
                'Попечитель',

            self::TYPE_AUTHORIZED =>
                'Уполномоченный представитель',

            self::TYPE_OTHER =>
                'Иной представитель',
        ];
    }

    public function getTypeLabelAttribute(): string
    {
        return self::typeOptions()[
        $this->representative_type
        ] ?? 'Представитель';
    }

    public function getFullNameAttribute(): string
    {
        return collect([
            $this->surname,
            $this->name,
            $this->patronym,
        ])
            ->filter()
            ->implode(' ');
    }
}
