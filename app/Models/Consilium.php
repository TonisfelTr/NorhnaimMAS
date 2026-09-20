<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Consilium extends Model
{
    use SoftDeletes;

    protected $table = 'consilia';

    protected $guarded = [];
}
