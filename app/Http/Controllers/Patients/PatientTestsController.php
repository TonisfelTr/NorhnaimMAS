<?php

declare(strict_types=1);

namespace App\Http\Controllers\Patients;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Doctors\TestSessionController as SharedTestSessionController;
use App\Models\Patient;
use App\Models\Test;
use App\Models\TestAssignment;
use App\Models\TestSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PatientTestsController extends Controller
{
    private const SOURCE_SELF = 'patient_self';
    private const SOURCE_ASSIGNED_PORTAL = 'doctor_assigned_patient_portal';

    private function currentPatient(Request $request): Patient
    {
        $user = $request->user();

        abort_unless($user && $user->hasRole('patients'), 403);

        return $user->patient()->firstOrFail();
    }

    public function index(Request $request): View
    {
        $patient = $this->currentPatient($request);

        $assignments = TestAssignment::query()
            ->where('patient_id', $patient->id)
            ->with([
                'test.sections.items',
                'test.items.options',
                'test.testCards',
                'test.sortCards',
                'session.test',
                'session.results.key',
            ])
            ->orderByDesc('created_at')
            ->limit(100)
            ->get();

        // Пациенту доступны все поддерживаемые тесты, которые НЕ принадлежат
        // ни одной клинике. Связь владения клиникой хранится в clinic_tests.
        // Никакого отдельного разрешения врача для самостоятельного прохождения нет.
        $catalog = Test::query()
            // Принадлежность клинике определяется только связью clinic_tests.
            // type может быть NULL у старых официальных опросников:
            // такие записи по умолчанию считаются questionnaire.
            ->whereDoesntHave('clinics')
            ->with([
                'sections.items',
                'items.options',
                'testCards',
                'sortCards',
            ])
            // Считаем завершённые попытки по всем пациентам.
            // submitted означает, что пациент закончил прохождение и отправил
            // тест врачу; остальные статусы отражают завершённую обработку.
            // Незавершённые, отменённые и просроченные сеансы не учитываем.
            ->withCount([
                'sessions as completed_runs_count' => static function ($query): void {
                    $query
                        ->whereNotNull('completed_at')
                        ->whereIn('status', [
                            'submitted',
                            'in_review',
                            'coding_done',
                            'completed',
                            'complete',
                            'finished',
                        ]);
                },
            ])
            ->orderBy('name')
            ->get();

        $selfSessions = TestSession::query()
            ->where('patient_id', $patient->id)
            ->where('context->source', self::SOURCE_SELF)
            ->with([
                'test',
                'results.key',
            ])
            ->orderByDesc('created_at')
            ->limit(100)
            ->get();

        $activeSelfByTest = $selfSessions
            ->filter(static fn (TestSession $session): bool =>
                (bool) $session->in_progress
                && $session->status === 'in_progress'
            )
            ->sortByDesc('created_at')
            ->groupBy('test_id')
            ->map(static fn ($sessions) => $sessions->first());

        $selectedTab = $request->query('tab') === 'self'
            ? 'self'
            : 'assigned';

        $assignedActiveCount = $assignments->filter(
            static function (TestAssignment $assignment): bool {
                $session = $assignment->session;

                if ($session) {
                    return (bool) $session->in_progress
                        || in_array($session->status, ['in_progress', 'started'], true);
                }

                return in_array(
                    (string) $assignment->status,
                    ['assigned', 'awaiting_unlock', 'started', 'in_progress'],
                    true
                );
            }
        )->count();

        $assignedCompletedCount = $assignments->filter(
            static function (TestAssignment $assignment): bool {
                $status = (string) ($assignment->session?->status ?? $assignment->status);

                return in_array(
                    $status,
                    ['completed', 'complete', 'finished'],
                    true
                );
            }
        )->count();

        return view('patients.tests.index', [
            'patient' => $patient,
            'assignments' => $assignments,
            'catalog' => $catalog,
            'selfSessions' => $selfSessions,
            'activeSelfByTest' => $activeSelfByTest,
            'selectedTab' => $selectedTab,
            'assignedActiveCount' => $assignedActiveCount,
            'assignedCompletedCount' => $assignedCompletedCount,
        ]);
    }

    public function startAssigned(Request $request, int $assignment): RedirectResponse
    {
        $patient = $this->currentPatient($request);

        $session = DB::transaction(function () use ($patient, $assignment): TestSession {
            $testAssignment = TestAssignment::query()
                ->where('patient_id', $patient->id)
                ->whereKey($assignment)
                ->lockForUpdate()
                ->firstOrFail();

            abort_if((bool) $testAssignment->is_kiosk, 403, 'Это тестирование проводится в клинике через киоск.');

            abort_if(
                in_array((string) $testAssignment->status, ['cancelled', 'completed', 'complete', 'finished'], true),
                409,
                'Это назначение уже завершено или отменено.'
            );

            $existing = $testAssignment->session()
                ->lockForUpdate()
                ->first();

            if ($existing && $existing->patient_id === null) {
                // Старые назначения могли создавать сессию без patient_id,
                // но сама связь assignment -> patient уже проверена выше.
                $existing->patient_id = (int) $patient->id;
                $existing->save();
            }

            if ($existing && (int) $existing->patient_id !== (int) $patient->id) {
                abort(404);
            }

            if ($existing) {
                $source = (string) data_get($existing->context, 'source', '');

                if (
                    $source === self::SOURCE_ASSIGNED_PORTAL
                    && (bool) $existing->in_progress
                    && $existing->status === 'in_progress'
                ) {
                    return $existing;
                }

                if ($existing->in_progress || $existing->status === 'in_progress') {
                    abort(409, 'Тест уже начат в клинике. Обратитесь к сотруднику.');
                }

                abort(409, 'Для этого назначения уже существует завершённая сессия.');
            }

            $context = array_merge(
                (array) ($testAssignment->context ?? []),
                [
                    'source' => self::SOURCE_ASSIGNED_PORTAL,
                    'assignment_id' => (int) $testAssignment->id,
                    'answers' => [],
                    'sort_order' => [],
                    'current_index' => 0,
                    'patient_portal' => true,
                ]
            );

            $session = TestSession::create([
                'test_id' => (int) $testAssignment->test_id,
                'clinician_id' => $testAssignment->clinician_id
                    ? (int) $testAssignment->clinician_id
                    : null,
                'patient_id' => (int) $patient->id,
                'context' => $context,
                'status' => 'in_progress',
                'in_progress' => true,
                // В пациентском кабинете не выставляем locked_by/locked_at:
                // они используются для блокировки панели врача и PIN-киоска.
            ]);

            $testAssignment->update([
                'session_id' => $session->id,
                'status' => 'started',
                'started_at' => $testAssignment->started_at ?? now(),
            ]);

            return $session;
        });

        return redirect()->route('patients.tests.run', $session);
    }

    public function startSelf(Request $request, int $test): RedirectResponse
    {
        $patient = $this->currentPatient($request);

        $session = DB::transaction(function () use ($patient, $test): TestSession {
            $testModel = Test::query()
                ->whereDoesntHave('clinics')
                ->whereKey($test)
                ->lockForUpdate()
                ->firstOrFail();

            abort_unless($this->patientCanAccessSelfTest($patient, $testModel), 404);

            $existing = TestSession::query()
                ->where('patient_id', $patient->id)
                ->where('test_id', $testModel->id)
                ->where('context->source', self::SOURCE_SELF)
                ->where('in_progress', true)
                ->where('status', 'in_progress')
                ->orderByDesc('created_at')
                ->lockForUpdate()
                ->first();

            if ($existing) {
                return $existing;
            }

            return TestSession::create([
                'test_id' => (int) $testModel->id,
                'clinician_id' => null,
                'patient_id' => (int) $patient->id,
                'context' => [
                    'source' => self::SOURCE_SELF,
                    'answers' => [],
                    'sort_order' => [],
                    'current_index' => 0,
                    'patient_portal' => true,
                ],
                'status' => 'in_progress',
                'in_progress' => true,
                // Самостоятельная сессия не получает врача и не блокирует панели.
            ]);
        });

        return redirect()->route('patients.tests.run', $session);
    }

    public function run(Request $request, int $session): View|RedirectResponse
    {
        $patient = $this->currentPatient($request);
        $testSession = $this->ownedPortalSession($patient, $session);

        if (!$testSession->in_progress || $testSession->status !== 'in_progress') {
            return redirect()->route('patients.tests.result', $testSession);
        }

        return view('patients.tests.run', [
            'patient' => $patient,
            'session' => $testSession->load('test'),
            'returnUrl' => route('patients.tests.index', [
                'tab' => data_get($testSession->context, 'source') === self::SOURCE_SELF
                    ? 'self'
                    : 'assigned',
            ]),
        ]);
    }

    public function payload(Request $request, int $session): JsonResponse
    {
        $patient = $this->currentPatient($request);
        $testSession = $this->ownedPortalSession($patient, $session);

        abort_unless($testSession->in_progress && $testSession->status === 'in_progress', 409);

        return app(SharedTestSessionController::class)->payload($testSession);
    }

    public function saveAnswer(Request $request, int $session): JsonResponse
    {
        $patient = $this->currentPatient($request);
        $testSession = $this->ownedPortalSession($patient, $session);

        abort_unless($testSession->in_progress && $testSession->status === 'in_progress', 409);

        return app(SharedTestSessionController::class)->submit($testSession, $request);
    }

    public function finish(Request $request, int $session): JsonResponse
    {
        $patient = $this->currentPatient($request);
        $testSession = $this->ownedPortalSession($patient, $session);

        abort_unless($testSession->in_progress && $testSession->status === 'in_progress', 409);

        return app(SharedTestSessionController::class)->finish($testSession);
    }

    public function result(Request $request, int $session): View|RedirectResponse
    {
        $patient = $this->currentPatient($request);
        $testSession = $this->ownedPortalSession($patient, $session, true);

        $source = (string) data_get($testSession->context, 'source');

        if ($source === self::SOURCE_SELF) {
            abort_unless(
                in_array($testSession->status, ['completed', 'complete', 'finished'], true),
                403
            );
        } else {
            abort_unless(
                in_array($testSession->status, ['completed', 'complete', 'finished'], true),
                403,
                'Результат станет доступен после завершения обработки врачом.'
            );
        }

        $testSession->load([
            'test',
            'results.key',
        ]);

        return view('patients.tests.result', [
            'patient' => $patient,
            'session' => $testSession,
            'isSelfTest' => $source === self::SOURCE_SELF,
            'results' => $testSession->results->sortBy(
                static fn ($result): int => $result->key_id ? 1 : 0
            )->values(),
            'returnUrl' => route('patients.tests.index', [
                'tab' => $source === self::SOURCE_SELF ? 'self' : 'assigned',
            ]),
        ]);
    }

    private function ownedPortalSession(
        Patient $patient,
        int $sessionId,
        bool $allowLegacyAssignedResult = false
    ): TestSession {
        $session = TestSession::query()
            ->whereKey($sessionId)
            ->where(function ($query) use ($patient): void {
                $query->where('patient_id', $patient->id)
                    ->orWhereHas('assignment', static function ($assignmentQuery) use ($patient): void {
                        $assignmentQuery->where('patient_id', $patient->id);
                    });
            })
            ->firstOrFail();

        $source = (string) data_get($session->context, 'source');

        if ($source === self::SOURCE_SELF) {
            abort_unless((int) $session->patient_id === (int) $patient->id, 404);
        }

        if ($source === '' && $allowLegacyAssignedResult) {
            // Старые назначения, пройденные через PIN-киоск, не имеют source marker.
            // Разрешаем только просмотр собственного завершённого результата по связи assignment.
            abort_unless(
                TestAssignment::query()
                    ->where('patient_id', $patient->id)
                    ->where('session_id', $session->id)
                    ->exists(),
                404
            );

            return $session;
        }

        abort_unless(
            in_array($source, [self::SOURCE_SELF, self::SOURCE_ASSIGNED_PORTAL], true),
            404
        );

        if ($source === self::SOURCE_ASSIGNED_PORTAL) {
            $assignmentId = (int) data_get($session->context, 'assignment_id', 0);

            $assignment = $assignmentId > 0
                ? TestAssignment::query()
                    ->whereKey($assignmentId)
                    ->where('patient_id', $patient->id)
                    ->where('session_id', $session->id)
                    ->first()
                : null;

            abort_unless($assignment, 404);
            abort_if($assignment->status === 'cancelled', 404);
        }

        return $session;
    }

    private function patientCanAccessSelfTest(Patient $patient, Test $test): bool
    {
        // Любой тест можно пройти самостоятельно, если он не привязан
        // к клинике через pivot clinic_tests.
        return !$test->clinics()->exists();
    }
}
