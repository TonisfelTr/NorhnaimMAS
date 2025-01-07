<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use LogicException;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    const TYPE_DOCTOR = 1;
    const TYPE_PATIENT = 2;

    protected $guarded = [];

    protected $with = [
        //'group'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function scopeDoctors(Builder $query): void {
        $query->where('type', self::TYPE_DOCTOR);
    }

    public function scopePatients(Builder $query): void {
        $query->where('type', self::TYPE_DOCTOR);
    }

    public function scopeNotBanned(Builder $query): void {
        $query->whereNotIn('id', Banned::all('user_id'));
    }

    public function userable(): MorphTo|null {
        if ($this->userable_type != 'administrators') {
            return $this->morphTo();
        } else {
            return null;
        }
    }

    public function group(): BelongsTo {
        return $this->belongsTo(\App\Models\Group::class);
    }

    public function hasPermission(string $permission): bool {
        return is_authed() && $this->group()->where($permission, true);
    }

    public function getUserType(): string {
        return match($this->userable_type) {
            '\App\Models\Doctor' => 'Доктор',
            '\App\Models\Patient' => 'Пациент',
            'administrators' => 'Администрация',
            default => 'Не установлено'
        };
    }

    public function formattedBalance() {
        return number_format($this->balance, 2, '.', ' ');
    }
}
