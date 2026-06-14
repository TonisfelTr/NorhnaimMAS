<?php

namespace App\Http\Controllers\Doctors;

use App\Http\Controllers\Controller;
use App\Models\TestAnswer;
use App\Models\TestAssignment;
use App\Models\TestOpenResponse;
use App\Models\TestSession;
use App\Services\TestScoringService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Illuminate\Validation\ValidationException;

class TestSessionController extends Controller
{
    private const MAX_SESSION_MINUTES = 60;

    /**
     * Если сессия длится слишком долго — считаем её истёкшей, завершаем и снимаем блокировку панели врача.
     * Возвращает true если сессия была завершена по таймауту (или уже неактивна).
     */
    private function enforceTimeout(TestSession $session): bool
    {
        if (!$session->in_progress) {
            return false;
        }

        $started = $session->locked_at ?? $session->created_at ?? null;
        if (!$started) {
            return false;
        }

        if (now()->diffInMinutes($started) < self::MAX_SESSION_MINUTES) {
            return false;
        }

        $session->update([
            'in_progress'  => false,
            'status'       => 'timeout',
            'completed_at' => now(),
            'locked_at'    => null,
            'locked_by'    => null,
        ]);

        if ($session->relationLoaded('assignment')) {
            $assignment = $session->assignment;
        } else {
            $assignment = $session->assignment()->first();
        }

        if ($assignment) {
            $assignment->update([
                'status'      => 'timeout',
                'finished_at' => now(),
            ]);
        }

        return true;
    }


    /**
     * Врач нажимает «Начать тест»
     * -> генерируется PIN (хэшируется)
     * -> назначение переходит в awaiting_unlock
     * -> панель будет заблокирована DoctorHardLockMiddleware до завершения/отмены
     */
    public function start(TestAssignment $assignment, Request $request): JsonResponse
    {
        $this->authorize('start', $assignment);

        if (empty($assignment->kiosk_token)) {
            $assignment->kiosk_token = Str::random(40);
        }

        $pin = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $assignment->pin_hash = password_hash($pin, PASSWORD_BCRYPT);
        $assignment->pin_expires_at = now()->addMinutes(15);
        $assignment->status = 'awaiting_unlock';
        $assignment->save();

        $patientUrl = route('doctors.patients.session.form', ['token' => $assignment->kiosk_token]);

        return response()->json([
            'ok' => true,
            'pin' => $pin,
            'token' => $assignment->kiosk_token,

            'patient_url' => $patientUrl,
            'test_url'    => $patientUrl,

            'expires_at'  => optional($assignment->pin_expires_at)->toIso8601String(),
            'expires_in'  => $assignment->pin_expires_at ? max(0, now()->diffInSeconds($assignment->pin_expires_at, false)) : null,
        ]);
    }

    /**
     * Врач вводит PIN (форма).
     * Если тест уже начат — отправляем в run.
     */
    public function form(TestAssignment $assignment): View|RedirectResponse
    {
        $this->authorize('start', $assignment);

        if ($assignment->status === 'started' && $assignment->session_id) {
            return redirect()->route('doctors.reception.tests.run', [
                'session' => $assignment->session_id
            ]);
        }

        return view('doctors.reception.tests.pin', [
            'assignment' => $assignment,
        ]);
    }

