<?php

namespace App\Enums\Tests;

enum TestAnswersTypeEnum: string {
    case FREE_TEXT = 'FREE_TEXT';
    case SINGLE_CHOICE = 'SINGLE_CHOICE';
    case MULTIPLE_CHOICE = 'MULTIPLE_CHOICE';

    public static function all(): array {
        return [
            'FREE_TEXT', 'SINGLE_CHOICE', 'MULTIPLE_CHOICE',
        ];
    }
}
