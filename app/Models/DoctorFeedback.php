<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DoctorFeedback extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function doctor(): HasOne {
        return $this->hasOne(Doctor::class);
    }

    public function user(): HasOne {
        return $this->hasOne(User::class);
    }
}
