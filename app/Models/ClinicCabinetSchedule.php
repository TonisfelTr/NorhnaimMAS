<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use InvalidArgumentException;

class ClinicCabinetSchedule extends Model
{
    protected $table = 'clinic_cabinet_schedules';

    protected $guarded = [];

    protected $casts = [
        'enabled' => 'boolean',
    ];

    public const DAYS = [
        1 => [
            'code' => 'mon',
            'label' => 'ПН',
        ],
        2 => [
            'code' => 'tue',
            'label' => 'ВТ',
        ],
        3 => [
            'code' => 'wed',
            'label' => 'СР',
        ],
        4 => [
            'code' => 'thu',
            'label' => 'ЧТ',
        ],
        5 => [
            'code' => 'fri',
            'label' => 'ПТ',
        ],
        6 => [
            'code' => 'sat',
            'label' => 'СБ',
        ],
        7 => [
            'code' => 'sun',
            'label' => 'ВС',
        ],
    ];

    public function cabinet(): BelongsTo
    {
        return $this->belongsTo(
            ClinicCabinet::class,
            'clinic_cabinet_id',
            'id'
        );
    }

    public static function dayToNumber(string $code): int
    {
        foreach (self::DAYS as $number => $day) {
            if ($day['code'] === $code) {
                return $number;
            }
        }

        throw new InvalidArgumentException(
            "Неизвестный день недели: {$code}"
        );
    }
}
