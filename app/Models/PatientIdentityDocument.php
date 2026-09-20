<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientIdentityDocument extends Model
{
    /*
    |--------------------------------------------------------------------------
    | Типы документов
    |--------------------------------------------------------------------------
    */

    public const TYPE_PASSPORT_RF = 'passport_rf';

    public const TYPE_BIRTH_CERTIFICATE = 'birth_certificate';

    public const TYPE_FOREIGN_PASSPORT = 'foreign_passport';

    public const TYPE_RESIDENCE_PERMIT = 'residence_permit';

    public const TYPE_TEMPORARY_IDENTITY = 'temporary_identity';

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

    public function patient(): BelongsTo
    {
        return $this->belongsTo(
            Patient::class,
            'patient_id'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public static function typeOptions(): array
    {
        return [
            self::TYPE_PASSPORT_RF =>
                'Паспорт гражданина РФ',

            self::TYPE_BIRTH_CERTIFICATE =>
                'Свидетельство о рождении',

            self::TYPE_FOREIGN_PASSPORT =>
                'Паспорт иностранного гражданина',

            self::TYPE_RESIDENCE_PERMIT =>
                'Вид на жительство',

            self::TYPE_TEMPORARY_IDENTITY =>
                'Временное удостоверение личности',

            self::TYPE_FOREIGN_IDENTITY =>
                'Иностранный документ, удостоверяющий личность',

            self::TYPE_OTHER =>
                'Иной документ',
        ];
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
