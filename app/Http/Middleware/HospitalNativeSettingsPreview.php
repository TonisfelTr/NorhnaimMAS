<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Controllers\Doctors\HospitalImpersonationController;
use App\Models\Clinic;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Read-only bridge to ORIGINAL doctors.settings.departments / rooms pages.
 * No changes to doctors.clinic_id, auth user, or DB. No regular writes allowed.
 * Do NOT attach to POST/PUT/DELETE routes.
 */
final class HospitalNativeSettingsPreview
{
    private const PREVIEW_ROUTES = [
        'doctors.settings.departments',
        'doctors.settings.rooms',
        'doctors.settings.rooms.data',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $context = $request->session()->get(HospitalImpersonationController::SESSION_KEY);
        if (!is_array($context)) {
            return $next($request); // Real inpatient doctors use native pages normally.
        }

        abort_unless($request->isMethod('GET'), 403);
        abort_unless(in_array($request->route()?->getName(), self::PREVIEW_ROUTES, true), 403);
        abort_unless($request->user() !== null, 403);

        $user = $request->user();
        $doctor = $user->doctor;
        $originId = (int) ($context['origin_clinic_id'] ?? 0);
        $hospitalId = (int) ($context['hospital_id'] ?? 0);
        $actorId = (int) ($context['actor_user_id'] ?? 0);
        $startedAt = (int) ($context['started_at'] ?? 0);

        abort_unless(
            $actorId > 0 && (int) $user->id === $actorId
                && $doctor && $originId > 0 && $hospitalId > 0
                && (int) $doctor->clinic_id === $originId
                && ClinicSettingsAccessMiddleware::allows($user)
                && $startedAt > 0
                && time() - $startedAt <= HospitalImpersonationController::MAX_MINUTES * 60,
            403
        );

        $hospital = Clinic::query()
            ->whereKey($hospitalId)
            ->where('clinic_type', Clinic::TYPE_INPATIENT)
            ->where('parent_clinic_id', $originId)
            ->whereNull('deleted_at')
            ->firstOrFail();

        // Clone is request-local. No calls to save() on this clone are allowed:
        // the guard only permits GET, and this bridge never persists anything.
        // Replace the cached User->doctor relation so unmodified *index()*
        // controllers resolve their existing clinic lookups to the hospital.
        $projection = clone $doctor;
        $projection->clinic_id = $hospital->id;
        $projection->setRelation('clinic', $hospital);

        $user->setRelation('doctor', $projection);
        try {
            $response = $next($request);
        } finally {
            // Restore original profile in RAM. Database value was never touched.
            $user->setRelation('doctor', $doctor);
        }

        // HospitalRoomController::data() includes bed.patient by design.
        // Facility administrators must NOT acquire patient/card data through
        // this preview. Keep occupancy counts, mask personally identifiable data.
        if ($request->routeIs('doctors.settings.rooms.data') && $response instanceof JsonResponse) {
            $payload = $response->getData(true);
            if (is_array($payload)) {
                foreach ($payload['rooms'] ?? [] as $roomIndex => $room) {
                    foreach ($room['beds'] ?? [] as $bedIndex => $bed) {
                        $payload['rooms'][$roomIndex]['beds'][$bedIndex]['patient'] = null;
                        $payload['rooms'][$roomIndex]['beds'][$bedIndex]['assigned_at'] = null;
                    }
                }
                $response->setData($payload);
            }
        }

        // Prevent browser caching of previewed hospital facility information.
        $response->headers->set('Cache-Control', 'no-store, private');
        return $response;
    }
}
