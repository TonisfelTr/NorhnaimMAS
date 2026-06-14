<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TestAnswer extends Model
{
    protected $guarded = [];

    public function item()
    {
        return $this->belongsTo(TestItem::class, 'item_id');
    }

    public function option()
    {
        return $this->belongsTo(TestItemOption::class, 'option_id');
    }

    public function session()
    {
        return $this->belongsTo(TestSession::class, 'session_id');
    }
}
