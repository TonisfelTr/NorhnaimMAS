<?php

namespace App\Filament\Widgets;

use App\Models\Clinic;
use App\Models\Diagnose;
use App\Models\Doctor;
use App\Models\Drug;
use App\Models\Patient;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Spatie\Permission\Models\Role;

class AdminOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Докторов', Doctor::count())
                ->icon('heroicon-o-user-group')
                ->color('success'),

            Stat::make('Пациентов', Patient::count())
                ->icon('heroicon-o-users')
                ->color('info'),

            Stat::make('Администраторов', User::role('admins')->count())
                ->icon('heroicon-o-shield-check')
                ->color('warning'),

            Stat::make('Клиник', Clinic::count())
                ->icon('heroicon-o-building-office')
                ->color('primary'),

            Stat::make('Лекарств', Drug::count())
                ->icon('heroicon-o-beaker')
                ->color('danger'),
        ];
    }
}
