<?php

namespace app\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientIndication extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'medicine_id'
    ];
}
