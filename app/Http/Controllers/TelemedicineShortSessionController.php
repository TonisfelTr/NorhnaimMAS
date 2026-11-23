<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\RegistryRecord;
use App\Services\TelemedicineProviderSessionService;
use App\Services\TelemedicineShortLinkService;
use App\Support\ClinicAppointmentTime;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Both clck.ru shortcuts resolve to this application's authenticated gateway.
 * The opaque meeting UUID is NOT a bearer credential. Role, appointment,
 * schedule snapshot, status, clinic and join window are verified again here.
 */
final class TelemedicineShortSessionController extends Controller
{
    public function doctor(
        Request $request,
        string $meeting,
        string $stamp,
        TelemedicineProviderSessionService $sessions,
        TelemedicineShortLinkService $shortener
    ): RedirectResponse {
        [$session, $record] = $this->validatedSession($meeting, $stamp, 'doctor', $shortener);
        $user = $request->user();
        abort_unless($user && $user->hasRole('doctors'), 403);
        abort_unless((int) ($user->doctor()->first()?->getKey() ?? 0) === (int) $session->doctor_id, 403);
        $patient = Patient::query()->findOrFail((int) $session->patient_id);
        $url = $sessions->doctorJoin($user, $patient, (int) $record->getKey());
        return $this->forward($url);
    }

    public function patient(
        Request $request,
        string $meeting,
        string $stamp,
        TelemedicineProviderSessionService $sessions,
        TelemedicineShortLinkService $shortener
    ): RedirectResponse {
        [$session] = $this->validatedSession($meeting, $stamp, 'patient', $shortener);
        $user = $request->user();
        abort_unless($user && $user->hasRole('patients'), 403);
        abort_unless((int) ($user->patient()->first()?->getKey() ?? 0) === (int) $session->patient_id, 403);
        $record = RegistryRecord::query()->whereKey($session->registry_record_id)->firstOrFail();
        $start = ClinicAppointmentTime::startInstant($record);
        abort_unless($start !== null, 410);
        $url = $sessions->patientJoin($meeting, $start->getTimestamp());
        return $this->forward($url);
    }

    private function validatedSession(
        string $meeting,
        string $stamp,
        string $role,
        TelemedicineShortLinkService $shortener
    ): array {
        $session = DB::table('telemedicine_provider_sessions')
            ->where('public_id', $meeting)
            ->where('status', 'ready')
            ->first(['registry_record_id', 'patient_id', 'doctor_id', 'clinic_id']);
        abort_unless($session !== null, 404);
        $record = RegistryRecord::query()
            ->whereKey((int) $session->registry_record_id)
            ->where('appointment_format', 'telemedicine')
            ->firstOrFail();
        abort_unless(
            (int) $session->patient_id === (int) $record->patient_id
            && (int) $session->doctor_id === (int) $record->doctor_id
            && (int) $session->clinic_id === (int) $record->clinic_id,
            410
        );
        $start = ClinicAppointmentTime::startInstant($record);
        abort_unless($start !== null && hash_equals(
            $shortener->signature($meeting, $role, $start->getTimestamp()),
            $stamp
        ), 410, 'Время приёма изменилось. Получите новую ссылку.');
        return [$session, $record];
    }

    private function forward(string $url): RedirectResponse
    {
        return redirect()->away($url)
            ->header('Cache-Control', 'private, no-store')
            ->header('Referrer-Policy', 'no-referrer')
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }
}
