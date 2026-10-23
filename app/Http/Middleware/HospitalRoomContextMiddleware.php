<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Controllers\Doctors\HospitalImpersonationController;
use App\Models\Clinic;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Выдаёт на ОДИН запрос проверенный контекст выбранного стационара.
 * Не изменяет пользователя, doctors.clinic_id и состояние клиники в БД.
 */
final class HospitalRoomContextMiddleware
{
    public const HOSPITAL_ATTRIBUTE = 'nh_managed_hospital';

    public function handle(Request $request, Closure $next): Response
    {
        $actor = $request->user();
        $session = $request->session()->get(HospitalImpersonationController::SESSION_KEY);
        abort_unless($actor && is_array($session), 403);
        abort_unless(ClinicSettingsAccessMiddleware::allows($actor), 403);

        $actorId = (int) ($session['actor_user_id'] ?? 0);
        $originId = (int) ($session['origin_clinic_id'] ?? 0);
        $targetId = (int) ($session['hospital_id'] ?? 0);
        $routeId = (int) $request->route('hospital');
        $startedAt = (int) ($session['started_at'] ?? 0);

        abort_unless(
            $actorId > 0 && $actorId === (int) $actor->id
            && $originId > 0 && $targetId > 0 && $routeId === $targetId
            && (int) ($actor->doctor?->clinic_id ?? 0) === $originId
            && $startedAt > 0 && time() >= $startedAt
            && time() - $startedAt <= HospitalImpersonationController::MAX_MINUTES * 60,
            403
        );

        $origin = Clinic::query()
            ->whereKey($originId)
            ->where('clinic_type', Clinic::TYPE_OUTPATIENT)
            ->whereNull('deleted_at')
            ->firstOrFail();

        $hospital = Clinic::query()
            ->whereKey($routeId)
            ->where('parent_clinic_id', $origin->id)
            ->where('clinic_type', Clinic::TYPE_INPATIENT)
            ->whereNull('deleted_at')
            ->firstOrFail();

        // Защита от использования POST в непредусмотренном маршруте.
        $name = (string) $request->route()?->getName();
        $methods = [
            'doctors.hospital-impersonation.rooms.index' => 'GET',
            'doctors.hospital-impersonation.rooms.data' => 'GET',
            'doctors.hospital-impersonation.rooms.store' => 'POST',
            'doctors.hospital-impersonation.rooms.update' => 'POST',
            'doctors.hospital-impersonation.rooms.delete' => 'POST',
            'doctors.hospital-impersonation.rooms.layout' => 'POST',
        ];
        abort_unless(isset($methods[$name]) && $request->isMethod($methods[$name]), 403);

        $request->attributes->set(self::HOSPITAL_ATTRIBUTE, $hospital);

        return $next($request);
    }
}
