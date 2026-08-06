<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Diagnose extends Model
{
    protected $guarded = [];

    protected $casts = [
        'relatives' => 'array',
        'exceptions' => 'array'
    ];

    public function patients(): BelongsToMany {
        return $this->belongsToMany(Patient::class);
    }

    public function drugs(): BelongsToMany {
        return $this->belongsToMany(Drug::class, 'medicine_indications', 'diagnose_id', 'medicine_id');
    }

    public function requiredSymptoms(): BelongsToMany {
        return $this->belongsToMany(Symptom::class, 'diagnose_symptoms', 'diagnose_id', 'symptom_id')
                    ->wherePivot('required', true);
    }

    public function relativeSymptoms(): BelongsToMany {
        return $this->belongsToMany(Symptom::class, 'diagnose_symptoms', 'diagnose_id', 'symptom_id')
                    ->wherePivot('required', false);
    }

    public function decipher(): string {
        return "{$this->code} {$this->title}";
    }


}
