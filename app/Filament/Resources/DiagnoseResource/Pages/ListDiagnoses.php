<?php

namespace App\Filament\Resources\DiagnoseResource\Pages;

use App\Filament\Resources\DiagnoseResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDiagnoses extends ListRecords
{
    protected static string $resource = DiagnoseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
