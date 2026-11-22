"""Asynchronous, HMAC-authenticated WebRTC audit delivery (Python stdlib only).

Only send transition metadata. NEVER send invitation tokens, names, IPs, SDP,
ICE candidates, media streams or personally identifying request headers.
Network failure does not block the video call. The queue is deliberately bounded.
"""
from __future__ import annotations

import datetime as dt
import hashlib
import hmac
import json
import os
import queue
import threading
import time
import urllib.error
import urllib.request
import uuid

EVENTS = frozenset({
    'room_created', 'join_attempt', 'participant_joined', 'media_connected',
    'media_disconnected', 'participant_left', 'call_ended',
    'connection_failed', 'access_denied',
})
ROLES = frozenset({'doctor', 'patient', 'system'})
REASON_CODES = frozenset({'left', 'hangup', 'network_error', 'timeout', 'denied', 'unknown'})


def signed_request(secret: str, payload: dict) -> tuple[bytes, dict[str, str]]:
    """Return signed request body+headers, useful for isolated integration tests."""
    raw = json.dumps(payload, ensure_ascii=False, separators=(',', ':')).encode('utf-8')
    timestamp = str(int(time.time()))
    signature = hmac.new(secret.encode('utf-8'), timestamp.encode('ascii') + b'.' + raw,
                         hashlib.sha256).hexdigest()
    return raw, {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Telemedicine-Timestamp': timestamp,
        'X-Telemedicine-Signature': 'sha256=' + signature,
    }


class AuditEmitter:
    def __init__(self, url: str | None = None, secret: str | None = None,
                 *, heartbeat_seconds: float = 30):
        self.url = url if url is not None else os.environ.get('TELEMEDICINE_AUDIT_URL', '')
        self.secret = secret if secret is not None else os.environ.get('TELEMEDICINE_AUDIT_SECRET', '')
        self.enabled = bool(self.url.startswith(('http://', 'https://')) and len(self.secret) >= 32)
        self._queue: queue.Queue[dict] = queue.Queue(maxsize=4096)
        self._heartbeat_seconds = max(5, float(heartbeat_seconds))
        self._worker: threading.Thread | None = None
        self._stop = threading.Event()
        self.dropped = 0
        if self.enabled:
            self._worker = threading.Thread(target=self._run, daemon=True, name='norhnaim-webrtc-audit')
            self._worker.start()

    def emit(self, appointment_id: int | None, room: str, event: str, role: str,
             *, reason_code: str | None = None,
             connection_evidence: str | None = None,
             duration_seconds: int | None = None) -> None:
        if not self.enabled or appointment_id is None:
            return  # Original demo rooms have no Laravel appointment to attribute.
        if (type(appointment_id) is not int or appointment_id <= 0
                or event not in EVENTS or role not in ROLES):
            raise ValueError('Invalid audit event')
        if reason_code is not None and reason_code not in REASON_CODES:
            raise ValueError('Invalid reason code')
        if connection_evidence not in (None, 'client_reported'):
            raise ValueError('Invalid evidence')
        payload = {
            'event_id': uuid.uuid4().hex,
            'registry_record_id': appointment_id,
            'room_id': room,
            'event': event,
            'role': role,
            'occurred_at': dt.datetime.now(dt.timezone.utc).isoformat(),
        }
        if reason_code is not None:
            payload['reason_code'] = reason_code
        if connection_evidence is not None:
            payload['connection_evidence'] = connection_evidence
        if duration_seconds is not None:
            payload['duration_seconds'] = int(duration_seconds)
        try:
            self._queue.put_nowait(payload)
        except queue.Full:
            self.dropped += 1
            if self.dropped == 1 or self.dropped % 100 == 0:
                print(f'[telemedicine-audit] queue full, dropped events: {self.dropped}', flush=True)

    def _post(self, payload: dict) -> bool:
        raw, headers = signed_request(self.secret, payload)
        request = urllib.request.Request(self.url, raw, headers=headers, method='POST')
        try:
            with urllib.request.urlopen(request, timeout=3) as result:
                return result.status in (200, 201, 202)
        except urllib.error.HTTPError as ex:
            # Non-transient errors must not retry forever. Do not log bodies.
            if ex.code not in (408, 429, 500, 502, 503, 504):
                print(f'[telemedicine-audit] rejected by Laravel: HTTP {ex.code}', flush=True)
                return True  # terminal failure; discarded rather than flooding
            return False
        except (OSError, TimeoutError, urllib.error.URLError):
            return False

    def _deliver(self, payload: dict) -> None:
        # Stable event_id across retries: DB deduplication prevents duplicates.
        for delay in (0, .5, 1.5, 3):
            if delay:
                self._stop.wait(delay)
            if self._stop.is_set():
                return
            if self._post(payload):
                return
        print('[telemedicine-audit] delivery failed; check Laravel and Docker network', flush=True)

    def _heartbeat(self) -> dict:
        return {
            'event_id': uuid.uuid4().hex,
            'event': 'bridge_heartbeat',
            'bridge': 'telemedicine-signaling',
        }

    def _run(self) -> None:
        # Send immediately after boot and then refresh the Laravel heartbeat.
        next_heartbeat = 0.0
        while not self._stop.is_set():
            if time.monotonic() >= next_heartbeat:
                self._deliver(self._heartbeat())
                next_heartbeat = time.monotonic() + self._heartbeat_seconds
            try:
                item = self._queue.get(timeout=1.0)
            except queue.Empty:
                continue
            try:
                self._deliver(item)
            finally:
                self._queue.task_done()

    def close(self) -> None:
        self._stop.set()
        if self._worker:
            self._worker.join(timeout=2)
