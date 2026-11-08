<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\ClinicTelemedicineSettingsAccess;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureClinicTelemedicineSettings
{
    public function handle(Request $request, Closure $next): Response
    {
        $clinic = ClinicTelemedicineSettingsAccess::current($request);
        $request->attributes->set('clinic_telemedicine_settings_clinic', $clinic);

        return $next($request);
    }
}
