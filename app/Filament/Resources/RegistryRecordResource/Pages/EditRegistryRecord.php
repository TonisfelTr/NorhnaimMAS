<?php

namespace App\Filament\Resources\RegistryRecordResource\Pages;

use App\Filament\Resources\RegistryRecordResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRegistryRecord extends EditRecord
{
    protected static string $resource = RegistryRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
