<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class MedicalPrescription extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected function getIssuedAtAttribute($value) {
        return Carbon::parse($value)->format('d.m.Y');
    }

    protected function getRussianGenericNameAttribute(): string {
        $drug = Drug::select('name')->where('latin_name', $this->generic_name)->first();

        return $drug->name;
    }
}
