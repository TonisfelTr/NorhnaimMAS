<?php

namespace App\Filament\Resources\NurseResource\Pages;

use App\Filament\Resources\NurseResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListNurses extends ListRecords
{
    protected static string $resource = NurseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
