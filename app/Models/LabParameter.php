<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LabParameter extends Model
{
    protected $guarded = [];
    protected $with = [
        'references'
    ];

    public function references(): HasMany
    {
        return $this->hasMany(LabReferenceRange::class, 'parameter_id');
    }
}
