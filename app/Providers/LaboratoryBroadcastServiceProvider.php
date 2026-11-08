<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

/** Register laboratory private channels even with `php artisan route:cache`. */
final class LaboratoryBroadcastServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        require base_path('routes/laboratory-channels.php');
    }
}
