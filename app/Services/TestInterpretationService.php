<?php

namespace App\Services;

use App\Models\TestSession;
use Illuminate\Support\Collection;
use App\Helpers\Interpreters\DTO\TestResult as InterpretedTestResult;
use App\Helpers\Interpreters\Support\InterpreterRegistry;

class TestInterpretationService
{
    private function hasRegisteredInterpreter(string $code): bool
    {
        return array_key_exists($code, InterpreterRegistry::map());
    }

    private function buildInterpreterAnswers(TestSession $session, string $code): array
    {
        return match ($code) {
            'PCL5', 'PCL-5' => $this->buildSequentialAnswers($session),
            default => $this->buildSequentialAnswers($session),
        };
    }

    private function buildSequentialAnswers(TestSession $session): array
    {
        $orderedItems = $session->test->sections
            ->sortBy(fn($section) => (int)($section->sort_order ?? 0))
            ->flatMap(function ($section) {
                return $section->items->sortBy(fn($item) => (int)($item->sort_order ?? 0));
            })
            ->values();

        $responsesByItem = $session->responses->keyBy('item_id');

        $answers = [];

        foreach ($orderedItems as $index => $item) {
            $answers['q' . ($index + 1)] = (int)($responsesByItem[$item->id]?->option?->value ?? 0);
        }

        return $answers;
    }

    /**
     * Возвращает готовую структуру для вывода в results.blade.php
     *
     * [
     *   'title' => 'Интерпретация',
     *   'badge' => ['text' => 'умеренная', 'class' => 'warning'],
     *   'text'  => '....',
     *   'items' => ['...', '...'],
     * ]
     */
    public function build(TestSession $session): array|InterpretedTestResult
    {
        $session->loadMissing([
            'test:id,code,name',
            'test.sections.items:id,test_section_id,sort_order',
            'responses.option:id,value',
            'results.key:id,title',
        ]);

        $code = (string)($session->test?->code ?? '');

        // Сначала пробуем новый интерпретатор из реестра
        if ($this->hasRegisteredInterpreter($code)) {
            try {
                $interpreter = InterpreterRegistry::make($code);
                $answers = $this->buildInterpreterAnswers($session, $code);

                return $interpreter->interpret($answers, [
                    'session_id' => $session->id,
                    'test_id' => $session->test_id,
                ]);
            } catch (\Throwable $e) {
                report($e);
                // fallback на старую логику ниже
            }
        }

        // Старый fallback
        $results = $session->results instanceof Collection ? $session->results : collect();

        $total = (int) optional($results->firstWhere('key_id', null))->score;

        $byKey = $results
            ->filter(fn($r) => !is_null($r->key_id))
            ->mapWithKeys(fn($r) => [(int)$r->key_id => (int)$r->score]);

        return match ($code) {
            'PHQ9'     => $this->phq9($total),
            'GAD7'     => $this->gad7($total),
            'HADS'     => $this->hads($session, $byKey, $total),
            'PCL5', 'PCL-5' => $this->pcl5($total),
            'ASRS'     => $this->asrs($session, $byKey, $total),
            'EPDS'     => $this->epds($total),
            'CAGEAID'  => $this->cageAid($total),
            'DAST10'   => $this->dast10($total),
            'AUDITC'   => $this->auditC($total),
            'SDS'      => $this->zungSds($total),
            'SAS'      => $this->zungSas($total),
            'BDI2'     => $this->bdi2($total),
            'BAI'      => $this->bai($total),
            'K10'      => $this->k10($total),
            'PSS10'    => $this->pss10($total),
            'WHO5'     => $this->who5($total),
            'PCPTSD5'  => $this->pcptsd5($total),
            'SCOFF'    => $this->scoff($total),
            'RORSCHACH'=> $this->projective('Тест Роршаха'),
            'PICAS4'   => $this->projective('Пикториальные ассоциации (4 картинки)'),
            default    => $this->notConfigured($code),
        };
    }

    // ---------------- helpers ----------------

    private function badge(string $text, string $class): array
    {
        // class: success|warning|danger|secondary|info
        return ['text' => $text, 'class' => $class];
    }

    private function notConfigured(string $code): array
    {
        return [
            'title' => 'Трактовка',
            'badge' => $this->badge('не настроено', 'secondary'),
            'text'  => "Интерпретация для теста ({$code}) пока не настроена.",
            'items' => [],
        ];
    }

