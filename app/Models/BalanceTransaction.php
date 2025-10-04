<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BalanceTransaction extends Model
{
    protected $table = 'balance_transactions';

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
