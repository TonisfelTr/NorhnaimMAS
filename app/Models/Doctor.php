<?php

namespace App\Models;

use App\Traits\FullnameConverter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Doctor extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, FullnameConverter;

    protected $guarded = [];

    protected $casts = [
        'birth_at' => 'date:d.m.Y',
    ];

    protected $appends = ['photo_url', 'experience'];

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function feedbacks(): HasMany
    {
        return $this->hasMany(DoctorFeedback::class);
    }

    public function pricelists(): HasMany
    {
        return $this->hasMany(DoctorPricelist::class);
    }

    public function lowestPrice(): float
    {
        return $this->pricelists()->min('price') ?? 0;
    }

    public function highPrice(): float|null
    {
        return $this->pricelists()->max('price');
    }

    public function rating(): float
    {
        return round($this->feedbacks()->avg('mark'), 2);
    }

    public function priceList()
    {
        return $this->pricelists->groupBy('group');
    }

    public function hasFeedback(): bool
    {
        return $this->feedbacks()
            ->where('user_id', auth()->id())
            ->exists();
    }

    public function feedbackMarksCount(): array
    {
        $feedbacks = $this->feedbacks()
            ->select('mark', \DB::raw('COUNT(*) as count'))
            ->groupBy('mark')
            ->pluck('count', 'mark')
            ->toArray();

        $marksCount = array_fill(1, 5, 0) + $feedbacks;

        return $marksCount;
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->surname}, {$this->name} {$this->patronym}";
    }

    public function shortFullName(): string
    {
        return sprintf('%s %s. %s.',
            $this->surname,
            mb_substr($this->name, 0, 1),
            mb_substr($this->patronym, 0, 1)
        );
    }

    public function getExperienceAttribute(): string
    {
        $years = $this->experience_years;
        $months = $this->experience_months;

        $format = fn($n, $f1, $f2, $f5) =>
        ($n % 10 == 1 && $n % 100 != 11) ? $f1 :
            (($n % 10 >= 2 && $n % 10 <= 4 && ($n % 100 < 10 || $n % 100 >= 20)) ? $f2 : $f5);

        $yearsText = $years ? "{$years} " . $format($years, 'год', 'года', 'лет') : '';
        $monthsText = $months ? "{$months} " . $format($months, 'месяц', 'месяца', 'месяцев') : '';

        return trim("$yearsText $monthsText") ?: 'Без опыта';
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('photos')->singleFile();
    }

    public function getPhotoUrlAttribute(): string
    {
        $media = $this->getFirstMedia('photos');
        return $media ? $media->getUrl() : asset('assets/images/backgrounds/feedback_placeholder.png');
    }

    public function getWebpPhoto(): string
    {
        $media = $this->getFirstMedia('photos');
        return $media ? $media->getUrl('webp') : asset('assets/images/backgrounds/feedback_placeholder.webp');
    }
}
