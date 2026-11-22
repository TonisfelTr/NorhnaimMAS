"""Local-only WebRTC demo: authenticated, memory-only HTTP signaling.

This is NOT a production telemedicine backend. Tokens are demo bearer links
and are NOT integrated with Laravel's doctor/patient identities or consent.
"""
from __future__ import annotations

import base64
from dataclasses import dataclass, field
import hashlib
import hmac
from http.server import BaseHTTPRequestHandler, ThreadingHTTPServer
import json
import os
import secrets
import re
from pathlib import Path
import threading
import time
from urllib.parse import urlsplit, parse_qs

from tokens import TokenError, verify
from audit import AuditEmitter
from appointment_guard import assert_appointment_open

ROOT = Path(__file__).resolve().parent
SIGNING_KEY = bytes.fromhex(os.environ.get("TELEMEDICINE_SIGNALING_SECRET", ""))
TURN_SECRET = os.environ.get("TELEMEDICINE_TURN_SECRET", "").encode("utf-8")
TURN_HOST = os.environ.get("TELEMEDICINE_TURN_HOST", "127.0.0.1")
TURN_PORT = int(os.environ.get("TELEMEDICINE_TURN_PORT", "3478"))
MAX_ROOMS = int(os.environ.get("TELEMEDICINE_MAX_ROOMS", "20"))
ALLOWED_ORIGINS = set(os.environ.get(
    "TELEMEDICINE_ALLOWED_ORIGINS",
    "http://localhost:3100,http://127.0.0.1:3100"
).split(","))

ROOM_IDLE_TIMEOUT = 240  # Memory retained briefly after a call ends
PEER_TIMEOUT = 40         # Polling heartbeat
MAX_EVENT_HISTORY = 180
MAX_JSON_REQUEST = 65_536
POLL_TIMEOUT = 12
ROLES = ("doctor", "patient")
SIGNAL_TYPES = ("offer", "answer", "candidate", "hangup")

if len(SIGNING_KEY) < 32 or len(TURN_SECRET) < 32:
    raise SystemExit("Missing signing secrets; run telemedicine/scripts/init.py")
if not (1 <= MAX_ROOMS <= 500) or not (1 <= TURN_PORT <= 65535):
    raise SystemExit("Invalid room/port settings")

AUDIT = AuditEmitter()

@dataclass
class Peer:
    session: str
    seen: float

@dataclass
class Room:
    # A room is pinned to exactly one scheduled appointment (or to legacy demo mode).
    appointment_id: int | None = None
    participants: dict[str, Peer] = field(default_factory=dict)
    media_connected: dict[str, bool] = field(default_factory=dict)
    messages: list[dict] = field(default_factory=list)
    sequence: int = 0
    touched: float = field(default_factory=time.monotonic)
    ended: bool = False

