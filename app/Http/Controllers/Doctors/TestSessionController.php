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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Illuminate\Validation\ValidationException;

class TestSessionController extends Controller
{
    private const DEFAULT_SESSION_MINUTES = 60;

    /**
     * Возвращает продолжительность и время окончания сессии.
     *
     * Если estimated_minutes не заполнено или равно 0,
     * используется значение по умолчанию.
     */
    private function resolveSessionTimeout(TestSession $session): array
    {
        $estimatedMinutes = (int) $session
            ->test()
            ->value('estimated_minutes');

        $minutes = $estimatedMinutes > 0
            ? $estimatedMinutes
            : self::DEFAULT_SESSION_MINUTES;

        $startedRaw = $session->locked_at
            ?? $session->created_at;

        $startedAt = $startedRaw
            ? Carbon::parse($startedRaw)
            : null;

        return [
            'minutes' => $minutes,
            'started_at' => $startedAt,
            'expires_at' => $startedAt
                ? $startedAt->copy()->addMinutes($minutes)
                : null,
        ];
    }

    /**
     * Завершает активную сессию, если истекло время,
     * заданное в tests.estimated_minutes.
     */
    private function enforceTimeout(TestSession $session): bool
    {
        if (!$session->in_progress) {
            return false;
        }

        $timeout = $this->resolveSessionTimeout($session);
        $expiresAt = $timeout['expires_at'];

        if (!$expiresAt) {
            return false;
        }

        if (now()->lt($expiresAt)) {
            return false;
        }

        DB::transaction(function () use ($session, $timeout): void {
            $context = (array) ($session->context ?? []);

            /*
             * Уже введённые ответы и sort_order сохраняются.
             */
            $context['timed_out'] = true;
            $context['timeout_minutes'] = $timeout['minutes'];
            $context['timed_out_at'] = now()->toIso8601String();

            $session->update([
                'in_progress' => false,
                'status' => 'timeout',
                'completed_at' => now(),

                /*
                 * Разблокируем панель врача.
                 */
                'locked_at' => null,
                'locked_by' => null,

                'context' => $context,
            ]);

            $assignment = $session->relationLoaded('assignment')
                ? $session->assignment
                : $session->assignment()->first();

            if ($assignment) {
                $assignment->update([
                    'status' => 'timeout',
                    'finished_at' => now(),
                ]);
            }
        });

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
                'test_id'      => $testId,
                'clinician_id' => $assignment->clinician_id,
                'patient_id'   => data_get($assignment->context, 'patient_id'),
                'context' => array_merge(
                    (array) ($assignment->context ?? []),
                    [
                        'assignment_id' => $assignment->id,
                        'answers' => [],
                        'sort_order' => [],
                        'current_index' => 0,
                        'final_pin_ok' => false,
                    ]
                ),
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
                    $ctx['sort_order'] = [];
                    $ctx['current_index'] = 0;

                    unset(
                        $ctx['timed_out'],
                        $ctx['timed_out_at'],
                        $ctx['timeout_minutes'],
                        $ctx['interrupted'],
                        $ctx['interrupted_at'],
                        $ctx['interrupted_by'],
                        $ctx['interrupted_with_pin']
                    );

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
                ->with(
                    'warning',
                    'Прохождение теста было завершено автоматически: истекло установленное время.'
                );
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
                'message' => 'Сессия истекла: превышено время прохождения теста.',
            ], 409);
        }

        $session->load([
            'test:id,code,name,type,description,instructions',

            /*
             * Обычный опросник.
             */
            'test.sections.items.options',

            /*
             * Карточки с изображением и текстовым ответом:
             * Роршах и аналогичные тесты.
             */
            'test.testCards.media',

            /*
             * Сортируемые карточки:
             * Люшер и другие тесты типа sort.
             */
            'test.sortCards.media',

            'responses',
            'openResponses',
        ]);

        $test = $session->test;
        $timeout = $this->resolveSessionTimeout($session);

        $expiresAt = $timeout['expires_at'];

        $remainingSeconds = $expiresAt
            ? max(0, now()->diffInSeconds($expiresAt, false))
            : null;

        if (!$test) {
            return response()->json([
                'ok' => false,
                'message' => 'Тест для данной сессии не найден.',
            ], 404);
        }

        /*
         * =========================================================
         * КАРТОЧКИ С ОТВЕТОМ — test_cards
         * Используются только для type = image.
         * =========================================================
         */
        $cards = [];

        if ($test->type === 'image') {
            $cards = $test->testCards
                ->sortBy(static fn ($card) => (int) $card->sort)
                ->values()
                ->map(static function ($card): array {
                    $imageUrl = $card->getFirstMediaUrl(
                        'test_card_image',
                        'front'
                    );

                    /*
                     * Если конверсия front ещё не создана,
                     * возвращаем оригинальное изображение.
                     */
                    if ($imageUrl === '') {
                        $imageUrl = $card->getFirstMediaUrl(
                            'test_card_image'
                        );
                    }

                    return [
                        'id' => (int) $card->id,
                        'title' => (string) $card->title,
                        'question' => (string) $card->question,
                        'sort' => (int) $card->sort,
                        'image_url' => $imageUrl,
                        'required' => true,
                    ];
                })
                ->all();
        }

        /*
         * =========================================================
         * СОРТИРУЕМЫЕ КАРТОЧКИ — test_sort_cards
         * Используются только для type = sort.
         *
         * Возможные типы:
         * - color
         * - text
         * - image
         * =========================================================
         */
        $sortCards = [];

        if ($test->type === 'sort') {
            $sortCards = $test->sortCards
                ->sortBy(static fn ($card) => (int) $card->sort)
                ->values()
                ->map(static function ($card): array {
                    $supportedTypes = [
                        'color',
                        'text',
                        'image',
                    ];

                    $cardType = in_array(
                        (string) $card->type,
                        $supportedTypes,
                        true
                    )
                        ? (string) $card->type
                        : 'text';

                    $imageUrl = '';

                    if ($cardType === 'image') {
                        $imageUrl = $card->getFirstMediaUrl(
                            'test_sort_card_image',
                            'front'
                        );

                        if ($imageUrl === '') {
                            $imageUrl = $card->getFirstMediaUrl(
                                'test_sort_card_image'
                            );
                        }
                    }

                    return [
                        'id' => (int) $card->id,
                        'type' => $cardType,
                        'title' => (string) $card->title,
                        'text' => (string) ($card->text ?? ''),
                        'color' => (string) ($card->color ?? ''),
                        'image_url' => $imageUrl,
                        'sort' => (int) $card->sort,
                    ];
                })
                ->all();
        }

        /*
         * =========================================================
         * ВОПРОСЫ ОБЫЧНОГО ОПРОСНИКА
         * =========================================================
         */
        $items = [];

        if ($test->type === 'questionnaire') {
            foreach ($test->sections as $section) {
                foreach ($section->items as $item) {
                    $hasOptions = $item->options
                        && $item->options->isNotEmpty();

                    $items[] = [
                        'id' => (int) $item->id,
                        'type' => $hasOptions
                            ? 'radio'
                            : 'text',

                        'title' => (string) ($item->text ?? ''),
                        'required' => true,
                        'help' => null,

                        'options' => $hasOptions
                            ? $item->options
                                ->map(static fn ($option): array => [
                                    'value' => (int) $option->id,
                                    'label' => (string) (
                                        $option->label ?? ''
                                    ),
                                ])
                                ->values()
                                ->all()
                            : [],
                    ];
                }
            }
        }

        /*
         * =========================================================
         * СОСТОЯНИЕ СЕССИИ
         * =========================================================
         */
        $ctx = (array) ($session->context ?? []);

        /*
         * answers:
         * - questionnaire — ответы на вопросы;
         * - image — ответы под карточками;
         * - sort — здесь не используется.
         */
        $answers = (array) ($ctx['answers'] ?? []);

        /*
         * Для обычного опросника восстанавливаем ответы
         * из связанных таблиц.
         */
        if ($test->type === 'questionnaire') {
            foreach ($session->responses as $response) {
                if (!$response->item_id) {
                    continue;
                }

                $answerValue =
                    $response->option_id
                    ?? $response->test_item_option_id
                    ?? null;

                $answers[(string) $response->item_id] =
                    $answerValue;
            }

            foreach ($session->openResponses as $openResponse) {
                $itemId = $openResponse->item_id ?? null;

                if (!$itemId) {
                    continue;
                }

                $answerValue =
                    $openResponse->response_text
                    ?? $openResponse->text
                    ?? null;

                $answers[(string) $itemId] =
                    $answerValue;
            }
        }

        /*
         * Восстанавливаем порядок сортировки.
         */
        $sortOrder = collect($ctx['sort_order'] ?? [])
            ->map(static fn ($id) => (int) $id)
            ->filter(static fn (int $id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        /*
         * Удаляем из sort_order идентификаторы карточек,
         * которых больше нет в данном тесте.
         */
        if ($test->type === 'sort') {
            $allowedSortCardIds = collect($sortCards)
                ->pluck('id')
                ->map(static fn ($id) => (int) $id)
                ->values();

            $sortOrder = collect($sortOrder)
                ->filter(
                    static fn (int $id) =>
                    $allowedSortCardIds->contains($id)
                )
                ->values()
                ->all();
        } else {
            $sortOrder = [];
        }

        /*
         * =========================================================
         * КОЛИЧЕСТВО ШАГОВ И ТЕКУЩИЙ ИНДЕКС
         * =========================================================
         */
        $total = match ($test->type) {
            'image' => count($cards),
            'sort' => count($sortCards),
            default => count($items),
        };

        if ($test->type === 'sort') {
            /*
             * В сортировке прогресс определяется количеством
             * уже выбранных карточек.
             *
             * Здесь допустимо current_index === total:
             * это означает, что все карточки расположены.
             */
            $currentIndex = min(
                count($sortOrder),
                $total
            );
        } else {
            $currentIndex = (int) (
                $ctx['current_index'] ?? 0
            );

            if ($total === 0) {
                $currentIndex = 0;
            } else {
                $currentIndex = max(
                    0,
                    min($currentIndex, $total - 1)
                );
            }
        }

        /*
         * Обновляем очищенное состояние сессии.
         */
        $ctx['answers'] = $answers;
        $ctx['sort_order'] = $sortOrder;
        $ctx['current_index'] = $currentIndex;

        $session->context = $ctx;
        $session->save();

        return response()->json([
            'ok' => true,

            'test' => [
                'id' => (int) $test->id,
                'code' => (string) $test->code,
                'title' => (string) $test->name,
                'type' => (string) $test->type,
                'description' => (string) (
                    $test->description ?? ''
                ),
                'instructions' => (string) (
                    $test->instructions ?? ''
                ),
                'estimated_minutes' => $timeout['minutes'],
            ],

            /*
             * Обычные вопросы.
             */
            'items' => $items,

            /*
             * Карточки с открытым ответом.
             */
            'cards' => $cards,

            /*
             * Карточки, которые пациент должен расположить.
             */
            'sort' => $sortCards,

            'state' => [
                /*
                 * Принудительно возвращаем JSON-объект {},
                 * а не пустой массив [].
                 */
                'answers' => (object) $answers,

                /*
                 * Порядок сортируемых карточек.
                 */
                'sort_order' => $sortOrder,

                'current_index' => $currentIndex,
            ],

            'session' => [
                'id' => (int) $session->id,
                'status' => (string) $session->status,
                'in_progress' => (bool) $session->in_progress,

                'locked_at' => $session->locked_at
                    ? $session->locked_at->toIso8601String()
                    : null,

                'expires_at' => $expiresAt
                    ? $expiresAt->toIso8601String()
                    : null,

                'remaining_seconds' => $remainingSeconds,
            ],
        ]);
    }

    /**
     * SUBMIT:
     *
     * questionnaire:
     * - item_id
     * - answer
     * - current_index
     *
     * image:
     * - card_id
     * - answer
     * - current_index
     *
     * sort:
     * - sort_order
     * - current_index
     */
    public function submit(
        TestSession $session,
        Request $request
    ): JsonResponse {
        if ($this->enforceTimeout($session)) {
            return response()->json([
                'ok' => false,
                'timed_out' => true,
                'message' => 'Сессия истекла: превышено время прохождения теста.',
            ], 409);
        }

        abort_unless($session->in_progress === true, 403);

        abort_if(
            $session->status === 'submitted',
            409,
            'Тест уже завершён'
        );

        $session->loadMissing([
            'test:id,type',
            'test.testCards:id,test_id',
            'test.sortCards:id,test_id',
        ]);

        $test = $session->test;

        if (!$test) {
            return response()->json([
                'ok' => false,
                'message' => 'Тест для данной сессии не найден.',
            ], 404);
        }

        /*
         * =========================================================
         * СОРТИРОВКА КАРТОЧЕК
         * =========================================================
         */
        if ($test->type === 'sort') {
            $data = $request->validate([
                /*
                 * present позволяет отправить пустой массив при сбросе.
                 */
                'sort_order' => [
                    'present',
                    'array',
                ],

                'sort_order.*' => [
                    'integer',
                    'distinct',
                ],

                'current_index' => [
                    'nullable',
                    'integer',
                    'min:0',
                ],
            ], [
                'sort_order.present' =>
                    'Не передан порядок карточек.',

                'sort_order.array' =>
                    'Порядок карточек имеет неверный формат.',

                'sort_order.*.integer' =>
                    'Передан некорректный идентификатор карточки.',

                'sort_order.*.distinct' =>
                    'Одна карточка указана в порядке несколько раз.',
            ]);

            $allowedCardIds = $test->sortCards
                ->pluck('id')
                ->map(static fn ($id): int => (int) $id)
                ->values();

            if ($allowedCardIds->isEmpty()) {
                throw ValidationException::withMessages([
                    'sort_order' =>
                        'В тесте отсутствуют карточки для сортировки.',
                ]);
            }

            $sortOrder = collect($data['sort_order'])
                ->map(static fn ($id): int => (int) $id)
                ->values();

            /*
             * Проверяем, что все переданные ID относятся
             * именно к текущему тесту.
             */
            if ($sortOrder->diff($allowedCardIds)->isNotEmpty()) {
                throw ValidationException::withMessages([
                    'sort_order' =>
                        'Передана карточка, которая не относится к данному тесту.',
                ]);
            }

            if ($sortOrder->count() > $allowedCardIds->count()) {
                throw ValidationException::withMessages([
                    'sort_order' =>
                        'Передано больше карточек, чем существует в тесте.',
                ]);
            }

            $context = (array) ($session->context ?? []);

            $context['sort_order'] = $sortOrder->all();

            /*
             * Индекс определяем на сервере, чтобы клиент
             * не мог передать некорректное значение.
             */
            $context['current_index'] = $sortOrder->count();

            $session->context = $context;
            $session->save();

            return response()->json([
                'ok' => true,
                'saved' => true,
                'type' => 'sort',
                'sort_order' => $sortOrder->all(),
                'current_index' => $sortOrder->count(),
            ]);
        }

        /*
         * =========================================================
         * ТЕСТ С ИЗОБРАЖЕНИЯМИ И ОТКРЫТЫМ ОТВЕТОМ
         * =========================================================
         */
        if ($test->type === 'image') {
            $data = $request->validate([
                'card_id' => [
                    'required',
                    'integer',
                ],

                'answer' => [
                    'required',
                    'string',
                    'max:20000',
                ],

                'current_index' => [
                    'required',
                    'integer',
                    'min:0',
                ],
            ], [
                'card_id.required' =>
                    'Не передан идентификатор карточки.',

                'answer.required' =>
                    'Введите ответ по карточке.',

                'current_index.required' =>
                    'Не передан номер текущей карточки.',
            ]);

            $card = $test->testCards
                ->firstWhere('id', (int) $data['card_id']);

            if (!$card) {
                throw ValidationException::withMessages([
                    'card_id' =>
                        'Карточка не найдена в данном тесте.',
                ]);
            }

            $context = (array) ($session->context ?? []);
            $answers = (array) ($context['answers'] ?? []);

            $answers[(string) $card->id] =
                trim((string) $data['answer']);

            $context['answers'] = $answers;
            $context['current_index'] =
                (int) $data['current_index'];

            $session->context = $context;
            $session->save();

            return response()->json([
                'ok' => true,
                'saved' => true,
                'type' => 'image',
                'card_id' => $card->id,
                'current_index' =>
                    (int) $data['current_index'],
            ]);
        }

        /*
         * =========================================================
         * ОБЫЧНЫЙ ОПРОСНИК
         * =========================================================
         */
        if ($test->type !== 'questionnaire') {
            return response()->json([
                'ok' => false,
                'message' =>
                    'Неподдерживаемый тип теста: ' . $test->type,
            ], 422);
        }

        $data = $request->validate([
            'item_id' => [
                'required',
                'integer',
            ],

            'answer' => [
                'nullable',
            ],

            'current_index' => [
                'required',
                'integer',
                'min:0',
            ],

            'stimulus_id' => [
                'nullable',
                'integer',
            ],
        ]);

        $session->loadMissing([
            'test.sections.items.options',
        ]);

        $test = $session->test;

        $item = $test->sections
            ->flatMap(
                static fn ($section) => $section->items
            )
            ->firstWhere('id', (int) $data['item_id']);

        if (!$item) {
            throw ValidationException::withMessages([
                'item_id' =>
                    'Вопрос не найден в данном тесте.',
            ]);
        }

        $hasOptions = $item->options
            && $item->options->isNotEmpty();

        if ($hasOptions) {
            $option = $item->options
                ->firstWhere('id', (int) $data['answer']);

            if (!$option) {
                throw ValidationException::withMessages([
                    'answer' =>
                        'Выбранный вариант не относится к вопросу.',
                ]);
            }

            TestAnswer::updateOrCreate(
                [
                    'session_id' => $session->id,
                    'item_id' => $item->id,
                ],
                [
                    'option_id' => $option->id,
                ]
            );
        } else {
            $stimulusId = (int) (
                $data['stimulus_id'] ?? 0
            );

            if ($stimulusId > 0) {
                TestOpenResponse::updateOrCreate(
                    [
                        'session_id' => $session->id,
                        'stimulus_id' => $stimulusId,
                        'sequence_no' => 1,
                    ],
                    [
                        'response_text' =>
                            is_null($data['answer'])
                                ? null
                                : (string) $data['answer'],
                    ]
                );
            }

            TestAnswer::query()
                ->where('session_id', $session->id)
                ->where('item_id', $item->id)
                ->delete();
        }

        $context = (array) ($session->context ?? []);
        $answers = (array) ($context['answers'] ?? []);

        $answers[(string) $item->id] =
            $data['answer'] ?? null;

        $context['answers'] = $answers;
        $context['current_index'] =
            (int) $data['current_index'];

        $session->context = $context;
        $session->save();

        return response()->json([
            'ok' => true,
            'saved' => true,
            'type' => 'questionnaire',
            'item_id' => $item->id,
            'current_index' =>
                (int) $data['current_index'],
        ]);
    }

    /**
     * FINISH = пациент завершил тест.
     */
    public function finish(TestSession $session): JsonResponse
    {
        if ($this->enforceTimeout($session)) {
            return response()->json([
                'ok' => true,
                'timed_out' => true,
            ]);
        }

        abort_unless($session->in_progress === true, 403);

        $session->loadMissing([
            'test:id,type',
            'test.testCards:id,test_id',
            'test.sortCards:id,test_id',
            'assignment',
        ]);

        $test = $session->test;

        if (!$test) {
            return response()->json([
                'ok' => false,
                'message' => 'Тест для данной сессии не найден.',
            ], 404);
        }

        /*
         * =========================================================
         * ПРОВЕРКА СОРТИРОВКИ
         * =========================================================
         */
        if ($test->type === 'sort') {
            $expectedIds = $test->sortCards
                ->pluck('id')
                ->map(static fn ($id): int => (int) $id)
                ->sort()
                ->values();

            if ($expectedIds->isEmpty()) {
                return response()->json([
                    'ok' => false,
                    'message' =>
                        'В тесте отсутствуют карточки для сортировки.',
                ], 422);
            }

            $rawSortOrder = data_get(
                $session->context,
                'sort_order',
                []
            );

            if (!is_array($rawSortOrder)) {
                return response()->json([
                    'ok' => false,
                    'message' =>
                        'Сохранённый порядок карточек повреждён.',
                ], 422);
            }

            $sortOrder = collect($rawSortOrder)
                ->map(static fn ($id): int => (int) $id)
                ->filter(static fn (int $id): bool => $id > 0)
                ->values();

            /*
             * Отдельно проверяем дубли.
             */
            if ($sortOrder->unique()->count() !== $sortOrder->count()) {
                return response()->json([
                    'ok' => false,
                    'message' =>
                        'Одна карточка расположена несколько раз.',
                ], 422);
            }

            $actualIds = $sortOrder
                ->sort()
                ->values();

            if (
                $actualIds->count() !== $expectedIds->count()
                || $actualIds->all() !== $expectedIds->all()
            ) {
                return response()->json([
                    'ok' => false,
                    'message' =>
                        'Перед завершением расположите все карточки.',
                ], 422);
            }
        }

        /*
         * =========================================================
         * ПРОВЕРКА ОТВЕТОВ ТЕСТА С ИЗОБРАЖЕНИЯМИ
         * =========================================================
         */
        if ($test->type === 'image') {
            $answers = (array) data_get(
                $session->context,
                'answers',
                []
            );

            $missingCardIds = $test->testCards
                ->pluck('id')
                ->filter(static function ($cardId) use ($answers): bool {
                    $key = (string) $cardId;

                    if (!array_key_exists($key, $answers)) {
                        return true;
                    }

                    $answer = $answers[$key];

                    return !is_string($answer)
                        || trim($answer) === '';
                })
                ->values();

            if ($missingCardIds->isNotEmpty()) {
                return response()->json([
                    'ok' => false,
                    'message' =>
                        'Перед завершением ответьте на все карточки.',
                ], 422);
            }
        }

        $session->update([
            'in_progress' => false,
            'status' => 'submitted',
            'completed_at' => now(),

            /*
             * Блокировку пока сохраняем — она будет снята
             * после финального PIN врача.
             */
            'locked_at' => now(),
        ]);

        /*
         * В старом варианте значение присваивалось,
         * но не сохранялось в БД.
         */
        if ($session->assignment) {
            $session->assignment->update([
                'finished_at' => now(),
            ]);
        }

        /*
         * Текущий TestScoringService рассчитан на вопросы
         * и варианты ответов. Для image и sort
         * автоматический подсчёт пока не запускаем.
         */
        if ($test->type === 'questionnaire') {
            app(TestScoringService::class)
                ->scoreAndPersist($session->fresh());
        }

        return response()->json([
            'ok' => true,
        ]);
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

    public function finalPinForm(
        Request $request,
        TestSession $session
    ): View|RedirectResponse {
        /*
         * Сначала обрабатываем таймаут.
         */
        if ($this->enforceTimeout($session)) {
            $patientId = $session->patient_id
                ?? data_get($session->context, 'patient_id');

            return $patientId
                ? redirect()->route(
                    'doctors.patients.medical_card',
                    ['patient' => $patientId]
                )
                : redirect()->route('doctors.main');
        }

        $doctorId = (int) optional(
            $request->user()?->doctor
        )->id;

        /*
         * Важно: оба идентификатора приводим к int.
         */
        abort_unless(
            $doctorId > 0
            && (int) $session->locked_by === $doctorId,
            403,
            'Эта тестовая сессия заблокирована другим врачом.'
        );

        $mode = $request->query('mode') === 'interrupt'
            ? 'interrupt'
            : 'finish';

        /*
         * Досрочное прерывание активной сессии.
         */
        if ($mode === 'interrupt') {
            abort_unless(
                (bool) $session->in_progress,
                409,
                'Тест уже не находится в процессе прохождения.'
            );

            return view(
                'doctors.reception.tests.final_pin',
                [
                    'session' => $session,
                    'mode' => 'interrupt',
                ]
            );
        }

        /*
         * Обычная финализация завершённого пациентом теста.
         */
        $finalOk = (bool) data_get(
            $session->context,
            'final_pin_ok',
            false
        );

        if ($finalOk) {
            return redirect()->route(
                'doctors.patients.sessions.show',
                $session->id
            );
        }

        if ($session->status !== 'submitted') {
            return redirect()->route(
                'doctors.reception.tests.run',
                $session->id
            );
        }

        return view(
            'doctors.reception.tests.final_pin',
            [
                'session' => $session,
                'mode' => 'finish',
            ]
        );
    }

    public function finalPinVerify(
        Request $request,
        TestSession $session
    ): RedirectResponse {
        $doctorId = (int) optional(
            $request->user()?->doctor
        )->id;

        abort_unless(
            $doctorId > 0
            && (int) $session->locked_by === $doctorId,
            403,
            'Эта тестовая сессия заблокирована другим врачом.'
        );

        $data = $request->validate([
            'pin' => [
                'required',
                'string',
                'min:4',
                'max:6',
            ],

            'mode' => [
                'required',
                'string',
                'in:finish,interrupt',
            ],
        ]);

        $mode = $data['mode'];

        $assignment = TestAssignment::query()
            ->where('session_id', $session->id)
            ->first();

        if (!$assignment) {
            $assignmentId = data_get(
                $session->context,
                'assignment_id'
            );

            $assignment = $assignmentId
                ? TestAssignment::find($assignmentId)
                : null;
        }

        if (
            !$assignment
            || empty($assignment->pin_hash)
        ) {
            return back()
                ->withInput()
                ->withErrors([
                    'pin' => 'Не найден PIN для данной сессии.',
                ]);
        }

        if (!password_verify($data['pin'], $assignment->pin_hash)) {
            return back()
                ->withInput()
                ->withErrors([
                    'pin' => 'Неверный PIN.',
                ]);
        }

        /*
         * =========================================================
         * ДОСРОЧНОЕ ПРЕРЫВАНИЕ
         * =========================================================
         */
        if ($mode === 'interrupt') {
            if (!$session->in_progress) {
                return back()->withErrors([
                    'pin' => 'Тест уже не находится в процессе прохождения.',
                ]);
            }

            DB::transaction(function () use (
                $session,
                $assignment,
                $doctorId
            ): void {
                $context = (array) ($session->context ?? []);

                /*
                 * answers и sort_order остаются в context.
                 * Частично введённые результаты не удаляются.
                 */
                $context['interrupted'] = true;
                $context['interrupted_at'] =
                    now()->toIso8601String();
                $context['interrupted_by'] = $doctorId;
                $context['interrupted_with_pin'] = true;

                unset(
                    $context['final_pin_ok'],
                    $context['final_pin_ok_at']
                );

                $session->update([
                    'status' => 'cancelled',
                    'in_progress' => false,
                    'completed_at' => now(),

                    /*
                     * Разблокируем докторскую панель.
                     */
                    'locked_at' => null,
                    'locked_by' => null,

                    'context' => $context,
                ]);

                $assignment->update([
                    'status' => 'cancelled',
                    'finished_at' => now(),
                ]);
            });

            $patientId = $session->patient_id
                ?? data_get($session->context, 'patient_id');

            if ($patientId) {
                return redirect()
                    ->route(
                        'doctors.patients.medical_card',
                        ['patient' => $patientId]
                    )
                    ->with(
                        'success',
                        'Тест прерван. Частичные ответы сохранены, панель разблокирована.'
                    );
            }

            return redirect()
                ->route('doctors.main')
                ->with(
                    'success',
                    'Тест прерван, панель разблокирована.'
                );
        }

        /*
         * =========================================================
         * ОБЫЧНОЕ ЗАВЕРШЕНИЕ
         * =========================================================
         */
        if ($session->status !== 'submitted') {
            return back()->withErrors([
                'pin' => 'Пациент ещё не завершил тест.',
            ]);
        }

        $context = (array) ($session->context ?? []);

        $context['final_pin_ok'] = true;
        $context['final_pin_ok_at'] =
            now()->toIso8601String();

        $session->context = $context;
        $session->locked_by = $doctorId;
        $session->locked_at = now();
        $session->save();

        return redirect()->route(
            'doctors.patients.sessions.show',
            $session->id
        );
    }
}
