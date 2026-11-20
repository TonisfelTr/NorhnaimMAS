<?php

namespace App\Http\Controllers\Doctors;

use App\Helpers\Interpreters\DTO\TestResult;
use App\Helpers\Interpreters\Support\Severity;
use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\TestAssignment;
use App\Services\TestInterpretationService;
use App\Services\Tests\TestResultViewDataService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Models\TestAnswer;
use App\Models\Test;
use App\Models\TestItem;
use App\Models\TestItemOption;
use App\Models\TestOpenResponse;
use App\Models\TestResponseCode;
use App\Models\TestRubric;
use App\Models\TestRubricScore;
use App\Models\TestSession;
use App\Models\TestStimulus;
use App\Services\TestScoringService;
use App\Services\QualitativeScoringService;

class TestsController extends Controller
{
    private function guardNotPatientSelfSession(TestSession $session): void
    {
        abort_if(
            data_get($session->context, 'source') === 'patient_self',
            404
        );
    }

    private function buildSectionsViewData(TestSession $session): array
    {
        /*
         * Для отображения ответа читаем label прямым JOIN из БД.
         *
         * Eloquent-связь TestAnswer::option() в некоторых старых сессиях
         * возвращала числовое значение ответа вместо подписи варианта.
         * Прямой JOIN гарантирует, что на странице врача будет показан
         * именно test_item_options.label, а не test_item_options.value.
         */
        $answersByItem = DB::table('test_answers as ta')
            ->leftJoin('test_item_options as tio', 'tio.id', '=', 'ta.option_id')
            ->where('ta.session_id', $session->id)
            ->orderBy('ta.id')
            ->get([
                'ta.item_id',
                'ta.option_id',
                'ta.value as answer_value',
                'ta.raw as answer_raw',
                'tio.label as option_label',
            ])
            ->mapWithKeys(function ($response): array {
                $answer = $response->option_label;

                // Для открытых/нестандартных ответов сохраняем исходный текст.
                if (($answer === null || trim((string) $answer) === '')
                    && $response->answer_raw !== null) {
                    $answer = is_string($response->answer_raw)
                        ? $response->answer_raw
                        : json_encode(
                            $response->answer_raw,
                            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                        );
                }

                // Значение value — только последний fallback, когда текстового
                // варианта и raw-ответа действительно нет.
                if (($answer === null || trim((string) $answer) === '')
                    && $response->answer_value !== null) {
                    $answer = (string) $response->answer_value;
                }

                return [(string) $response->item_id => $answer];
            });

        // Не полагаемся на Test::items(): в старой модели такой relation отсутствует.
        // test_items.test_id — источник вопросов; section_id может быть NULL.
        $items = TestItem::query()
            ->where('test_id', $session->test_id)
            ->orderBy('order')
            ->orderBy('id')
            ->get();

        $sections = $session->test->sections
            ->sortBy('order')
            ->values();

        $result = [];

        foreach ($sections as $section) {
            $sectionItems = $items->where('section_id', $section->id)->values();

            if ($sectionItems->isEmpty()) {
                continue;
            }

            $result[] = [
                'title' => (string) ($section->title ?: 'Раздел'),
                'items' => $sectionItems
                    ->map(function ($item) use ($answersByItem): array {
                        return [
                            'id' => $item->id,
                            'text' => (string) ($item->text ?? ''),
                            'answer' => $answersByItem->get((string) $item->id),
                        ];
                    })
                    ->all(),
            ];
        }

        $knownSectionIds = $sections->pluck('id')->map(
            static fn ($id): int => (int) $id
        )->all();

        $unassignedItems = $items
            ->reject(static function ($item) use ($knownSectionIds): bool {
                return $item->section_id !== null
                    && in_array((int) $item->section_id, $knownSectionIds, true);
            })
            ->values();

        if ($unassignedItems->isNotEmpty()) {
            $result[] = [
                'title' => 'Вопросы',
                'items' => $unassignedItems
                    ->map(function ($item) use ($answersByItem): array {
                        return [
                            'id' => $item->id,
                            'text' => (string) ($item->text ?? ''),
                            'answer' => $answersByItem->get((string) $item->id),
                        ];
                    })
                    ->all(),
            ];
        }

        return $result;
    }

