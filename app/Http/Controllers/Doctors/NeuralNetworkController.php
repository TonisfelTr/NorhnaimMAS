<?php

namespace App\Http\Controllers\Doctors;

use App\Http\Controllers\Controller;
use App\Services\AnamnesisAnalyzeService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class NeuralNetworkController extends Controller
{
    public function analyzeAnamnesis(Request $request)
    {
        $data = $request->validate([
            'text' => ['required','string','min:3'],
        ]);

        $text = trim($data['text']);

        $serviceResult = app(AnamnesisAnalyzeService::class)->process(mb_strtolower($text));

        if (!$serviceResult || isset($serviceResult['error'])) {
            return response()->json([
                'summary' => null,
                'flags'   => ['Ошибка анализа'],
                'diagnoses' => [],
                'raw'     => $serviceResult,
            ], 500);
        }

        $primary      = Arr::get($serviceResult, 'primary_diagnosis');
        $differential = Arr::get($serviceResult, 'differential', []);
        $matched      = Arr::get($serviceResult, 'matched_symptoms', []);
        $warnings     = Arr::get($serviceResult, 'warnings', []);

        $symShort = collect($matched)->take(6)->implode(', ');
        $summary  = $primary
            ? sprintf(
                'Предположительно: %s (%s%s). Ключевые симптомы: %s.',
                Arr::get($primary, 'title', 'без названия'),
                Arr::get($primary, 'code', '—'),
                Arr::has($primary, 'score') ? ', ' . Arr::get($primary, 'score') . '%': '',
                $symShort ?: '—'
            )
            : ($symShort ? "Ключевые симптомы: $symShort." : 'Сводка не получена.');

        $flags = collect($warnings);
        $lt = mb_strtolower($text);
        foreach (['суицид','самоповрежд','галлюцинац','бред','агресс'] as $trigger) {
            if (str_contains($lt, $trigger)) {
                $flags->push("Найден индикатор риска: «{$trigger}»");
            }
        }
        $flags = $flags->unique()->values();

        $diagnoses = collect();
        if ($primary)   $diagnoses->push([
            'code'  => Arr::get($primary, 'code'),
            'title' => Arr::get($primary, 'title'),
            'score' => Arr::get($primary, 'score'),
        ]);
        foreach ($differential as $d) {
            $diagnoses->push([
                'code'  => Arr::get($d, 'code'),
                'title' => Arr::get($d, 'title'),
                'score' => Arr::get($d, 'score'),
            ]);
        }

        return response()->json([
            'summary'   => $summary,
            'flags'     => $flags,
            'diagnoses' => $diagnoses,
            'raw'       => $serviceResult,
        ]);
    }
}
