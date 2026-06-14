<?php

namespace App\Helpers\Interpreters\Support;

final class Cutoff
{
    public function __construct(
        public readonly int $min,
        public readonly int $max,
        public readonly string $severity,
        public readonly string $label
    ) {}
}
