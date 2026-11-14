<?php

namespace App\Filament\Resources\PatientLinkRequestResource\Pages;

use App\Filament\Resources\PatientLinkRequestResource;
use Filament\Resources\Pages\ListRecords;

class ListPatientLinkRequests extends ListRecords
{
    protected static string $resource = PatientLinkRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
