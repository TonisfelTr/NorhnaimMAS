<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicineIndication extends Model
{
    use HasFactory;

    protected $table = 'medicine_indications';

    protected $fillable = [
        'medicine_id',
        'diagnose_id',
    ];

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Drug::class, 'medicine_id');
    }

    public function drug(): BelongsTo
    {
        return $this->medicine();
    }

    public function diagnose(): BelongsTo
    {
        return $this->belongsTo(Diagnose::class, 'diagnose_id');
    }
}