    private function calculateProgress(array $sections): array
    {
        $total = 0;
        $answered = 0;

        foreach ($sections as $section) {
            foreach (($section['items'] ?? []) as $item) {
                $total++;
                if (!empty($item['answer'])) {
                    $answered++;
                }
            }
        }

        return [$total, $answered];
    }

    private function buildChartViewData(TestSession $session): array
    {
        return $session->results
            ->filter(fn($r) => !is_null($r->key_id))
            ->map(fn($r) => [
                'label' => $r->key?->title ?? 'Шкала',
                'value' => (int)$r->score,
            ])
            ->values()
            ->all();
    }

    private function buildOverviewViewData(TestSession $session, array $interpretation): array
    {
        $results = $this->resultsCollection($session);
        $main = $results->first(fn($r) => is_null($r->key_id)) ?? $results->first();

        $maxScore = $this->detectMaxScore($session, $main, $interpretation);
        $scoreMain = $main ? (int)$main->score : 0;
        $percentMain = $maxScore
            ? max(0, min(100, (int)round($scoreMain / $maxScore * 100)))
            : null;

        return [
            'score' => $scoreMain,
            'max' => $maxScore,
            'percent' => $percentMain,
        ];
    }

    private function buildResultCardsViewData(TestSession $session, array $interpretation): array
    {
        $results = $this->resultsCollection($session);
        $main = $results->first(fn($r) => is_null($r->key_id)) ?? $results->first();
        $maxScore = $this->detectMaxScore($session, $main, $interpretation);
        $testCode = (string)($session->test->code ?? '');

        $scaleResults = $results->filter(fn($r) => !is_null($r->key_id));

        // Если есть только одна шкала и она совпадает с общим итогом — убираем дубль
        if ($main && $scaleResults->count() === 1) {
            $singleScale = $scaleResults->first();

            if ((int)$singleScale->score === (int)$main->score) {
                $results = $results
                    ->reject(fn($r) => !is_null($r->key_id) && $r->id === $singleScale->id)
                    ->values();
            }
        }

        return $results->map(function ($r) use ($maxScore, $testCode) {
            $score = (int)$r->score;
            $min = 0;
            $max = null;

            if (!empty($r->range_text)) {
                [$minTmp, $maxTmp] = $this->parseRange($r->range_text);
                if ($minTmp !== null) {
                    $min = $minTmp;
                }
                if ($maxTmp !== null) {
                    $max = $maxTmp;
                }
            }

            if (is_null($max) && is_null($r->key_id) && $maxScore) {
                $max = $maxScore;
            }

            $percent = $max !== null
                ? max(0, min(100, (int)round((($score - $min) / max(1, ($max - $min))) * 100)))
                : null;

            return [
                'label' => $r->key_id
                    ? ($r->key?->title ?? 'Шкала')
                    : ('Суммарный балл ' . $testCode),
                'score' => $score,
                'range_text' => !empty($r->range_text)
                    ? $r->range_text
                    : ((is_null($r->key_id) && $maxScore) ? ('0-' . $maxScore) : null),
                'percent' => $percent,
            ];
        })->values()->all();
    }

