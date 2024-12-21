<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ArticleHashtag extends Model
{
    use HasFactory;

    public $guarded = [];

    public function article(): BelongsTo {
        return $this->belongsTo(Article::class);
    }
}
