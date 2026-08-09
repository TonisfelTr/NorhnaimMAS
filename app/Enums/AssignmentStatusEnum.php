<?php

namespace App\Enums;

enum AssignmentStatusEnum: string {
    case ORDERED = 'ordered';
    case PROCESSING = 'processing';
    case READY = 'ready';

    public static function all(): array
    {
        return [self::ORDERED, self::PROCESSING, self::READY];
    }
}
