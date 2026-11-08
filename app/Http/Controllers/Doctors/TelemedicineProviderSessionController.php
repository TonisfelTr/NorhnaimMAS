<?php

namespace App\Http\Controllers\Doctors;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Services\TelemedicineProviderSessionService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

final class TelemedicineProviderSessionController extends Controller
{
    public function show(Request $request, Patient $patient, int $recordId, TelemedicineProviderSessionService $sessions)
    {
        return response()->view('doctors.telemedicine.session', $sessions->staffContext($request->user(), $patient, $recordId))
            ->header('Cache-Control', 'private, no-store')->header('Referrer-Policy', 'no-referrer');
    }

    public function prepare(Request $request, Patient $patient, int $recordId, TelemedicineProviderSessionService $sessions)
    {
        $data = $this->validateManualInput($request, ['manual_join_url' => ['nullable', 'string', 'max:2048']]);
        try { $sessions->prepare($request->user(), $patient, $recordId, $data['manual_join_url'] ?? null); }
        catch (QueryException $e) { $this->duplicateMeeting($e); }
        return redirect()->route('doctors.telemedicine-session.show', ['patient' => $patient->getKey(), 'recordId' => $recordId]);
    }

    public function recover(Request $request, Patient $patient, int $recordId, TelemedicineProviderSessionService $sessions)
    {
        $data = $this->validateManualInput($request, [
            'manual_join_url' => ['required', 'string', 'max:2048'],
            'manual_existing_confirmed' => ['required', 'accepted'],
        ]);
        try { $sessions->attachManual($request->user(), $patient, $recordId, $data['manual_join_url']); }
        catch (QueryException $e) { $this->duplicateMeeting($e); }
        return redirect()->route('doctors.telemedicine-session.show', ['patient' => $patient->getKey(), 'recordId' => $recordId]);
    }

    public function doctorJoin(Request $request, Patient $patient, int $recordId, TelemedicineProviderSessionService $sessions)
    {
        $url = $sessions->doctorJoin($request->user(), $patient, $recordId);
        return redirect()->away($url)->header('Cache-Control', 'private, no-store')->header('Referrer-Policy', 'no-referrer');
    }

    private function duplicateMeeting(QueryException $e): never
    {
        if (in_array((string) ($e->errorInfo[0] ?? $e->getCode()), ['23505', '23000'], true)) {
            throw ValidationException::withMessages(['manual_join_url' => 'Эта встреча уже сохранена. Обновите страницу; для другого приёма нужна отдельная встреча.']);
        }
        throw $e;
    }

    private function validateManualInput(Request $request, array $rules): array
    {
        $input = $request->only(array_keys($rules));
        // A conference URL is a bearer invitation. Do not retain it in old input.
        $request->request->remove('manual_join_url');
        $request->query->remove('manual_join_url');
        if ($request->isJson()) $request->json()->remove('manual_join_url');
        return Validator::make($input, $rules)->validate();
    }
}
