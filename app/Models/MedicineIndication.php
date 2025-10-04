<?php

namespace app\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class MedicineIndication extends Model
{
    use HasFactory;

    protected $fillable = [
        'medicine_id',
        'diagnose_id'
    ];

    public function medicine(): BelongsToMany {
        return $this->belongsToMany(Drug::class);
    }

    public function diagnose(): BelongsToMany {
        return $this->belongsToMany(Diagnose::class);
    }
}
