<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClinicDistrict extends Model
{
    protected $guarded = [];

    protected $casts = [
        'address_rules' => 'array',
        'enabled' => 'boolean',
        'transfer_from' => 'date',
        'transfer_until' => 'date',
    ];

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(
            Doctor::class,
            'doctor_id'
        );
    }

    public function homeVisitDoctor(): BelongsTo
    {
        return $this->belongsTo(
            Doctor::class,
            'home_visit_doctor_id'
        );
    }

    public function transferredToDistrict(): BelongsTo
    {
        return $this->belongsTo(
            self::class,
            'transfer_to_district_id'
        );
    }
}
