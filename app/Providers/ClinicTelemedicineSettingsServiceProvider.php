<?php

declare(strict_types=1);

namespace App\Providers;

use App\Support\ClinicTelemedicineSettingsAccess;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View as RenderedView;

final class ClinicTelemedicineSettingsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(base_path('routes/clinic_telemedicine_settings.php'));

        View::composer('doctors.settings', static function (RenderedView $view): void {
            $view->with('clinicTelemedicineNavigation',
                ClinicTelemedicineSettingsAccess::navigation(request()));
        });
    }
}
