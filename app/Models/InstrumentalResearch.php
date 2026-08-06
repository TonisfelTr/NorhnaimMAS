<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class InstrumentalResearch extends Model implements HasMedia
{
    use InteractsWithMedia;

    public const MEDIA_COLLECTION_RESULTS = 'instrumental_results';

    public const TYPES = [
        'mri' => 'МРТ',
        'ct' => 'КТ',
        'eeg' => 'ЭЭГ',
        'ecg' => 'ЭКГ',
        'ultrasound' => 'УЗИ',
        'fluorography' => 'Флюорография',
        'other' => 'Другое',
    ];
    public const STATUSES = [
        'ordered' => 'Назначено',
        'scheduled' => 'Запланировано',
        'performed' => 'Выполнено',
        'ready' => 'Заключение готово',
        'cancelled' => 'Отменено',
    ];
    public const PRIORITIES = [
        'normal' => 'Обычный',
        'urgent' => 'Срочный',
    ];

    protected $table ='instrumental_researches';

    protected $guarded = [];

    protected $casts = [
        'with_contrast' => 'boolean',
        'planned_at' => 'datetime',
        'performed_at' => 'datetime',
        'result_at' => 'datetime',
        'result_showed_at' => 'datetime',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->study_type]
            ?? $this->study_type;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status]
            ?? $this->status;
    }

    public function getPriorityLabelAttribute(): string
    {
        return self::PRIORITIES[$this->priority]
            ?? $this->priority;
    }

    public function getStatusClassAttribute(): string
    {
        return match ($this->status) {
            'ordered' => 'primary',
            'scheduled' => 'info',
            'performed' => 'warning',
            'ready' => 'success',
            'cancelled' => 'secondary',
            default => 'light',
        };
    }

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection(self::MEDIA_COLLECTION_RESULTS)
            ->useDisk('local')
            ->acceptsMimeTypes([
                'application/pdf',
                'image/jpeg',
                'image/png',
                'image/webp',
                'application/zip',
                'application/x-zip-compressed',
            ]);
    }
}
