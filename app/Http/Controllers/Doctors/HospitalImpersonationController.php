<?php

declare(strict_types=1);

namespace App\Http\Controllers\Doctors;

use App\Http\Controllers\Controller;
use App\Http\Middleware\ClinicSettingsAccessMiddleware;
use App\Models\Clinic;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

/**
 * Временный КОНТЕКСТ стационара, а не вход под чужой учётной записью.
 * Единственный администратор амбулатории остаётся авторизован под СВОИМ User.
 * Не изменяются users, roles, doctors.clinic_id, clinics.clinic_type.
 */
class HospitalImpersonationController extends Controller
{
    // Имя сохранено для совместимости с уже установленными Blade/layout/guard.
    public const SESSION_KEY = 'norhnaim.hospital_impersonation';
    public const MAX_MINUTES = 60;

    public function enter(Request $request, int $hospital): RedirectResponse
    {
        $actor = $request->user();
        abort_unless($actor && ClinicSettingsAccessMiddleware::allows($actor), 403);
        abort_unless(! $request->session()->has(self::SESSION_KEY), 409,
            'Режим стационара уже включён. Сначала вернитесь в амбулаторию.');

        $parentId = (int) ($actor->doctor?->clinic_id ?? 0);
        abort_unless($parentId > 0, 403);
        $parent = Clinic::query()
            ->whereKey($parentId)
            ->where('clinic_type', Clinic::TYPE_OUTPATIENT)
            ->whereNull('deleted_at')
            ->firstOrFail();

        $target = Clinic::query()
            ->whereKey($hospital)
            ->where('clinic_type', Clinic::TYPE_INPATIENT)
            ->where('parent_clinic_id', $parent->id)
            ->whereNull('deleted_at')
            ->firstOrFail();

        // Не используем Auth::login, loginUsingId и не назначаем новую роль.
        $request->session()->regenerate();
        $request->session()->put(self::SESSION_KEY, [
            'actor_user_id' => (int) $actor->id,
            'origin_clinic_id' => (int) $parent->id,
            'hospital_id' => (int) $target->id,
            'origin_name' => (string) $parent->name,
            'hospital_name' => (string) $target->name,
            'started_at' => time(),
            'token' => bin2hex(random_bytes(16)),
        ]);
        Log::info('Hospital administration context entered', [
            'actor_user_id' => $actor->id,
            'origin_clinic_id' => $parent->id,
            'hospital_id' => $target->id,
        ]);

        // Сохраняем токен вкладки: новое открытие начинается с амбулатории.
        return redirect()->route('doctors.hospital-impersonation.bridge');
    }

    public function bridge(Request $request): View
    {
        $ctx = $request->session()->get(self::SESSION_KEY);
        abort_unless(is_array($ctx) &&
            (int) ($ctx['actor_user_id'] ?? 0) === (int) $request->user()?->id, 403);

        return view('doctors.hospital-login-bridge', [
            'token' => (string) $ctx['token'],
            'destination' => route('doctors.hospital-impersonation.workspace',
                ['hospital' => (int) $ctx['hospital_id']]),
        ]);
    }

    public function leave(Request $request): RedirectResponse
    {
        self::finish($request, 'manual_exit');
        return redirect()->route('doctors.settings.clinic-affiliations.index')
            ->with('success', 'Вы вернулись в амбулаторию. Режим стационара выключен.');
    }

    /** Завершает контекст. Учетная запись пользователя вообще не менялась. */
    public static function finish(Request $request, string $reason): void
    {
        $ctx = $request->session()->get(self::SESSION_KEY);
        if (!is_array($ctx)) {
            return;
        }
        $request->session()->forget(self::SESSION_KEY);
        Log::info('Hospital administration context left', [
            'reason' => $reason,
            'actor_user_id' => $ctx['actor_user_id'] ?? null,
            'origin_clinic_id' => $ctx['origin_clinic_id'] ?? null,
            'hospital_id' => $ctx['hospital_id'] ?? null,
        ]);
        // Не invalidate(): иначе пользователь будет разлогинен и потеряет session.
        $request->session()->regenerate();
    }
}
