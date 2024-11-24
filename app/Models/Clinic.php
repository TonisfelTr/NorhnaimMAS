<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
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

    public function getPhotoAttribute($value): string
    {
        if (!is_null($value)) {
            return asset('storage/' . $value);
        }

        return asset('assets/images/backgrounds/feedback_placeholder.png');
    }

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
                    ->where('clinic_id', $this->id)
                    ->count();
    }
}
