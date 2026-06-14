<?php

namespace App\Helpers\Interpreters;

use App\Helpers\Interpreters\DTO\TestResult;
use App\Helpers\Interpreters\Support\Severity;

final class PCL5Interpreter extends BaseInterpreter
{
    public function code(): string { return 'PCL-5'; }
    public function name(): string { return 'PCL-5'; }

    protected function expectedKeys(): ?array
    {
        // 20 пунктов: q1..q20
        return [
            'q1','q2','q3','q4','q5',
            'q6','q7',
            'q8','q9','q10','q11','q12','q13','q14',
            'q15','q16','q17','q18','q19','q20',
        ];
    }

    protected function valueRange(): array { return [0, 4]; }

    // Base требует cutoffs(). Для PCL-5 делаем ручную интерпретацию в interpret().
    protected function cutoffs(array $context = []): array { return []; }

    protected function computeScores(array $answers, array $context = []): array
    {
        // DSM-5 кластеры:
        $B = ['q1','q2','q3','q4','q5'];                         // Intrusion
        $C = ['q6','q7'];                                        // Avoidance
        $D = ['q8','q9','q10','q11','q12','q13','q14'];          // Negative cognitions/mood
        $E = ['q15','q16','q17','q18','q19','q20'];              // Arousal/reactivity

        $sumB = $this->sumKeys($answers, $B);
        $sumC = $this->sumKeys($answers, $C);
        $sumD = $this->sumKeys($answers, $D);
        $sumE = $this->sumKeys($answers, $E);

        return [
            'total' => $sumB + $sumC + $sumD + $sumE,
            'subscales' => [
                'intrusion_B' => $sumB,
                'avoidance_C' => $sumC,
                'negative_D'  => $sumD,
                'arousal_E'   => $sumE,
            ],
        ];
    }

    public function interpret(array $answers, array $context = []): TestResult
    {
        $answers = $this->normalizeAnswers($answers);
        $this->validateAnswers($answers);

        $scores = $this->computeScores($answers, $context);
        $total  = (int)$scores['total'];

        // Настройки (можно переопределять через $context)
        $cutoffTotal       = (int)($context['cutoff_total'] ?? config('tests.pcl5.cutoff_total') ?? 33);
        $symptomThreshold  = (int)($context['symptom_threshold'] ?? config('tests.pcl5.symptom_threshold') ?? 2); // >=2
        $useOrLogic        = (bool)($context['probable_logic_or'] ?? config('tests.pcl5.probable_logic_or') ?? true);
        // true  -> probable = aboveCutoff OR clustersMet
        // false -> probable = aboveCutoff AND clustersMet

        // Симптомы "засчитаны" если >= threshold
        $countB = $this->countAtLeast($answers, ['q1','q2','q3','q4','q5'], $symptomThreshold);
        $countC = $this->countAtLeast($answers, ['q6','q7'], $symptomThreshold);
        $countD = $this->countAtLeast($answers, ['q8','q9','q10','q11','q12','q13','q14'], $symptomThreshold);
        $countE = $this->countAtLeast($answers, ['q15','q16','q17','q18','q19','q20'], $symptomThreshold);

        // Кластерное правило (аналог DSM-5): B>=1, C>=1, D>=2, E>=2
        $clustersMet = ($countB >= 1) && ($countC >= 1) && ($countD >= 2) && ($countE >= 2);

        $aboveCutoff = $total >= $cutoffTotal;

        $probablePtsd = $useOrLogic
            ? ($aboveCutoff || $clustersMet)
            : ($aboveCutoff && $clustersMet);

        // Условные уровни выраженности (для UI; не “официальные” диагноз-градации)
        $band = $this->severityBand($total);

        $flags = []; // В PCL-5 нет прямых “суицид”-айтемов
        $recs  = [];

        if ($probablePtsd) {
            $recs[] = 'Результат совместим с вероятным ПТСР (скрининг).';
            $recs[] = 'Для уточнения важно оценить: наличие травматического события, длительность симптомов и влияние на функционирование.';
            if ($total >= 41) {
                $recs[] = 'Выраженность симптомов высокая: при значимом ухудшении сна/функционирования имеет смысл обратиться за помощью без откладывания.';
            }
        } else {
            if ($total >= 21) {
                $recs[] = 'Симптомы выражены умеренно/заметно. Если они мешают жизни или сохраняются, рекомендуется консультация специалиста.';
            } else {
                $recs[] = 'Выраженность симптомов низкая. При изменении состояния можно повторить опросник для мониторинга.';
            }
        }

        $text = "PCL-5: {$total}/80 ({$band}). "
            . "Порог {$cutoffTotal}: " . ($aboveCutoff ? 'достигнут' : 'не достигнут') . ". "
            . "Кластеры: " . ($clustersMet ? 'выполнены' : 'не выполнены') . ". "
            . ($probablePtsd ? 'Есть признаки вероятного ПТСР (скрининг).' : 'Признаки вероятного ПТСР по шкале не выявлены.');

        return new TestResult(
            code: $this->code(),
            name: $this->name(),
            totalScore: $total,
            severity: $probablePtsd ? Severity::POSITIVE : Severity::NEGATIVE, // общий “скрининг-статус”
            subscales: $scores['subscales'],
            cutoffsHit: [
                'probable_ptsd' => $probablePtsd ? Severity::POSITIVE : Severity::NEGATIVE,
                'total_cutoff'  => $aboveCutoff ? Severity::POSITIVE : Severity::NEGATIVE,
                'clusters_rule' => $clustersMet ? Severity::POSITIVE : Severity::NEGATIVE,
                'band'          => $band,
            ],
            redFlags: $flags,
            recommendations: $recs,
            narrative: $text,
            meta: [
                'max_total' => 80,
                'cutoff_total' => $cutoffTotal,
                'symptom_threshold' => $symptomThreshold,
                'probable_logic' => $useOrLogic ? 'OR' : 'AND',
                'symptom_counts' => [
                    'B' => $countB,
                    'C' => $countC,
                    'D' => $countD,
                    'E' => $countE,
                ],
                'clusters_requirements' => [
                    'B' => '>=1',
                    'C' => '>=1',
                    'D' => '>=2',
                    'E' => '>=2',
                ],
            ],
        );
    }

    private function sumKeys(array $answers, array $keys): int
    {
        $sum = 0;
        foreach ($keys as $k) $sum += (int)$answers[$k];
        return $sum;
    }

    private function countAtLeast(array $answers, array $keys, int $threshold): int
    {
        $c = 0;
        foreach ($keys as $k) {
            if ((int)$answers[$k] >= $threshold) $c++;
        }
        return $c;
    }

    private function severityBand(int $total): string
    {
        // Условные диапазоны для UX (можешь легко подкрутить)
        if ($total <= 10) return 'minimal';
        if ($total <= 20) return 'mild';
        if ($total <= 40) return 'moderate';
        if ($total <= 60) return 'high';
        return 'very_high';
    }
}
