<?php

namespace App\Helpers\Interpreters;

use App\Helpers\Interpreters\DTO\TestResult;
use App\Helpers\Interpreters\Support\Severity;

final class HADSInterpreter extends BaseInterpreter
{
    public function code(): string { return 'HADS'; }
    public function name(): string { return 'HADS'; }

    protected function expectedKeys(): ?array
    {
        // Пример: 14 пунктов. Важно: вам нужно закрепить, какие ключи относятся к A и D.
        return ['q1','q2','q3','q4','q5','q6','q7','q8','q9','q10','q11','q12','q13','q14'];
    }

    protected function valueRange(): array { return [0, 3]; }

    // cutoffs() для total тут не нужен, но Base требует. Можно вернуть пусто и mapSeverity переопределить.
    protected function cutoffs(array $context = []): array { return []; }

    protected function computeScores(array $answers, array $context = []): array
    {
        // ВАЖНО: ниже пример разбиения, проверьте соответствие вашей форме HADS.
        // Часто A: 1,3,5,7,9,11,13; D: 2,4,6,8,10,12,14 (по номерам).
        $A = ['q1','q3','q5','q7','q9','q11','q13'];
        $D = ['q2','q4','q6','q8','q10','q12','q14'];

        $sumA = 0; foreach ($A as $k) $sumA += (int)$answers[$k];
        $sumD = 0; foreach ($D as $k) $sumD += (int)$answers[$k];

        return [
            'total' => $sumA + $sumD,
            'subscales' => [
                'anxiety' => $sumA,
                'depression' => $sumD,
            ],
        ];
    }

    public function interpret(array $answers, array $context = []): TestResult
    {
        $answers = $this->normalizeAnswers($answers);
        $this->validateAnswers($answers);

        $scores = $this->computeScores($answers, $context);
        $sumA = $scores['subscales']['anxiety'];
        $sumD = $scores['subscales']['depression'];

        $sevA = $this->subSeverity($sumA);
        $sevD = $this->subSeverity($sumD);

        $flags = []; // у HADS обычно нет прямых суицид-айтемов
        $recs  = [];
        if ($sumA >= 11 || $sumD >= 11) $recs[] = 'Есть клинически значимый уровень по одной из субшкал (≥11). Рекомендуется консультация специалиста.';

        $text = "HADS: тревога={$sumA} ({$sevA}), депрессия={$sumD} ({$sevD}).";

        return new TestResult(
            code: $this->code(),
            name: $this->name(),
            totalScore: $scores['total'],
            severity: null, // можно не задавать общую severity, либо вывести max(A,D)
            subscales: $scores['subscales'],
            cutoffsHit: [
                'anxiety' => $sevA,
                'depression' => $sevD,
            ],
            redFlags: $flags,
            recommendations: $recs,
            narrative: $text,
            meta: [],
        );
    }

    private function subSeverity(int $v): string
    {
        if ($v <= 7) return Severity::NEGATIVE;  // “норма”
        if ($v <= 10) return 'borderline';
        return Severity::POSITIVE; // “клинически значимо”
    }
}
