<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientClinicalDocument extends Model
{
    protected $table = 'patient_clinical_documents';

    // Заполнение выполняется только явным списком полей в новом контроллере.
    protected $guarded = ['id'];

    protected $casts = [
        'issued_at' => 'date',
        'period_from' => 'date',
        'period_to' => 'date',
        'finalized_at' => 'datetime',
        'include_diagnosis' => 'boolean',
        'fields' => 'array',
        'patient_snapshot' => 'array',
        'source_options' => 'array',
        'source_snapshot' => 'array',
        'version' => 'integer',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function getNumberAttribute(): string
    {
        return 'МД-' . ($this->issued_at?->format('Y') ?? date('Y'))
            . '-' . str_pad((string) $this->getKey(), 7, '0', STR_PAD_LEFT);
    }
}
