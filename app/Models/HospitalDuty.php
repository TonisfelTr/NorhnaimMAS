<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HospitalDuty extends Model
{
    protected $fillable = [
        'clinic_id',
        'department_id',
        'employee_type',
        'employee_id',
        'starts_at',
        'ends_at',
        'comment',
        'created_by',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function department()
    {
        return $this->belongsTo(HospitalDepartment::class, 'department_id');
    }
}
