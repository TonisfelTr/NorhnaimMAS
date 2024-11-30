<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TopicsCategories extends Model
{
    public function topics(): BelongsTo {
        return $this->belongsTo(Topic::class);
    }
}