class RoomStore:
    def __init__(self, max_rooms: int):
        self.max_rooms = max_rooms
        self.rooms: dict[str, Room] = {}
        self.lock = threading.RLock()
        self.condition = threading.Condition(self.lock)

    @staticmethod
    def _other(role: str) -> str:
        return 'patient' if role == 'doctor' else 'doctor'

    @staticmethod
    def _audit(room_id: str, room: Room, event: str, role: str, **kwargs) -> None:
        AUDIT.emit(room.appointment_id, room_id, event, role, **kwargs)

    def _end_if_paired(self, room_id: str, room: Room, reason: str) -> None:
        if not room.ended and len(room.participants) == 2:
            room.ended = True
            self._audit(room_id, room, 'call_ended', 'system', reason_code=reason)

    def _purge(self, now: float) -> None:
        for room_id, room in list(self.rooms.items()):
            for role, peer in list(room.participants.items()):
                if now - peer.seen > PEER_TIMEOUT:
                    self._end_if_paired(room_id, room, 'timeout')
                    room.participants.pop(role, None)
                    room.media_connected.pop(role, None)
                    self._audit(room_id, room, 'participant_left', role, reason_code='timeout')
                    other = self._other(role)
                    if other in room.participants:
                        self._send(room, other, 'hangup', None)
                        self.condition.notify_all()
            if not room.participants and now - room.touched > ROOM_IDLE_TIMEOUT:
                del self.rooms[room_id]

    @staticmethod
    def _send(room: Room, target: str, kind: str, data: object) -> None:
        room.sequence += 1
        room.messages.append({'id': room.sequence, 'to': target, 'type': kind, 'data': data})
        if len(room.messages) > MAX_EVENT_HISTORY:
            del room.messages[:len(room.messages) - MAX_EVENT_HISTORY]
        room.touched = time.monotonic()

    def join(self, room_id: str, role: str, session: str,
             appointment_id: int | None = None) -> bool:
        now = time.monotonic()
        other = self._other(role)
        with self.condition:
            self._purge(now)
            room = self.rooms.get(room_id)
            if room is None:
                if len(self.rooms) >= self.max_rooms:
                    raise RuntimeError('Room capacity reached')
                room = self.rooms[room_id] = Room(appointment_id=appointment_id)
                self._audit(room_id, room, 'room_created', 'system')
            elif room.appointment_id != appointment_id:
                # Never allow legacy demo token or a different appointment into this room.
                raise PermissionError('Invitation belongs to another appointment')
            if role in room.participants:
                # A reused bearer invitation cannot create a third active participant.
                self._audit(room_id, room, 'access_denied', role, reason_code='denied')
                raise PermissionError('This role is already connected to the room')
            room.participants[role] = Peer(session=session, seen=now)
            room.media_connected.pop(role, None)
            room.touched = now
            other_present = other in room.participants
            self._audit(room_id, room, 'participant_joined', role)
            if other_present:
                room.ended = False
                self._send(room, other, 'peer-ready', None)
                self.condition.notify_all()
            return other_present

    def _current(self, room_id: str, role: str, session: str) -> tuple[Room, str]:
        self._purge(time.monotonic())
        room = self.rooms.get(room_id)
        peer = room.participants.get(role) if room else None
        if not peer or not session or not secrets.compare_digest(peer.session, session):
            raise LookupError('Join the call first')
        return room, self._other(role)

    def emit(self, room_id: str, role: str, session: str,
             kind: str, data: object) -> None:
        with self.condition:
            room, other = self._current(room_id, role, session)
            if kind not in SIGNAL_TYPES:
                raise ValueError('Unknown signaling type')
            if (kind == 'offer' and role != 'doctor') or (kind == 'answer' and role != 'patient'):
                raise ValueError('The signaling type is not allowed for this role')
            if kind in ('offer', 'answer'):
                if not isinstance(data, dict) or data.get('type') != kind:
                    raise ValueError('Invalid SDP')
                if not isinstance(data.get('sdp'), str) or len(data['sdp']) > 50000:
                    raise ValueError('Invalid SDP')
            elif kind == 'candidate':
                if not isinstance(data, dict) or not isinstance(data.get('candidate'), str):
                    raise ValueError('Invalid ICE candidate')
                if len(data['candidate']) > 5000:
                    raise ValueError('Invalid ICE candidate')
            elif data is not None:
                raise ValueError('Hangup payload must be null')
            room.participants[role].seen = time.monotonic()
            if kind == 'hangup':
                self._end_if_paired(room_id, room, 'hangup')
            self._send(room, other, kind, data)
            self.condition.notify_all()

    def media(self, room_id: str, role: str, session: str, state: str) -> None:
        """Browser status is client-reported, not an attested server media measurement."""
        with self.condition:
            room, other = self._current(room_id, role, session)
            room.participants[role].seen = time.monotonic()
            if state == 'connected':
                if other not in room.participants:
                    raise LookupError('Other participant has not joined')
                if not room.media_connected.get(role):
                    room.media_connected[role] = True
                    self._audit(room_id, room, 'media_connected', role,
                                connection_evidence='client_reported')
            elif state in ('disconnected', 'failed'):
                if room.media_connected.pop(role, None):
                    self._audit(room_id, room, 'media_disconnected', role,
                                connection_evidence='client_reported', reason_code='network_error')
                if state == 'failed':
                    self._audit(room_id, room, 'connection_failed', role,
                                connection_evidence='client_reported', reason_code='network_error')
            else:
                raise ValueError('Invalid client media state')

    def poll(self, room_id: str, role: str, session: str, after: int) -> dict:
        deadline = time.monotonic() + POLL_TIMEOUT
        with self.condition:
            while True:
                now = time.monotonic()
                room, other = self._current(room_id, role, session)
                room.participants[role].seen = now
                room.touched = now
                events = [
                    {'id': msg['id'], 'type': msg['type'], 'data': msg['data']}
                    for msg in room.messages if msg['id'] > after and msg['to'] == role
                ]
                if events or now >= deadline:
                    other_peer = room.participants.get(other)
                    return {
                        'events': events,
                        'next': room.sequence,
                        'peer_present': bool(other_peer and now - other_peer.seen <= PEER_TIMEOUT),
                    }
                self.condition.wait(timeout=max(.05, deadline - now))

    def leave(self, room_id: str, role: str, session: str) -> None:
        with self.condition:
            room, other = self._current(room_id, role, session)
            self._end_if_paired(room_id, room, 'hangup')
            room.participants.pop(role, None)
            room.media_connected.pop(role, None)
            room.touched = time.monotonic()
            self._audit(room_id, room, 'participant_left', role, reason_code='left')
            self._send(room, other, 'hangup', None)
            self.condition.notify_all()

