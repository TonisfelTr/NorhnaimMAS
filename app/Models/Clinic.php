<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Clinic extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    public const TYPE_OUTPATIENT = 'outpatient';
    public const TYPE_INPATIENT = 'inpatient';

    public const INPATIENT_MODE_DAY = 'day';
    public const INPATIENT_MODE_NIGHT = 'night';
    public const INPATIENT_MODE_ROUND_THE_CLOCK = 'round_the_clock';

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'datetime:d.m.Y H:i:s',
        'inpatient_working_hours' => 'array',
        'inpatient_visiting_hours' => 'array',
    ];

    // Форматированная дата создания
    public function getCreatedAtAttribute($value): string
    {
        return Carbon::parse($value)->format('d.m.Y H:i:s');
    }

    public function doctor(): HasMany
    {
        return $this->HasMany(Doctor::class);
    }

    // Отношение к отзывам
    public function feedbacks(): HasMany
    {
        return $this->hasMany(ClinicFeedback::class);
    }

    public function workSchedules(): HasMany
    {
        return $this->hasMany(ClinicWorkShedule::class);
    }

    // Средний рейтинг клиники
    public function rating(): float
    {
        return round($this->feedbacks()->avg('mark') ?? 0, 2);
    }

    // Проверка, оставил ли текущий пользователь отзыв
    public function hasFeedback(): bool
    {
        return $this->feedbacks()
            ->where('user_id', auth()->id())
            ->exists();
    }

    // Отношение к услугам
    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    // Основное изображение клиники (оригинал)
    public function coverPhoto(): string
    {
        $media = $this->getFirstMedia('photos');

        return $media
            ? $media->getUrl()
            : asset('assets/images/backgrounds/feedback_placeholder.png');
    }

    // Основное изображение клиники в формате WebP
    public function getCoverWebpPhoto(): string
    {
        $media = $this->getFirstMedia('photos');

        return $media
            ? $media->getUrl('webp')
            : asset('assets/images/backgrounds/feedback_placeholder.webp');
    }

    // Регистрация коллекций медиа
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('photos')->singleFile();
    }

    public function registerMediaConversions(?\Spatie\MediaLibrary\MediaCollections\Models\Media $media = null): void
    {
        $this->addMediaConversion('webp')
            ->format('webp')
            ->width(1024)
            ->queued();
    }

}
