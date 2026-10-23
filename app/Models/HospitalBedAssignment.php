<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class HospitalBedAssignment extends Model
{
    protected $guarded = [];

    protected $casts = [
        'assigned_at' => 'datetime',
        'released_at' => 'datetime',
    ];

    public function bed(): BelongsTo
    {
        return $this->belongsTo(HospitalBed::class, 'bed_id');
    }

    /** Current department even if no bed is assigned. */
    public function department(): BelongsTo
    {
        return $this->belongsTo(HospitalDepartment::class, 'department_id');
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    /** Лечащий врач текущей госпитализации (не patients.doctor_id). */
    public function attendingDoctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'attending_doctor_id');
    }

    /** Full persistent transfer history. */
    public function departmentTransfers(): HasMany
    {
        return $this->hasMany(HospitalDepartmentTransfer::class, 'assignment_id');
    }

    /** Last transfer, independent from the current bed and doctor. */
    public function latestDepartmentTransfer(): HasOne
    {
        return $this->hasOne(HospitalDepartmentTransfer::class, 'assignment_id')->latestOfMany('id');
    }
}
