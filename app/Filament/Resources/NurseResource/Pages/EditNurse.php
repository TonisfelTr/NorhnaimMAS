<?php

namespace App\Filament\Resources\NurseResource\Pages;

use App\Filament\Resources\NurseResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditNurse extends EditRecord
{
    protected static string $resource = NurseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
