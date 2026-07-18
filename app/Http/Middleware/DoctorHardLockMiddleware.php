<?php

namespace App\Http\Middleware;

use App\Models\TestSession;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class DoctorHardLockMiddleware
{
    /**
     * Резервный лимит, если у теста не заполнено estimated_minutes.
     */
    private const DEFAULT_SESSION_MINUTES = 60;

    public function handle(
        Request $request,
        Closure $next
    ): Response {
        $user = $request->user();

        /*
         * Middleware применяется только к врачу.
         */
        if (!$user || !$user->doctor) {
            return $next($request);
        }

        $doctorId = (int) $user->doctor->id;

        /*
         * Ищем сессию, которая сейчас блокирует панель врача.
         */
        $active = TestSession::query()
            ->with([
                'test:id,estimated_minutes',
                'assignment',
            ])
            ->where('locked_by', $doctorId)
            ->whereNotNull('locked_at')
            ->whereIn('status', [
                'in_progress',
                'submitted',
                'in_review',
                'coding_done',
            ])
            ->orderByDesc('locked_at')
            ->first();

        /*
         * Активной блокирующей сессии нет.
         */
        if (!$active) {
            return $next($request);
        }

        /*
         * Тайм-аут применяется только пока пациент проходит тест.
         *
         * Для submitted/in_review/coding_done время прохождения уже
         * закончилось, поэтому estimated_minutes к ним не применяется.
         */
        if (
            $active->status === 'in_progress'
            && (bool) $active->in_progress
            && $this->hasTimedOut($active)
        ) {
            $this->timeoutSession($active);

            /*
             * Блокировка снята — разрешаем текущий запрос.
             */
            return $next($request);
        }

        $status = (string) $active->status;

        $finalPinOk = (bool) data_get(
            $active->context,
            'final_pin_ok',
            false
        );

        /*
         * =========================================================
         * 1. ПАЦИЕНТ ПРОХОДИТ ТЕСТ
         * =========================================================
         */
        if (
            $status === 'in_progress'
            && (bool) $active->in_progress
        ) {
            $allowedRoutes = [
                /*
                 * Страница прохождения и API теста.
                 */
                'doctors.reception.tests.run',
                'doctors.reception.tests.payload',
                'doctors.reception.tests.submit',
                'doctors.reception.tests.finish',

                /*
                 * Переход к PIN для досрочного прерывания.
                 */
                'doctors.tests.final_pin.form',
                'doctors.tests.final_pin.verify',

                /*
                 * Оставляем для совместимости, если маршрут
                 * принудительного завершения ещё используется.
                 */
                'doctors.reception.tests.force-finish',

                /*
                 * Начальный PIN теста.
                 */
                'doctors.tests.pin.form',
                'doctors.tests.pin.verify',
            ];

            if (!$request->routeIs($allowedRoutes)) {
                return redirect()->route(
                    'doctors.reception.tests.run',
                    [
                        'session' => $active->id,
                    ]
                );
            }

            return $next($request);
        }

        /*
         * =========================================================
         * 2. ПАЦИЕНТ ЗАВЕРШИЛ ТЕСТ, ФИНАЛЬНЫЙ PIN НЕ ВВЕДЁН
         * =========================================================
         */
        if (
            $status === 'submitted'
            && !$finalPinOk
        ) {
            $allowedRoutes = [
                'doctors.reception.tests.run',
                'doctors.reception.tests.payload',

                /*
                 * Форма и проверка финального PIN.
                 */
                'doctors.tests.final_pin.form',
                'doctors.tests.final_pin.verify',
            ];

            if (!$request->routeIs($allowedRoutes)) {
                return redirect()->route(
                    'doctors.tests.final_pin.form',
                    [
                        'session' => $active->id,
                    ]
                );
            }

            return $next($request);
        }

        /*
         * =========================================================
         * 3. ФИНАЛЬНЫЙ PIN ВВЕДЁН, НО БЛОКИРОВКА ЕЩЁ НЕ СНЯТА
         * =========================================================
         */
        if (
            $status === 'submitted'
            && $finalPinOk
        ) {
            $allowedRoutes = [
                /*
                 * Просмотр результата.
                 */
                'doctors.patients.sessions.show',
                'doctors.patients.sessions.results.page',
                'doctors.patients.sessions.result',

                /*
                 * Окончательная финализация и разблокировка.
                 */
                'doctors.patients.sessions.finish',
                'doctors.patients.ts.unlock',

                /*
                 * Финальный PIN.
                 */
                'doctors.tests.final_pin.form',
                'doctors.tests.final_pin.verify',
            ];

            if (!$request->routeIs($allowedRoutes)) {
                return redirect()->route(
                    'doctors.patients.sessions.show',
                    [
                        'session' => $active->id,
                    ]
                );
            }

            return $next($request);
        }

        /*
         * =========================================================
         * 4. СЕССИЯ НА ПРОВЕРКЕ ИЛИ КОДИРОВАНИИ
         * =========================================================
         */
        if (
            in_array(
                $status,
                ['in_review', 'coding_done'],
                true
            )
        ) {
            $allowedRoutes = [
                'doctors.patients.sessions.show',
                'doctors.patients.sessions.results.page',
                'doctors.patients.sessions.result',
                'doctors.patients.sessions.finish',

                'doctors.patients.responses.code',
                'doctors.patients.responses.rubric',

                'doctors.patients.ts.unlock',

                'doctors.tests.final_pin.form',
                'doctors.tests.final_pin.verify',
            ];

            if (!$request->routeIs($allowedRoutes)) {
                return redirect()->route(
                    'doctors.patients.sessions.show',
                    [
                        'session' => $active->id,
                    ]
                );
            }

            return $next($request);
        }

        return $next($request);
    }

    /**
     * Определяет лимит времени для теста.
     */
    private function timeoutMinutes(
        TestSession $session
    ): int {
        $estimatedMinutes = (int) (
            $session->test?->estimated_minutes ?? 0
        );

        return $estimatedMinutes > 0
            ? $estimatedMinutes
            : self::DEFAULT_SESSION_MINUTES;
    }

    /**
     * Проверяет, истекло ли время прохождения.
     */
    private function hasTimedOut(
        TestSession $session
    ): bool {
        if (!$session->locked_at) {
            return false;
        }

        $limitMinutes = $this->timeoutMinutes($session);

        $startedAt = Carbon::parse(
            $session->locked_at
        );

        $expiresAt = $startedAt
            ->copy()
            ->addMinutes($limitMinutes);

        return now()->greaterThanOrEqualTo($expiresAt);
    }

    /**
     * Завершает тест по тайм-ауту и снимает блокировку панели.
     */
    private function timeoutSession(
        TestSession $session
    ): void {
        $limitMinutes = $this->timeoutMinutes($session);

        DB::transaction(function () use (
            $session,
            $limitMinutes
        ): void {
            $context = $this->normalizeContext(
                $session->context
            );

            /*
             * answers и sort_order не очищаем:
             * частично заполненный результат сохраняется.
             */
            $context['timed_out'] = true;
            $context['timeout_minutes'] = $limitMinutes;
            $context['timed_out_at'] =
                now()->toIso8601String();

            $session->update([
                'status' => 'timeout',
                'in_progress' => false,
                'completed_at' => now(),

                /*
                 * Снимаем блокировку докторской панели.
                 */
                'locked_at' => null,
                'locked_by' => null,

                'context' => $context,
            ]);

            $assignment = $session->relationLoaded(
                'assignment'
            )
                ? $session->assignment
                : $session->assignment()->first();

            if ($assignment) {
                $assignment->update([
                    'status' => 'timeout',
                    'finished_at' => now(),
                ]);
            }
        });
    }

    /**
     * Безопасно преобразует context в массив.
     */
    private function normalizeContext(
        mixed $context
    ): array {
        if (is_array($context)) {
            return $context;
        }

        if (is_string($context)) {
            $decoded = json_decode(
                $context,
                true
            );

            return is_array($decoded)
                ? $decoded
                : [];
        }

        if (is_object($context)) {
            return (array) $context;
        }

        return [];
    }
}
