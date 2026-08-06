<?php

namespace App\Filament\Resources\BannedResource\Pages;

use App\Filament\Resources\BannedResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBanneds extends ListRecords
{
    protected static string $resource = BannedResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
