<?php

namespace App\Services;

use App\Models\Patient;
use App\Models\PatientProfileEvent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class PatientProfileAuditService
{
    private const LABELS = [
        'surname' => 'Фамилия',
        'name' => 'Имя',
        'patronym' => 'Отчество',
        'birth_at' => 'Дата рождения',
        'gender' => 'Пол',
        'phone' => 'Телефон',
        'email' => 'Контактный email',
        'birth_place' => 'Место рождения',
        'address_registration' => 'Адрес регистрации',
        'address_residence' => 'Адрес проживания',
    ];

    public function recordChanges(
        Patient $patient,
        array $before,
        array $after
    ): void {
        $changes = [];

        foreach (self::LABELS as $field => $label) {
            if (! array_key_exists($field, $after)) {
                continue;
            }

            $old = $this->normalize($field, $before[$field] ?? null);
            $new = $this->normalize($field, $after[$field] ?? null);

            if ($old === $new) {
                continue;
            }

            $changes[$label] = [
                'old' => $old,
                'new' => $new,
            ];
        }

        if ($changes === []) {
            return;
        }

        $this->record(
            $patient,
            'profile_updated',
            'Изменены личные данные',
            $changes
        );
    }

    public function record(
        Patient $patient,
        string $eventType,
        string $title,
        ?array $changes = null,
        ?array $meta = null
    ): void {
        if (! Schema::hasTable('patient_profile_events')) {
            return;
        }

        PatientProfileEvent::query()->create([
            'patient_id' => $patient->getKey(),
            'actor_user_id' => Auth::id(),
            'event_type' => $eventType,
            'title' => $title,
            'changes' => $changes,
            'meta' => $meta,
            'occurred_at' => now(),
        ]);
    }

    private function normalize(string $field, mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($field === 'birth_at') {
            return substr((string) $value, 0, 10);
        }

        if ($field === 'gender') {
            return match ((string) $value) {
                'M' => 'Мужской',
                'F' => 'Женский',
                default => (string) $value,
            };
        }

        return trim((string) $value);
    }
}
