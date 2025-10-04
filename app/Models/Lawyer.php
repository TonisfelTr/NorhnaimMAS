<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lawyer extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $casts = [
      'skills' => 'array'
    ];

    protected function getSkillsAttribute($value): array {
        return is_array($value)
            ? $value
            : json_decode(json_decode($value), true);
    }
}
