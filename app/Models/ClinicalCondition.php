<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClinicalCondition extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function patientConditions(): HasMany
    {
        return $this->hasMany(PatientCondition::class, 'condition_id');
    }

    public function drugRules(): HasMany
    {
        return $this->hasMany(DrugConditionRule::class, 'condition_id');
    }
}
