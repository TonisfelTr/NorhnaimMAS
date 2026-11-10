<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Doctor;
use App\Models\DoctorProfileEvent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

final class DoctorProfileAuditService
{
    private const LABELS = [
        'name' => 'Имя',
        'surname' => 'Фамилия',
        'patronym' => 'Отчество',
        'birth_at' => 'Дата рождения',
        'address_job' => 'Место работы',
        'clinic_id' => 'Клиника',
        'status' => 'Специальность',
        'profession' => 'Должность',
        'phone' => 'Телефон',
        'email' => 'E-mail',
        'experience_years' => 'Стаж, лет',
        'experience_months' => 'Стаж, месяцев',
        'city' => 'Город',
        'ready_for_telemedicine' => 'Готов к телемедицине',
        'about' => 'О себе',
        'snils' => 'СНИЛС',
        'frmr_id' => 'Идентификатор ФРМР',
        'accreditation_status' => 'Статус аккредитации',
        'accreditation_number' => 'Номер аккредитации',
        'accreditation_speciality' => 'Специальность аккредитации',
        'accreditation_valid_from' => 'Аккредитация действует с',
        'accreditation_valid_until' => 'Аккредитация действует до',
        'professional_verification_source' => 'Источник проф. проверки',
        'professional_verified_at' => 'Дата проф. проверки',
    ];

    public function recordDoctorChanges(Doctor $doctor): void
    {
        if (!Schema::hasTable('doctor_profile_events')) {
            return;
        }

        $changed = $doctor->getChanges();
        $payload = [];

        foreach (self::LABELS as $field => $label) {
            if (!array_key_exists($field, $changed)) {
                continue;
            }

            $payload[$label] = [
                'old' => $doctor->getRawOriginal($field),
                'new' => $doctor->getAttribute($field),
            ];
        }

        if ($payload === []) {
            return;
        }

        $this->record(
            $doctor,
            'profile_changed',
            'Изменены сведения о враче',
            $payload
        );
    }

    public function record(
        Doctor $doctor,
        string $eventType,
        string $title,
        ?array $changes = null,
        ?array $meta = null
    ): void {
        if (!Schema::hasTable('doctor_profile_events')) {
            return;
        }

        DoctorProfileEvent::query()->create([
            'doctor_id' => $doctor->getKey(),
            'actor_user_id' => Auth::id(),
            'event_type' => $eventType,
            'title' => $title,
            'changes' => $changes,
            'meta' => $meta,
            'occurred_at' => now(),
        ]);
    }
}
