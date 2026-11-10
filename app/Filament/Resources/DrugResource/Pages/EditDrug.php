<?php

namespace App\Filament\Resources\DrugResource\Pages;

use App\Filament\Resources\DrugResource;
use App\Filament\Resources\DrugResource\Support\DrugFormData;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDrug extends EditRecord
{
    protected static string $resource = DrugResource::class;

    protected array $drugRelationState = [];

    protected function mutateFormDataBeforeFill(array $data): array
    {
        return DrugFormData::prepareForFill($data, $this->record);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->drugRelationState = DrugFormData::extractRelations($data);

        return DrugFormData::prepareForSave($data, $this->record);
    }

    protected function afterSave(): void
    {
        DrugFormData::syncRelations($this->record, $this->drugRelationState);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