STORE = RoomStore(MAX_ROOMS)

def turn_ice_servers(role: str, room_id: str) -> list[dict]:
    expiry = int(time.time()) + 7200
    username = f"{expiry}:{role}:{room_id}"
    password = base64.b64encode(hmac.new(TURN_SECRET, username.encode(), hashlib.sha1).digest()).decode()
    host = TURN_HOST
    if ":" in host and not host.startswith("["):
        host = f"[{host}]"
    return [
        {"urls": [f"stun:{host}:{TURN_PORT}"]},
        {
            "urls": [f"turn:{host}:{TURN_PORT}?transport=udp", f"turn:{host}:{TURN_PORT}?transport=tcp"],
            "username": username,
            "credential": password,
        },
    ]

class Handler(BaseHTTPRequestHandler):
    server_version = "NorhnaimDevSignaling/1.0"
    sys_version = ""

    def log_message(self, fmt: str, *args) -> None:
        # Avoid logging bearer tokens, SDP, identities, or room identifiers.
        return

    def _write(self, status: int, obj: dict) -> None:
        data = json.dumps(obj, ensure_ascii=False, separators=(",", ":")).encode()
        self.send_response(status)
        self.send_header("Content-Type", "application/json; charset=utf-8")
        self.send_header("Content-Length", str(len(data)))
        self._security_headers()
        self.end_headers()
        self.wfile.write(data)

    def _security_headers(self) -> None:
        self.send_header("Cache-Control", "no-store")
        self.send_header("X-Content-Type-Options", "nosniff")
        self.send_header("Referrer-Policy", "no-referrer")
        self.send_header("Content-Security-Policy", "default-src 'self'; connect-src 'self'; script-src 'self'; style-src 'self'; img-src 'self' data:; object-src 'none'; base-uri 'none'; frame-ancestors 'none'")

    def _file(self, filename: str, mimetype: str) -> None:
        content = (ROOT / filename).read_bytes()
        self.send_response(200)
        self.send_header("Content-Type", mimetype)
        self.send_header("Content-Length", str(len(content)))
        self._security_headers()
        self.end_headers()
        self.wfile.write(content)

    def _session_page_denied(self) -> None:
        # Important: this decision is made before sending the page. A URL fragment
        # (#token=...) is not sent to the server and cannot authorize /session.
        content = (ROOT / 'session_denied.html').read_bytes()
        self.send_response(403)
        self.send_header('Content-Type', 'text/html; charset=utf-8')
        self.send_header('Content-Length', str(len(content)))
        self._security_headers()
        self.end_headers()
        self.wfile.write(content)

    def _authorized_session_page(self, query: str) -> bool:
        # URL query is a bearer credential: do not reflect it, cache it or log it.
        if len(query) > 4096:
            return False
        params = parse_qs(query, keep_blank_values=True)
        tokens = params.get('token', [])
        if len(tokens) != 1:
            return False
        try:
            verify(SIGNING_KEY, tokens[0])
            return True
        except TokenError:
            return False

    def _auth(self) -> dict:
        authorization = self.headers.get("Authorization", "")
        if not authorization.startswith("Bearer "):
            raise TokenError("Missing invitation")
        return verify(SIGNING_KEY, authorization[len("Bearer "):].strip())

    def _origin_ok(self) -> bool:
        origin = self.headers.get("Origin")
        return origin is None or origin in ALLOWED_ORIGINS

    def _json_body(self) -> dict:
        try:
            size = int(self.headers.get("Content-Length", "0"))
        except ValueError as exc:
            raise ValueError("Invalid content length") from exc
        if size <= 0 or size > MAX_JSON_REQUEST:
            raise ValueError("Invalid request size")
        try:
            body = json.loads(self.rfile.read(size))
        except (ValueError, UnicodeDecodeError) as exc:
            raise ValueError("Invalid JSON") from exc
        if not isinstance(body, dict):
            raise ValueError("Expected object")
        return body

    def _session(self) -> str:
        session = self.headers.get('X-Telemedicine-Session', '')
        if not re.fullmatch(r'[A-Za-z0-9_-]{20,100}', session):
            raise LookupError('Join the call first')
        return session

    def do_GET(self) -> None:
        parsed = urlsplit(self.path)
        path = parsed.path
        if path == '/health':
            return self._write(200, {'ok': True})
        if path in ('/', '/demo'):
            return self._session_page_denied()
        if path == '/session':
            if not self._authorized_session_page(parsed.query):
                return self._session_page_denied()
            return self._file('index.html', 'text/html; charset=utf-8')
        if path == '/app.js':
            return self._file('app.js', 'text/javascript; charset=utf-8')
        if path == '/style.css':
            return self._file('style.css', 'text/css; charset=utf-8')
        if path == '/brand/norhneim-viking-mark.png':
            return self._file('brand/norhneim-viking-mark.png', 'image/png')
        if path == '/brand/favicon-32x32.png':
            return self._file('brand/favicon-32x32.png', 'image/png')
        if path == '/brand/favicon-16x16.png':
            return self._file('brand/favicon-16x16.png', 'image/png')
        if path not in ('/api/config', '/api/poll'):
            return self._write(404, {'error': 'Not found'})
        try:
            claim = self._auth()
            if path == '/api/config':
                return self._write(200, {
                    'role': claim['role'],
                    'room': claim['room'],
                    'iceServers': turn_ice_servers(claim['role'], claim['room']),
                    'demo': True,
                })
            params = parse_qs(parsed.query)
            after = int(params.get('after', ['0'])[0])
            if not 0 <= after <= 100_000_000:
                raise ValueError('Invalid sequence')
            return self._write(200, STORE.poll(claim['room'], claim['role'],
                                                self._session(), after))
        except TokenError:
            return self._write(401, {'error': 'Invitation invalid or expired'})
        except LookupError as exc:
            return self._write(409, {'error': str(exc)})
        except (ValueError, IndexError):
            return self._write(400, {'error': 'Invalid request'})

    def do_POST(self) -> None:
        path = urlsplit(self.path).path
        if path not in ('/api/join', '/api/signal', '/api/leave', '/api/media'):
            return self._write(404, {'error': 'Not found'})
        if not self._origin_ok():
            return self._write(403, {'error': 'Origin forbidden'})
        try:
            claim = self._auth()
            body = self._json_body()
            room, role = claim['room'], claim['role']
            appointment_id = claim.get('registry_record_id')
            if path == '/api/join':
                assert_appointment_open(claim, AUDIT.url, AUDIT.secret)
                AUDIT.emit(appointment_id, room, 'join_attempt', role)
                session = secrets.token_urlsafe(24)
                peer_present = STORE.join(room, role, session, appointment_id)
                return self._write(200, {'peer_present': peer_present, 'session': session})
            session = self._session()
            if path == '/api/leave':
                STORE.leave(room, role, session)
            elif path == '/api/media':
                STORE.media(room, role, session, body.get('state'))
            else:
                STORE.emit(room, role, session, body.get('type'), body.get('data'))
            return self._write(200, {'ok': True})
        except TokenError:
            return self._write(401, {'error': 'Invitation invalid or expired'})
        except PermissionError:
            return self._write(403, {'error': 'Role already occupied or appointment mismatch'})
        except LookupError as exc:
            return self._write(409, {'error': str(exc)})
        except RuntimeError as exc:
            return self._write(429, {'error': str(exc)})
        except (ValueError, TypeError):
            return self._write(400, {'error': 'Invalid request'})

def _sweep_inactive_peers() -> None:
    # A crashed browser sends no /api/leave; record the timeout automatically.
    while True:
        time.sleep(5)
        with STORE.condition:
            STORE._purge(time.monotonic())

if __name__ == "__main__":
    threading.Thread(target=_sweep_inactive_peers, daemon=True,
                     name='webrtc-room-timeout-sweep').start()
    server = ThreadingHTTPServer(("0.0.0.0", int(os.environ.get("TELEMEDICINE_LISTEN_PORT", "8080"))), Handler)
    server.daemon_threads = True
    print(f"[telemedicine-demo] Listening on :{server.server_port} (local access only via Docker port binding)", flush=True)
    try:
        server.serve_forever(poll_interval=0.4)
    except KeyboardInterrupt:
        pass
    finally:
        server.server_close()
        AUDIT.close()
