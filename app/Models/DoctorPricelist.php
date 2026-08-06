<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoctorPricelist extends Model
{
    use HasFactory;

    protected $fillable = [
        'doctor_id', 'name', 'group', 'price', 'discount_price'
    ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }
}
