<?php

namespace app\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Banned extends Model
{
    use SoftDeletes;

    protected $guarded = [];
    protected $table = 'banned';
    protected $with = [
        'admin', 'user'
    ];

    public function admin(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    public function rule(): BelongsTo {
        return $this->belongsTo(Rule::class);
    }

    public function getFromAttribute(): string {
        return Carbon::from($this->from)->format('d.m.Y H:i:s');
    }

    public function getToAttribute(): string {
        return Carbon::from($this->to)->format('d.m.Y H:i:s');
    }
}
