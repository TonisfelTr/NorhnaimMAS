<?php

namespace app\Models;

use App\Models\SymptomAlias;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Symptom extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function aliases(): HasMany
    {
        return $this->hasMany(SymptomAlias::class);
    }

    public function epicrises(): BelongsToMany
    {
        return $this->belongsToMany(Epicrisis::class, 'epicrisis_symptoms')
            ->withPivot(['doctor_id','active','created_at','updated_at'])
            ->withTimestamps();
    }
}
