<?php

namespace app\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicineIndication extends Model
{
    use HasFactory;

    protected $fillable = [
        'medicine_id',
        'diagnose_id'
    ];
}
