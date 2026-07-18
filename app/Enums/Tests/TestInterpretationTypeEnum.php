<?php

namespace App\Enums\Tests;

enum TestInterpretationTypeEnum: string {
    case MANUAL = 'manual';
    case TEMPLATE = 'template';
    case SCALE = 'scale';

    public static function all(): array
    {
        return [
            'manual',
            'template',
            'scale',
        ];
    }
}
