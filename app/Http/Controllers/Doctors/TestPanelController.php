<?php

namespace App\Http\Controllers\Doctors;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTestImagesRequest;
use App\Http\Requests\StoreTestQuestionnaireRequest;
use App\Http\Requests\StoreTestSortTableRequest;
use App\Models\Doctor;
use App\Models\Test;
use App\Models\TestAssignment;
use App\Models\TestCardInterpretation;
use App\Models\TestItem;
use App\Models\TestItemOption;
use App\Models\TestQuestionnaire;
use App\Models\TestSession;
use App\Models\TestSortInterpretation;
use App\Services\Tests\TestDeletionService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class TestPanelController extends Controller
{
    private const TYPE_QUESTIONNAIRE = 'questionnaire';
    private const TYPE_IMAGE = 'image';
    private const TYPE_SORT = 'sort';

    private function currentDoctor(): Doctor
    {
        $doctor = request()->user()?->doctor;

        abort_unless(
            $doctor,
            403,
            'Пользователь не связан с врачом.'
        );

        return $doctor;
    }

    /**
     * @return array{0: bool, 1: bool}
     */
    private function doctorFlags(Doctor $doctor): array
    {
        $doesHaveClinic = !is_null($doctor->clinic_id);

        $isHeadmaster =
            $doesHaveClinic
            && (int) $doctor->id ===
            (int) $doctor->clinic?->headmaster_id;

        return [$isHeadmaster, $doesHaveClinic];
    }

    private function canManageTestTemplates(Doctor $doctor): bool
    {
        [$isHeadmaster, $doesHaveClinic] =
            $this->doctorFlags($doctor);

        return !$doesHaveClinic || $isHeadmaster;
    }

    private function authorizedTestCreator(): Doctor
    {
        $doctor = $this->currentDoctor();

        abort_unless(
            $this->canManageTestTemplates($doctor),
            403,
            'У вас нет права создавать тесты для клиники.'
        );

        return $doctor;
    }

    private function createOwnedTest(
        Doctor $doctor,
        array $attributes
    ): Test {
        $test = Test::create(array_merge(
            $attributes,
            [
                'owner_doctor_id' => $doctor->id,
            ]
        ));

        if ($doctor->clinic_id) {
            $test->clinics()->syncWithoutDetaching([
                $doctor->clinic_id,
            ]);
        }

        return $test;
    }

    /**
     * Запрос для страницы управления пользовательскими тестами.
     *
     * Частный врач видит собственные личные тесты.
     * Врач клиники видит пользовательские тесты,
     * опубликованные для его клиники через clinic_tests.
     */
    private function managedTestsQuery(Doctor $doctor): Builder
    {
        $query = Test::query()
            ->whereNotNull('owner_doctor_id');

        if (is_null($doctor->clinic_id)) {
            return $query
                ->where('owner_doctor_id', $doctor->id)
                ->whereDoesntHave('clinics');
        }

        return $query->whereHas(
            'clinics',
            static function (Builder $clinicQuery) use ($doctor): void {
                $clinicQuery->where(
                    'clinics.id',
                    $doctor->clinic_id
                );
            }
        );
    }

    /**
     * В debug-режиме разрешаем удаление любого пользовательского теста,
     * доступного текущему врачу. Системные тесты не удаляем.
     *
     * В обычном режиме удалить тест может только его владелец:
     * частный врач либо заведующий клиникой.
     */
    private function canDeleteTest(
        Doctor $doctor,
        Test $test
    ): bool {
        if (is_null($test->owner_doctor_id)) {
            return false;
        }

        if (app()->hasDebugModeEnabled()) {
            return $this->managedTestsQuery($doctor)
                ->whereKey($test->getKey())
                ->exists();
        }

        return $this->canManageTestTemplates($doctor)
            && (int) $test->owner_doctor_id ===
            (int) $doctor->id;
    }

    private function encodeResourceProfile(mixed $value): string
    {
        return json_encode(
            $value ?: [],
            JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
        );
    }

    /**
     * Подготавливает строки журнала результатов.
     */
    private function buildRecentResultRows(
        Collection $sessions
    ): Collection {
        return $sessions
            ->map(
                fn (TestSession $session): object =>
                $this->buildRecentResultRow($session)
            )
            ->values();
    }

    private function buildRecentResultRow(
        TestSession $session
    ): object {
        $test = $session->test;
        $testType = $this->normalizeTestType($test?->type);

        [$score, $scaleName, $resultName] =
            $this->resultSummary($session, $testType);

        [$resultStatus, $statusClass, $statusText] =
            $this->resultStatus($session);

        return (object) [
            'session_id' => $session->id,
            'patient_id' => $session->patient_id,
            'patient' => $session->patient,
            'test' => $test,
            'test_type_label' => $this->testTypeLabel($testType),
            'score' => $score,
            'scale_name' => $scaleName,
            'result_name' => $resultName,
            'status' => $resultStatus,
            'status_class' => $statusClass,
            'status_text' => $statusText,
            'created_at' =>
                $session->completed_at
                ?? $session->updated_at
                    ?? $session->created_at,
            'medical_card_url' => $session->patient_id
                ? route(
                    'doctors.patients.medical_card',
                    $session->patient_id
                )
                : null,
            'result_url' => route(
                'doctors.patients.sessions.results.page',
                $session->id
            ),
        ];
    }

    /**
     * @return array{0: string|int|float, 1: string, 2: string}
     */
    private function resultSummary(
        TestSession $session,
        string $testType
    ): array {
        if ($testType === self::TYPE_IMAGE) {
            $answeredCount = collect(
                (array) data_get(
                    $session->context,
                    'answers',
                    []
                )
            )
                ->filter(
                    fn (mixed $value): bool =>
                    $this->hasValue($value)
                )
                ->count();

            $totalCards =
                $session->test?->testCards?->count() ?? 0;

            return [
                $answeredCount . ' / ' . $totalCards,
                'Ответы по карточкам',
                $answeredCount === $totalCards
                    ? 'Все карточки заполнены'
                    : 'Тест заполнен частично',
            ];
        }

        if ($testType === self::TYPE_SORT) {
            $orderedCount = collect(
                (array) data_get(
                    $session->context,
                    'sort_order',
                    []
                )
            )
                ->filter()
                ->unique()
                ->count();

            $totalCards =
                $session->test?->sortCards?->count() ?? 0;

            return [
                $orderedCount . ' / ' . $totalCards,
                'Порядок карточек',
                $orderedCount === $totalCards
                    ? 'Порядок полностью сохранён'
                    : 'Сортировка заполнена частично',
            ];
        }

        $primaryResult = $session->results
            ->first(
                static fn ($result): bool =>
                is_null($result->key_id)
            )
            ?? $session->results->first();

        return [
            $primaryResult?->score ?? '—',
            $primaryResult?->key?->title
            ?? 'Суммарный балл',
                $primaryResult?->range_text
                ?? Str::limit(
                (string) (
                    $primaryResult?->interpretation
                    ?? ''
                ),
                90
            )
                ?: 'Результат рассчитан',
        ];
    }

    /**
     * @return array{0: string, 1: string, 2: string}
     */
    private function resultStatus(
        TestSession $session
    ): array {
        $resultStatus = data_get(
            $session->context,
            'result_status'
        );

        if (
            !in_array(
                $resultStatus,
                ['normal', 'attention', 'risk'],
                true
            )
        ) {
            $resultStatus = match ((string) $session->status) {
                'submitted',
                'in_review' => 'attention',

                'coding_done',
                'complete',
                'completed',
                'finished' => 'normal',

                default => 'default',
            };
        }

        $statusClass = match ($resultStatus) {
            'normal' => 'result-normal',
            'attention' => 'result-attention',
            'risk' => 'result-risk',
            default => 'result-default',
        };

        $statusText = match ((string) $session->status) {
            'submitted' => 'Ожидает завершения врачом',
            'in_review' => 'На проверке',
            'coding_done' => 'Кодирование завершено',
            'complete',
            'completed',
            'finished' => 'Завершён',
            default => 'Результат получен',
        };

        if ($resultStatus === 'risk') {
            $statusText = 'Высокий риск';
        }

        return [$resultStatus, $statusClass, $statusText];
    }

    private function normalizeTestType(mixed $type): string
    {
        return match (
        mb_strtolower(trim((string) $type))
        ) {
            self::TYPE_IMAGE => self::TYPE_IMAGE,
            self::TYPE_SORT => self::TYPE_SORT,
            default => self::TYPE_QUESTIONNAIRE,
        };
    }

    private function testTypeLabel(string $type): string
    {
        return match ($type) {
            self::TYPE_IMAGE => 'Тест с изображениями',
            self::TYPE_SORT => 'Сортировка карточек',
            default => 'Опросник',
        };
    }

    private function hasValue(mixed $value): bool
    {
        if ($value === null) {
            return false;
        }

        if (is_string($value)) {
            return trim($value) !== '';
        }

        if (is_array($value)) {
            return $value !== [];
        }

        return true;
    }

    /**
     * Журнал результатов тестирования текущего врача.
     */
    public function index(): View
    {
        $user = request()->user();
        $doctor = $this->currentDoctor();

        [$isHeadmaster, $doesHaveClinic] = $this->doctorFlags($doctor);

        /*
         * В текущей схеме clinician_id хранит ID пользователя,
         * а не ID записи doctors.
         */
        $sessions = TestSession::query()
            ->with([
                'patient',
                'test:id,name,code,type',
                'test.testCards:id,test_id',
                'test.sortCards:id,test_id',
                'results.key:id,title',
            ])
            ->whereNotNull('patient_id')
            ->whereIn('status', [
                'submitted',
                'in_review',
                'coding_done',
                'complete',
                'completed',
                'finished',
            ])
            ->where(function (Builder $query) use ($user): void {
                $query
                    ->where('clinician_id', $user->id)
                    ->orWhereHas(
                        'assignment',
                        static function (Builder $assignmentQuery) use ($user): void {
                            $assignmentQuery->where(
                                'clinician_id',
                                $user->id
                            );
                        }
                    );
            })
            ->orderByRaw(
                'COALESCE(completed_at, updated_at, created_at) DESC'
            )
            ->limit(100)
            ->get();

        $recentResults = $this->buildRecentResultRows($sessions);

        $totalResults = $recentResults->count();

        $todayResults = $recentResults
            ->filter(
                static fn (object $result): bool =>
                    $result->created_at?->isToday() === true
            )
            ->count();

        $attentionResults = $recentResults
            ->whereIn('status', ['attention', 'risk'])
            ->count();

        $uniquePatients = $recentResults
            ->pluck('patient_id')
            ->filter()
            ->unique()
            ->count();

        return view(
            'doctors.tests.home',
            compact(
                'isHeadmaster',
                'doesHaveClinic',
                'recentResults',
                'totalResults',
                'todayResults',
                'attentionResults',
                'uniquePatients'
            )
        );
    }

    /**
     * Управление пользовательскими тестами.
     */
    public function mine(): View
    {
        $doctor = $this->currentDoctor();

        [$isHeadmaster, $doesHaveClinic] =
            $this->doctorFlags($doctor);

        $debugCascadeDeleteEnabled =
            app()->hasDebugModeEnabled();

        $myTests = $this->managedTestsQuery($doctor)
            ->withCount([
                'assignments',
                'sessions',
                'clinics',
            ])
            ->latest('created_at')
            ->get()
            ->each(function (Test $test) use (
                $doctor,
                $debugCascadeDeleteEnabled
            ): void {
                $hasUsage =
                    (int) $test->assignments_count > 0
                    || (int) $test->sessions_count > 0;

                $test->setAttribute('has_usage', $hasUsage);
                $test->setAttribute(
                    'can_delete',
                    $this->canDeleteTest($doctor, $test)
                );
                $test->setAttribute(
                    'debug_cascade_delete',
                    $debugCascadeDeleteEnabled && $hasUsage
                );
            });

        $pageTitle = $doesHaveClinic
            ? 'Тесты клиники'
            : 'Мои тесты';

        return view(
            'doctors.tests.mine',
            compact(
                'isHeadmaster',
                'doesHaveClinic',
                'myTests',
                'pageTitle',
                'debugCascadeDeleteEnabled'
            )
        );
    }

    public function storeImageTest(
        StoreTestImagesRequest $request
    ): RedirectResponse {
        try {
            DB::transaction(function () use ($request): void {
                $doctor = $this->authorizedTestCreator();

                $test = $this->createOwnedTest($doctor, [
                    'name' => $request->name,
                    'code' => mb_strtoupper((string) $request->code),
                    'status' => $request->status,
                    'estimated_minutes' => $request->estimated_minutes,
                    'description' => $request->description,
                    'instructions' => $request->instructions,
                    'type' => self::TYPE_IMAGE,
                    'resource_profile' => $this->encodeResourceProfile(
                        $request->resource_profile ?? []
                    ),
                ]);

                TestCardInterpretation::create([
                    'test_id' => $test->id,
                    'interpretation_type' => $request->interpretation_type,
                    'answers_type' => $request->answers_type,
                    'display_mode' => $request->display_mode,
                ]);

                foreach ($request->input('cards', []) as $index => $cardData) {
                    $card = $test->testCards()->create([
                        'title' => $cardData['title'] ?? null,
                        'question' => $cardData['question'] ?? null,
                        'sort' => $index + 1,
                    ]);

                    if ($request->hasFile("cards.$index.image")) {
                        $card
                            ->addMedia(
                                $request->file("cards.$index.image")
                            )
                            ->toMediaCollection('test_card_image');
                    }
                }
            });
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->withErrors([
                    'Не удалось сохранить тест с изображениями. '
                    . 'Обратитесь к администрации.',
                ]);
        }

        return back()->with(
            'success',
            'Тест с изображениями успешно создан.'
        );
    }

    public function storeQuestionnaireTest(
        StoreTestQuestionnaireRequest $request
    ): RedirectResponse {
        try {
            DB::transaction(function () use ($request): void {
                $doctor = $this->authorizedTestCreator();

                $test = $this->createOwnedTest($doctor, [
                    'name' => $request->name,
                    'code' => mb_strtoupper((string) $request->code),
                    'description' => $request->description,
                    'status' => $request->status,
                    'type' => self::TYPE_QUESTIONNAIRE,
                    'estimated_minutes' => $request->estimated_minutes,
                    'instructions' => $request->instructions,
                    'resource_profile' => $this->encodeResourceProfile(
                        $request->resource_profile ?? []
                    ),
                ]);

                TestQuestionnaire::create([
                    'test_id' => $test->id,
                    'scoring_type' => $request->scoring_type,
                    'min_scoring' => $request->min_scoring,
                    'max_scoring' => $request->max_scoring,
                    'attention_score' => $request->attention_score,
                    'description_interpretation' =>
                        $request->description_interpretation,
                ]);

                $questions = data_get(
                    $request->resource_profile,
                    'questions',
                    []
                );

                foreach ($questions as $questionIndex => $questionData) {
                    $item = new TestItem();
                    $item->test_id = $test->id;
                    $item->text = $questionData['text'];
                    $item->order = $questionIndex + 1;
                    $item->save();

                    foreach (
                        $questionData['options'] ?? []
                        as $optionIndex => $optionData
                    ) {
                        $option = new TestItemOption();
                        $option->item_id = $item->id;
                        $option->label = $optionData['text'];
                        $option->value = $optionData['score'];
                        $option->order = $optionIndex + 1;
                        $option->save();
                    }
                }
            });
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->withErrors([
                    'Не удалось создать тест-опросник. '
                    . 'Обратитесь к администрации портала.',
                ]);
        }

        return back()->with(
            'success',
            'Тест-опросник успешно создан.'
        );
    }

    public function storeSortTest(
        StoreTestSortTableRequest $request
    ): RedirectResponse {
        try {
            DB::transaction(function () use ($request): void {
                $doctor = $this->authorizedTestCreator();

                $test = $this->createOwnedTest($doctor, [
                    'name' => $request->name,
                    'code' => mb_strtoupper((string) $request->code),
                    'status' => $request->status,
                    'description' => $request->description,
                    'instructions' => $request->instructions,
                    'estimated_minutes' => $request->duration,
                    'resource_profile' => $this->encodeResourceProfile(
                        $request->resource_profile ?? []
                    ),
                    'type' => self::TYPE_SORT,
                ]);

                TestSortInterpretation::create([
                    'test_id' => $test->id,
                    'resource_title' => $request->resource_title,
                    'resource_about' => $request->resource_about,
                    'left_label' => $request->left_label,
                    'right_label' => $request->right_label,
                ]);

                foreach (
                    $request->validated('cards', [])
                    as $index => $cardData
                ) {
                    $type = mb_strtolower(
                        trim((string) ($cardData['type'] ?? 'text'))
                    );

                    if (
                        !in_array(
                            $type,
                            ['color', 'text', 'image'],
                            true
                        )
                    ) {
                        throw ValidationException::withMessages([
                            "cards.$index.type" =>
                                'Недопустимый тип карточки.',
                        ]);
                    }

                    $card = $test->sortCards()->create([
                        'type' => $type,
                        'title' => trim(
                            (string) ($cardData['title'] ?? '')
                        ),
                        'text' => $type === 'text'
                            ? trim((string) ($cardData['text'] ?? ''))
                            : null,
                        'color' => $type === 'color'
                            ? trim((string) ($cardData['color'] ?? ''))
                            : null,
                        'sort' => isset($cardData['sort'])
                            ? (int) $cardData['sort']
                            : $index + 1,
                    ]);

                    if ($type !== 'image') {
                        continue;
                    }

                    $uploadedImage =
                        $cardData['image']
                        ?? $request->file("cards.$index.image");

                    if (!$uploadedImage) {
                        throw ValidationException::withMessages([
                            "cards.$index.image" =>
                                'Для графической карточки '
                                . 'необходимо загрузить изображение.',
                        ]);
                    }

                    $card
                        ->addMedia($uploadedImage)
                        ->toMediaCollection(
                            'test_sort_card_image'
                        );
                }
            });
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->withErrors([
                    'Не удалось сохранить тест с сортировкой. '
                    . 'Обратитесь к администратору.',
                ]);
        }

        return back()->with(
            'success',
            'Тест с сортировкой успешно создан.'
        );
    }

    /**
     * Удаление пользовательского теста.
     */
    public function destroy(
        Test $test,
        TestDeletionService $deletionService
    ): RedirectResponse {
        $doctor = $this->currentDoctor();

        abort_unless(
            $this->canDeleteTest($doctor, $test),
            403,
            'У вас нет права удалить этот тест.'
        );

        $hasUsage =
            TestAssignment::query()
                ->where('test_id', $test->id)
                ->exists()
            || TestSession::query()
                ->where('test_id', $test->id)
                ->exists();

        if ($hasUsage && !app()->hasDebugModeEnabled()) {
            return back()->withErrors([
                'test' =>
                    'Нельзя удалить тест, который уже назначался '
                    . 'пациентам или имеет историю прохождений.',
            ]);
        }

        try {
            $deletionService->delete(
                $test,
                purgeUsage: $hasUsage
            );
        } catch (Throwable $exception) {
            report($exception);

            return back()->withErrors([
                'test' =>
                    'Не удалось удалить тест и связанные данные. '
                    . $exception->getMessage(),
            ]);
        }

        return redirect()
            ->route('doctors.tests.mine')
            ->with(
                'success',
                $hasUsage
                    ? 'Тест и все связанные с ним данные удалены.'
                    : 'Тест успешно удалён.'
            );
    }
}
