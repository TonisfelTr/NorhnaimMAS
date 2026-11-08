<?php

namespace App\Support;

use App\Models\Doctor;
use App\Models\RegistryRecord;
use App\Services\DoctorAppointmentAvailability;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Used by ReceptionController. Existing clinic/access methods stay in that
 * controller; no caller-supplied clinic id is used for availability or writes.
 */
trait ValidatesDoctorAppointmentTime
{
    public function availability(Request $request): JsonResponse
    {
        $values = $request->validate([
            'doctor_id' => ['required', 'integer', 'min:1'],
            'date' => ['required', 'date_format:Y-m-d'],
            'appointment_duration_minutes' => ['bail', 'required', 'integer', 'between:5,480', 'multiple_of:5'],
            'appointment_format' => ['sometimes', 'required', 'string', 'in:in_person,telemedicine'],
            'record_id' => ['nullable', 'integer', 'min:1'],
        ]);

        $clinicId = $this->currentReceptionClinicId($request);
        abort_unless($clinicId, 403, 'Не удалось определить клинику пользователя.');

        if (!empty($values['record_id'])) {
            RegistryRecord::query()
                ->where('clinic_id', $clinicId)
                ->findOrFail($values['record_id']);
        }

        $doctor = Doctor::query()
            ->where('clinic_id', $clinicId)
            ->findOrFail($values['doctor_id']);
        $appointmentFormat = (string) ($values['appointment_format'] ?? 'in_person');
        $this->assertAppointmentFormatAccess($doctor, (int) $clinicId, $appointmentFormat);

        $data = app(DoctorAppointmentAvailability::class)->day(
            $doctor,
            (int) $clinicId,
            $values['date'],
            (int) $values['appointment_duration_minutes'],
            $appointmentFormat
        );

        return response()->json($data)->header('Cache-Control', 'private, no-store');
    }

    private function requiredAppointmentDuration(Request $request): int
    {
        $values = $request->validate([
            'appointment_duration_minutes' => ['bail', 'required', 'integer', 'between:5,480', 'multiple_of:5'],
        ], [
            'appointment_duration_minutes.required' => 'Укажите продолжительность приёма.',
            'appointment_duration_minutes.integer' => 'Продолжительность приёма должна быть указана целым числом минут.',
            'appointment_duration_minutes.between' => 'Продолжительность приёма должна составлять от 5 до 480 минут.',
            'appointment_duration_minutes.multiple_of' => 'Укажите продолжительность с шагом 5 минут.',
        ]);

        return (int) $values['appointment_duration_minutes'];
    }

    /** Old reception forms have no format control and only create in-person visits. */
    private function requestedAppointmentFormat(Request $request): string
    {
        $values = $request->validate([
            'appointment_format' => ['sometimes', 'required', 'string', 'in:in_person,telemedicine'],
        ], [
            'appointment_format.required' => 'Выберите формат приёма.',
            'appointment_format.in' => 'Выберите очный или телемедицинский приём.',
        ]);

        return (string) ($values['appointment_format'] ?? 'in_person');
    }

    /** Preserve the controller's clinic settings and physical/remote access rules. */
    private function assertAppointmentFormatAccess(Doctor $doctor, int $clinicId, string $appointmentFormat): void
    {
        if (!in_array($appointmentFormat, ['in_person', 'telemedicine'], true)) {
            throw ValidationException::withMessages([
                'appointment_format' => 'Выберите очный или телемедицинский приём.',
            ]);
        }

        if (method_exists($this, 'assertAppointmentFormatEnabled')) {
            $this->assertAppointmentFormatEnabled($clinicId, $appointmentFormat);
            $this->assertDoctorAvailableForReception($doctor, $clinicId, $appointmentFormat);
            return;
        }

        if ($appointmentFormat !== 'in_person') {
            throw ValidationException::withMessages([
                'appointment_format' => 'Текущая версия регистратуры не поддерживает телемедицинскую запись.',
            ]);
        }

        $this->assertDoctorAvailableForReception($doctor, $clinicId);
    }

