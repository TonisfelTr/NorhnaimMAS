<?php

declare(strict_types=1);

namespace App\Support;

use App\Http\Controllers\Doctors\HospitalImpersonationController;
use App\Http\Middleware\ClinicSettingsAccessMiddleware;
use App\Models\Clinic;
use App\Services\ClinicTelemedicineSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

final class ClinicTelemedicineSettingsAccess
{
    public static function inHospitalPreview(Request $request): bool
    {
        $controller = HospitalImpersonationController::class;

        return $request->hasSession()
            && defined($controller . '::SESSION_KEY')
            && is_array($request->session()->get(constant($controller . '::SESSION_KEY')));
    }

    public static function current(Request $request, bool $lock = false): Clinic
    {
        abort_unless(ClinicSettingsAccessMiddleware::allows($request->user()), 403);
        abort_if(self::inHospitalPreview($request), 403,
            'Вернитесь из режима стационара, чтобы изменить настройки телемедицины своей клиники.');

        $clinicId = (int) ($request->user()?->doctor?->clinic_id ?? 0);
        abort_unless($clinicId > 0, 403, 'Для настройки телемедицины требуется привязка к клинике.');

        $query = Clinic::query()->whereKey($clinicId);

        if ($lock) {
            $query->lockForUpdate();
        }

        $clinic = $query->firstOrFail();
        abort_if($clinic->getRawOriginal('deleted_at') !== null, 403);
        abort_unless(ClinicTelemedicineSettings::enabled($clinic), 403,
            'Сначала включите и сохраните формат «Телемедицина» в общих настройках клиники.');

        return $clinic;
    }

    public static function navigation(Request $request): array
    {
        $hidden = ['visible' => false, 'url' => '', 'active' => false];

        if (!ClinicSettingsAccessMiddleware::allows($request->user())
            || self::inHospitalPreview($request)
            || !Route::has('doctors.settings.telemedicine.index')) {
            return $hidden;
        }

        $clinicId = (int) ($request->user()?->doctor?->clinic_id ?? 0);

        if ($clinicId <= 0) {
            return $hidden;
        }

        $clinic = Clinic::query()->find($clinicId);

        if (!$clinic || $clinic->getRawOriginal('deleted_at') !== null
            || !ClinicTelemedicineSettings::enabled($clinic)) {
            return $hidden;
        }

        return [
            'visible' => true,
            'url' => route('doctors.settings.telemedicine.index'),
            'active' => $request->routeIs('doctors.settings.telemedicine.*'),
        ];
    }
}
