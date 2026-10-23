<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** An immutable record of a patient's interdepartmental transfer. */
class HospitalDepartmentTransfer extends Model
{
    protected $guarded = [];

    protected $casts = [
        'transferred_at' => 'datetime',
    ];

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(HospitalBedAssignment::class, 'assignment_id');
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function transferredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'transferred_by_user_id');
    }
}
