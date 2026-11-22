#!/usr/bin/env python3
"""Emit a server-signed, minimally scoped WebRTC event to the NORHNAIM audit log.

Use this INSIDE the trusted signaling server code when a state transition occurs.
Never call it from the browser; never send SDP/ICE, IPs, tokens, or media metadata.

Environment:
  TELEMEDICINE_AUDIT_URL=http://laravel.test/internal/telemedicine/audit/events
  TELEMEDICINE_AUDIT_SECRET=<random 32+ chars, identical to Laravel .env>
"""

from __future__ import annotations

import argparse
import datetime as dt
import hashlib
import hmac
import json
import os
import sys
import time
import urllib.error
import urllib.request
import uuid

ALLOWED_EVENTS = (
    "room_created", "join_attempt", "participant_joined",
    "media_connected", "media_disconnected", "participant_left",
    "call_ended", "connection_failed", "access_denied",
    "invitation_created", "invitation_activated", "invitation_expired",
)


def emit_event(
    registry_record_id: int,
    room_id: str,
    event: str,
    role: str,
    *,
    reason_code: str | None = None,
    duration_seconds: int | None = None,
    url: str | None = None,
    secret: str | None = None,
    event_id: str | None = None,
    timeout: int = 5,
) -> dict:
    """Return parsed JSON response; raise RuntimeError on failure.

    For network retries, reuse the same event_id to keep an event idempotent.
    """
    if event not in ALLOWED_EVENTS:
        raise ValueError(f"Unexpected event: {event!r}")
    if role not in ("doctor", "patient", "system"):
        raise ValueError(f"Unexpected role: {role!r}")
    url = url or os.environ.get("TELEMEDICINE_AUDIT_URL", "")
    secret = secret or os.environ.get("TELEMEDICINE_AUDIT_SECRET", "")
    if not url or len(secret) < 32:
        raise RuntimeError("Set TELEMEDICINE_AUDIT_URL and a 32+ character TELEMEDICINE_AUDIT_SECRET")
    payload = {
        "event_id": event_id or str(uuid.uuid4()),
        "registry_record_id": int(registry_record_id),
        "room_id": room_id,
        "event": event,
        "role": role,
        "occurred_at": dt.datetime.now(dt.timezone.utc).isoformat(),
    }
    if reason_code is not None:
        payload["reason_code"] = reason_code
    if duration_seconds is not None:
        payload["duration_seconds"] = int(duration_seconds)

    raw = json.dumps(payload, ensure_ascii=False, separators=(",", ":")).encode("utf-8")
    stamp = str(int(time.time()))
    signature = hmac.new(secret.encode("utf-8"), stamp.encode("ascii") + b"." + raw, hashlib.sha256).hexdigest()
    request = urllib.request.Request(url, raw, {
        "Content-Type": "application/json",
        "Accept": "application/json",
        "X-Telemedicine-Timestamp": stamp,
        "X-Telemedicine-Signature": f"sha256={signature}",
    }, method="POST")
    try:
        with urllib.request.urlopen(request, timeout=timeout) as response:
            return json.loads(response.read().decode("utf-8"))
    except urllib.error.HTTPError as exc:
        body = exc.read(300).decode("utf-8", "replace")
        raise RuntimeError(f"Audit API returned HTTP {exc.code}: {body}") from exc
    except urllib.error.URLError as exc:
        raise RuntimeError(f"Audit API not reachable: {exc.reason}") from exc


def main() -> int:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--registry-record-id", type=int, required=True)
    parser.add_argument("--room-id", required=True)
    parser.add_argument("--event", required=True, choices=ALLOWED_EVENTS)
    parser.add_argument("--role", required=True, choices=("doctor", "patient", "system"))
    parser.add_argument("--reason-code", default=None)
    args = parser.parse_args()
    try:
        print(json.dumps(emit_event(args.registry_record_id, args.room_id, args.event,
                                    args.role, reason_code=args.reason_code), ensure_ascii=False))
        return 0
    except (RuntimeError, ValueError) as error:
        print(f"Audit error: {error}", file=sys.stderr)
        return 1


if __name__ == "__main__":
    sys.exit(main())
