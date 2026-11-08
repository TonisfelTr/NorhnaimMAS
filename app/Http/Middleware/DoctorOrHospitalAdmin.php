<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Общий вход в панель врача.
 * Выделенный администратор admin@admin.ru с ролью admins допускается
 * без Doctor-модели независимо от APP_DEBUG.
 * Остальные врачи проходят DoctorHardLockMiddleware.
 * Права работы с конкретной клиникой проверяются отдельно.
 */
class DoctorOrHospitalAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        // NORHNAIM_TELEMED_ACCESS_V1: card route only; controller checks this patient.
        if ($request->routeIs('doctors.patients.medical_card')
            && app(\App\Support\TelemedicineJournalAccess::class)->hasAdministrativeClinic($request->user())) {
            return $next($request);
        }

        $user = $request->user();
        abort_unless($user, 403);


        // NORHNAIM_CLINIC_ADMIN_GLOBAL_MEDCARD_V1
        // clinic_admin имеет глобальные права именно на маршруты медицинских карт.
        // Остальные doctors.* продолжают проходить штатные ограничения.
        $isGlobalClinicAdmin = $user->hasRole('clinic_admin');

        if (!$isGlobalClinicAdmin) {
            foreach (['email', 'login'] as $field) {
                if (
                    strcasecmp(
                        trim((string) $user->getRawOriginal($field)),
                        'admin@admin.ru'
                    ) === 0
                ) {
                    $isGlobalClinicAdmin = true;
                    break;
                }
            }
        }

        if (
            $isGlobalClinicAdmin
            && $request->routeIs('doctors.patients.*')
        ) {
            return $next($request);
        }

        // Зарезервированный bootstrap-аккаунт: доступ только при совпадении
        // email И наличии системной роли admins. Одного email/login недостаточно.
        $isBootstrapIdentity = in_array('admin@admin.ru', [
            strtolower(trim((string) $user->getRawOriginal('email'))),
            strtolower(trim((string) $user->getRawOriginal('login'))),
        ], true);
        if ($isBootstrapIdentity) {
            abort_unless(ClinicSettingsAccessMiddleware::isDedicatedAdmin($user), 403);

            return $next($request);
        }

        // Медсестре разрешён вход ТОЛЬКО на маршрут чтения медкарты.
        // Остальные doctors.* по-прежнему защищены, а сам контроллер
        // дополнительно проверяет, относится ли пациент к её клинике.
        $isNurseRole = $user->hasRole('nurses') || $user->hasRole('nurse');
        $hasNurseProfile = method_exists($user, 'nurse')
            && (int) ($user->nurse?->clinic_id ?? 0) > 0;

        if ($request->routeIs('doctors.patients.medical_card')
            && $isNurseRole && $hasNurseProfile) {
            return $next($request);
        }

        abort_unless($user->hasRole('doctors'), 403);

        return app(DoctorHardLockMiddleware::class)->handle($request, $next);
    }
}
