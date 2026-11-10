<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

final class DoctorCredential extends Model implements HasMedia
{
    use InteractsWithMedia;

    public const KIND_DIPLOMA = 'diploma';
    public const KIND_ACCREDITATION = 'accreditation';
    public const KIND_ATTESTATION = 'attestation';
    public const KIND_ADVANCED_TRAINING = 'advanced_training';
    public const KIND_RETRAINING = 'retraining';
    public const KIND_CERTIFICATE = 'certificate';
    public const KIND_OTHER = 'other';

    public const STATUS_PENDING = 'pending';
    public const STATUS_VERIFIED = 'verified';
    public const STATUS_NOT_FOUND = 'not_found';
    public const STATUS_MISMATCH = 'mismatch';
    public const STATUS_MANUAL_REVIEW = 'manual_review';
    public const STATUS_UNAVAILABLE = 'unavailable';

    protected $guarded = [];

    protected $casts = [
        'issued_at' => 'date',
        'valid_until' => 'date',
        'verification_requested_at' => 'datetime',
        'verified_at' => 'datetime',
        'verification_payload' => 'array',
    ];

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('document')
            ->singleFile()
            ->useDisk('local');
    }

    public static function kindOptions(): array
    {
        return [
            self::KIND_DIPLOMA =>
                'Диплом о профессиональном образовании',

            self::KIND_ACCREDITATION =>
                'Свидетельство / выписка об аккредитации',

            self::KIND_ATTESTATION =>
                'Аттестат / квалификационная категория',

            self::KIND_ADVANCED_TRAINING =>
                'Удостоверение о повышении квалификации',

            self::KIND_RETRAINING =>
                'Диплом о профессиональной переподготовке',

            self::KIND_CERTIFICATE =>
                'Сертификат',

            self::KIND_OTHER =>
                'Прочий документ',
        ];
    }

    public function kindLabel(): string
    {
        return self::kindOptions()[$this->kind] ?? 'Документ';
    }

    public function verificationLabel(): string
    {
        return match ($this->verification_status) {
            self::STATUS_VERIFIED =>
                'Подтверждено',

            self::STATUS_NOT_FOUND =>
                'Не найдено',

            self::STATUS_MISMATCH =>
                'Есть расхождения',

            self::STATUS_MANUAL_REVIEW =>
                'Нужна проверка',

            self::STATUS_UNAVAILABLE =>
                'Источник не подключён',

            default =>
                'Ожидает проверки',
        };
    }

    public function verificationBadgeClass(): string
    {
        return match ($this->verification_status) {
            self::STATUS_VERIFIED => 'success',

            self::STATUS_NOT_FOUND,
            self::STATUS_MISMATCH => 'danger',

            self::STATUS_MANUAL_REVIEW => 'warning',
            self::STATUS_UNAVAILABLE => 'secondary',

            default => 'info',
        };
    }

    public function verificationSourceLabel(): string
    {
        return match ($this->verification_source) {
            'frdo' => 'ФИС ФРДО',
            'frmr' => 'ФРМР ЕГИСЗ',
            'n3' => 'N3.Health / Нетрика',
            'manual' => 'Ручная проверка',
            default => 'Не подключён',
        };
    }

    public function isExpired(): bool
    {
        return $this->valid_until !== null
            && $this->valid_until->isPast();
    }
}
