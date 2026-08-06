<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LabResearchResult extends Model
{
    protected $guarded = [];

    public function research(): BelongsTo
    {
        return $this->belongsTo(
            LabResearch::class,
            'lab_research_id'
        );
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(
            Patient::class,
            'patient_id'
        );
    }

    public function parameter(): BelongsTo
    {
        return $this->belongsTo(
            LabParameter::class,
            'lab_parameter_id'
        );
    }
}
