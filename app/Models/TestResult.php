<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TestResult extends Model
{
    protected $guarded = [];

    protected $casts = [
        'meta' => 'array'
    ];

    public function key(): HasOne
    {
        return $this->hasOne(TestKey::class, 'id', 'key_id');
    }
}