    private function normalizeInterpretationForView($interpretation, ?string $testCode = null): array
    {
        if (is_array($interpretation)) {
            if (
                empty($interpretation['meta']['max_total'])
                && in_array((string)$testCode, ['PCL-5', 'PCL5'], true)
            ) {
                $interpretation['meta']['max_total'] = 80;
            }

            return array_merge([
                'title' => 'Трактовка',
                'badge' => null,
                'text' => '',
                'items' => [],
                'meta' => [],
            ], $interpretation);
        }

        if (!$interpretation instanceof TestResult) {
            return [
                'title' => 'Трактовка',
                'badge' => null,
                'text' => '',
                'items' => [],
                'meta' => [],
            ];
        }

        $probable = false;
        $probableFlag = $interpretation->cutoffsHit['probable_ptsd'] ?? null;

        if ($probableFlag instanceof Severity) {
            $probable = $probableFlag === Severity::POSITIVE;
        } elseif (is_string($probableFlag)) {
            $probable = mb_strtolower($probableFlag) === 'positive';
        } elseif (is_bool($probableFlag)) {
            $probable = $probableFlag;
        }

        $meta = is_array($interpretation->meta ?? null) ? $interpretation->meta : [];

        if (
            empty($meta['max_total'])
            && in_array((string)$testCode, ['PCL-5', 'PCL5'], true)
        ) {
            $meta['max_total'] = 80;
        }

        return [
            'title' => 'Трактовка',
            'badge' => [
                'class' => $probable ? 'danger' : 'success',
                'text' => $probable ? 'выше порога' : 'ниже порога',
            ],
            'text' => (string)($interpretation->narrative ?? ''),
            'items' => is_array($interpretation->recommendations ?? null)
                ? $interpretation->recommendations
                : [],
            'meta' => $meta,
        ];
    }

    private function detectMaxScore(TestSession $session, $mainResult, array $interpretation): ?int
    {
        if (!empty($interpretation['max'])) {
            return (int)$interpretation['max'];
        }

        if (!empty($interpretation['meta']['max_total'])) {
            return (int)$interpretation['meta']['max_total'];
        }

        if ($mainResult && !empty($mainResult->range_text)) {
            [, $maxFromRange] = $this->parseRange($mainResult->range_text);
            if ($maxFromRange !== null) {
                return $maxFromRange;
            }
        }

        $code = (string)($session->test->code ?? '');
        if (in_array($code, ['PCL-5', 'PCL5'], true)) {
            return 80;
        }

        try {
            $items = TestItem::query()
                ->where('test_id', $session->test_id)
                ->with('options')
                ->orderBy('order')
                ->orderBy('id')
                ->get();

            $questionsCount = $items->count();

            if ($questionsCount <= 0) {
                return null;
            }

            $allOptionValues = $items->flatMap(function ($item) {
                return collect($item->options ?? [])->pluck('value');
            })->filter(fn($v) => $v !== null)->map(fn($v) => (int)$v);

            if ($allOptionValues->isEmpty()) {
                return null;
            }

            $minOpt = $allOptionValues->min();
            $maxOpt = $allOptionValues->max();

            if ($minOpt === 0 && $maxOpt !== null && $maxOpt > 0) {
                return (int)$maxOpt * (int)$questionsCount;
            }
        } catch (\Throwable $e) {
            return null;
        }

        return null;
    }

    private function parseRange(?string $rangeText): array
    {
        if (!$rangeText) {
            return [null, null];
        }

        if (preg_match('/(\d+)\s*[-–—]\s*(\d+)/u', $rangeText, $matches)) {
            return [(int)$matches[1], (int)$matches[2]];
        }

        return [null, null];
    }

    private function resultsCollection(TestSession $session): Collection
    {
        return $session->results instanceof Collection
            ? $session->results
            : collect($session->results ?? []);
    }

    /** Список тестов для модалки назначения */
    public function listTests()
    {
        $tests = Cache::remember('doctor_tests_list', 3600, function(){
            return Test::select('id','code','name','description')->orderBy('name')->get();
        });

        return response()->json([
            'ok' => true,
            'tests' => $tests
        ])->header('Cache-Control', 'public, max-age=3600');
    }

