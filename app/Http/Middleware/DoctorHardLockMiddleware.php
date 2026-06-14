<?php

namespace App\Http\Middleware;

use App\Models\TestSession;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;

class DoctorHardLockMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();
        if (!$user || !$user->doctor) {
            return $next($request);
        }

        $doctorId = $user->doctor->id;

        $active = TestSession::query()
            ->where('locked_by', $doctorId)
            ->whereNotNull('locked_at')
            ->whereIn('status', ['in_progress', 'submitted', 'in_review', 'coding_done'])
            ->orderByDesc('locked_at')
            ->first();

        if (!$active) {
            return $next($request);
        }

        // таймаут — снимаем lock и пропускаем
        $lockedAt = $active->locked_at ? Carbon::parse($active->locked_at) : null;
        if ($lockedAt && $lockedAt->lte(now()->subHour())) {
            $active->status = 'timeout';
            $active->in_progress = false;
            $active->completed_at = now();
            $active->locked_at = null;
            $active->locked_by = null;
            $active->save();

            return $next($request);
        }

        $status  = $active->status;
        $finalOk = (bool) data_get($active->context, 'final_pin_ok', false);

        // --- 1) тест идёт ---
        if ($status === 'in_progress' && $active->in_progress) {
            $allowed = [
                'doctors.reception.tests.run',
                'doctors.reception.tests.payload',
                'doctors.reception.tests.submit',
                'doctors.reception.tests.finish',
                // форма/проверка PIN пациента (если вдруг надо вернуться)
                'doctors.tests.pin.form',
                'doctors.tests.pin.verify',
            ];

            if (!$request->routeIs($allowed)) {
                return redirect()->route('doctors.reception.tests.run', $active->id);
            }

            return $next($request);
        }

        // --- 2) пациент закончил, PIN врача ЕЩЁ НЕ введён ---
        if ($status === 'submitted' && !$finalOk) {
            $allowed = [
                // остаёмся в тестовом контуре
                'doctors.reception.tests.run',
                'doctors.reception.tests.payload',
                // ввод PIN врача
                'doctors.tests.final_pin.form',
                'doctors.tests.final_pin.verify',
            ];

            if (!$request->routeIs($allowed)) {
                return redirect()->route('doctors.tests.final_pin.form', $active->id);
            }

            return $next($request);
        }

        // --- 3) PIN врача введён, но панель ЕЩЁ заблокирована (до кнопки unlock) ---
        if ($status === 'submitted' && $finalOk) {
            $allowed = [
                'doctors.patients.sessions.show',
                'doctors.patients.sessions.results.page',
                'doctors.patients.sessions.result',
                'doctors.patients.sessions.finish',
                'doctors.patients.ts.unlock',
                'doctors.tests.final_pin.form',
                'doctors.tests.final_pin.verify',
            ];

            if (!$request->routeIs($allowed)) {
                return redirect()->route('doctors.patients.sessions.show', $active->id);
            }

            return $next($request);
        }

        return $next($request);
    }
}
