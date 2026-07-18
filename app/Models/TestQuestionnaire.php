<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TestQuestionnaire extends Model
{
    protected $guarded = [];

    public function test(): BelongsTo
    {
        return $this->belongsTo(Test::class);
    }
}
