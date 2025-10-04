<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements HasMedia, FilamentUser, HasAvatar
{
    use HasRoles, HasFactory, Notifiable, SoftDeletes, InteractsWithMedia;

    protected $guarded = [];

    protected $appends = ['name'];

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

    public function getLoginAttribute($value): string
    {
        if (!Cache::has("users.{$this->id}.login")) {
            Cache::set("users.{$this->id}.login", $value);
        }

        return Cache::get("users.{$this->id}.login");
    }

    public function setLoginAttribute($value)
    {
        Cache::set("users.{$this->id}.login", $value);
        $this->attributes['login'] = $value;
    }

    public function getBalanceAttribute($value): string
    {
        if (!Cache::has("users.{$this->id}.balance")) {
            Cache::set("users.{$this->id}.balance", $value);
        }

        return number_format(Cache::get("users.{$this->id}.balance"), 2, thousands_separator: ' ');
    }

    public function setBalanceAttribute($value): string
    {
        Cache::set("users.{$this->id}.balance", $value);
        $this->attributes['balance'] = $value;
    }

    public function getNameAttribute(): string
    {
        return $this->login ?? $this->email ?? 'Администратор';
    }

    public function doctor(): HasOne
    {
        return $this->hasOne(Doctor::class);
    }

    public function patient(): HasOne
    {
        return $this->hasOne(Patient::class);
    }

    public function nurse(): HasOne
    {
        return $this->hasOne(Nurse::class);
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->avatar
            ? asset('storage/' . $this->avatar)
            : null;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasPermissionTo('adminpanel_see');
    }
}
