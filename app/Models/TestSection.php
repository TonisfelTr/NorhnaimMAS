<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Testing\TestResponse;

class TestSection extends Model
{
    protected $guarded = [];

    public function items()
    {
        return $this->hasMany(TestItem::class, 'section_id')->orderBy('id');
    }
}
