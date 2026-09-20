<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class MilitaryCommissionDocument extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PREPARED = 'prepared';

    public const TYPE_MEDICAL_INFORMATION = 'medical_information';
    public const TYPE_VVK_CONCLUSION = 'vvk_conclusion';
    public const TYPE_REQUEST_RESPONSE = 'request_response';

    protected $guarded = [];

    protected $casts = [
        'document_date' => 'date',
        'request_date' => 'date',
        'prepared_at' => 'datetime',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'created_by_doctor_id');
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_DRAFT => 'Черновик',
            self::STATUS_PREPARED => 'Документ оформлен',
        ];
    }

    public static function documentTypeOptions(): array
    {
        return [
            self::TYPE_MEDICAL_INFORMATION => 'Медицинские сведения',
            self::TYPE_VVK_CONCLUSION => 'Заключение врача для ВВК',
            self::TYPE_REQUEST_RESPONSE => 'Ответ на запрос военкомата',
        ];
    }

    public function statusLabel(): string
    {
        return self::statusOptions()[$this->status] ?? (string) $this->status;
    }

    public function documentTypeLabel(): string
    {
        return self::documentTypeOptions()[$this->document_type] ?? (string) $this->document_type;
    }

    public function diagnosisLabel(): string
    {
        $code = trim((string) $this->diagnosis_code);
        $title = trim((string) $this->diagnosis_title);

        if ($code === '' && $title === '') {
            return 'Диагноз не указан';
        }

        return $code . ($code !== '' && $title !== '' ? ' — ' : '') . $title;
    }

    public function patientFullName(): string
    {
        $patient = $this->patient;

        return collect([
            $patient?->surname,
            $patient?->name,
            $patient?->patronym,
        ])->filter()->implode(' ') ?: 'Пациент не указан';
    }

    public function patientBirthDateLabel(): string
    {
        $birthDate = $this->patient?->getRawOriginal('birth_at');

        if (!$birthDate) {
            return 'Не указана';
        }

        try {
            return Carbon::parse($birthDate)->format('d.m.Y');
        } catch (\Throwable) {
            return (string) $birthDate;
        }
    }
}
