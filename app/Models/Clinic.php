<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Clinic extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'datetime:d.m.Y H:i:s',
    ];

    // Форматированная дата создания
    public function getCreatedAtAttribute($value): string
    {
        return Carbon::parse($value)->format('d.m.Y H:i:s');
    }

    // Отношение к отзывам
    public function feedbacks(): HasMany
    {
        return $this->hasMany(ClinicFeedback::class);
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