    /**
     * Проверка PIN врача.
     * После этого создаём TestSession (если нет) и переходим в run.
     */
    public function verify(TestAssignment $assignment, Request $request)
    {
        $this->authorize('start', $assignment);

        $request->validate(['pin' => 'required|string|min:4|max:6']);

        if (
            empty($assignment->pin_hash) ||
            empty($assignment->pin_expires_at) ||
            now()->greaterThan($assignment->pin_expires_at) ||
            !password_verify($request->pin, $assignment->pin_hash)
        ) {
            return back()->withErrors(['pin' => 'Неверный или истёкший PIN.']);
        }

        $testId = $assignment->test_id
            ?? data_get($assignment->context, 'test_id')
            ?? data_get($assignment->context, 'test.id');

        if (!$testId) {
            return back()->withErrors([
                'pin' => 'Назначение теста повреждено: не указан test_id. Переназначьте тест пациенту.'
            ]);
        }

        if (!$assignment->session_id) {
            $session = TestSession::create([
                'test_id'      => $assignment->test_id,
                'clinician_id' => $assignment->clinician_id,
                'patient_id'   => data_get($assignment->context, 'patient_id'),
                'context'      => array_merge((array)($assignment->context ?? []), [
                    'assignment_id' => $assignment->id,
                    'answers' => [],
                    'current_index' => 0,
                    'final_pin_ok' => false
                ]),
                'in_progress'  => true,
                'locked_by'    => auth()->user()->doctor->id,
                'locked_at' => now(),
                'status'       => 'in_progress',
            ]);

            $session->locked_at = now();
            $session->save();

            $assignment->update([
                'session_id' => $session->id,
                'status'     => 'started',
                'started_at' => $assignment->started_at ?? now(),
            ]);

            return redirect()->route('doctors.reception.tests.run', $assignment->session_id);
        } else {
            $session = TestSession::find($assignment->session_id);

            if ($session) {
                $ctx = (array)($session->context ?? []);

                // если сессия была завершена/разлочена/неактивна — начинаем заново
                if (!$session->in_progress || is_null($session->locked_at) || $session->status !== 'in_progress') {
                    $ctx['answers'] = [];
                    $ctx['current_index'] = 0;

                    // стартовый PIN НЕ должен означать финальный PIN
                    $ctx['start_pin_ok'] = true;
                    $ctx['final_pin_ok'] = false;

                    $session->context      = $ctx;
                    $session->status       = 'in_progress';
                    $session->in_progress  = true;
                    $session->started_at   = $session->started_at ?? now();
                    $session->completed_at = null;

                    $session->locked_by = auth()->user()->doctor->id;
                    $session->locked_at = now();

                    $session->save();

                    $assignment->update([
                        'started_at' => $assignment->started_at ?? now(),
                        'status'     => 'started',
                    ]);
                }
            }
        }

        return redirect()->route('doctors.reception.tests.run', $assignment->session_id);
    }

    /**
     * Изолированная страница прохождения теста (без панели врача).
     * Vue рисует «инструкция -> 1 вопрос -> следующий».
     */
    public function run(TestSession $session): View|RedirectResponse
    {
        if ($this->enforceTimeout($session)) {
            return view('doctors.reception.tests.run', compact('session'))
                ->with('warning', 'Прохождение теста было завершено автоматически (превышен лимит 60 минут).');
        }

        if ($session->in_progress) {
            return view('doctors.reception.tests.run', [
                'session' => $session,
                'isFinished' => false,
            ]);
        }

        $finalOk = (bool) data_get($session->context, 'final_pin_ok', false);

        if ($session->status === 'submitted' && !$finalOk) {
            return redirect()->route('doctors.tests.final_pin.form', $session->id);
        }

        return app(TestsController::class)->show($session);
    }

    /**

     * Payload для Vue:
     * - тест (название/описание)
     * - список вопросов (из sections.items.options)
     * - ранее сохранённые ответы (responses/openResponses + context.answers)
     */
    public function payload(TestSession $session): JsonResponse
    {
        if ($this->enforceTimeout($session)) {
            return response()->json([
                'ok' => false,
                'timed_out' => true,
                'message' => 'Сессия истекла (превышен лимит 60 минут).',
            ], 409);
        }

        $session->load(['test:id,code,name,description', 'test.sections.items.options', 'responses', 'openResponses']);

        $items = [];
        foreach ($session->test->sections as $section) {
            foreach ($section->items as $item) {
                $hasOptions = $item->options && $item->options->count() > 0;

                $items[] = [
                    'id'       => $item->id,
                    'type'     => $hasOptions ? 'radio' : 'text',
                    'title'    => (string)($item->text ?? ''),
                    'required' => true, // если у тебя есть поле required — замени
                    'help'     => null,
                    'options'  => $hasOptions
                        ? $item->options->map(fn($o) => [
                            'value' => $o->id,
                            'label' => (string)($o->label ?? ''),
                        ])->values()
                        : [],
                ];
            }
        }

        $answers = [];

        foreach ($session->responses as $resp) {
            if ($resp->item_id) {
                $answers[(string)$resp->item_id] = $resp->test_item_option_id;
            }
        }
        foreach ($session->openResponses as $open) {
            if ($open->item_id) {
                $answers[(string)$open->item_id] = $open->text;
            }
        }

        $ctx = (array)($session->context ?? []);
        $ctx['answers'] = $ctx['answers'] ?? [];

        $total = count($items);

        $ci = (int)($ctx['current_index'] ?? 0);

        if ($total <= 0) {
            $ci = 0;
        } else {
            if ($ci < 0) $ci = 0;
            if ($ci >= $total) $ci = $total - 1;
        }

        $ctx['current_index'] = $ci;
        $session->context = $ctx;
        $session->save();

        return response()->json([
            'ok' => true,
            'test' => [
                'id'          => $session->test->id,
                'code'        => $session->test->code,
                'title'       => $session->test->name,
                'description' => $session->test->description,
                'instructions' => $session->test()->first()->instructions,
            ],
            'items' => $items,
            'state' => [
                'answers'       => $answers,
                'current_index' => $ci,
            ],
            'session' => [
                'id'          => $session->id,
                'status'      => $session->status,
                'in_progress' => (bool)$session->in_progress,
                'locked_at'   => $session->locked_at ? $session->locked_at->toIso8601String() : null,
            ],
        ]);
    }

