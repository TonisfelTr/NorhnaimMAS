<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientDiagnosisHistory extends Model
{
    protected $table = 'patient_diagnosis_history';

    protected $guarded = [];

    protected $casts = [
        'set_at' => 'datetime',
    ];
}
