<?php

namespace App\Services\Tests;

use App\Models\TestItem;
use App\Models\TestItemOption;

final class ItemScoreResolver
{
    /**
     * Возвращает балл выбранного варианта с учётом обратного кодирования вопроса.
     *
     * Для обратного вопроса используется формула min + max - raw:
     * 0..3 => 3..0, 1..4 => 4..1, 0..4 => 4..0.
     */
    public function resolve(?TestItem $item, ?TestItemOption $option): int
    {
        if (!$option) {
            return 0;
        }

        $value = (int) $option->value;

        if (!$item || !(bool) $item->reverse_score) {
            return $value;
        }

        $options = $item->relationLoaded('options')
            ? $item->options
            : $item->options()->get(['id', 'item_id', 'value']);

        $values = $options
            ->pluck('value')
            ->filter(static fn ($score): bool => $score !== null)
            ->map(static fn ($score): int => (int) $score);

        if ($values->isEmpty()) {
            return $value;
        }

        return (int) $values->min() + (int) $values->max() - $value;
    }
};