    /** Список назначений (сессий) по пациенту для вкладки медкарты */
    public function listAssignments(Patient $patient)
    {
        $assignments = TestAssignment::query()
            ->with(['test:id,code,name'])
            ->where(function($q) use ($patient){
                $q->where('patient_id', $patient->id);
            })
            ->latest('created_at')
            ->limit(200)
            ->get();

        $sessionById = [];
        $sessionIds = $assignments->pluck('session_id')->filter()->unique()->values();
        if ($sessionIds->isNotEmpty()) {
            $sessions = TestSession::whereIn('id', $sessionIds)->get()->keyBy('id');
            $sessionById = $sessions;
        }

        $rows = $assignments->map(function($a) use ($sessionById){
            $s = $a->session_id ? ($sessionById[$a->session_id] ?? null) : null;

            $uiStatus = $s?->status ?? $a->status;

            return [
                'assignment_id' => $a->id,
                'session_id'    => $a->session_id,
                'test_id'       => $a->test_id,
                'test_name'     => $a->test->name ?? '',
                'code'          => $a->test->code ?? '',
                'status'        => $uiStatus,
                'assignment_status' => $a->status,
                'session_status'    => $s?->status,
                'created_at'    => optional($a->created_at)->toDateTimeString(),
                'updated_at'    => optional($a->updated_at)->toDateTimeString(),
            ];
        });

        return response()->json([
            'ok'       => true,
            'sessions' => $rows, // оставляем ключ 'sessions', чтобы не ломать фронт
        ]);
    }

    /** Назначить тест пациенту */
    public function assignTest(Patient $patient, Request $request)
    {
        $data = $request->validate(['test_id' => ['required','exists:tests,id']]);

        $assignment = TestAssignment::create([
            'test_id'      => $data['test_id'],
            'patient_id'   => $patient->id, // подставь корректное поле пользователя
            'clinician_id' => auth()->id(),
            'status'       => 'assigned',
            'context'      => ['patient_id' => $patient->id, 'assigned_by' => $request->user()->id],
        ]);

        return response()->json([
            'ok'            => true,
            'assignment_id' => $assignment->id,
            'start_url'     => route('doctors.patients.tests.start', $assignment),
        ], 201);
    }

    /** Отменить назначение */
    public function cancelSession(TestSession $session)
    {
        $this->guardNotPatientSelfSession($session);
        $session->update([
            'status'      => 'cancelled',
            'in_progress' => false,
            'locked_at'   => null,
            'locked_by'   => null,
            'completed_at'=> now(),
        ]);

        if ($session->assignment) {
            $session->assignment->update([
                'status'      => 'cancelled',
                'finished_at' => now(),
            ]);
        }

        return response()->json(['ok' => true]);
    }

    /** Краткий результат (для всплывашки) */
    public function sessionResult(TestSession $session)
    {
        $this->guardNotPatientSelfSession($session);
        $session->load(['test:id,name,code', 'results.key:id,title']);

        return response()->json([
            'ok' => true,
            'session' => [
                'id'     => $session->id,
                'test'   => $session->test?->name,
                'code'   => $session->test?->code,
                'status' => $session->status,
                'results'=> $session->results->map(fn($r) => [
                    'key'   => $r->key?->title ?? 'Итог',
                    'score' => (int)$r->score,
                    'range' => $r->range_text,
                    'text'  => $r->interpretation,
                ])->values(),
            ]
        ]);
    }

