<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TopicsCategory extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function topics(): BelongsTo {
        return $this->belongsTo(Topic::class);
    }
}
