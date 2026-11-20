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
        'reviewed_at' => 'datetime',
        'medical_file' => 'boolean',
        'file_size' => 'integer',
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

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'reviewed_by_doctor_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }

    public function fileExtension(): string
    {
        return pathinfo($this->storage_path, PATHINFO_EXTENSION);
    }
}
