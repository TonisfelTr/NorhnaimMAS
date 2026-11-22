"""No external packages: python3 -m unittest discover -s telemedicine/tests -p 'test_audit_bridge.py' -v"""
from __future__ import annotations
import hashlib
import hmac
from http.server import BaseHTTPRequestHandler, ThreadingHTTPServer
import json
import os
from pathlib import Path
import secrets
import sys
import threading
import time
import unittest
from urllib.request import Request, urlopen
from urllib.error import HTTPError

P = Path(__file__).resolve().parents[1] / 'signaling'
sys.path.insert(0, str(P))
os.environ.setdefault('TELEMEDICINE_SIGNALING_SECRET', 'aa' * 32)
os.environ.setdefault('TELEMEDICINE_TURN_SECRET', 'b' * 64)
os.environ.pop('TELEMEDICINE_AUDIT_SECRET', None)  # Avoid requesting real Laravel from tests
from tokens import issue, verify, TokenError
import server
from audit import AuditEmitter


class TokenTests(unittest.TestCase):
    def test_scheduled_claim_is_signed(self):
        secret = bytes.fromhex(os.environ['TELEMEDICINE_SIGNALING_SECRET'])
        token = issue(secret, secrets.token_urlsafe(20), 'doctor', registry_record_id=71)
        self.assertEqual(verify(secret, token)['registry_record_id'], 71)
        self.assertNotIn('patient', verify(secret, token))
        with self.assertRaises(ValueError):
            issue(secret, secrets.token_urlsafe(20), 'doctor', registry_record_id=-2)
        with self.assertRaises(TokenError):
            verify(secret, token + 'x')


class StoreTests(unittest.TestCase):
    def setUp(self):
        self.store = server.RoomStore(20)
        self.room = secrets.token_urlsafe(20)

    def test_two_slots_appointments_and_session(self):
        self.assertFalse(self.store.join(self.room, 'doctor', 'session-doctor', 101))
        self.assertTrue(self.store.join(self.room, 'patient', 'session-patient', 101))
        with self.assertRaises(PermissionError):
            self.store.join(self.room, 'doctor', 'session-second-doctor', 101)
        with self.assertRaises(PermissionError):
            self.store.join(self.room, 'doctor', 'wrong-record', 102)
        with self.assertRaises(LookupError):
            self.store.poll(self.room, 'doctor', 'wrong-session', 0)
        events = self.store.poll(self.room, 'doctor', 'session-doctor', 0)
        self.assertEqual(events['events'][0]['type'], 'peer-ready')
        self.store.media(self.room, 'doctor', 'session-doctor', 'connected')
        self.store.media(self.room, 'patient', 'session-patient', 'connected')
        self.store.emit(self.room, 'doctor', 'session-doctor', 'offer', {'type': 'offer', 'sdp': 'v=0\r\n'})
        remote = self.store.poll(self.room, 'patient', 'session-patient', 0)
        self.assertEqual(remote['events'][0]['type'], 'offer')
        self.store.leave(self.room, 'patient', 'session-patient')
        with self.assertRaises(LookupError):
            self.store.poll(self.room, 'patient', 'session-patient', 0)
        self.assertEqual(self.store.poll(self.room, 'doctor', 'session-doctor', events['next'])['events'][-1]['type'], 'hangup')

    def test_demo_and_scheduled_room_do_not_mix(self):
        self.store.join(self.room, 'doctor', 'test-session', None)
        with self.assertRaises(PermissionError):
            self.store.join(self.room, 'patient', 'other-session', 1)


class HttpTests(unittest.TestCase):
    @classmethod
    def setUpClass(cls):
        cls.httpd = ThreadingHTTPServer(('127.0.0.1', 0), server.Handler)
        cls.thread = threading.Thread(target=cls.httpd.serve_forever, daemon=True)
        cls.thread.start()
        cls.url = f'http://127.0.0.1:{cls.httpd.server_port}'
        cls.room = secrets.token_urlsafe(20)
        cls.key = bytes.fromhex(os.environ['TELEMEDICINE_SIGNALING_SECRET'])

    @classmethod
    def tearDownClass(cls):
        cls.httpd.shutdown(); cls.httpd.server_close(); cls.thread.join(timeout=3)

    @classmethod
    def request(cls, endpoint, role='doctor', method='GET', session=None, payload=None):
        token = issue(cls.key, cls.room, role, registry_record_id=1011)
        headers = {'Authorization': 'Bearer ' + token}
        if session: headers['X-Telemedicine-Session'] = session
        body = json.dumps(payload).encode('utf-8') if payload is not None else None
        if body: headers['Content-Type'] = 'application/json'
        req = Request(cls.url + endpoint, data=body, headers=headers, method=method)
        try:
            with urlopen(req, timeout=5) as rsp:
                return rsp.status, json.load(rsp)
        except HTTPError as err:
            return err.code, json.load(err)

    def test_join_media_leave_with_session(self):
        status, doctor = self.request('/api/join', method='POST', payload={})
        self.assertEqual(status, 200)
        self.assertTrue(doctor['session'])
        status, _ = self.request('/api/join', method='POST', payload={})
        self.assertEqual(status, 403)
        status, patient = self.request('/api/join', role='patient', method='POST', payload={})
        self.assertEqual(status, 200)
        status, _ = self.request('/api/media', method='POST', session=doctor['session'], payload={'state':'connected'})
        self.assertEqual(status, 200)
        status, _ = self.request('/api/media', method='POST', role='patient', session=patient['session'], payload={'state':'connected'})
        self.assertEqual(status, 200)
        status, _ = self.request('/api/media', method='POST', session='not-real', payload={'state':'connected'})
        self.assertEqual(status, 409)
        status, _ = self.request('/api/leave', method='POST', role='patient', session=patient['session'], payload={})
        self.assertEqual(status, 200)
        status, _ = self.request('/api/leave', method='POST', session=doctor['session'], payload={})
        self.assertEqual(status, 200)


