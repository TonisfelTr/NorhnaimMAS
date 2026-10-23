<?php

namespace App\Http\Middleware;

use App\Support\HospitalPatientAccess;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Общий вход в панель врача.
 * Специальный admin@admin.ru допускается без Doctor-модели только при
 * APP_DEBUG=true. Обычные врачи проходят прежний DoctorHardLockMiddleware.
 * Права работы с конкретной клиникой проверяются в контроллерах.
 */
class DoctorOrHospitalAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        abort_unless($user, 403);

        // Специальный bootstrap-аккаунт полностью отключён вне dev-режима,
        // даже если у него случайно останутся дополнительные роли.
        $isBootstrapIdentity = in_array('admin@admin.ru', [
            strtolower(trim((string) $user->getRawOriginal('email'))),
            strtolower(trim((string) $user->getRawOriginal('login'))),
        ], true);
        if ($isBootstrapIdentity && !config('app.debug')) {
            abort(403);
        }

        if (app(HospitalPatientAccess::class)->isDebugAdmin($user)) {
            return $next($request);
        }

        abort_unless($user->hasRole('doctors'), 403);

        return app(DoctorHardLockMiddleware::class)->handle($request, $next);
    }
}
