<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class DriverPsychiatricExamination extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PREPARED = 'prepared';

    public const PURPOSE_CANDIDATE = 'candidate';
    public const PURPOSE_RENEWAL = 'renewal';
    public const PURPOSE_RETURN_AFTER_DEPRIVATION = 'return_after_deprivation';
    public const PURPOSE_PROFESSIONAL = 'professional';
    public const PURPOSE_OTHER = 'other';

    public const RESULT_NO_CONTRAINDICATIONS = 'no_contraindications';
    public const RESULT_REFER_COMMISSION = 'refer_commission';
    public const RESULT_COMMISSION_NO_CONTRAINDICATIONS = 'commission_no_contraindications';
    public const RESULT_COMMISSION_CONTRAINDICATIONS = 'commission_contraindications';
    public const RESULT_REFUSED_COMMISSION = 'refused_commission';

    public const CONTRA_ASSESSMENT_NOT_CONFIRMED = 'not_confirmed';
    public const CONTRA_ASSESSMENT_COMMISSION_REQUIRED = 'commission_required';
    public const CONTRA_ASSESSMENT_CONFIRMED = 'confirmed';

    protected $guarded = [];

    protected $casts = [
        'examination_date' => 'date',
        'vehicle_categories' => 'array',
        'patient_snapshot' => 'array',
        'risk_flags' => 'array',
        'medical_record_reviewed' => 'boolean',
        'commission_protocol_date' => 'date',
        'commission_refusal' => 'boolean',
        'certificate_date' => 'date',
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
            self::STATUS_PREPARED => 'Оформлено',
        ];
    }

    public static function purposeOptions(): array
    {
        return [
            self::PURPOSE_CANDIDATE => 'Кандидат в водители',
            self::PURPOSE_RENEWAL => 'Замена / продление водительского удостоверения',
            self::PURPOSE_RETURN_AFTER_DEPRIVATION => 'Возврат права управления после лишения',
            self::PURPOSE_PROFESSIONAL => 'Профессиональная водительская деятельность',
            self::PURPOSE_OTHER => 'Иная цель',
        ];
    }

    public static function vehicleCategoryOptions(): array
    {
        return [
            'M' => 'M',
            'A' => 'A',
            'A1' => 'A1',
            'B' => 'B',
            'B1' => 'B1',
            'BE' => 'BE',
            'C' => 'C',
            'C1' => 'C1',
            'C1E' => 'C1E',
            'CE' => 'CE',
            'D' => 'D',
            'D1' => 'D1',
            'D1E' => 'D1E',
            'DE' => 'DE',
            'Tm' => 'Tm',
            'Tb' => 'Tb',
        ];
    }

    public static function vehicleCategoryDescriptions(): array
    {
        return [
            'M' => 'Мопеды и лёгкие квадрициклы.',
            'A' => 'Мотоциклы.',
            'A1' => 'Лёгкие мотоциклы: объём двигателя до 125 см³ и мощность до 11 кВт.',
            'B' => 'Легковые и другие автомобили до 3,5 т и не более 8 пассажирских мест, не считая места водителя; прицеп — в пределах условий категории B.',
            'B1' => 'Трициклы и квадрициклы.',
            'BE' => 'Автомобиль категории B с тяжёлым прицепом, когда состав выходит за пределы обычной категории B.',
            'C' => 'Автомобили свыше 3,5 т, кроме автобусов категории D; прицеп до 750 кг.',
            'C1' => 'Автомобили свыше 3,5 т, но не более 7,5 т, кроме категории D; прицеп до 750 кг.',
            'C1E' => 'Автомобиль C1 с прицепом свыше 750 кг; общая масса состава — не более 12 т при установленных законом условиях.',
            'CE' => 'Автомобиль категории C с прицепом свыше 750 кг.',
            'D' => 'Автобусы и другие автомобили для перевозки пассажиров с более чем 8 пассажирскими местами; прицеп до 750 кг.',
            'D1' => 'Небольшие автобусы: от 9 до 16 пассажирских мест, не считая места водителя; прицеп до 750 кг.',
            'D1E' => 'Автомобиль D1 с непассажирским прицепом свыше 750 кг; общая масса состава — не более 12 т при установленных законом условиях.',
            'DE' => 'Автомобиль категории D с прицепом свыше 750 кг, а также сочленённые автобусы.',
            'Tm' => 'Трамваи.',
            'Tb' => 'Троллейбусы.',
        ];
    }

    public static function resultOptions(): array
    {
        return [
            self::RESULT_NO_CONTRAINDICATIONS => 'Медицинские противопоказания не выявлены',
            self::RESULT_REFER_COMMISSION => 'Направлен на психиатрическое освидетельствование врачебной комиссией',
            self::RESULT_COMMISSION_NO_CONTRAINDICATIONS => 'ВК: противопоказания не подтверждены',
            self::RESULT_COMMISSION_CONTRAINDICATIONS => 'ВК: медицинские противопоказания подтверждены',
            self::RESULT_REFUSED_COMMISSION => 'Отказался от психиатрического освидетельствования ВК',
        ];
    }

    public static function contraindicationAssessmentOptions(): array
    {
        return [
            self::CONTRA_ASSESSMENT_NOT_CONFIRMED => 'Условие противопоказания не подтверждено',
            self::CONTRA_ASSESSMENT_COMMISSION_REQUIRED => 'Требуется решение психиатрической ВК / профильного специалиста',
            self::CONTRA_ASSESSMENT_CONFIRMED => 'Медицинское противопоказание подтверждено',
        ];
    }

    public static function riskFlagOptions(): array
    {
        return [
            'psychotic' => 'Психотическая симптоматика',
            'affective' => 'Выраженная аффективная нестабильность',
            'cognitive' => 'Когнитивные нарушения',
            'behavior' => 'Дезорганизация поведения / выраженные поведенческие нарушения',
            'suicidal' => 'Суицидальный риск / недавнее суицидальное поведение',
            'impulsivity' => 'Выраженная импульсивность / агрессивность',
        ];
    }

    /**
     * F-коды из действующего перечня медицинских противопоказаний
     * к управлению транспортным средством.
     *
     * Распоряжение Правительства РФ от 12.04.2025 № 892-р
     * (ред. от 19.08.2026).
     *
     * Важно: для раздела I одного диагноза недостаточно. Противопоказание
     * применяется при указанном в распоряжении характере течения расстройства.
     * Раздел II относится к расстройствам, связанным с ПАВ, и оценивается
     * врачом-психиатром-наркологом.
     */
    public static function drivingContraindicationGroups(): array
    {
        $psychiatricCondition = 'Противопоказание действует, если расстройство является хроническим и затяжным с тяжелыми стойкими или часто обостряющимися болезненными проявлениями.';

        return [
            'organic' => [
                'number' => 1,
                'section' => 'I',
                'title' => 'Органические, включая симптоматические психические расстройства',
                'codes' => ['F00', 'F01', 'F02', 'F03', 'F04', 'F05', 'F06', 'F07', 'F09'],
                'condition' => $psychiatricCondition,
                'specialist' => 'psychiatrist',
            ],
            'schizophrenia' => [
                'number' => 2,
                'section' => 'I',
                'title' => 'Шизофрения, шизотипические и бредовые расстройства',
                'codes' => ['F20', 'F21', 'F22.0', 'F22.9', 'F23', 'F24', 'F25', 'F28', 'F29'],
                'condition' => $psychiatricCondition,
                'specialist' => 'psychiatrist',
            ],
            'mood' => [
                'number' => 3,
                'section' => 'I',
                'title' => 'Расстройства настроения (аффективные расстройства)',
                'codes' => ['F30', 'F31', 'F32', 'F33', 'F34', 'F38', 'F39'],
                'condition' => $psychiatricCondition,
                'specialist' => 'psychiatrist',
            ],
            'neurotic' => [
                'number' => 4,
                'section' => 'I',
                'title' => 'Невротические, связанные со стрессом и соматоформные расстройства',
                'codes' => ['F40', 'F41', 'F42', 'F43', 'F44', 'F45', 'F48'],
                'condition' => $psychiatricCondition,
                'specialist' => 'psychiatrist',
            ],
            'personality' => [
                'number' => 5,
                'section' => 'I',
                'title' => 'Расстройства личности и поведения в зрелом возрасте',
                'codes' => ['F60', 'F61', 'F62', 'F63', 'F64', 'F65', 'F66', 'F68', 'F69'],
                'condition' => $psychiatricCondition,
                'specialist' => 'psychiatrist',
            ],
            'intellectual' => [
                'number' => 6,
                'section' => 'I',
                'title' => 'Умственная отсталость',
                'codes' => ['F70', 'F71', 'F72', 'F73', 'F78', 'F79'],
                'condition' => $psychiatricCondition,
                'specialist' => 'psychiatrist',
            ],
            'development' => [
                'number' => 7,
                'section' => 'I',
                'title' => 'Общие расстройства психологического развития',
                'codes' => ['F84'],
                'condition' => $psychiatricCondition,
                'specialist' => 'psychiatrist',
            ],
            'substances' => [
                'number' => 8,
                'section' => 'II',
                'title' => 'Психические расстройства и расстройства поведения, связанные с употреблением психоактивных веществ',
                'codes' => ['F10', 'F11', 'F12', 'F13', 'F14', 'F15', 'F16', 'F18', 'F19'],
                'condition' => 'Противопоказание действует до прекращения диспансерного наблюдения в связи со стойкой ремиссией (выздоровлением). Оценка относится к компетенции врача-психиатра-нарколога.',
                'specialist' => 'psychiatrist_narcologist',
            ],
        ];
    }

    public static function drivingContraindicationMatch(?string $diagnosisCode): ?array
    {
        $code = self::normalizeIcdCode($diagnosisCode);

        if ($code === '') {
            return null;
        }

        foreach (self::drivingContraindicationGroups() as $key => $group) {
            foreach ((array) ($group['codes'] ?? []) as $listedCode) {
                $listedCode = self::normalizeIcdCode((string) $listedCode);

                if (!self::icdCodeMatches($code, $listedCode)) {
                    continue;
                }

                return array_merge($group, [
                    'key' => $key,
                    'diagnosis_code' => $code,
                    'matched_code' => $listedCode,
                ]);
            }
        }

        return null;
    }

    private static function normalizeIcdCode(?string $value): string
    {
        $value = mb_strtoupper(trim((string) $value));
        $value = str_replace([' ', ',', '–', '—'], ['', '.', '-', '-'], $value);

        if (preg_match('/F\d{2}(?:\.\d+)?/u', $value, $matches) === 1) {
            return $matches[0];
        }

        return '';
    }

    private static function icdCodeMatches(string $diagnosisCode, string $listedCode): bool
    {
        if ($diagnosisCode === '' || $listedCode === '') {
            return false;
        }

        if (str_contains($listedCode, '.')) {
            return $diagnosisCode === $listedCode;
        }

        return $diagnosisCode === $listedCode
            || str_starts_with($diagnosisCode, $listedCode . '.');
    }

    public function statusLabel(): string
    {
        return self::statusOptions()[$this->status] ?? (string) $this->status;
    }

    public function purposeLabel(): string
    {
        return self::purposeOptions()[$this->purpose] ?? (string) $this->purpose;
    }

    public function resultLabel(): string
    {
        return self::resultOptions()[$this->result] ?? ($this->result ?: 'Решение не указано');
    }

    public function diagnosisLabel(): string
    {
        $code = trim((string) $this->diagnosis_code);
        $title = trim((string) $this->diagnosis_title);

        if ($code === '' && $title === '') {
            return 'Диагноз в карте не указан';
        }

        return $code . ($code !== '' && $title !== '' ? ' — ' : '') . $title;
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
        $birthDate = data_get($this->patient_snapshot, 'birth_date')
            ?: $this->patient?->getRawOriginal('birth_at');

        if (!$birthDate) {
            return 'Не указана';
        }

        try {
            return Carbon::parse($birthDate)->format('d.m.Y');
        } catch (\Throwable) {
            return (string) $birthDate;
        }
    }

    public function vehicleCategoryLabels(): array
    {
        return collect($this->vehicle_categories ?: [])
            ->map(static fn (mixed $value): string => (string) $value)
            ->filter()
            ->values()
            ->all();
    }

    public function riskFlagLabels(): array
    {
        $options = self::riskFlagOptions();

        return collect($this->risk_flags ?: [])
            ->map(static fn (mixed $key): ?string => $options[(string) $key] ?? null)
            ->filter()
            ->values()
            ->all();
    }

    public function contraindicationAssessmentLabel(): string
    {
        return self::contraindicationAssessmentOptions()[$this->contraindication_assessment]
            ?? 'Не оценено';
    }

    public function hasConfirmedDrivingContraindication(): bool
    {
        return $this->contraindication_assessment === self::CONTRA_ASSESSMENT_CONFIRMED
            || $this->result === self::RESULT_COMMISSION_CONTRAINDICATIONS;
    }

    public function requiresDrivingContraindicationAssessment(): bool
    {
        return self::drivingContraindicationMatch($this->diagnosis_code) !== null
            && !in_array($this->contraindication_assessment, [
                self::CONTRA_ASSESSMENT_NOT_CONFIRMED,
                self::CONTRA_ASSESSMENT_COMMISSION_REQUIRED,
                self::CONTRA_ASSESSMENT_CONFIRMED,
            ], true);
    }

    public function isDrivingDocumentOutputBlocked(): bool
    {
        return $this->hasConfirmedDrivingContraindication()
            || $this->requiresDrivingContraindicationAssessment();
    }

    public function drivingDocumentBlockReason(): ?string
    {
        if ($this->hasConfirmedDrivingContraindication()) {
            return 'У пациента подтверждено медицинское противопоказание к управлению транспортными средствами. Сохранение положительного водительского документа, выдача справки и печать заблокированы.';
        }

        if ($this->requiresDrivingContraindicationAssessment()) {
            return 'Диагноз входит в перечень № 892-р, но обязательная клиническая оценка условия противопоказания ещё не выполнена. Печать заблокирована до повторной проверки документа.';
        }

        return null;
    }

    public function isCertificateResult(): bool
    {
        return in_array($this->result, [
            self::RESULT_NO_CONTRAINDICATIONS,
            self::RESULT_COMMISSION_NO_CONTRAINDICATIONS,
        ], true);
    }

    public function isCommissionResult(): bool
    {
        return in_array($this->result, [
            self::RESULT_COMMISSION_NO_CONTRAINDICATIONS,
            self::RESULT_COMMISSION_CONTRAINDICATIONS,
        ], true);
    }
}
