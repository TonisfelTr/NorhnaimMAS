<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeVacation extends Model
{
    protected $fillable = [
        'clinic_id',
        'employee_type',
        'employee_id',
        'replace_doctor_id',
        'replace_nurse_id',
        'vacation_type',
        'date_from',
        'date_to',
        'status',
        'comment',
    ];

    protected $casts = [
        'date_from' => 'date',
        'date_to' => 'date',
    ];

    public function replaceDoctor(): BelongsTo
    {
        return $this->belongsTo(
            Doctor::class,
            'replace_doctor_id'
        );
    }

    public function replaceNurse(): BelongsTo
    {
        return $this->belongsTo(
            Nurse::class,
            'replace_nurse_id'
        );
    }
}
