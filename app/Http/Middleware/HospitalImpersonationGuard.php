<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Controllers\Doctors\HospitalImpersonationController;
use App\Models\Clinic;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Установить в web middleware group (не только doctors) в bootstrap/app.php.
 * Пока активен контекст, доступны ТОЛЬКО изолированные настройки стационара,
 * адресные подсказки и выход. Ни Filament, ни медкарты, ни общие настройки.
 * Сохранение форм не завершает режим; выход только по явному действию.
 */
class HospitalImpersonationGuard
{
    public function handle(Request $request, Closure $next): Response
    {
        $ctx = $request->session()->get(HospitalImpersonationController::SESSION_KEY);
        if (!is_array($ctx)) {
            return $next($request);
        }

        $user = $request->user();
        $actorId = (int) ($ctx['actor_user_id'] ?? 0);
        $originId = (int) ($ctx['origin_clinic_id'] ?? 0);
        $hospitalId = (int) ($ctx['hospital_id'] ?? 0);

        // В предыдущей версии сессии был login-as под чужой User.
        // Старые активные сеансы нужно завершить ДО развертывания v3.
        if (!$user || $actorId < 1 || (int) $user->id !== $actorId) {
            abort(403, 'Сессия временного режима некорректна. Повторите вход.');
        }

        // Выход должен оставаться доступным даже если стационар удалён,
        // привязка изменена или права пользователя отозваны.
        if ($request->routeIs('doctors.hospital-impersonation.leave')) {
            return $next($request);
        }
        $origin = Clinic::query()->whereKey($originId)
            ->where('clinic_type', Clinic::TYPE_OUTPATIENT)
            ->whereNull('deleted_at')->first();
        $hospital = Clinic::query()->whereKey($hospitalId)
            ->where('clinic_type', Clinic::TYPE_INPATIENT)
            ->where('parent_clinic_id', $originId)
            ->whereNull('deleted_at')->first();

        if (!$origin || !$hospital
            || !ClinicSettingsAccessMiddleware::allows($user)
            || (int) ($user->doctor?->clinic_id ?? 0) !== $originId
            || time() - (int) ($ctx['started_at'] ?? 0)
                > HospitalImpersonationController::MAX_MINUTES * 60
        ) {
            HospitalImpersonationController::finish($request, 'expired_or_revoked');
            abort(403, 'Режим стационара завершён: истёк срок или изменились права.');
        }

        // Клик «вернуться» или переход на список после закрытия вкладки
        // всегда завершает контекст. Авторизация текущего User сохраняется.
        if ($request->isMethod('GET') && $request->routeIs('doctors.settings.clinic-affiliations.index')) {
            HospitalImpersonationController::finish($request, 'returned_to_parent');
            return $next($request);
        }
        if ($request->routeIs('doctors.hospital-impersonation.leave')
            || $request->routeIs('doctors.hospital-impersonation.bridge')) {
            return $next($request);
        }

        if ($request->isMethod('GET') && $request->routeIs('api-search-address')) {
            return $next($request);
        }

        // Отделения пока доступны через прежний безопасный GET-предпросмотр.
        // Обычные doctors.settings.rooms больше НЕ открываются во временном режиме:
        // для палат теперь есть собственный /hospital-impersonation/.../rooms.
        if ($request->isMethod('GET') && $request->routeIs('doctors.settings.departments')) {
            return $next($request);
        }

        if ($request->routeIs('doctors.hospital-impersonation.rooms.*')) {
            // В отдельной группе маршрутов HospitalRoomContextMiddleware также
            // проверит текущего пользователя, принадлежность и метод запроса.
            abort_unless((int) $request->route('hospital') === $hospitalId, 403);
            $response = $next($request);
            if ($request->isMethod('POST')) {
                Log::info('Hospital room settings changed', [
                    'actor_user_id' => $actorId,
                    'origin_clinic_id' => $originId,
                    'hospital_id' => $hospitalId,
                    'route' => $request->route()?->getName(),
                    'status' => $response->getStatusCode(),
                ]);
            }
            return $response;
        }

        // НЕЛЬЗЯ допускать другие doctors.settings.*: они могут взять
        // clinic_id из исходного профиля и изменить амбулаторию.
        abort_unless(
            ($request->isMethod('GET')
                && $request->routeIs('doctors.hospital-impersonation.workspace'))
            || ($request->isMethod('POST')
                && $request->routeIs('doctors.hospital-impersonation.workspace.save')),
            403,
            'Временно разрешены только настройки выбранного стационара. Вернитесь в амбулаторию для остальных разделов.'
        );
        // Дополнительная проверка точного ID маршрута.
        abort_unless((int) $request->route('hospital') === $hospitalId, 403);
        $response = $next($request);

        if (!$request->isMethod('POST')) {
            return $response;
        }
        Log::info('Hospital administration settings request', [
            'actor_user_id' => $actorId,
            'origin_clinic_id' => $originId,
            'hospital_id' => $hospitalId,
            'route' => $request->route()?->getName(),
            'http_status' => $response->getStatusCode(),
        ]);

        // Успешное сохранение НЕ завершает режим: администратор может
        // последовательно настроить корпус, отделение и палаты.
        // Сессия сбрасывается только по «В амбулаторию» или при истечении прав.
        // Ответы 422/403/500 также возвращаются без сброса контекста.
        return $response;
    }
}
