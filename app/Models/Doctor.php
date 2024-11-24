<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Carbon;

class Doctor extends Model
{
    use HasFactory;
    public const USER_TYPE = User::TYPE_DOCTOR;

    protected $guarded = [];
    protected $casts = [
        "birth_at" => "date:d.m.Y"
    ];

    public function getBirthAtAttribute($value) {
        return Carbon::parse($value)->format('d.m.Y');
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
        return $this->hasMany(DoctorFeedback::class);
    }

    public function rating(): float {
        return round($this->feedbacks()->avg('mark'),2);
    }
}
