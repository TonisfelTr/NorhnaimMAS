<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected ?bool $hasDatabaseTransactions = true;

    private array $doctorFormData = [];

    protected function mutateFormDataBeforeFill(array $data): array
    {
        return array_merge($data, UserResource::doctorFormValues($this->record));
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->doctorFormData = UserResource::pullDoctorFields($data);

        return $data;
    }

    protected function afterSave(): void
    {
        // Updating the existing user's doctor profile. Does not create duplicates.
        UserResource::saveDoctorProfile($this->record, $this->doctorFormData);
    }

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
