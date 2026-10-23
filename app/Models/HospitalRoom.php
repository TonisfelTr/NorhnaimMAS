<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HospitalRoom extends Model
{
    protected $guarded = [];

    protected $casts = [
        'enabled' => 'boolean',
        'canvas_width' => 'integer',
        'canvas_height' => 'integer',
        'sort_order' => 'integer',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(HospitalDepartment::class, 'department_id');
    }

    public function beds(): HasMany
    {
        return $this->hasMany(HospitalBed::class, 'room_id')
            ->orderBy('number');
    }

    public function items(): HasMany
    {
        return $this->hasMany(HospitalRoomItem::class, 'room_id')
            ->orderBy('sort_order')
            ->orderBy('id');
    }
}
