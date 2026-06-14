<?php

namespace App\Helpers\Interpreters\DTO;

final class TestResult
{
    public function __construct(
        public readonly string $code,
        public readonly string $name,
        public readonly ?int $totalScore,
        public readonly ?string $severity,      // e.g. none|mild|moderate|severe|positive|negative
        public readonly array $subscales = [],  // e.g. ['anxiety' => 9, 'depression' => 6]
        public readonly array $cutoffsHit = [], // e.g. ['>=10' => true]
        public readonly array $redFlags = [],   // e.g. ['suicidality' => true]
        public readonly array $recommendations = [],
        public readonly string $narrative = '',
        public readonly array $meta = [],       // any extras
    ) {}
}
