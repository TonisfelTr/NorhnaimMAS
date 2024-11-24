<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Clinic extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function feedbacks(): HasMany {
        return $this->hasMany(ClinicFeedback::class);
    }

    public function rating(): float {
        return round($this->feedbacks()->avg('mark'), 2) ?? 0;
    }
}
