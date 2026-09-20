<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class MseReferral extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PREPARED = 'prepared';

    public const REFERRAL_INITIAL = 'initial';
    public const REFERRAL_REPEATED = 'repeated';

    public const EXAMINATION_IN_PERSON = 'in_person';
    public const EXAMINATION_WITHOUT_PRESENCE = 'without_presence';

    public const NOTIFICATION_PHONE_SMS = 'phone_sms';
    public const NOTIFICATION_REGISTERED_MAIL = 'registered_mail';
    public const NOTIFICATION_GOSUSLUGI = 'gosuslugi';

    /**
     * Скан подписанного разрешения хранится только в приватном local-диске.
     * Доступ к файлу выдаётся через авторизованный маршрут контроллера.
     */
    public const CONSENT_DOCUMENT_DISK = 'local';

    protected $guarded = [];

    protected $casts = [
        'document_date' => 'date',
        'protocol_date' => 'date',
        'consent_date' => 'date',
        'requires_home_examination' => 'boolean',
        'palliative_care' => 'boolean',
        'primary_prosthetics' => 'boolean',
        'purposes' => 'array',
        'patient_snapshot' => 'array',
        'details' => 'array',
        'consent_document_size' => 'integer',
        'consent_document_uploaded_at' => 'datetime',
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

    public function consentDocumentUploader(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'consent_document_uploaded_by_doctor_id');
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_DRAFT => 'Черновик',
            self::STATUS_PREPARED => 'Направление оформлено',
        ];
    }

    public static function referralKindOptions(): array
    {
        return [
            self::REFERRAL_INITIAL => 'Первично',
            self::REFERRAL_REPEATED => 'Повторно',
        ];
    }

    public static function examinationFormatOptions(): array
    {
        return [
            self::EXAMINATION_IN_PERSON => 'С личным присутствием (очно)',
            self::EXAMINATION_WITHOUT_PRESENCE => 'Без личного присутствия (заочно)',
        ];
    }

    public static function notificationMethodOptions(): array
    {
        return [
            self::NOTIFICATION_PHONE_SMS => 'Телефонная связь / СМС',
            self::NOTIFICATION_REGISTERED_MAIL => 'Заказное почтовое отправление',
            self::NOTIFICATION_GOSUSLUGI => 'ЕПГУ «Госуслуги»',
        ];
    }

    public static function purposeOptions(): array
    {
        return [
            'disability_group' => '5.1. Установление группы инвалидности',
            'disabled_child' => '5.2. Установление категории «ребёнок-инвалид»',
            'disability_cause' => '5.3. Установление причины инвалидности',
            'disability_onset_time' => '5.4. Установление времени наступления инвалидности',
            'disability_term' => '5.5. Установление срока инвалидности',
            'professional_capacity_loss' => '5.6. Определение степени утраты профессиональной трудоспособности',
            'service_capacity_loss' => '5.7. Определение стойкой утраты трудоспособности сотрудника силовых органов',
            'military_relative_care' => '5.8. Определение нуждаемости родственника военнослужащего в постоянном постороннем уходе',
            'civil_service_relative_care' => '5.9. Определение нуждаемости родственника государственного служащего в постоянном постороннем уходе',
            'ipra' => '5.10. Разработка или корректировка ИПРА',
            'occupational_rehabilitation_program' => '5.11. Разработка программы реабилитации пострадавшего на производстве',
        ];
    }

    public static function sexOptions(): array
    {
        return [
            'male' => 'Мужской',
            'female' => 'Женский',
        ];
    }

    public static function citizenshipOptions(): array
    {
        return [
            'russian' => 'Гражданин Российской Федерации',
            'foreign' => 'Гражданин иностранного государства',
            'stateless' => 'Лицо без гражданства',
        ];
    }

    public static function militaryDutyOptions(): array
    {
        return [
            'registered' => 'Состоит на воинском учёте',
            'not_registered_but_required' => 'Не состоит, но обязан состоять на воинском учёте',
            'entering_registration' => 'Поступает на воинский учёт',
            'not_registered' => 'Не состоит на воинском учёте',
        ];
    }

    public static function locationTypeOptions(): array
    {
        return [
            'hospital' => 'Медицинская организация (стационар)',
            'social_service' => 'Стационарная организация социального обслуживания',
            'correctional' => 'Исправительное учреждение',
            'other_organization' => 'Иная организация',
            'residence' => 'По месту жительства / пребывания',
        ];
    }

    public static function disabilityGroupOptions(): array
    {
        return [
            'first' => 'Первая группа',
            'second' => 'Вторая группа',
            'third' => 'Третья группа',
            'disabled_child' => 'Категория «ребёнок-инвалид»',
        ];
    }

    public static function disabilityPeriodOptions(): array
    {
        return [
            'one_year' => 'Один год',
            'two_years' => 'Два года',
            'three_years' => 'Три года',
            'four_or_more_years' => 'Четыре и более лет',
        ];
    }

    public static function disabilityCauseOptions(): array
    {
        return [
            'general_disease' => 'Общее заболевание',
            'work_injury' => 'Трудовое увечье',
            'occupational_disease' => 'Профессиональное заболевание',
            'childhood' => 'Инвалидность с детства',
            'childhood_due_to_injury' => 'Инвалидность с детства вследствие ранения / контузии / увечья',
            'military_injury' => 'Военная травма',
            'military_service_disease' => 'Заболевание получено в период военной службы',
            'radiation_disease' => 'Заболевание связано с радиационной катастрофой',
            'other' => 'Иная причина',
        ];
    }

    public static function prognosisOptions(): array
    {
        return [
            'favorable' => 'Благоприятный',
            'relatively_favorable' => 'Относительно благоприятный',
            'uncertain' => 'Сомнительный (неопределённый)',
            'unfavorable' => 'Неблагоприятный',
        ];
    }

    public static function rehabilitationPotentialOptions(): array
    {
        return [
            'high' => 'Высокий',
            'satisfactory' => 'Удовлетворительный',
            'low' => 'Низкий',
            'absent' => 'Отсутствует',
        ];
    }

    public static function rehabilitationEffectOptions(): array
    {
        return [
            'full' => 'Полное',
            'partial' => 'Частичное',
            'none' => 'Положительные результаты отсутствуют',
        ];
    }

    public function statusLabel(): string
    {
        return self::statusOptions()[$this->status] ?? (string) $this->status;
    }

    public function referralKindLabel(): string
    {
        return self::referralKindOptions()[$this->referral_kind] ?? (string) $this->referral_kind;
    }

    public function examinationFormatLabel(): string
    {
        return self::examinationFormatOptions()[$this->examination_format] ?? (string) $this->examination_format;
    }

    public function notificationMethodLabel(): string
    {
        return self::notificationMethodOptions()[$this->notification_method] ?? 'Не указан';
    }

    public function purposeLabels(): array
    {
        $options = self::purposeOptions();

        return collect($this->purposes ?: [])
            ->map(static fn (string $key): string => $options[$key] ?? $key)
            ->values()
            ->all();
    }

    public function conditionLabels(): array
    {
        return collect([
            $this->requires_home_examination ? 'МСЭ необходимо провести на дому' : null,
            $this->palliative_care ? 'Нуждается в паллиативной медицинской помощи' : null,
            $this->primary_prosthetics ? 'Нуждается в первичном протезировании после ампутации' : null,
        ])->filter()->values()->all();
    }

    public function patientFullName(): string
    {
        $snapshotName = trim((string) data_get($this->patient_snapshot, 'full_name'));

        if ($snapshotName !== '') {
            return $snapshotName;
        }

        return collect([
            $this->patient?->surname,
            $this->patient?->name,
            $this->patient?->patronym,
        ])->filter()->implode(' ') ?: 'Пациент не указан';
    }

    public function patientBirthDateLabel(): string
    {
        $snapshotDate = data_get($this->patient_snapshot, 'birth_date');

        if ($snapshotDate) {
            try {
                return Carbon::parse($snapshotDate)->format('d.m.Y');
            } catch (\Throwable) {
                return (string) $snapshotDate;
            }
        }

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

    public function patientSexLabel(): string
    {
        $sex = data_get($this->patient_snapshot, 'sex');

        return self::sexOptions()[$sex] ?? 'Не указан';
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

    public function hasConsentDocument(): bool
    {
        return trim((string) $this->consent_document_path) !== '';
    }

    public function consentDocumentOriginalName(): string
    {
        $name = trim((string) $this->consent_document_original_name);

        return $name !== '' ? $name : 'Разрешение на проведение МСЭ';
    }

    public function consentDocumentSizeLabel(): string
    {
        $bytes = (int) ($this->consent_document_size ?? 0);

        if ($bytes <= 0) {
            return 'Размер не указан';
        }

        $units = ['Б', 'КБ', 'МБ', 'ГБ'];
        $value = (float) $bytes;
        $unitIndex = 0;

        while ($value >= 1024 && $unitIndex < count($units) - 1) {
            $value /= 1024;
            $unitIndex++;
        }

        $precision = $unitIndex === 0 ? 0 : ($value >= 10 ? 1 : 2);

        return number_format($value, $precision, ',', ' ') . ' ' . $units[$unitIndex];
    }

    public function consentDocumentDisk(): string
    {
        $disk = trim((string) $this->consent_document_disk);

        return $disk !== '' ? $disk : self::CONSENT_DOCUMENT_DISK;
    }

    public function detail(string $path, mixed $default = null): mixed
    {
        return data_get($this->details ?: [], $path, $default);
    }
}
