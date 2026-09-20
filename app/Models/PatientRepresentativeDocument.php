<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientRepresentativeDocument extends Model
{
    /*
    |--------------------------------------------------------------------------
    | Назначение документа
    |--------------------------------------------------------------------------
    */

    /**
     * Документ удостоверяет личность представителя.
     */
    public const PURPOSE_IDENTITY = 'identity';

    /**
     * Документ подтверждает полномочия представителя.
     */
    public const PURPOSE_AUTHORITY = 'authority';


    /*
    |--------------------------------------------------------------------------
    | Типы документов
    |--------------------------------------------------------------------------
    */

    public const TYPE_PASSPORT_RF = 'passport_rf';

    public const TYPE_BIRTH_CERTIFICATE = 'birth_certificate';

    public const TYPE_GUARDIANSHIP_ORDER = 'guardianship_order';

    public const TYPE_POWER_OF_ATTORNEY = 'power_of_attorney';

    public const TYPE_FOREIGN_IDENTITY = 'foreign_identity';

    public const TYPE_OTHER = 'other';


    /*
    |--------------------------------------------------------------------------
    | Model
    |--------------------------------------------------------------------------
    */

    protected $guarded = [];

    protected $casts = [
        'issued_at' => 'date',
        'is_primary' => 'boolean',
        'is_active' => 'boolean',
    ];


    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function representative(): BelongsTo
    {
        return $this->belongsTo(
            PatientRepresentative::class,
            'representative_id'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public static function purposeOptions(): array
    {
        return [
            self::PURPOSE_IDENTITY =>
                'Удостоверяет личность',

            self::PURPOSE_AUTHORITY =>
                'Подтверждает полномочия',
        ];
    }

    public static function typeOptions(): array
    {
        return [
            self::TYPE_PASSPORT_RF =>
                'Паспорт гражданина РФ',

            self::TYPE_BIRTH_CERTIFICATE =>
                'Свидетельство о рождении',

            self::TYPE_GUARDIANSHIP_ORDER =>
                'Документ органа опеки',

            self::TYPE_POWER_OF_ATTORNEY =>
                'Доверенность',

            self::TYPE_FOREIGN_IDENTITY =>
                'Иностранный документ, удостоверяющий личность',

            self::TYPE_OTHER =>
                'Иной документ',
        ];
    }

    public function getPurposeLabelAttribute(): string
    {
        return self::purposeOptions()[
        $this->document_purpose
        ] ?? 'Документ';
    }

    public function getTypeLabelAttribute(): string
    {
        return self::typeOptions()[
        $this->document_type
        ] ?? (
        $this->document_name
            ?: 'Документ'
        );
    }
}
