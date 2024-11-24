<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ClinicFeedback extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function clinic(): HasOne {
        return $this->hasOne(Clinic::class);
    }

    public function user(): HasOne {
        return $this->hasOne(User::class);
    }
}
