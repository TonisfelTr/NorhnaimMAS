<?php

namespace App\Filament\Resources\BannedResource\Pages;

use App\Filament\Resources\BannedResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBanned extends EditRecord
{
    protected static string $resource = BannedResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
