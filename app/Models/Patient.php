<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Carbon;

class Patient extends Model
{
    use HasFactory;

    public const USER_TYPE = User::TYPE_PATIENT;

    protected $guarded = [];
    protected $casts = [
        "birth_at" => "date:d.m.Y"
    ];

    public function getBirthAtAttribute(string $value) {
        return Carbon::parse($value)->format('d.m.Y');
    }

    public function user(): MorphOne {
        return $this->morphOne(User::class, 'userable');
    }

    public function diagnose(): BelongsTo {
        return $this->belongsTo(Diagnose::class);
    }

    public function fullName(): string {
        return "{$this->surname}, {$this->name} {$this->patronym}";
    }

    public function trimmedName(): string {
        return "{$this->surname}, {$this->name}";
    }
}
