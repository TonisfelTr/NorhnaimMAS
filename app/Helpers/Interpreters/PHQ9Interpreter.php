<?php

namespace App\Helpers\Interpreters;

use App\Helpers\Interpreters\Support\Cutoff;
use App\Helpers\Interpreters\Support\Severity;

final class PHQ9Interpreter extends BaseInterpreter
{
    public function code(): string { return 'PHQ9'; }
    public function name(): string { return 'PHQ-9'; }

    protected function expectedKeys(): ?array
    {
        return ['q1','q2','q3','q4','q5','q6','q7','q8','q9'];
    }

    protected function valueRange(): array { return [0, 3]; }

    protected function cutoffs(array $context = []): array
    {
        return [
            new Cutoff(0, 4, Severity::MINIMAL, '0-4'),
            new Cutoff(5, 9, Severity::MILD, '5-9'),
            new Cutoff(10, 14, Severity::MODERATE, '10-14'),
            new Cutoff(15, 19, Severity::MODERATELY_SEVERE, '15-19'),
            new Cutoff(20, 27, Severity::SEVERE, '20-27'),
        ];
    }

    protected function detectRedFlags(array $answers, array $scores, array $context = []): array
    {
        // q9: thoughts of self-harm/death (if your questionnaire uses another key — change here)
        return [
            'suicidality_item9' => ((int)($answers['q9'] ?? 0) > 0),
        ];
    }

    protected function recommendations(array $scores, ?string $severity, array $flags, array $context = []): array
    {
        $recs = [];
        $total = $scores['total'] ?? 0;

        if ($total >= 10) $recs[] = 'Скрининг положительный (≥10). Рекомендуется клиническая оценка.';
        if (!empty($flags['suicidality_item9'])) $recs[] = 'Есть ответ по пункту 9 (суицидальные мысли). Нужна оценка риска и очная консультация.';
        return $recs;
    }

    protected function narrative(array $scores, ?string $severity, array $flags, array $context = []): string
    {
        $total = $scores['total'] ?? null;
        $sev = $severity ?? 'не определено';
        $text = "PHQ-9: суммарный балл {$total}, уровень: {$sev}.";
        if (!empty($flags['suicidality_item9'])) $text .= " Отмечены суицидальные мысли по пункту 9.";
        return $text;
    }
}
