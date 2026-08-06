<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Article extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    public function hashtags(): HasMany {
        return $this->hasMany(ArticleHashtag::class, 'article_id');
    }

    public function getAuthorsAttribute($value) {
        $authors = json_decode($value);

        return implode(', ', $authors);
    }

    public function getCreatedAtAttribute($value) {
        return Carbon::parse($value)->format('d.m.Y H:i:s');
    }
}
