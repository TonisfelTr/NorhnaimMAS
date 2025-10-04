<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\AdminOverview;
use Filament\Pages\Dashboard as BasePage;

class Dashboard extends BasePage
{
    protected function getHeaderWidgets(): array
    {
        return [
            AdminOverview::class,
        ];
    }
}
