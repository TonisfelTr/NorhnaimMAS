<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MedicineDocument extends Model
{
    use SoftDeletes;

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'medical_file' => 'boolean'
    ];

    public function scopeMedicalFile(Builder $query): void
    {
        $query->where('medical_file', true);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function fileExtension(): string
    {
        return pathinfo($this->storage_path, PATHINFO_EXTENSION);
    }
}
