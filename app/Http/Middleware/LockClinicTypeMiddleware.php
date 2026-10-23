<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

/** Тип учреждения задаётся только при регистрации; настройки его не изменяют. */
class LockClinicTypeMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $clinic = $request->user()?->doctor?->clinic;
        abort_unless($clinic, 403);

        $actualType = (string) $clinic->clinic_type;
        $incoming = $request->input('clinic_type');
        if ($request->exists('clinic_type')
            && (!is_string($incoming) || $incoming !== $actualType)) {
            throw ValidationException::withMessages([
                'clinic_type' => 'Тип учреждения задаётся при регистрации и не меняется в настройках.',
            ]);
        }

        // В старой версии SettingsController есть присвоение из request.
        // Даже если в Blade поле удалено, передаём ТОЛЬКО действующий тип.
        $request->merge(['clinic_type' => $actualType]);

        return $next($request);
    }
}
