<?php

namespace App\Http\Controllers\Doctors;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Support\DoctorAppointmentTimezone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class DoctorAppointmentTimezoneController extends Controller
{
    public function update(
        Request $request,
        Doctor $doctor
    ): JsonResponse {
        $user = $request->user();
        abort_unless($user, 403);

        $isClinicAdmin = method_exists($user, 'hasRole')
            && $user->hasRole('clinic_admin');

        $isThisDoctor = Doctor::query()
            ->whereKey($doctor->getKey())
            ->where('user_id', $user->getKey())
            ->exists();

        abort_unless(
            $isClinicAdmin || $isThisDoctor,
            403,
            'Изменять часовой пояс врача может clinic_admin или сам врач.'
        );

        abort_unless(
            Schema::hasColumn('doctors', 'appointment_timezone'),
            503,
            'Сначала выполните миграцию часового пояса врача.'
        );

        $request->validate([
            'timezone' => ['nullable', 'string', 'max:64'],
        ]);

        $timezone = DoctorAppointmentTimezone::normalize(
            $request->input('timezone')
        );

        $doctor->setAttribute('appointment_timezone', $timezone);
        $doctor->save();

        return response()
            ->json([
                'ok' => true,
                'timezone' => DoctorAppointmentTimezone::resolve($doctor),
                'explicit' => DoctorAppointmentTimezone::explicit($doctor),
            ])
            ->header('Cache-Control', 'private, no-store');
    }
}