    private function projective(string $name): array
    {
        return [
            'title' => 'Трактовка',
            'badge' => $this->badge('требует специалиста', 'info'),
            'text'  => "{$name}: автоматическая интерпретация не применяется. Оценка выполняется специалистом по методике и нормам.",
            'items' => [
                'Рекомендуется экспертная интерпретация клиницистом/психологом.',
                'Можно добавить рубрики/кодирование (у тебя это уже предусмотрено моделями open responses).',
            ],
        ];
    }

    // ---------------- interpretations ----------------

    private function phq9(int $score): array
    {
        // 0–4 minimal, 5–9 mild, 10–14 moderate, 15–19 moderately severe, 20–27 severe :contentReference[oaicite:0]{index=0}
        return match (true) {
            $score <= 4  => ['title'=>'Трактовка', 'badge'=>$this->badge('минимальная', 'success'), 'text'=>"Суммарный балл PHQ-9: {$score}. Минимальная выраженность симптомов.", 'items'=>[]],
            $score <= 9  => ['title'=>'Трактовка', 'badge'=>$this->badge('лёгкая', 'info'), 'text'=>"Суммарный балл PHQ-9: {$score}. Лёгкая выраженность.", 'items'=>[]],
            $score <= 14 => ['title'=>'Трактовка', 'badge'=>$this->badge('умеренная', 'warning'), 'text'=>"Суммарный балл PHQ-9: {$score}. Умеренная выраженность.", 'items'=>[]],
            $score <= 19 => ['title'=>'Трактовка', 'badge'=>$this->badge('умеренно-тяжёлая', 'warning'), 'text'=>"Суммарный балл PHQ-9: {$score}. Умеренно-тяжёлая выраженность.", 'items'=>[]],
            default      => ['title'=>'Трактовка', 'badge'=>$this->badge('тяжёлая', 'danger'), 'text'=>"Суммарный балл PHQ-9: {$score}. Тяжёлая выраженность.", 'items'=>[]],
        };
    }

    private function gad7(int $score): array
    {
        // 5/10/15 cutoffs mild/moderate/severe :contentReference[oaicite:1]{index=1}
        return match (true) {
            $score <= 4  => ['title'=>'Трактовка', 'badge'=>$this->badge('минимальная', 'success'), 'text'=>"Суммарный балл GAD-7: {$score}. Минимальная выраженность тревоги.", 'items'=>[]],
            $score <= 9  => ['title'=>'Трактовка', 'badge'=>$this->badge('лёгкая', 'info'), 'text'=>"Суммарный балл GAD-7: {$score}. Лёгкая тревога.", 'items'=>[]],
            $score <= 14 => ['title'=>'Трактовка', 'badge'=>$this->badge('умеренная', 'warning'), 'text'=>"Суммарный балл GAD-7: {$score}. Умеренная тревога.", 'items'=>[]],
            default      => ['title'=>'Трактовка', 'badge'=>$this->badge('тяжёлая', 'danger'), 'text'=>"Суммарный балл GAD-7: {$score}. Высокая/тяжёлая тревога.", 'items'=>[]],
        };
    }

    private function hads(TestSession $session, Collection $byKey, int $total): array
    {
        // HADS обычно 2 шкалы: тревога/депрессия, каждая 0–21
        // Cutoffs: 0–7 normal, 8–10 borderline, 11–21 abnormal :contentReference[oaicite:2]{index=2}
        $session->loadMissing(['results.key:id,title']);

        $parts = [];
        foreach ($session->results->filter(fn($r)=>!is_null($r->key_id)) as $r) {
            $title = (string)($r->key?->title ?? 'Шкала');
            $score = (int)$r->score;

            $level = match (true) {
                $score <= 7  => ['норма', 'success'],
                $score <= 10 => ['погранично', 'warning'],
                default      => ['клинически значимо', 'danger'],
            };

            $parts[] = "{$title}: {$score} ({$level[0]})";
        }

        return [
            'title' => 'Трактовка',
            'badge' => $this->badge('алгоритм', 'secondary'),
            'text'  => 'HADS интерпретируется по двум шкалам. ' . (count($parts) ? 'Результаты: ' . implode('; ', $parts) . '.' : "Суммарный балл: {$total}."),
            'items' => [
                '0–7 — норма, 8–10 — пограничные значения, 11+ — клинически значимые проявления.',
            ],
        ];
    }

