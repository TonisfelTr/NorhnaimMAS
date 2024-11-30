<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class Clinic extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $casts = [
        'created_at' => 'datetime:d.m.Y H:i:s'
    ];
    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->format('d.m.Y H:i:s');
    }
    public function feedbacks(): HasMany {
        return $this->hasMany(ClinicFeedback::class);
    }

    public function rating(): float {
        return round($this->feedbacks()->avg('mark'), 2) ?? 0;
    }

    public function services(): Collection {
        return DB::table('services')->where('clinic_id', $this->id)->get();
    }

    public function getCoverWebpPhoto(): string
    {
        $coverPhoto = $this->photos()->where('is_cover', true)->first();

        if ($coverPhoto && !empty($coverPhoto->photo)) {
            $unformatPhoto = pathinfo($coverPhoto->photo, PATHINFO_FILENAME) . '.webp';

            return asset('storage/clinic_photos/' . $unformatPhoto);
        }

        return asset('assets/images/backgrounds/feedback_placeholder.webp');
    }


    public function hasFeedback(): bool {
        return $this->feedbacks()
                    ->where('user_id', auth()->user()->id)
                    ->where('clinic_id', $this->id)
                    ->count();
    }

    public function photos(): HasMany {
        return $this->hasMany(ClinicPhoto::class);
    }

    public function coverPhoto(): ?string
    {
        $coverPhoto = $this->photos()->where('is_cover', true)->first();

        if ($coverPhoto) {
            return asset('storage/clinic_photos/' . $coverPhoto->photo);
        }

        return asset('assets/images/backgrounds/feedback_placeholder.png');
    }
}
