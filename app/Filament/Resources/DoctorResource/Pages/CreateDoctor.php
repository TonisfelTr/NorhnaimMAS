<?php

namespace App\Filament\Resources\DoctorResource\Pages;

use App\Filament\Resources\DoctorResource;
use App\Models\Clinic;
use Filament\Resources\Pages\CreateRecord;

class CreateDoctor extends CreateRecord
{
    protected static string $resource = DoctorResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->normalizeDoctorData($data);
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    private function normalizeDoctorData(array $data): array
    {
        $data['surname'] = trim((string) ($data['surname'] ?? ''));
        $data['name'] = trim((string) ($data['name'] ?? ''));
        $data['patronym'] = $this->nullableText($data['patronym'] ?? null);
        $data['profession'] = trim((string) ($data['profession'] ?? '')) ?: 'Врач';
        $data['status'] = trim((string) ($data['status'] ?? '')) ?: 'Психиатр';
        $data['phone'] = $this->nullableText($data['phone'] ?? null);
        $data['email'] = $this->nullableText($data['email'] ?? null);
        $data['about'] = $this->nullableText($data['about'] ?? null);
        $data['snils'] = $this->nullableText($data['snils'] ?? null);
        $data['ready_for_telemedicine'] = (bool) ($data['ready_for_telemedicine'] ?? false);

        if (!empty($data['clinic_id'])) {
            $clinic = Clinic::query()
                ->select([
                    'id',
                    'city',
                    'address',
                ])
                ->find($data['clinic_id']);

            if ($clinic) {
                // Не сохраняем старые тестовые/ручные значения врача:
                // при привязке к клинике источником истины являются её настройки.
                $data['city'] = trim((string) ($clinic->city ?? ''));
                $data['address_job'] = trim((string) ($clinic->address ?? ''));
            }
        }

        return $data;
    }

    private function nullableText(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value !== ''
            ? $value
            : null;
    }
}
