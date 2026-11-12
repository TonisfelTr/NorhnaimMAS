<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class DevelopmentAccountsAuthenticationMiddleware
{
    public const array DEVELOPMENT_ACCOUNTS = [
        'admin@admin.ru',
        'doctor@doctor.ru',
        'patient@patient.ru',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (config('app.debug')) {
            return $next($request);
        }

        /*
         * Если тестовая учётка была авторизована ещё при APP_DEBUG=true,
         * немедленно завершаем её сессию после перехода в production.
         */
        if (Auth::check() && $this->isDevelopmentEmail(Auth::user()?->email)) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('main.index')
                ->withErrors([
                    'email' => 'Неправильный логин или пароль.',
                ])
                ->with('open_modal', 'authorization-block')
                ->with('auth_mode', 'login');
        }

        /*
         * Email есть также в формах регистрации и восстановления пароля.
         * Поэтому входную попытку блокируем ТОЛЬКО на actions.login.
         */
        if (
            $request->routeIs('actions.login')
            && $this->isDevelopmentEmail($request->input('email'))
        ) {
            return redirect()
                ->back()
                ->withInput($request->only('email', 'remember_me'))
                ->withErrors([
                    'email' => 'Неправильный логин или пароль.',
                ])
                ->with('open_modal', 'authorization-block')
                ->with('auth_mode', 'login');
        }

        return $next($request);
    }

    private function isDevelopmentEmail(mixed $email): bool
    {
        $email = mb_strtolower(trim((string) $email));

        return $email !== ''
            && in_array($email, self::DEVELOPMENT_ACCOUNTS, true);
    }
}
