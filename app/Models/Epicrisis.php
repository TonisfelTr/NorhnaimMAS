<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Epicrisis extends Model
{
    protected $table = 'epicrises';
    protected $guarded = [];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function symptoms(): BelongsToMany
    {
        return $this->belongsToMany(Symptom::class, 'epicrisis_symptoms')
            ->withPivot(['doctor_id','active','created_at','updated_at'])
            ->withTimestamps();
    }

    public function epicrisisSymptoms(): HasMany
    {
        return $this->hasMany(EpicrisisSymptom::class);
    }
}
