<?php

namespace App\Enums\Tests;

enum TestCardDisplayModeEnum: string {
    case SEQUENTIAL = 'SEQUENTIAL';
    case ALL = 'ALL';

    public static function all(): array {
        return [
            'SEQUENTIAL' => 'SEQUENTIAL',
            'ALL' => 'ALL',
        ];
    }
}
