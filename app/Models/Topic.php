<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Topic extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function hashtags(): HasMany {
        return $this->hasMany(TopicsHashtag::class);
    }

    public function photo(): string
    {
        if (!empty($this->photo)) {
            return asset('storage/topics/' . $this->photo);
        }

        return asset('assets/images/placeholders/topic_placeholder.png');
    }

    public function webpPhoto(): string
    {
        if (!empty($this->photo)) {
            $pathInfo = pathinfo($this->photo);
            $webpPath = $pathInfo['filename'] . '.webp';

            return asset('storage/topics/' . $webpPath);
        }

        // Возвращаем путь к placeholder, если фото не задано
        return asset('assets/images/placeholders/topic_placeholder.webp');
    }

    public function getContentAttribute($value) {
        $allowedTags = '<p><a><strong><em><ul><li><ol><blockquote><h1><h2><h3><h4><h5><h6><img><br><hr><pre>';

        return strip_tags($value, $allowedTags);
    }

}
