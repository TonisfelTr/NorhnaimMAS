<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Feedback extends Model
{
    protected $table = 'feedbacks';
    protected $guarded = [];

    public function user(): HasOne {
        return $this->hasOne(User::class);
    }
}