    public function show(TestSession $session)
    {
        $this->guardNotPatientSelfSession($session);

        /*
         * В старых/официальных тестах test_items.section_id может быть NULL.
         * Поэтому загружаем вопросы непосредственно через test.items,
         * а не только через test.sections.items.
         */
        $session->load([
            'test.sections',
            'test.testCards.media',
            'test.sortCards.media',
            'responses.option',
            'openResponses',
        ]);

        abort_if(!$session->test, 404, 'Тест не найден.');

        $finalOk = (bool) data_get(
            $session->context,
            'final_pin_ok',
            false
        );

        $isPatientPortalAssignment = data_get(
            $session->context,
            'source'
        ) === 'doctor_assigned_patient_portal';

        // Для авторизованного прохождения из личного кабинета PIN киоска не нужен.
        if (
            in_array(
                $session->status,
                ['submitted', 'in_review', 'complete'],
                true
            )
            && !$finalOk
            && !$isPatientPortalAssignment
        ) {
            return redirect()->route(
                'doctors.tests.final_pin.form',
                $session->id
            );
        }

        /*
         * Единый сборщик учитывает:
         * - вопросы, привязанные к разделам;
         * - вопросы без раздела (section_id IS NULL);
         * - сохранённые ответы TestAnswer.
         */
        $sections = $this->buildSectionsViewData($session);
        [$total, $answered] = $this->calculateProgress($sections);
        $missed = max(0, $total - $answered);

        $assignmentId = data_get(
            $session->context,
            'assignment_id'
        );

        $unlockToken = null;

        if ($assignmentId) {
            $unlockToken = TestAssignment::query()
                ->whereKey($assignmentId)
                ->value('kiosk_token');
        }

        $resultView = app(TestResultViewDataService::class)->make(
            session: $session,
            sections: $sections,
            stats: [
                'total' => $total,
                'answered' => $answered,
                'missed' => $missed,
            ],
        );

        return view(
            'doctors.reception.tests.session_show',
            array_merge([
                'session' => $session,
                'test' => $session->test,
                'sections' => $sections,
                'total' => $total,
                'answered' => $answered,
                'missed' => $missed,
                'unlockToken' => $unlockToken,
                'canRestart' => in_array(
                    $session->status,
                    ['submitted', 'in_progress'],
                    true
                ),
            ], $resultView)
        );
    }

    /** Кодирование ответа (врач) */
    public function saveResponseCode(TestOpenResponse $response, Request $r)
    {
        $session = $response->session;          // belongsTo
        abort_if(data_get($session?->context, 'source') === 'patient_self', 404);

        $data = $r->validate([
            'code_type'  => ['required','string','max:100'],
            'code_value' => ['required','string','max:100'],
            'score'      => ['nullable','integer','min:0','max:100'],
            'notes'      => ['nullable','string'],
        ]);

        TestResponseCode::create([
            'open_response_id' => $response->id,
            'coder_id'         => $r->user()->id,
            'code_type'        => $data['code_type'],
            'code_value'       => $data['code_value'],
            'score'            => $data['score'] ?? null,
            'notes'            => $data['notes'] ?? null,
            'meta'             => null,
        ]);

        // при первом кодировании можно пометить сессию как "in_review"
        if ($session->status === 'submitted') {
            $session->update(['status' => 'in_review']);
        }

        return response()->json(['ok'=>true]);
    }

    /** Оценка по рубрике (врач) */
    public function saveRubricScore(TestOpenResponse $response, Request $r)
    {
        $session = $response->session;
        abort_if(data_get($session?->context, 'source') === 'patient_self', 404);
        $this->authorize('update', $session);

        $data = $r->validate([
            'rubric_id' => ['required','exists:test_rubrics,id'],
            'score'     => ['required','integer','min:0','max:100'],
            'notes'     => ['nullable','string'],
        ]);

        TestRubricScore::updateOrCreate(
            ['open_response_id'=>$response->id, 'rubric_id'=>$data['rubric_id']],
            ['score'=>$data['score'], 'notes'=>$data['notes'] ?? null]
        );

        if ($session->status === 'submitted') {
            $session->update(['status' => 'in_review']);
        }

        return response()->json(['ok'=>true]);
    }

    /**
     * Финализировать: доступно только после сдачи пациентом
     * Допустимые статусы для финализации: submitted/in_review/coding_done
     */
    public function finish(TestSession $session, TestScoringService $scoring)
    {
        $this->guardNotPatientSelfSession($session);
        // Финализация врачом: снять блок панели + зафиксировать результаты
        $session->update([
            'in_progress'  => false,
            'status'       => 'finished',
            'completed_at' => now(),
            'locked_at'    => null,
            'locked_by'    => null,
        ]);

        // пересчёт и сохранение интерпретаций/результатов
        $scoring->scoreAndPersist($session->fresh());

        if ($session->assignment) {
            $session->assignment->update([
                'status' => 'completed',
                'finished_at' => $session->assignment->finished_at ?? now(),
            ]);
        }

        $patientId = $session->patient_id ?? data_get($session->context, 'patient_id');
        $redirectUrl = $patientId
            ? route('doctors.patients.medical_card', ['patient' => $patientId])
            : null;

        // если это ajax/fetch — отдай JSON с редиректом
        if (request()->expectsJson()) {
            return response()->json(['ok' => true, 'redirect' => $redirectUrl]);
        }

        // если обычная форма — редирект в карту
        return $redirectUrl ? redirect()->to($redirectUrl) : redirect()->back();
    }

