<?php

namespace App\Http\Controllers\Doctors;

use App\Http\Controllers\Controller;
use App\Models\RegistryRecord;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class ReceptionArrivalController extends Controller
{
    public function confirm(Request $request, int $registryRecord): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user, 403);

        $record = RegistryRecord::query()->withTrashed()->findOrFail($registryRecord);

        if ($record->deleted_at !== null) {
            throw ValidationException::withMessages([
                'appointment' => 'Нельзя подтвердить явку по отменённой записи.',
            ]);
        }

        if (($record->appointment_format ?: 'in_person') === 'telemedicine') {
            throw ValidationException::withMessages([
                'appointment' => 'Для телемедицинской записи используется факт подключения, а не физическая явка.',
            ]);
        }

        $this->assertSameClinic($request, $record);

        $rawDate = $record->getRawOriginal('for_datetime') ?: $record->for_datetime;
        if (!$rawDate) {
            throw ValidationException::withMessages([
                'appointment' => 'У записи не указаны дата и время приёма.',
            ]);
        }

        try {
            $appointmentDate = Carbon::parse($rawDate);
        } catch (\Throwable) {
            throw ValidationException::withMessages([
                'appointment' => 'Не удалось определить дату записи.',
            ]);
        }

        $arrivalAvailableAt = $appointmentDate->copy()->subHour();

        if (now()->lt($arrivalAvailableAt)) {
            throw ValidationException::withMessages([
                'appointment' => sprintf(
                    'Подтвердить явку можно не раньше чем за 1 час до приёма (%s).',
                    $arrivalAvailableAt->format('d.m.Y H:i')
                ),
            ]);
        }

        if (!$record->appointment) {
            $record->appointment = true;
            $record->save();
        }

        return back()->with(
            'success',
            'Пациент пришёл. Консультация отмечена как состоявшаяся.'
        );
    }

    private function assertSameClinic(Request $request, RegistryRecord $record): void
    {
        $user = $request->user();
        $doctor = $user && method_exists($user, 'doctor') ? $user->doctor()->first() : null;
        $nurse = $user && method_exists($user, 'nurse') ? $user->nurse()->first() : null;
        $recordClinicId = (int) ($record->clinic_id ?? 0);

        if ($recordClinicId > 0) {
            $currentClinicId = $doctor?->clinic_id
                ? (int) $doctor->clinic_id
                : ($nurse?->clinic_id ? (int) $nurse->clinic_id : null);

            if (
                $currentClinicId === null
                && app()->hasDebugModeEnabled()
                && method_exists($user, 'hasRole')
                && $user->hasRole('admins')
            ) {
                $currentClinicId = $recordClinicId;
            }

            abort_unless(
                $currentClinicId !== null && $currentClinicId === $recordClinicId,
                403,
                'Нельзя подтверждать явку по записи другой клиники.'
            );
            return;
        }

        abort_unless(
            $doctor && (int) $doctor->getKey() === (int) $record->doctor_id,
            403,
            'Нет доступа к этой записи.'
        );
    }
}
