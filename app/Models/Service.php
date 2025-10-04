<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Service extends Model
{
    use HasFactory;

    protected $fillable = ['clinic_id', 'name'];

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }
}
