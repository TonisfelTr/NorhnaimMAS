<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

final class ClinicTelemedicineSessionServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(base_path('routes/clinic_telemedicine_sessions.php'));
    }
}
