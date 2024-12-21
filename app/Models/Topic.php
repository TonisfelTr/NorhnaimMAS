<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class Topic extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function topics_category(): BelongsTo {
        return $this->belongsTo(TopicsCategory::class);
    }

    public function hashtags(): HasMany {
        return $this->hasMany(TopicsHashtag::class);
    }

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    public function photo(): string
    {
        if (!empty($this->photo)) {
            return asset('storage/topics/' . $this->photo);
        }

        return asset('assets/images/backgrounds/topic_placeholder.png');
    }

    public function webpPhoto(): string
    {
        if (!empty($this->photo)) {
            $pathInfo = pathinfo($this->photo);
            $webpPath = $pathInfo['filename'] . '.webp';

            return asset('storage/topics/' . $webpPath);
        }

        // Возвращаем путь к placeholder, если фото не задано
        return asset('assets/images/backgrounds/topic_placeholder.webp');
    }

    public function getContentAttribute($value) {
        $allowedTags = '<p><a><strong><em><ul><li><ol><blockquote><h1><h2><h3><h4><h5><h6><img><br><hr><pre>';

        return strip_tags($value, $allowedTags);
    }

    public function getDescriptionAttribute($value) {
        $allowedTags = '<p><a><strong><em><ul><li><ol><blockquote><h1><h2><h3><h4><h5><h6><img><br><hr><pre>';

        return Str::limit(strip_tags($value, $allowedTags), 155);
    }

    public function getCreatedAtAttribute($value) {
        return Carbon::parse($value)->format('d.m.Y H:i:s');
    }

    public function getUpdatedAtAttribute($value) {
        return Carbon::parse($value)->format('d.m.Y H:i:s');
    }

}
