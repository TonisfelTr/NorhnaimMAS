<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class TestSortCard extends Model implements HasMedia
{
    use InteractsWithMedia;

    public const TYPE_COLOR = 'color';
    public const TYPE_TEXT = 'text';
    public const TYPE_IMAGE = 'image';

    protected $guarded = [];

    protected $casts = [
        'sort' => 'integer',
    ];

    public function test(): BelongsTo
    {
        return $this->belongsTo(Test::class);
    }

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('test_sort_card_image')
            ->singleFile()
            ->acceptsMimeTypes([
                'image/jpeg',
                'image/png',
                'image/webp',
            ]);
    }

    public function registerMediaConversions(
        ?Media $media = null
    ): void {
        $this
            ->addMediaConversion('thumb')
            ->performOnCollections('test_sort_card_image')
            ->width(400)
            ->height(300)
            ->nonQueued();

        $this
            ->addMediaConversion('front')
            ->performOnCollections('test_sort_card_image')
            ->width(1200)
            ->height(900)
            ->nonQueued();
    }
}
