<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommissionDocument extends Model
{
    public const TYPE_MILITARY = 'military';
    public const TYPE_MSE = 'mse';

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PREPARED = 'prepared';

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'type',
        'status',
        'number',
        'document_date',
        'data',
    ];

    protected function casts(): array
    {
        return [
            'document_date' => 'date',
            'data' => 'array',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }


    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }
}
