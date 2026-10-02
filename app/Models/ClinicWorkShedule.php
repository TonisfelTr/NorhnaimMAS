<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClinicWorkShedule extends Model
{
    protected $fillable = [
        'day_number',
        'clinic_id',
        'start_time',
        'end_time',
        'enabled'
    ];

    public static function dayToNumber(string $key): int
    {
        return match ($key) {
            'mon' => 1,
            'tue' => 2,
            'wed' => 3,
            'thu' => 4,
            'fri' => 5,
            'sat' => 6,
            'sun' => 7,
        };
    }

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }
}
