<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TestAssignment extends Model
{
    protected $guarded = [];
    protected $casts = [
        'due_at' => 'datetime',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'pin_expires_at' => 'datetime',
        'is_kiosk' => 'boolean',
        'context' => 'array'
    ];

    protected $hidden = ['pin_hash'];
    public function session()
    {
        return $this->belongsTo(TestSession::class, 'session_id');
    }

    public function test()
    {
        return $this->belongsTo(Test::class, 'test_id');
    }
}
