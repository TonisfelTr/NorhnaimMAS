<?php

namespace App\Http\Controllers\Doctors;

use App\Http\Controllers\Controller;
use App\Models\RegistryRecord;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

/** Server-to-server gate. An audit webhook is NOT a bearer invitation validator. */
class TelemedicineJournalGateController extends Controller
{
    public function check(Request $request): JsonResponse
    {
        $secret = (string) config('medical_visit_audit.secret', '');
        $stamp = (string) $request->header('X-Telemedicine-Timestamp', '');
        $signature = (string) $request->header('X-Telemedicine-Signature', '');
        if (str_starts_with($signature, 'sha256=')) $signature = substr($signature, 7);
        if (strlen($secret) < 32 || !ctype_digit($stamp) || abs(time() - (int) $stamp) > 60
            || !preg_match('/^[a-f0-9]{64}$/D', $signature)) {
            return response()->json(['allowed' => false], 401)->header('Cache-Control', 'no-store');
        }
        $expected = hash_hmac('sha256', 'telemedicine-journal-v1' . "\n" . $stamp . '.' . $request->getContent(), $secret);
        if (!hash_equals($expected, $signature)) {
            return response()->json(['allowed' => false], 401)->header('Cache-Control', 'no-store');
        }
        $validator = Validator::make($request->json()->all(), [
            'audience' => ['required', 'in:telemedicine-journal-v1'],
            // clinic-telemedicine-timezones:provider-gate-claims:v1:begin
            'registry_record_id' => ['required', 'integer', 'min:1'],
            'room' => ['nullable', 'string', 'regex:/\A[A-Za-z0-9_-]{16,100}\z/'],
            'appointment_start' => ['nullable', 'integer', 'min:1'],
            // clinic-telemedicine-timezones:provider-gate-claims:v1:end
        ]);
        if ($validator->fails()) return response()->json(['allowed' => false], 422)->header('Cache-Control', 'no-store');
        $record = RegistryRecord::query()->whereKey($validator->validated()['registry_record_id'])
            ->whereNull('deleted_at')->where('appointment_format', 'telemedicine')->first();
        $allowed = $record !== null;
        if ($allowed && Schema::hasTable('medical_visit_confirmations')) {
            $allowed = !DB::table('medical_visit_confirmations')->where('registry_record_id', $record->getKey())
                ->whereIn('outcome', ['held', 'not_held'])->exists();
        }
        if ($allowed && Schema::hasTable('medical_encounters')) {
            $allowed = !DB::table('medical_encounters')->where('registry_record_id', $record->getKey())
                ->whereIn('consultation_status', ['completed', 'not_held'])->exists();
        }
        // clinic-telemedicine-timezones:provider-gate-current-session:v1:begin
        if ($allowed) {
            $verified = $validator->validated();
            $allowed = app(\App\Services\TelemedicineProviderSessionService::class)->signalingAllowed(
                $record, $verified['room'] ?? null,
                isset($verified['appointment_start']) ? (int) $verified['appointment_start'] : null
            );
        }
        return response()->json(['allowed' => $allowed])->header('Cache-Control', 'no-store');
        // clinic-telemedicine-timezones:provider-gate-current-session:v1:end
    }
}
