<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class SymptomAlias extends Model
{
    protected $fillable = ['symptom_id', 'alias'];
    public function symptom() {
        return $this->belongsTo(Symptom::class);
    }
}
