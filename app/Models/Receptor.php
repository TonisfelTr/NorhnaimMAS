<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Receptor extends Model
{
    protected $guarded = [
        'name',
        'drug_id'
    ];
}
