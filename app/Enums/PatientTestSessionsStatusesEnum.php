<?php

namespace App\Enums;
enum PatientTestSessionsStatusesEnum: string {
    case TS_ACTIVE = 'active';
    case TS_FINISHED = 'finished';
    case TS_CANCELED = 'canceled';

    public static function values(): array
    {
        $values = [];

        array_map(static function ($value) use (&$values) {
            return $values[] = $value->value;
        }, PatientTestSessionsStatusesEnum::cases());

        return $values;
    }
}
