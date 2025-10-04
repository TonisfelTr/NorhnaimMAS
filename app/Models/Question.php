<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $guarded = [];

    public function answersByUser($userId)
    {
        return $this->hasMany(Answer::class)
                    ->where('user_id', $userId); // Фильтрация по user_id
    }
}
