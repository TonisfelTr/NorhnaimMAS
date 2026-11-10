<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class DoctorProfileEvent extends Model
{
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'changes' => 'array',
        'meta' => 'array',
        'occurred_at' => 'datetime',
    ];

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }

    public function changeLines(): array
    {
        $lines = [];

        foreach ((array) $this->changes as $label => $values) {
            if (!is_array($values)) {
                continue;
            }

            $lines[] = sprintf(
                '%s: %s → %s',
                $label,
                $this->displayValue($values['old'] ?? null),
                $this->displayValue($values['new'] ?? null)
            );
        }

        return $lines;
    }

    private function displayValue(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '—';
        }

        if (is_bool($value)) {
            return $value ? 'Да' : 'Нет';
        }

        if (is_array($value)) {
            return json_encode(
                $value,
                JSON_UNESCAPED_UNICODE
                | JSON_UNESCAPED_SLASHES
            ) ?: '—';
        }

        return (string) $value;
    }
}
