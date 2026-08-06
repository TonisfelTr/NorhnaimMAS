<?php

namespace app\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicineContraindication extends Model
{
    protected $fillable = [
        'drug_id',
        'type',
        'contraindication_id'
    ];
}
