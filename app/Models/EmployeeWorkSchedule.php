<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class EmployeeWorkSchedule extends Model
{
    protected $table = 'employee_work_schedules';

    protected $guarded = [];

    protected $casts = [
        'enabled' => 'boolean',
    ];

    public const DAYS = [
        1 => ['code' => 'mon', 'label' => 'ПН'],
        2 => ['code' => 'tue', 'label' => 'ВТ'],
        3 => ['code' => 'wed', 'label' => 'СР'],
        4 => ['code' => 'thu', 'label' => 'ЧТ'],
        5 => ['code' => 'fri', 'label' => 'ПТ'],
        6 => ['code' => 'sat', 'label' => 'СБ'],
        7 => ['code' => 'sun', 'label' => 'ВС'],
    ];

    public function scopeForDoctor(Builder $query, int $doctorId): Builder
    {
        return $query
            ->where('employee_type', 'doctor')
            ->where('employee_id', $doctorId);
    }

    public function scopeForNurse(Builder $query, int $nurseId): Builder
    {
        return $query
            ->where('employee_type', 'nurse')
            ->where('employee_id', $nurseId);
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
