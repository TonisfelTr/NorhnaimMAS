<?php

namespace App\Filament\Resources\DiagnoseResource\Pages;

use App\Filament\Resources\DiagnoseResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDiagnose extends EditRecord
{
    protected static string $resource = DiagnoseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