    private function pcl5(int $score): array
    {
        // Часто используют порог 31–33 для вероятного PTSD (скрининг), зависит от контекста :contentReference[oaicite:3]{index=3}
        return match (true) {
            $score < 31 => [
                'title'=>'Трактовка',
                'badge'=>$this->badge('ниже порога', 'success'),
                'text'=>"Суммарный балл PCL-5: {$score}. Ниже типичного порога скрининга.",
                'items'=>['Порог зависит от клинического контекста и цели скрининга.'],
            ],
            $score <= 33 => [
                'title'=>'Трактовка',
                'badge'=>$this->badge('погранично', 'warning'),
                'text'=>"Суммарный балл PCL-5: {$score}. Близко к пороговым значениям.",
                'items'=>['Рекомендуется клиническая оценка симптомов.'],
            ],
            default => [
                'title'=>'Трактовка',
                'badge'=>$this->badge('выше порога', 'danger'),
                'text'=>"Суммарный балл PCL-5: {$score}. Выше типичного порога скрининга.",
                'items'=>['Это скрининг, не диагноз. Нужна клиническая верификация.'],
            ],
        };
    }

    private function asrs(TestSession $session, Collection $byKey, int $total): array
    {
        // ASRS v1.1: обычно Part A (6 вопросов) — если ≥4 “критичных” отметок, вероятен ADHD :contentReference[oaicite:4]{index=4}
        // Но у тебя scoring идёт суммой value (как задано options.value). Поэтому делаем интерпретацию “по сумме” нейтрально.
        return [
            'title' => 'Трактовка',
            'badge' => $this->badge('алгоритм', 'secondary'),
            'text'  => "ASRS: суммарный балл (по весам вариантов): {$total}.",
            'items' => [
                'Если ты хочешь “классическое” правило Part A (≥4), нужно считать не сумму value, а количество ответов, попадающих в пороговые зоны Part A.',
                'Сейчас трактовка по сумме зависит от того, какие value заданы в test_item_options.',
            ],
        ];
    }

    private function epds(int $score): array
    {
        // Часто используют пороги 10 и 13, зависит от цели скрининга :contentReference[oaicite:5]{index=5}
        return match (true) {
            $score < 10 => ['title'=>'Трактовка','badge'=>$this->badge('ниже порога', 'success'),'text'=>"EPDS: {$score}. Обычно ниже пороговых значений скрининга.",'items'=>[]],
            $score < 13 => ['title'=>'Трактовка','badge'=>$this->badge('повышен', 'warning'),'text'=>"EPDS: {$score}. Повышенный уровень симптомов — возможна лёгкая/умеренная выраженность.",'items'=>['Порог зависит от политики учреждения и контекста.']],
            default => ['title'=>'Трактовка','badge'=>$this->badge('высокий риск', 'danger'),'text'=>"EPDS: {$score}. Выше часто используемых порогов скрининга.",'items'=>['Рекомендуется клиническая оценка.']],
        };
    }

    private function cageAid(int $score): array
    {
        // CAGE-AID: обычно ≥2 — положительный скрининг :contentReference[oaicite:6]{index=6}
        return match (true) {
            $score <= 1 => ['title'=>'Трактовка','badge'=>$this->badge('отрицательный', 'success'),'text'=>"CAGE-AID: {$score}. Скрининг чаще интерпретируется как отрицательный.",'items'=>[]],
            default     => ['title'=>'Трактовка','badge'=>$this->badge('положительный', 'danger'),'text'=>"CAGE-AID: {$score}. Положительный скрининг (часто используют порог ≥2).",'items'=>['Рекомендуется уточняющая оценка/анамнез.']],
        };
    }

    private function dast10(int $score): array
    {
        // DAST-10 ranges часто: 0 none, 1–2 low, 3–5 moderate, 6–8 substantial, 9–10 severe :contentReference[oaicite:7]{index=7}
        return match (true) {
            $score === 0 => ['title'=>'Трактовка','badge'=>$this->badge('нет признаков', 'success'),'text'=>"DAST-10: {$score}. Признаков проблемного употребления не выявлено по скринингу.",'items'=>[]],
            $score <= 2  => ['title'=>'Трактовка','badge'=>$this->badge('низкий', 'info'),'text'=>"DAST-10: {$score}. Низкий уровень проблем.",'items'=>[]],
            $score <= 5  => ['title'=>'Трактовка','badge'=>$this->badge('умеренный', 'warning'),'text'=>"DAST-10: {$score}. Умеренный уровень проблем.",'items'=>[]],
            $score <= 8  => ['title'=>'Трактовка','badge'=>$this->badge('выраженный', 'warning'),'text'=>"DAST-10: {$score}. Выраженный уровень проблем.",'items'=>[]],
            default      => ['title'=>'Трактовка','badge'=>$this->badge('тяжёлый', 'danger'),'text'=>"DAST-10: {$score}. Тяжёлый уровень проблем.",'items'=>[]],
        };
    }

