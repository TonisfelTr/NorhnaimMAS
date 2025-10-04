<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

class DoctorFeedback extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function getCreatedAtAttribute($value): string {
        return Carbon::parse($value)->format('d.m.Y H:i:s');
    }

    public function doctor(): BelongsTo {
        return $this->belongsTo(Doctor::class, 'doctor_id', 'id');
    }

    public function user(): BelongsTo {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
