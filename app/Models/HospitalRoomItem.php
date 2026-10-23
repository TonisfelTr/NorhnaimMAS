<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HospitalRoomItem extends Model
{
    public const TYPE_CABINET = 'cabinet';
    public const TYPE_BEDSIDE_TABLE = 'bedside_table';
    public const TYPE_COLUMN = 'column';
    public const TYPE_ENTRANCE = 'entrance';
    public const TYPE_WINDOW = 'window';
    public const TYPE_SOCKET = 'socket';
    public const TYPE_TOILET = 'toilet';

    public const TYPES = [
        self::TYPE_CABINET,
        self::TYPE_BEDSIDE_TABLE,
        self::TYPE_COLUMN,
        self::TYPE_ENTRANCE,
        self::TYPE_WINDOW,
        self::TYPE_SOCKET,
        self::TYPE_TOILET,
    ];

    protected $guarded = [];

    protected $casts = [
        'x' => 'integer',
        'y' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
        'rotation' => 'float',
        'sort_order' => 'integer',
        'meta' => 'array',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(HospitalRoom::class, 'room_id');
    }
}
