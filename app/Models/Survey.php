<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Survey extends Model
{
    protected $guarded = [];

    protected $casts = [
        'points' => 'array'
    ];

    public function questions(): HasMany {
        return $this->hasMany(Question::class);
    }
}
