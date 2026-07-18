<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class TestCard extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $guarded = [];

    public function test()
    {
        return $this->belongsTo(Test::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('test_card_image')
            ->singleFile()
            ->acceptsMimeTypes([
                'image/jpeg',
                'image/png',
                'image/webp',
            ]);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(368)
            ->height(232)
            ->nonQueued();

        $this->addMediaConversion('front')
            ->width(900)
            ->height(600)
            ->nonQueued();
    }

    protected function sort(Builder $query): void
    {
        $query->orderBy('sort');
    }
}