    /**
     * SUBMIT = отправка ответа на текущий вопрос.
     * Вызывается при "Следующий вопрос" и на автосохранении.
     *
     * Ожидает:
     * - item_id: ID вопроса (test_items.id)
     * - answer: для radio -> ID option (test_item_options.id), для text -> строка
     * - current_index: индекс вопроса на фронте (для восстановления прогресса)
     */
    public function submit(TestSession $session, Request $request): JsonResponse
    {
        if ($this->enforceTimeout($session)) {
            return response()->json([
                'ok' => false,
                'timed_out' => true,
                'message' => 'Сессия истекла (превышен лимит 60 минут).',
            ], 409);
        }

        abort_unless($session->in_progress == true, 403);
        abort_if($session->status === 'submitted', 409, 'Тест уже завершён');

        $data = $request->validate([
            'item_id'       => 'required|integer',
            'answer'        => 'nullable',
            'current_index' => 'required|integer|min:0',
        ]);

        $session->load(['test.sections.items.options']);

        $item = $session->test->sections
            ->flatMap(fn($s) => $s->items)
            ->firstWhere('id', (int)$data['item_id']);

        abort_unless($item, 422);

        $hasOptions = $item->options && $item->options->count() > 0;

        if ($hasOptions) {
            TestAnswer::updateOrCreate(
                ['session_id' => $session->id, 'item_id' => $item->id],
                ['option_id' => $data['answer']]
            );
        } else {
            $stimulusId = (int)($data['stimulus_id'] ?? 0);
            if ($stimulusId <= 0) {
                throw ValidationException::withMessages(['stimulus_id' => 'Не передан stimulus_id для открытого ответа.']);
            }

            $text = is_null($data['answer']) ? null : (string)$data['answer'];

            TestOpenResponse::updateOrCreate(
                ['session_id' => $session->id, 'stimulus_id' => $stimulusId, 'sequence_no' => 1],
                ['response_text' => $text]
            );

            TestAnswer::where('session_id', $session->id)
                ->where('item_id', $item->id)
                ->delete();
        }

        $ctx = (array)($session->context ?? []);
        $ctx['answers'] = $ctx['answers'] ?? [];
        $ctx['answers'][(string)$item->id] = $data['answer'];
        $ctx['current_index'] = $data['current_index'];

        $session->update(['context' => $ctx]);

        return response()->json(['ok' => true]);
    }

    /**
     * FINISH = пациент завершил тест целиком.
     * Меняем статус на submitted, чтобы врач мог финализировать в TestsController@finish.
     */
    public function finish(TestSession $session): JsonResponse
    {
        if ($this->enforceTimeout($session)) {
            return response()->json(['ok' => true, 'timed_out' => true]);
        }

        abort_unless($session->in_progress, 403);

        $session->update([
            'in_progress'  => false,
            'status'       => 'submitted',
            'completed_at' => now(),
            'locked_at'    => now(),
        ]);
        $session->assignment->finished_at = now();

        app(TestScoringService::class)->scoreAndPersist($session->fresh());

        return response()->json(['ok' => true]);
    }