class CaptureAuditHandler(BaseHTTPRequestHandler):
    messages = []
    secret = 'x' * 40
    def log_message(self, *args): pass
    def do_POST(self):
        raw = self.rfile.read(int(self.headers['Content-Length']))
        stamp = self.headers['X-Telemedicine-Timestamp']
        expected = 'sha256=' + hmac.new(self.secret.encode(),
            stamp.encode() + b'.' + raw, hashlib.sha256).hexdigest()
        if not hmac.compare_digest(self.headers['X-Telemedicine-Signature'], expected):
            self.send_response(401); self.end_headers(); return
        self.messages.append(json.loads(raw))
        self.send_response(201); self.send_header('Content-Length', '0'); self.end_headers()


class SinkTests(unittest.TestCase):
    def test_room_emits_real_transitions_to_signed_endpoint(self):
        CaptureAuditHandler.messages = []
        httpd = ThreadingHTTPServer(('127.0.0.1', 0), CaptureAuditHandler)
        thread = threading.Thread(target=httpd.serve_forever, daemon=True)
        thread.start()
        sink = AuditEmitter(f'http://127.0.0.1:{httpd.server_port}/audit', CaptureAuditHandler.secret)
        previous = server.AUDIT
        server.AUDIT = sink
        try:
            store = server.RoomStore(20)
            room = secrets.token_urlsafe(20)
            store.join(room, 'doctor', 'session-doc', 991)
            store.join(room, 'patient', 'session-pat', 991)
            store.media(room, 'doctor', 'session-doc', 'connected')
            store.media(room, 'patient', 'session-pat', 'connected')
            store.leave(room, 'patient', 'session-pat')
            deadline = time.time() + 5
            while len(CaptureAuditHandler.messages) < 8 and time.time() < deadline:
                time.sleep(.05)
        finally:
            server.AUDIT = previous
            sink.close()
            httpd.shutdown(); httpd.server_close(); thread.join(timeout=3)
        events = [item['event'] for item in CaptureAuditHandler.messages]
        for kind in ('room_created', 'participant_joined', 'media_connected', 'participant_left', 'call_ended'):
            self.assertIn(kind, events)
        self.assertEqual(sum(item.get('event') == 'media_connected' for item in CaptureAuditHandler.messages), 2)
        self.assertTrue(all(item.get('registry_record_id') == 991 for item in CaptureAuditHandler.messages if item.get('event') != 'bridge_heartbeat'))

    def test_real_hmac_delivery_and_heartbeat(self):
        CaptureAuditHandler.messages = []
        httpd = ThreadingHTTPServer(('127.0.0.1',0), CaptureAuditHandler)
        worker = threading.Thread(target=httpd.serve_forever, daemon=True)
        worker.start()
        sink = AuditEmitter(f'http://127.0.0.1:{httpd.server_port}/audit', CaptureAuditHandler.secret,
                            heartbeat_seconds=30)
        sink.emit(123, 'room_' + secrets.token_urlsafe(20), 'participant_joined', 'doctor')
        deadline = time.time() + 5
        while len(CaptureAuditHandler.messages) < 2 and time.time() < deadline:
            time.sleep(.05)
        sink.close(); httpd.shutdown(); httpd.server_close(); worker.join(timeout=3)
        events = {item['event']: item for item in CaptureAuditHandler.messages}
        self.assertIn('bridge_heartbeat', events)
        self.assertIn('participant_joined', events)
        self.assertEqual(events['participant_joined']['registry_record_id'], 123)
        self.assertNotIn('token', events['participant_joined'])
        self.assertNotIn('sdp', events['participant_joined'])


if __name__ == '__main__':
    unittest.main()
