<?php

namespace App\Filament\Resources\RegistryRecordResource\Pages;

use App\Filament\Resources\RegistryRecordResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRegistryRecords extends ListRecords
{
    protected static string $resource = RegistryRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
