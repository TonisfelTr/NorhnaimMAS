<?php

namespace App\Http\Controllers\Doctors;

use App\Http\Controllers\Controller;
use App\Models\RegistryRecord;
use App\Services\MedicalVisitAuditService;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * Server-to-server WebRTC signaling audit hook.
 * Never expose TELEMEDICINE_AUDIT_SECRET to a browser or an invite URL.
 */
class TelemedicineEventIngestController extends Controller
{
    private const EVENTS = [
        'room_created', 'join_attempt', 'participant_joined',
        'media_connected', 'media_disconnected', 'participant_left',
        'call_ended', 'connection_failed', 'access_denied',
        'invitation_created', 'invitation_activated', 'invitation_expired',
    ];

    public function store(Request $request, MedicalVisitAuditService $audit): JsonResponse
    {
        $secret = (string) config('medical_visit_audit.secret', '');
        if (strlen($secret) < 32) {
            return response()->json(['error' => 'Audit ingestion is not configured'], 503);
        }

        $timestamp = (string) $request->header('X-Telemedicine-Timestamp', '');
        $signature = strtolower((string) $request->header('X-Telemedicine-Signature', ''));
        $signature = str_starts_with($signature, 'sha256=') ? substr($signature, 7) : $signature;
        $skew = max(10, (int) config('medical_visit_audit.max_clock_skew_seconds', 300));

        if (!ctype_digit($timestamp) || abs(time() - (int) $timestamp) > $skew
            || !preg_match('/^[a-f0-9]{64}$/D', $signature)) {
            return response()->json(['error' => 'Invalid event signature'], 401);
        }

        $expected = hash_hmac('sha256', $timestamp . '.' . $request->getContent(), $secret);
        if (!hash_equals($expected, $signature)) {
            return response()->json(['error' => 'Invalid event signature'], 401);
        }

        $payload = $request->json()->all();
        // A signed heartbeat proves that the signaling container can reach
        // Laravel. Do not confuse a configured secret with a working bridge.
        if (is_array($payload) && ($payload['event'] ?? null) === 'bridge_heartbeat') {
            $ping = Validator::make($payload, [
                'event_id' => ['required', 'string', 'max:100', 'regex:/^[A-Za-z0-9_-]+$/D'],
                'event' => ['required', Rule::in(['bridge_heartbeat'])],
                'bridge' => ['required', Rule::in(['telemedicine-signaling'])],
            ]);
            if ($ping->fails()) {
                return response()->json(['error' => 'Invalid heartbeat'], 422);
            }
            Cache::put('telemedicine_audit:signaling_heartbeat', time(), now()->addMinutes(10));
            return response()->json(['ok' => true]);
        }

        if (!Schema::hasTable('medical_visit_events') || !Schema::hasTable('telemedicine_room_bindings')) {
            return response()->json(['error' => 'Database migration is required'], 503);
        }

        $validator = Validator::make(is_array($payload) ? $payload : [], [
            'event_id' => ['required', 'string', 'max:100', 'regex:/^[A-Za-z0-9_-]+$/D'],
            'registry_record_id' => ['required', 'integer', 'min:1'],
            'room_id' => ['required', 'string', 'min:4', 'max:128', 'regex:/^[A-Za-z0-9_.:-]+$/D'],
            'event' => ['required', Rule::in(self::EVENTS)],
            'role' => ['required', Rule::in(['doctor', 'patient', 'system'])],
            'occurred_at' => ['nullable', 'date'],
            'reason_code' => ['nullable', Rule::in(['left', 'hangup', 'network_error', 'timeout', 'denied', 'unknown'])],
            'connection_evidence' => ['nullable', Rule::in(['client_reported'])],
            'duration_seconds' => ['nullable', 'integer', 'min:0', 'max:86400'],
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Invalid event', 'errors' => $validator->errors()], 422);
        }
        $data = $validator->validated();
        $occurredAt = isset($data['occurred_at']) ? Carbon::parse($data['occurred_at']) : now();
        if ($occurredAt->greaterThan(now()->addMinute()) || $occurredAt->lessThan(now()->subDay())) {
            return response()->json(['error' => 'Event timestamp is out of range'], 422);
        }

        $record = RegistryRecord::query()->withTrashed()->whereKey($data['registry_record_id'])->first();
        if (!$record || $record->appointment_format !== 'telemedicine') {
            return response()->json(['error' => 'Telemedicine appointment not found'], 404);
        }

        try {
            return DB::transaction(function () use ($data, $record, $audit, $occurredAt): JsonResponse {
                $existingEvent = DB::table('medical_visit_events')
                    ->where('event_uid', $data['event_id'])->first(['registry_record_id', 'event_type', 'room_id']);
                if ($existingEvent) {
                    return (int) $existingEvent->registry_record_id === (int) $record->getKey()
                        && $existingEvent->event_type === $data['event']
                        && $existingEvent->room_id === $data['room_id']
                        ? response()->json(['ok' => true, 'duplicate' => true])
                        : response()->json(['error' => 'Event ID conflict'], 409);
                }

                // Fail closed if the same room ID is reused for another appointment.
                DB::table('telemedicine_room_bindings')->insertOrIgnore([
                    'room_id' => $data['room_id'],
                    'registry_record_id' => $record->getKey(),
                    'patient_id' => $record->patient_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $bound = DB::table('telemedicine_room_bindings')
                    ->where('room_id', $data['room_id'])->first();
                if (!$bound || (int) $bound->registry_record_id !== (int) $record->getKey()) {
                    return response()->json(['error' => 'Room is bound to another appointment'], 409);
                }

                $details = [];
                if (isset($data['reason_code'])) {
                    $details['reason_code'] = $data['reason_code'];
                }
                if (isset($data['duration_seconds'])) {
                    $details['duration_seconds'] = (int) $data['duration_seconds'];
                }
                if (isset($data['connection_evidence'])) {
                    $details['connection_evidence'] = $data['connection_evidence'];
                }

                $audit->appendEvent(
                    (int) $record->getKey(),
                    (int) $record->patient_id,
                    $data['event'],
                    'signaling',
                    $data['role'],
                    null,
                    $data['room_id'],
                    $details,
                    $occurredAt,
                    $data['event_id']
                );

                return response()->json(['ok' => true], 201);
            });
        } catch (QueryException $exception) {
            // Unique event IDs also enforce idempotency under concurrent retries.
            $alreadySaved = DB::table('medical_visit_events')
                ->where('event_uid', $data['event_id'])
                ->where('registry_record_id', $record->getKey())
                ->where('event_type', $data['event'])
                ->where('room_id', $data['room_id'])
                ->exists();
            if ($alreadySaved) {
                return response()->json(['ok' => true, 'duplicate' => true]);
            }
            throw $exception;
        }
    }
}
