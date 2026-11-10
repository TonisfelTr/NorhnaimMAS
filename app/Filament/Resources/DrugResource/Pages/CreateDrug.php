<?php

namespace App\Filament\Resources\DrugResource\Pages;

use App\Filament\Resources\DrugResource;
use App\Filament\Resources\DrugResource\Support\DrugFormData;
use Filament\Resources\Pages\CreateRecord;

class CreateDrug extends CreateRecord
{
    protected static string $resource = DrugResource::class;

    protected array $drugRelationState = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->drugRelationState = DrugFormData::extractRelations($data);

        return DrugFormData::prepareForSave($data);
    }

    protected function afterCreate(): void
    {
        DrugFormData::syncRelations($this->record, $this->drugRelationState);
    }
}
