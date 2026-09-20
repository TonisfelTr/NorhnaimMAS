<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DoctorDestination extends Model
{
    protected $guarded = [];

    public function doctor(): HasOne
    {
        return $this->hasOne(Doctor::class, 'id', 'doctor_id');
    }

    public function toDoctor(): HasOne
    {
        return $this->hasOne(Doctor::class, 'id', 'to_doctor_id');
    }

    public function patient(): HasOne
    {
        return $this->hasOne(Patient::class);
    }

    public function diagnose(): HasOne
    {
        return $this->hasOne(Diagnose::class);
    }
}
