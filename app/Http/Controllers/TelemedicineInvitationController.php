<?php

namespace App\Http\Controllers;

use App\Services\TelemedicineProviderSessionService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

final class TelemedicineInvitationController extends Controller
{
    public function show(Request $request, string $meeting, TelemedicineProviderSessionService $sessions)
    {
        try {
            $context = $sessions->invitationContext($meeting, $this->expectedStart($request));
        } catch (ValidationException $e) {
            return $this->unavailable($e);
        }
        return response()->view('telemedicine.invitation', $context)
            ->header('Cache-Control', 'private, no-store')->header('Referrer-Policy', 'no-referrer')
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }

    public function join(Request $request, string $meeting, TelemedicineProviderSessionService $sessions)
    {
        try {
            $url = $sessions->patientJoin($meeting, $this->expectedStart($request));
        } catch (ValidationException $e) {
            return $this->unavailable($e);
        }
        return redirect()->away($url)->header('Cache-Control', 'private, no-store')->header('Referrer-Policy', 'no-referrer');
    }

    private function expectedStart(Request $request): int
    {
        $start = $request->query('start');
        abort_unless(is_string($start) && preg_match('/\A[0-9]{1,12}\z/', $start), 404);
        return (int) $start;
    }

    private function unavailable(ValidationException $e)
    {
        return response()->view('telemedicine.unavailable', ['messages' => array_merge(...array_values($e->errors()))], 410)
            ->header('Cache-Control', 'private, no-store')->header('Referrer-Policy', 'no-referrer')
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }
}
