<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TestItem extends Model
{
    protected $casts = [
        'reverse_score' => 'boolean',
    ];

    public function options(): HasMany
    {
        return $this->hasMany(TestItemOption::class, 'item_id')
            ->orderBy('order')
            ->orderBy('id');
    }
}
