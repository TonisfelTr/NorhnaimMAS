<?php

namespace App\Helpers\Interpreters;

use App\Helpers\Interpreters\Contracts\TestInterpreter;
use App\Helpers\Interpreters\DTO\TestResult;
use App\Helpers\Interpreters\Support\Cutoff;

abstract class BaseInterpreter implements TestInterpreter
{
    /**
     * Override if your items have specific keys and you want strict validation.
     * Example: ['q1','q2',...]
     */
    protected function expectedKeys(): ?array { return null; }

    /** Min/max allowed value for each answer (override if needed) */
    protected function valueRange(): array { return [0, 3]; }

    /** Default: sum all numeric answers */
    protected function computeScores(array $answers, array $context = []): array
    {
        $total = 0;
        foreach ($answers as $k => $v) {
            if (is_numeric($v)) $total += (int)$v;
        }
        return ['total' => $total, 'subscales' => []];
    }

    /** Provide cutoffs for total score interpretation */
    abstract protected function cutoffs(array $context = []): array; // array<Cutoff>

    /** Detect red flags (override per test if needed) */
    protected function detectRedFlags(array $answers, array $scores, array $context = []): array
    {
        return [];
    }

    /** Recommendations (override per test) */
    protected function recommendations(array $scores, ?string $severity, array $flags, array $context = []): array
    {
        return [];
    }

    /** Human-readable summary */
    protected function narrative(array $scores, ?string $severity, array $flags, array $context = []): string
    {
        $total = $scores['total'] ?? null;
        return $total === null ? '' : "Суммарный балл: {$total}.";
    }

    /** Main template method */
    public function interpret(array $answers, array $context = []): TestResult
    {
        $answers = $this->normalizeAnswers($answers);
        $this->validateAnswers($answers);

        $scores = $this->computeScores($answers, $context);
        $total = $scores['total'] ?? null;

        [$severity, $cutoffsHit] = $this->mapSeverity($total, $context);

        $flags = $this->detectRedFlags($answers, $scores, $context);
        $recs  = $this->recommendations($scores, $severity, $flags, $context);
        $text  = $this->narrative($scores, $severity, $flags, $context);

        return new TestResult(
            code: $this->code(),
            name: $this->name(),
            totalScore: $total,
            severity: $severity,
            subscales: $scores['subscales'] ?? [],
            cutoffsHit: $cutoffsHit,
            redFlags: $flags,
            recommendations: $recs,
            narrative: $text,
            meta: $scores['meta'] ?? [],
        );
    }

    protected function normalizeAnswers(array $answers): array
    {
        // if answers come as list of ['key'=>'q1','value'=>2], convert here — keep simple for now
        return $answers;
    }

    protected function validateAnswers(array $answers): void
    {
        $expected = $this->expectedKeys();
        if (is_array($expected)) {
            foreach ($expected as $k) {
                if (!array_key_exists($k, $answers)) {
                    throw new \InvalidArgumentException("Missing answer key: {$kч}");
                }
            }
        }

        [$min, $max] = $this->valueRange();
        foreach ($answers as $k => $v) {
            if (!is_numeric($v)) {
                throw new \InvalidArgumentException("Answer '{$k}' must be numeric.");
            }
            $iv = (int)$v;
            if ($iv < $min || $iv > $max) {
                throw new \InvalidArgumentException("Answer '{$k}' out of range ({$min}-{$max}).");
            }
        }
    }

    protected function mapSeverity(?int $total, array $context = []): array
    {
        if ($total === null) return [null, []];

        $hit = [];
        foreach ($this->cutoffs($context) as $c) {
            /** @var Cutoff $c */
            if ($total >= $c->min && $total <= $c->max) {
                $hit[$c->label] = true;
                return [$c->severity, $hit];
            }
        }
        return [null, $hit];
    }
}