    /**
     * Старый unlock по токену (совместимость).
     * Важно: started_at В БД НЕТ — убрали.
     */
    public function unlock(Request $request): JsonResponse
    {
        $data = $request->validate([
            'token'      => 'nullable|string',
            'session_id' => 'nullable|integer',
        ]);

        $token = isset($data['token']) ? trim((string)$data['token']) : null;

        if ($token) {
            $assignment = TestAssignment::query()
                ->where('kiosk_token', $token)
                ->first();

            if (!$assignment) {
                throw ValidationException::withMessages([
                    'token' => 'Сессия не найдена или ссылка устарела.',
                ]);
            }

            $testId = $assignment->test_id
                ?? data_get($assignment->context, 'test_id')
                ?? data_get($assignment->context, 'test.id');

            if (!$testId) {
                throw ValidationException::withMessages([
                    'test_id' => 'Назначение теста повреждено: не указан test_id. Переназначьте тест пациенту.',
                ]);
            }

            $patientId = $assignment->patient_id ?? data_get($assignment->context, 'patient_id');

            if (!$assignment->session_id) {
                $session = TestSession::create([
                    'test_id'      => $testId,
                    'clinician_id' => $assignment->clinician_id,
                    'patient_id'   => $patientId,
                    'context'      => array_merge((array)($assignment->context ?? []), [
                        'assignment_id' => $assignment->id,
                        'answers'       => [],
                        'current_index' => 0,
                        'final_pin_ok'  => false,
                    ]),
                    'in_progress'  => true,
                    'locked_by'    => optional(auth()->user()->doctor)->id,
                    'status'       => 'in_progress',
                ]);

                $session->locked_at = now();
                $session->save();

                $assignment->update([
                    'session_id' => $session->id,
                    'status'     => 'started',
                    'started_at' => $assignment->started_at ?? now(),
                ]);
            } else {
                $session = TestSession::query()->find($assignment->session_id);
                if ($session) {
                    $session->update([
                        'status'      => 'finished',
                        'in_progress' => false,
                        'locked_at'   => null,
                        'locked_by'   => null,
                    ]);
                }
            }

            return response()->json([
                'ok'         => true,
                'session_id' => $assignment->session_id,
                'run_url'    => $patientId ? route('doctors.patients.medical_card', $patientId) : null,
            ]);
        }

        $sessionId = $data['session_id'] ?? null;

        if (!$sessionId) {
            throw ValidationException::withMessages([
                'token' => 'Не передан токен сессии.',
            ]);
        }

        $session = TestSession::query()->find($sessionId);
        if (!$session) {
            throw ValidationException::withMessages([
                'session_id' => 'Сессия не найдена.',
            ]);
        }

        $doctorId = optional(auth()->user()->doctor)->id;
        if (!$doctorId) abort(403);

        $session->update([
            'status'      => 'finished',
            'in_progress' => false,
            'locked_at'   => null,
            'locked_by'   => null,
        ]);

        $patientId = $session->patient_id ?? data_get($session->context, 'patient_id');

        return response()->json([
            'ok'         => true,
            'session_id' => $session->id,
            'run_url'    => $patientId ? route('doctors.patients.medical_card', $patientId) : null,
        ]);
    }

    public function finalPinForm(TestSession $session)
    {
        abort_unless($session->locked_by === auth()->user()->doctor->id, 403);

        if ($this->enforceTimeout($session)) {
            return redirect()->route('doctors.patients.medical_card', $session->patient_id);
        }

        $finalOk = (bool) data_get($session->context, 'final_pin_ok', false);

        if ($finalOk) {
            return redirect()->route('doctors.patients.sessions.show', $session->id)->with([
                'session' => $session,
            ]);
        }

        if ($session->status !== 'submitted') {
            return redirect()->route('doctors.reception.tests.run', $session->id);
        }

        return view('doctors.reception.tests.final_pin', compact('session'));
    }

    public function finalPinVerify(Request $request, TestSession $session)
    {
        abort_unless($session->locked_by === auth()->user()->doctor->id, 403);

        if (!$request->filled('pin')) {
            return redirect()->route('doctors.tests.final_pin.form', $session->id);
        }

        $request->validate(['pin' => 'required|string|min:4|max:6']);

        $assignment = TestAssignment::query()
            ->where('session_id', $session->id)
            ->first();

        if (!$assignment) {
            $assignmentId = data_get($session->context, 'assignment_id');
            $assignment = $assignmentId ? TestAssignment::find($assignmentId) : null;
        }

        if (!$assignment || empty($assignment->pin_hash) || empty($assignment->pin_expires_at)) {
            return back()->withErrors(['pin' => 'Не найден PIN для проверки. Начните тест заново.']);
        }

        if (now()->greaterThan($assignment->pin_expires_at) || !password_verify($request->pin, $assignment->pin_hash)) {
            return back()->withErrors(['pin' => 'Неверный или истёкший PIN.']);
        }

        $ctx = (array) ($session->context ?? []);
        $ctx['final_pin_ok'] = true;
        $session->context = $ctx;

        $session->locked_by = auth()->user()->doctor->id;
        $session->locked_at = now();
        $session->save();

        return redirect()->route('doctors.patients.sessions.show', $session->id);
    }
}
