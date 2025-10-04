<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientSymptom extends Model
{
    protected $guarded = [];

    protected $with = ['patient', 'symptom'];

    /**
     * Пациент, у которого найден симптом
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Симптом (один, а не hasMany!)
     */
    public function symptom(): BelongsTo
    {
        return $this->belongsTo(Symptom::class);
    }

    /**
     * Анамнез, к которому относится симптом (опционально)
     */
    public function anamnesis(): BelongsTo
    {
        return $this->belongsTo(Anamnesis::class);
    }

    /**
     * Эпикриз, к которому относится симптом (опционально)
     */
    public function epicrisis(): BelongsTo
    {
        return $this->belongsTo(Epicrisis::class);
    }
}
