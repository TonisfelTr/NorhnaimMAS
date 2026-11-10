<?php

namespace App\Filament\Resources\ClinicResource\Pages;

use App\Filament\Resources\ClinicResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditClinic extends EditRecord
{
    protected static string $resource = ClinicResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        foreach ([
            'phones',
            'services',
            'inpatient_working_hours',
            'inpatient_visiting_hours',
        ] as $field) {
            if (
                isset($data[$field])
                && is_string($data[$field])
            ) {
                $data[$field] = json_decode(
                    $data[$field],
                    true
                ) ?: [];
            }
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['name'] = trim((string) ($data['name'] ?? ''));
        $data['address'] = trim((string) ($data['address'] ?? ''));
        $data['city'] = $this->nullableText($data['city'] ?? null);
        $data['description'] = $this->nullableText(
            $data['description'] ?? null
        );

        $data['clinic_type'] = in_array(
            $data['clinic_type'] ?? null,
            ['outpatient', 'inpatient'],
            true
        )
            ? $data['clinic_type']
            : 'outpatient';

        $data['phones'] = $this->normalizePhones(
            $data['phones'] ?? []
        );

        if ($data['clinic_type'] === 'outpatient') {
            $data['services'] = array_values(
                array_intersect(
                    (array) ($data['services'] ?? []),
                    [
                        'in_person',
                        'telemedicine',
                    ]
                )
            );

            $data['parent_clinic_id'] = null;
        } else {
            $data['inpatient_mode'] = in_array(
                $data['inpatient_mode'] ?? null,
                [
                    'round_the_clock',
                    'day',
                    'night',
                ],
                true
            )
                ? $data['inpatient_mode']
                : 'round_the_clock';
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Удалить клинику')
                ->requiresConfirmation(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    private function normalizePhones(mixed $phones): array
    {
        if (is_string($phones)) {
            $phones = json_decode(
                $phones,
                true
            ) ?: [];
        }

        if (!is_array($phones)) {
            return [];
        }

        return collect($phones)
            ->map(
                static fn ($row): array => [
                    'label' => trim(
                        (string) (
                            $row['label']
                            ?? ''
                        )
                    ),
                    'phone' => trim(
                        (string) (
                            $row['phone']
                            ?? ''
                        )
                    ),
                ]
            )
            ->filter(
                static fn (array $row): bool =>
                    $row['phone'] !== ''
            )
            ->values()
            ->all();
    }

    private function nullableText(mixed $value): ?string
    {
        $value = trim(
            (string) ($value ?? '')
        );

        return $value !== ''
            ? $value
            : null;
    }
}
