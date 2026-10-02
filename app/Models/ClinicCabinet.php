<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ClinicCabinet extends Model
{
    public const TYPE_DOCTOR = 'doctor';
    public const TYPE_PROCEDURE = 'procedure';
    public const TYPE_FUNCTIONAL = 'functional';

    public const FUNCTIONAL_METHODS = [
        'eeg' => [
            'label' => 'Электроэнцефалография (ЭЭГ)',
            'short' => 'ЭЭГ',
        ],
        'ecg' => [
            'label' => 'Электрокардиография (ЭКГ)',
            'short' => 'ЭКГ',
        ],
        'holter_ecg' => [
            'label' => 'Холтеровское мониторирование ЭКГ',
            'short' => 'Холтер ЭКГ',
        ],
        'evoked_potentials' => [
            'label' => 'Исследование вызванных потенциалов',
            'short' => 'Вызванные потенциалы',
        ],
    ];

    public $guarded = [];

    protected $casts = [
        'functional_methods' => 'array',
    ];

    public function building(): BelongsTo
    {
        return $this->belongsTo(
            ClinicBuilding::class,
            'building_id',
            'id'
        );
    }

    public function mainDoctor(): BelongsTo
    {
        return $this->belongsTo(
            Doctor::class,
            'doctor_id',
            'id'
        );
    }

    public function mainNurse(): BelongsTo
    {
        return $this->belongsTo(
            Nurse::class,
            'nurse_id',
            'id'
        );
    }

    public function alternativeDoctor(): BelongsTo
    {
        return $this->belongsTo(
            Doctor::class,
            'replace_doctor_id',
            'id'
        );
    }

    public function alternativeNurse(): BelongsTo
    {
        return $this->belongsTo(
            Doctor::class,
            'replace_nurse_id',
            'id'
        );
    }

    public function procedureSchedules(): HasMany
    {
        return $this->hasMany(
            ClinicCabinetSchedule::class,
            'clinic_cabinet_id',
            'id'
        )->orderBy('day_number');
    }

    public function medcards(): HasMany
    {
        return $this->hasMany(
            Patient::class,
            'cabinet_id',
            'id'
        );
    }

    public function patients(): HasMany
    {
        return $this->hasMany(
            Patient::class,
            'cabinet_id',
            'id'
        );
    }

    public function doctor(): HasOne
    {
        return $this->hasOne(
            Doctor::class,
            'id',
            'doctor_id'
        );
    }

    public function getCabinetTypeLabelAttribute(): string
    {
        return [
            self::TYPE_DOCTOR => 'Врачебный кабинет',
            self::TYPE_PROCEDURE => 'Процедурный кабинет',
            self::TYPE_FUNCTIONAL => 'Функциональная диагностика',
        ][$this->cabinet_type] ?? 'Кабинет';
    }

    public function getCabinetTypeIconAttribute(): string
    {
        return [
            self::TYPE_DOCTOR => 'bi-person-badge',
            self::TYPE_PROCEDURE => 'bi-bandaid',
            self::TYPE_FUNCTIONAL => 'bi-activity',
        ][$this->cabinet_type] ?? 'bi-door-open';
    }

    public function getCabinetCardIconAttribute(): string
    {
        return [
            self::TYPE_DOCTOR => 'bi-door-open',
            self::TYPE_PROCEDURE => 'bi-bandaid-fill',
            self::TYPE_FUNCTIONAL => 'bi-heart-pulse',
        ][$this->cabinet_type] ?? 'bi-door-open';
    }

    public function getFunctionalMethodAttribute(): ?string
    {
        $methods = $this->functional_methods ?? [];

        if (!is_array($methods) || empty($methods)) {
            return null;
        }

        return (string) reset($methods);
    }

    public function getFunctionalMethodLabelAttribute(): ?string
    {
        $code = $this->functional_method;

        if (!$code) {
            return null;
        }

        return self::FUNCTIONAL_METHODS[$code]['label']
            ?? $code;
    }

    public function getFunctionalMethodShortLabelAttribute(): ?string
    {
        $code = $this->functional_method;

        if (!$code) {
            return null;
        }

        return self::FUNCTIONAL_METHODS[$code]['short']
            ?? $code;
    }

    public function issuedPrescriptions(): int
    {
        if (
            $this->cabinet_type !== self::TYPE_DOCTOR
            || !$this->doctor_id
        ) {
            return 0;
        }

        return MedicalPrescription::where(
            'is_strict',
            true
        )
            ->where('doctor_id', $this->doctor_id)
            ->whereDate('issued_at', today())
            ->count();
    }

    public function remainingPrescriptions(): int
    {
        return max(
            0,
            $this->count_148
            - $this->issuedPrescriptions()
        );
    }
}
