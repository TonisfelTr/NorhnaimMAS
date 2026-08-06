<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Banned extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];
    protected $table = 'banned';

    protected $casts = [
        'from' => 'datetime',
        'to' => 'datetime',
    ];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function rule(): BelongsTo
    {
        return $this->belongsTo(Rule::class, 'rule_id');
    }

    public function getFromFormattedAttribute(): string
    {
        return $this->from->format('d.m.Y H:i:s');
    }

    public function getToFormattedAttribute(): string
    {
        return $this->to->format('d.m.Y H:i:s');
    }
}
