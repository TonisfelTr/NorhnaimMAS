<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class Doctor extends Model
{
    use HasFactory;
    public const USER_TYPE = User::TYPE_DOCTOR;

    protected $guarded = [];
    protected $casts = [
        "birth_at" => "datetime", // Сохраняем как объект Carbon
    ];

    public function getExperienceAttribute(): string {
        $years = $this->experience_years;
        $months = $this->experience_months;

        $yearsForms = ['год', 'года', 'лет'];
        $monthsForms = ['месяц', 'месяца', 'месяцев'];

        $getWordForm = function (int $number, array $forms): string {
            $mod10 = $number % 10;
            $mod100 = $number % 100;

            if ($mod10 == 1 && $mod100 != 11) {
                return $forms[0];
            } elseif ($mod10 >= 2 && $mod10 <= 4 && ($mod100 < 10 || $mod100 >= 20)) {
                return $forms[1];
            } else {
                return $forms[2];
            }
        };

        $yearsText = $years > 0 ? $years . ' ' . $getWordForm($years, $yearsForms) : '';
        $monthsText = $months > 0 ? $months . ' ' . $getWordForm($months, $monthsForms) : '';

        if ($yearsText && $monthsText) {
            return $yearsText . ' ' . $monthsText;
        } elseif ($yearsText) {
            return $yearsText;
        } elseif ($monthsText) {
            return $monthsText;
        }

        return 'Без опыта';
    }

    public function getPhotoAttribute($value): string
    {
        if (!is_null($value)) {
            return asset('storage/' . $value);
        }

        return asset('assets/images/backgrounds/feedback_placeholder.png');
    }

    public function getBirthAtAttribute() {
        return $this->attributes['birth_at'] ? Carbon::parse($this->attributes['birth_at'])->format('d.m.Y') : null;
    }

    public function getBirthAtForInputAttribute() {
        return $this->attributes['birth_at'] ? Carbon::parse($this->attributes['birth_at'])->format('Y-m-d') : null;
    }

    public function user(): MorphOne {
        return $this->morphOne(User::class, 'userable');
    }

    public function clinic(): BelongsTo {
        return $this->belongsTo(Clinic::class);
    }

    public function fullName(): string {
        return "{$this->surname}, {$this->name} {$this->patronym}";
    }

    public function feedbacks(): HasMany {
        return $this->hasMany(DoctorFeedback::class, 'doctor_id', 'id');
    }

    public function lowestPrice(): float {
        return DB::table('doctors_pricelists')
            ->where('doctor_id', $this->id)
            ->min('price') ?? 0;
    }

    public function highPrice(): float|null {
        return DB::table('doctors_pricelists')
            ->where('doctor_id', $this->id)
            ->max('price');
    }

    public function rating(): float {
        return round($this->feedbacks()->avg('mark'),2);
    }

    public function priceList(): Collection {
        return DB::table('doctors_pricelists')->where('doctor_id', $this->id)->get()->groupBy('group');
    }

    public function getWebpPhoto(): string
    {
        if (!empty($this->attributes['photo'])) {
            $unformatPhoto = pathinfo($this->attributes['photo'], PATHINFO_FILENAME) . '.webp';

            return asset('storage/photos/' . $unformatPhoto);
        }

        return asset('assets/images/backgrounds/feedback_placeholder.webp');
    }

    public function hasFeedback(): bool {
        return $this->feedbacks()
                    ->where('user_id', auth()->user()->id)
                    ->where('doctor_id', $this->id)
                    ->count();
    }

    public function feedbackMarksCount(): array
    {
        $feedbacks = DB::table('doctor_feedback')
                       ->select('mark', DB::raw('COUNT(*) as count'))
                       ->where('doctor_id', $this->id)
                       ->groupBy('mark')
                       ->pluck('count', 'mark')
                       ->toArray();

        $marksCount = [];
        for ($i = 1; $i <= 5; $i++) {
            $marksCount[$i] = $feedbacks[$i] ?? 0;
        }

        return $marksCount;
    }
}
