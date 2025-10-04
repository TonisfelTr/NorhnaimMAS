<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Anamnesis extends Model
{
    protected $guard = [];
    protected $with = ['patient', 'diagnose'];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function diagnose(): BelongsTo
    {
        return $this->belongsTo(Diagnose::class);
    }

    public function symptoms(): BelongsToMany
    {
        return $this->belongsToMany(
            Symptom::class,          // модель симптома
            'patient_symptoms',      // имя pivot-таблицы
            'anamnesis_id',          // FK на анамнез
            'symptom_id'             // FK на симптом
        )->withTimestamps();         // если в pivot есть timestamps
        // ->withPivot(['source','severity']); // если есть доп. поля
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }
}
