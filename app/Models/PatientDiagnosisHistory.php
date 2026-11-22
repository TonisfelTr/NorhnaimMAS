<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientDiagnosisHistory extends Model
{
    protected $table = 'patient_diagnosis_history';

    protected $guarded = [];

    protected $casts = [
        'set_at' => 'datetime',
    ];

    public function diagnose(): BelongsTo
    {
        return $this->belongsTo(Diagnose::class, 'diagnose_id');
    }
}
