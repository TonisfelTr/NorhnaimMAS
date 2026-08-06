<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasPrescriptionRepeats
{
    /**
     * Исходный рецепт, на основании которого создан текущий рецепт.
     */
    public function repeatedFrom(): BelongsTo
    {
        return $this->belongsTo(self::class, 'repeated_from_id');
    }

    /**
     * Рецепты, повторно оформленные на основании текущего рецепта.
     */
    public function repeatedPrescriptions(): HasMany
    {
        return $this->hasMany(self::class, 'repeated_from_id');
    }

    /**
     * Только рецепты, которые действительно были созданы повторно.
     */
    public function scopeRepeated(Builder $query): Builder
    {
        return $query->whereNotNull(
            $query->getModel()->qualifyColumn('repeated_from_id')
        );
    }
}
