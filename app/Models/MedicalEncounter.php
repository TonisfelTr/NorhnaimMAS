<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Manual clinical/attendance record attached to one registry appointment.
 * Not an automatically verified WebRTC connection and not an e-signed document.
 */
class MedicalEncounter extends Model
{
    protected $fillable = [
        'registry_record_id', 'attendance_status', 'consultation_status',
        'visit_type', 'noncompletion_reason', 'patient_condition', 'disability_group', 'disability_details',
        'complaints', 'disease_history', 'life_history', 'social_history',
        'mental_status', 'diagnosis_code', 'diagnosis_title', 'conclusion',
        'recommendations', 'service_code', 'service_name',
    ];

    protected function casts(): array
    {
        return ['completed_at' => 'datetime'];
    }

    public function registryRecord(): BelongsTo
    {
        return $this->belongsTo(RegistryRecord::class, 'registry_record_id');
    }
}
