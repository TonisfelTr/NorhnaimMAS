<?php

namespace App\Filament\Resources\PatientProfileChangeRequestResource\Pages;

use App\Filament\Resources\PatientProfileChangeRequestResource;
use Filament\Resources\Pages\ListRecords;

class ListPatientProfileChangeRequests extends ListRecords
{
    protected static string $resource = PatientProfileChangeRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
