<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    // Users, roles, clinics and doctors should all commit (or roll back) together.
    protected ?bool $hasDatabaseTransactions = true;

    private array $doctorFormData = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->doctorFormData = UserResource::pullDoctorFields($data);

        return $data;
    }

    protected function afterCreate(): void
    {
        // Here $this->record is already the NEW user with its own ID.
        UserResource::saveDoctorProfile($this->record, $this->doctorFormData);
    }
}