    private function auditC(int $score): array
    {
        // AUDIT-C пороги различаются по полу/популяции; часто: ≥4 мужчины, ≥3 женщины :contentReference[oaicite:8]{index=8}
        return [
            'title' => 'Трактовка',
            'badge' => $this->badge('скрининг', 'secondary'),
            'text'  => "AUDIT-C: {$score}. Порог зависит от пола и клинического контекста (часто используют ≥4 для мужчин и ≥3 для женщин).",
            'items' => [
                'Это скрининг, не диагноз.',
            ],
        ];
    }

    private function zungSds(int $rawScore): array
    {
        // Zung SDS часто считают индексом (raw*1.25). Пороги по индексу: 50/60/70 :contentReference[oaicite:9]{index=9}
        $index = (int) round($rawScore * 1.25);
        return match (true) {
            $index < 50 => ['title'=>'Трактовка','badge'=>$this->badge('норма', 'success'),'text'=>"Zung SDS: raw={$rawScore}, индекс≈{$index}. Обычно в пределах нормы.",'items'=>[]],
            $index < 60 => ['title'=>'Трактовка','badge'=>$this->badge('лёгкая', 'info'),'text'=>"Zung SDS: raw={$rawScore}, индекс≈{$index}. Лёгкая выраженность.",'items'=>[]],
            $index < 70 => ['title'=>'Трактовка','badge'=>$this->badge('умеренная', 'warning'),'text'=>"Zung SDS: raw={$rawScore}, индекс≈{$index}. Умеренная выраженность.",'items'=>[]],
            default     => ['title'=>'Трактовка','badge'=>$this->badge('тяжёлая', 'danger'),'text'=>"Zung SDS: raw={$rawScore}, индекс≈{$index}. Тяжёлая выраженность.",'items'=>[]],
        };
    }

    private function zungSas(int $rawScore): array
    {
        // Zung SAS также индекс raw*1.25, пороги встречаются 45/60/75
        $index = (int) round($rawScore * 1.25);
        return match (true) {
            $index < 45 => ['title'=>'Трактовка','badge'=>$this->badge('норма', 'success'),'text'=>"Zung SAS: raw={$rawScore}, индекс≈{$index}. Обычно в пределах нормы.",'items'=>[]],
            $index < 60 => ['title'=>'Трактовка','badge'=>$this->badge('лёгкая', 'info'),'text'=>"Zung SAS: raw={$rawScore}, индекс≈{$index}. Лёгкая тревога.",'items'=>[]],
            $index < 75 => ['title'=>'Трактовка','badge'=>$this->badge('умеренная', 'warning'),'text'=>"Zung SAS: raw={$rawScore}, индекс≈{$index}. Умеренная тревога.",'items'=>[]],
            default     => ['title'=>'Трактовка','badge'=>$this->badge('тяжёлая', 'danger'),'text'=>"Zung SAS: raw={$rawScore}, индекс≈{$index}. Высокая тревога.",'items'=>[]],
        };
    }

    private function bdi2(int $score): array
    {
        // 0–13 minimal, 14–19 mild, 20–28 moderate, 29–63 severe :contentReference[oaicite:11]{index=11}
        return match (true) {
            $score <= 13 => ['title'=>'Трактовка','badge'=>$this->badge('минимальная', 'success'),'text'=>"BDI-II: {$score}. Минимальная выраженность.",'items'=>[]],
            $score <= 19 => ['title'=>'Трактовка','badge'=>$this->badge('лёгкая', 'info'),'text'=>"BDI-II: {$score}. Лёгкая выраженность.",'items'=>[]],
            $score <= 28 => ['title'=>'Трактовка','badge'=>$this->badge('умеренная', 'warning'),'text'=>"BDI-II: {$score}. Умеренная выраженность.",'items'=>[]],
            default      => ['title'=>'Трактовка','badge'=>$this->badge('тяжёлая', 'danger'),'text'=>"BDI-II: {$score}. Тяжёлая выраженность.",'items'=>[]],
        };
    }

    private function bai(int $score): array
    {
        // 0–7 minimal, 8–15 mild, 16–25 moderate, 26–63 severe :contentReference[oaicite:12]{index=12}
        return match (true) {
            $score <= 7  => ['title'=>'Трактовка','badge'=>$this->badge('минимальная', 'success'),'text'=>"BAI: {$score}. Минимальная тревога.",'items'=>[]],
            $score <= 15 => ['title'=>'Трактовка','badge'=>$this->badge('лёгкая', 'info'),'text'=>"BAI: {$score}. Лёгкая тревога.",'items'=>[]],
            $score <= 25 => ['title'=>'Трактовка','badge'=>$this->badge('умеренная', 'warning'),'text'=>"BAI: {$score}. Умеренная тревога.",'items'=>[]],
            default      => ['title'=>'Трактовка','badge'=>$this->badge('тяжёлая', 'danger'),'text'=>"BAI: {$score}. Тяжёлая тревога.",'items'=>[]],
        };
    }

