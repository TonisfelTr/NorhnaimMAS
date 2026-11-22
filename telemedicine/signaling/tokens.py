"""Small self-contained, authenticated demo invitation format.

NOT a medical patient identity mechanism: a room token is a bearer credential.
The API was kept standard-library-only to avoid npm/PyPI network dependencies.
"""
import base64
import hashlib
import hmac
import json
import re
import secrets
import time
from typing import Any

AUDIENCE = "norhnaim-webrtc-dev-v1"
ROOM_PATTERN = re.compile(r"^[A-Za-z0-9_-]{16,100}$")
ROLES = ("doctor", "patient")


class TokenError(ValueError):
    pass


def b64u(value: bytes) -> str:
    return base64.urlsafe_b64encode(value).rstrip(b"=").decode("ascii")


def unb64u(value: str) -> bytes:
    if not re.fullmatch(r"[A-Za-z0-9_-]+", value):
        raise TokenError("Invalid token encoding")
    try:
        return base64.urlsafe_b64decode(value + "=" * (-len(value) % 4))
    except (ValueError, base64.binascii.Error) as exc:
        raise TokenError("Invalid token encoding") from exc


def issue(secret: bytes, room: str, role: str, *, ttl: int = 5400, now: int | None = None,
          registry_record_id: int | None = None) -> str:
    if len(secret) < 32:
        raise ValueError("Secret too short")
    if role not in ROLES or not ROOM_PATTERN.fullmatch(room):
        raise ValueError("Invalid room/role")
    if not (60 <= ttl <= 14400):
        raise ValueError("Invalid TTL")
    now = int(time.time()) if now is None else now
    payload = {
        "aud": AUDIENCE,
        "room": room,
        "role": role,
        "iat": now,
        "exp": now + ttl,
        "jti": secrets.token_urlsafe(16),
    }
    # The appointment is part of the signed token, NEVER supplied by the browser.
    # Legacy demo tokens without it work but cannot generate clinical audit rows.
    if registry_record_id is not None:
        if type(registry_record_id) is not int or not 0 < registry_record_id < 2**63:
            raise ValueError('Invalid appointment ID')
        payload['registry_record_id'] = registry_record_id
    data = b64u(json.dumps(payload, separators=(",", ":"), sort_keys=True).encode("utf-8"))
    signature = b64u(hmac.new(secret, data.encode("ascii"), hashlib.sha256).digest())
    return data + "." + signature


def verify(secret: bytes, token: str, *, now: int | None = None) -> dict[str, Any]:
    if not token or len(token) > 2048 or token.count(".") != 1:
        raise TokenError("Invalid invitation")
    encoded, signature = token.split(".")
    expected = b64u(hmac.new(secret, encoded.encode("ascii"), hashlib.sha256).digest())
    if not hmac.compare_digest(signature, expected):
        raise TokenError("Invalid invitation")
    try:
        body = json.loads(unb64u(encoded))
    except (ValueError, UnicodeDecodeError, TypeError) as exc:
        raise TokenError("Invalid invitation") from exc
    now = int(time.time()) if now is None else now
    if not isinstance(body, dict):
        raise TokenError("Invalid invitation")
    if body.get("aud") != AUDIENCE or body.get("role") not in ROLES:
        raise TokenError("Invalid invitation")
    if not isinstance(body.get("room"), str) or not ROOM_PATTERN.fullmatch(body["room"]):
        raise TokenError("Invalid invitation")
    if not isinstance(body.get("iat"), int) or not isinstance(body.get("exp"), int):
        raise TokenError("Invalid invitation")
    if body["iat"] > now + 30 or body["exp"] <= now or body["exp"] > body["iat"] + 14400:
        raise TokenError("Invitation expired or invalid")
    if not isinstance(body.get("jti"), str) or len(body["jti"]) < 16:
        raise TokenError("Invalid invitation")
    appointment_id = body.get('registry_record_id')
    if appointment_id is not None and (type(appointment_id) is not int
                                       or not 0 < appointment_id < 2**63):
        raise TokenError('Invalid appointment ID')
    return body