    /**
     * Null means that the displayed appointment fields did not change.
     * A blank legacy duration is never silently converted into 30 minutes.
     */
    private function changedAppointmentDuration(Request $request, RegistryRecord $record): ?int
    {
        $values = $request->validate([
            'appointment_timezone_confirmed' => ['sometimes', 'boolean'],
        ], [
            'appointment_timezone_confirmed.boolean' => 'Подтвердите часовой пояс записи установленным флажком.',
        ]);
        $clinicId = $this->currentReceptionClinicId($request);
        $zone = ClinicAppointmentTime::timezone($clinicId);
        $original = ClinicAppointmentTime::describe($record, $zone);
        $rawValue = $request->input('appointment_datetime');
        $requested = is_string($rawValue) ? ClinicAppointmentTime::minuteValue($rawValue) : null;
        $explicitConfirmation = $original['legacy']
            && in_array($values['appointment_timezone_confirmed'] ?? false, [true, 1, '1'], true);

        $originalDuration = $record->getAttribute('appointment_duration_minutes');
        $originalDuration = $originalDuration === null ? null : (int) $originalDuration;
        $requestedDuration = $request->input('appointment_duration_minutes');
        $hasDuration = $requestedDuration !== null && $requestedDuration !== '';
        $duration = $hasDuration ? $this->requiredAppointmentDuration($request) : $originalDuration;
        $appointmentFormat = $this->requestedAppointmentFormat($request);
        $originalFormat = (string) ($record->getAttribute('appointment_format') ?: 'in_person');

        $same = !$explicitConfirmation && $requested !== null
            && (int) $request->input('doctor_id') === (int) $record->doctor_id
            && $requested === $original['datetime_local']
            && $duration === $originalDuration
            && $appointmentFormat === $originalFormat;
        if ($same) {
            // Preserve the exact old stored value, including seconds and unknown timezone.
            return null;
        }
        if ($original['legacy'] && $requested === $original['datetime_local'] && !$explicitConfirmation) {
            throw ValidationException::withMessages([
                'appointment_timezone_confirmed' => 'Подтвердите часовой пояс прежнего времени записи либо явно выберите новые дату и время.',
            ]);
        }
        $this->appointmentStart(is_string($rawValue) ? $rawValue : '', $clinicId);
        if ($duration === null) {
            throw ValidationException::withMessages([
                'appointment_duration_minutes' => 'Для изменения параметров записи или подтверждения часового пояса укажите длительность приёма.',
            ]);
        }
        return $duration;
    }

    /** Browser datetime-local is interpreted in the authenticated clinic timezone. */
    private function appointmentStart(string $value, ?int $clinicId = null): DateTimeImmutable
    {
        $clinicId ??= $this->currentReceptionClinicId(request());
        return ClinicAppointmentTime::parseLocal($value, $clinicId);
    }

    private function assertDoctorAppointmentTime(
        int $doctorId,
        int $clinicId,
        string $value,
        int $durationMinutes,
        string $appointmentFormat = 'in_person'
    ): void {
        $doctor = Doctor::query()->where('clinic_id', $clinicId)->findOrFail($doctorId);
        $this->assertAppointmentFormatAccess($doctor, $clinicId, $appointmentFormat);
        app(DoctorAppointmentAvailability::class)->assert(
            $doctor,
            $clinicId,
// clinic-telemedicine-timezones:timezone-assert-clinic:v1:begin
$this->appointmentStart($value, $clinicId),
// clinic-telemedicine-timezones:timezone-assert-clinic:v1:end
            $durationMinutes,
            $appointmentFormat
        );
    }

    /** Preserve Eloquent fillable rules and create events while storing duration. */
    private function createAppointmentWithDuration(array $attributes, int $durationMinutes): RegistryRecord
    {
        $record = new RegistryRecord();
        $record->fill($attributes);
        $record->setAttribute('appointment_duration_minutes', $durationMinutes);
        // clinic-telemedicine-timezones:timezone-create-duration-stamp:v1:begin
        $clinicId = $this->currentReceptionClinicId(request());
        abort_unless($clinicId && (int) $record->clinic_id === $clinicId, 403);
        $rawStart = $attributes['for_datetime'] ?? null;
        $value = $rawStart instanceof DateTimeInterface
            ? $rawStart->format('Y-m-d\TH:i:s') : (is_string($rawStart) ? $rawStart : '');
        ClinicAppointmentTime::stamp($record, $this->appointmentStart($value, $clinicId), $clinicId);
        // clinic-telemedicine-timezones:timezone-create-duration-stamp:v1:end
        $record->save();

        return $record;
    }
}
