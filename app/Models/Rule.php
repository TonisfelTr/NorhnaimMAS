<?php

namespace app\Models;

use Illuminate\Database\Eloquent\Model;

class Rule extends Model
{
    protected $fillable = [
        'point',
        'text'
    ];
}
