<?php

namespace App\Services;

use App\Models\TestAnswer;
use App\Models\TestKey;
use App\Models\TestResult;
use App\Models\TestSession;

class TestScoringService
{
    public function scoreAndPersist(TestSession $session): void
    {
        // Важно: нам нужен порядок items внутри sections, чтобы item_ids в key понимались как "номер вопроса"
        $session->load(['test.sections.items.options']);

        if (!$session->test) {
            return;
        }

        // 1) item_id -> порядковый номер (1..N) по реальному порядку в тесте
        $orderedItems = $session->test->sections
            ->sortBy(fn($s) => (int)($s->order ?? 0))
            ->flatMap(fn($s) => $s->items->sortBy(fn($i) => (int)($i->order ?? 0)))
            ->values();

        $seqByItemId = [];
        foreach ($orderedItems as $idx => $item) {
            $seqByItemId[$item->id] = $idx + 1;
        }

        // 2) Загружаем ключи (шкалы) и строим seq -> key_id
        $keys = TestKey::where('test_id', $session->test_id)->get();

        $keyBySeq = []; // seq => key_id
        foreach ($keys as $key) {
            $ids = $key->item_ids;

            // item_ids может быть json в строке, либо уже массивом (в зависимости от кастов модели)
            if (is_string($ids)) {
                $ids = json_decode($ids, true);
            }
            if (!is_array($ids)) {
                $ids = [];
            }

            foreach ($ids as $seq) {
                $seq = (int)$seq;
                if ($seq > 0) {
                    $keyBySeq[$seq] = $key->id;
                }
            }
        }

        // 3) Считаем сумму по шкалам и общий итог
        $answers = TestAnswer::query()
            ->where('session_id', $session->id)
            ->with(['option:id,value']) // option.value берём из test_item_options.value
            ->get();

        $sumByKey = []; // key_id => score
        $total = 0;

        foreach ($answers as $a) {
            $val = (int)($a->option?->value ?? 0);
            $total += $val;

            $seq = (int)($seqByItemId[$a->item_id] ?? 0);
            $keyId = $seq > 0 ? ($keyBySeq[$seq] ?? null) : null;

            if ($keyId) {
                $sumByKey[$keyId] = ($sumByKey[$keyId] ?? 0) + $val;
            }
        }

        // 4) Пишем результаты по шкалам (key_id != null)
        foreach ($sumByKey as $keyId => $score) {
            TestResult::updateOrCreate(
                ['session_id' => $session->id, 'key_id' => (int)$keyId],
                [
                    'score' => (int)$score,
                    // range_text / interpretation можно не хранить здесь (будем строить в TestInterpretationService),
                    // но оставим поля, чтобы не ломать схему и на будущее:
                    'range_text' => null,
                    'interpretation' => null,
                    'meta' => null,
                ]
            );
        }

        // 5) Общий итог (key_id = null)
        TestResult::updateOrCreate(
            ['session_id' => $session->id, 'key_id' => null],
            [
                'score' => (int)$total,
                'range_text' => null,
                'interpretation' => null,
                'meta' => null,
            ]
        );
    }
}