    private function k10(int $score): array
    {
        // ranges 10–15 low, 16–21 moderate, 22–29 high, 30–50 very high :contentReference[oaicite:13]{index=13}
        return match (true) {
            $score <= 15 => ['title'=>'Трактовка','badge'=>$this->badge('низкий', 'success'),'text'=>"K10: {$score}. Низкий уровень дистресса.",'items'=>[]],
            $score <= 21 => ['title'=>'Трактовка','badge'=>$this->badge('умеренный', 'info'),'text'=>"K10: {$score}. Умеренный уровень дистресса.",'items'=>[]],
            $score <= 29 => ['title'=>'Трактовка','badge'=>$this->badge('высокий', 'warning'),'text'=>"K10: {$score}. Высокий уровень дистресса.",'items'=>[]],
            default      => ['title'=>'Трактовка','badge'=>$this->badge('очень высокий', 'danger'),'text'=>"K10: {$score}. Очень высокий уровень дистресса.",'items'=>[]],
        };
    }

    private function pss10(int $score): array
    {
        // часто используют: 0–13 low, 14–26 moderate, 27–40 high :contentReference[oaicite:14]{index=14}
        return match (true) {
            $score <= 13 => ['title'=>'Трактовка','badge'=>$this->badge('низкий', 'success'),'text'=>"PSS-10: {$score}. Низкий уровень воспринимаемого стресса.",'items'=>[]],
            $score <= 26 => ['title'=>'Трактовка','badge'=>$this->badge('умеренный', 'warning'),'text'=>"PSS-10: {$score}. Умеренный уровень воспринимаемого стресса.",'items'=>[]],
            default      => ['title'=>'Трактовка','badge'=>$this->badge('высокий', 'danger'),'text'=>"PSS-10: {$score}. Высокий уровень воспринимаемого стресса.",'items'=>[]],
        };
    }

    private function who5(int $score): array
    {
        // WHO-5 raw 0–25, часто используют ≤50% (≤12/13) как порог депрессии :contentReference[oaicite:15]{index=15}
        $percent = (int) round(($score / 25) * 100);
        return match (true) {
            $score >= 13 => [
                'title'=>'Трактовка',
                'badge'=>$this->badge('выше порога', 'success'),
                'text'=>"WHO-5: {$score}/25 (~{$percent}%). Обычно выше порога скрининга депрессии.",
                'items'=>[],
            ],
            default => [
                'title'=>'Трактовка',
                'badge'=>$this->badge('ниже порога', 'warning'),
                'text'=>"WHO-5: {$score}/25 (~{$percent}%). Ниже типичного порога (≤50%) — возможны признаки сниженного благополучия.",
                'items'=>['Это скрининг, не диагноз.'],
            ],
        };
    }

    private function pcptsd5(int $score): array
    {
        // PC-PTSD-5 часто: ≥3 — положительный скрининг :contentReference[oaicite:16]{index=16}
        return match (true) {
            $score < 3 => ['title'=>'Трактовка','badge'=>$this->badge('отрицательный', 'success'),'text'=>"PC-PTSD-5: {$score}. Обычно ниже порога скрининга.",'items'=>[]],
            default    => ['title'=>'Трактовка','badge'=>$this->badge('положительный', 'danger'),'text'=>"PC-PTSD-5: {$score}. Положительный скрининг (часто используют ≥3).",'items'=>['Рекомендуется уточняющая клиническая оценка.']],
        };
    }

    private function scoff(int $score): array
    {
        // SCOFF: ≥2 — положительный скрининг :contentReference[oaicite:17]{index=17}
        return match (true) {
            $score < 2 => ['title'=>'Трактовка','badge'=>$this->badge('отрицательный', 'success'),'text'=>"SCOFF: {$score}. Обычно ниже порога скрининга.",'items'=>[]],
            default    => ['title'=>'Трактовка','badge'=>$this->badge('положительный', 'danger'),'text'=>"SCOFF: {$score}. Положительный скрининг (часто используют ≥2).",'items'=>['Рекомендуется уточняющее интервью/оценка.']],
        };
    }
}
