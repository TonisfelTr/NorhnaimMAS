<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TestItem extends Model
{
    public function options() {
        return $this->hasMany(TestItemOption::class, 'item_id')->orderBy('id');
    }
}
