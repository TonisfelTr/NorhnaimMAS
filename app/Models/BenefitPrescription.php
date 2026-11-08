<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** A snapshot of one paper prescription. It is never a MedicalPrescription replica. */
class BenefitPrescription extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'snapshot' => 'array',
            'version' => 'integer',
            'issued_at' => 'immutable_datetime',
            'voided_at' => 'immutable_datetime',
        ];
    }
}
