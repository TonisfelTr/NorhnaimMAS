<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $guarded = [];

    public function hasPermission(string $permissionName): bool {
        return $this->getAttribute($permissionName) > 0;
    }

    public function getMembersCount(): int {
        return User::whereGroupId($this->id)->get()->count();
    }
}
