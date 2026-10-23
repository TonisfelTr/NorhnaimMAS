<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ClinicSettingsAccessMiddleware
{
    public static function allows(?User $user): bool
    {
        return $user !== null && (
            $user->hasRole('clinic_admin') || self::isDedicatedDebugTester($user)
        );
    }

    public static function isDedicatedDebugTester(?User $user): bool
    {
        return $user !== null
            && config('app.debug') === true
            && strcasecmp((string) $user->email, 'admin@admin.ru') === 0;
    }

    /**
     * Историческое имя метода сохраняем для обратной совместимости.
     * В старом проекте оно означало "clinic_admin ИЛИ debug-тестер".
     */
    public static function isDebugTester(?User $user): bool
    {
        return self::allows($user);
    }

    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(self::allows($request->user()), 403);
        return $next($request);
    }
}
