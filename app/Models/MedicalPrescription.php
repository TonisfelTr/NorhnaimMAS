<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class MedicalPrescription extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected function getUpdatedAtAttribute($value): string
    {
        return Carbon::parse($this->updated_at)->format('d.m.Y');
    }

    protected function getCreatedAtAttribute(): string
    {
        return Carbon::parse($this->attributes['created_at'])->format('d.m.Y H:i:s');
    }

    protected function getIssuedAtAttribute($value)
    {
        return Carbon::parse($value)->format('d.m.Y');
    }

    protected function getRussianGenericNameAttribute(): string {
        $drug = Drug::select('name')->where('latin_name', $this->generic_name)->first();

        return $drug->name;
    }

    public function drug(): BelongsTo
    {
        return $this->belongsTo(Drug::class, 'generic_name', 'latin_name');
    }
}