    public function submit(TestSession $session, TestScoringService $scoring): JsonResponse
    {
        $this->guardNotPatientSelfSession($session);
        $session->update([
            'in_progress'  => false,
            'status'       => 'submitted',
            'completed_at' => now(),
        ]);

        // Сразу считаем результаты + интерпретации
        $scoring->scoreAndPersist($session->fresh());

        return response()->json(['ok' => true]);
    }

    public function restart(TestSession $session)
    {
        $this->guardNotPatientSelfSession($session);
        abort_unless(in_array($session->status, ['submitted','in_progress'], true), 403);

        TestAnswer::where('session_id', $session->id)->delete();

        $ctx = (array)($session->context ?? []);
        $ctx['answers'] = [];
        $ctx['current_index'] = 0;

        unset($ctx['final_pin_ok'], $ctx['final_pin_ok_at']);

        $session->update([
            'status'       => 'in_progress',
            'in_progress'  => true,
            'completed_at' => null,
            'locked_at'    => now(),
            'locked_by'    => auth()->user()->doctor->id,
            'context'      => $ctx,
        ]);

        return redirect()->route('doctors.reception.tests.run', $session->id);
    }

    public function resultsPage(TestSession $session)
    {
        $this->guardNotPatientSelfSession($session);
        $session->load([
            'test',
            'test.sections.items.options',
            'test.testCards.media',
            'responses.option:id,label,value',
            'results.key:id,title',
        ]);

        abort_if(!$session->test, 404, 'Тест не найден.');

        $testType = mb_strtolower(trim((string) ($session->test->type ?? 'questionnaire')));
        $isQuestionnaire = $testType === 'questionnaire';

        $sections = [];
        $total = 0;
        $answered = 0;

        $interpretation = [
            'title' => 'Интерпретация',
            'badge' => null,
            'text' => '',
            'items' => [],
            'meta' => [],
        ];

        $chart = [];
        $showChart = false;

        $overview = [
            'score' => 0,
            'max' => null,
            'percent' => null,
        ];

        $resultCards = [];

        if ($isQuestionnaire) {
            if ($session->results->isEmpty() || request()->boolean('recalc')) {
                $session->results()->delete();
                app(TestScoringService::class)->scoreAndPersist($session);
                $session->load(['results.key:id,title']);
            }

            $sections = $this->buildSectionsViewData($session);
            [$total, $answered] = $this->calculateProgress($sections);

            $rawInterpretation = app(TestInterpretationService::class)->build($session);
            $interpretation = $this->normalizeInterpretationForView(
                $rawInterpretation,
                (string) ($session->test->code ?? '')
            );

            $chart = $this->buildChartViewData($session);
            $showChart = count($chart) > 1;

            $overview = $this->buildOverviewViewData(
                $session,
                $interpretation
            );

            $resultCards = $this->buildResultCardsViewData(
                $session,
                $interpretation
            );
        }

        $missed = max(0, $total - $answered);

        $resultView = app(TestResultViewDataService::class)->make(
            session: $session,
            sections: $sections,
            stats: [
                'total' => $total,
                'answered' => $answered,
                'missed' => $missed,
            ],
            resultData: [
                'interpretation' => $interpretation,
                'chart' => $chart,
                'showChart' => $showChart,
                'overview' => $overview,
                'resultCards' => $resultCards,
            ],
        );

        return view(
            'doctors.reception.tests.results',
            array_merge([
                'session' => $session,
                'test' => $session->test,
                'sections' => $sections,
                'total' => $total,
                'answered' => $answered,
                'missed' => $missed,
            ], $resultView)
        );
    }
}
