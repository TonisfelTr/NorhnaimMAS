"""Run from project root: python3 -m unittest discover -s telemedicine/tests -v."""
from contextlib import contextmanager
from http.server import ThreadingHTTPServer
import hashlib
import hmac
import json
import os
from pathlib import Path
import secrets
import sys
import threading
import time
import unittest
from urllib.error import HTTPError
from urllib.request import Request, urlopen

ROOT = Path(__file__).resolve().parents[2]
sys.path.insert(0, str(ROOT / "telemedicine" / "signaling"))

os.environ.setdefault("TELEMEDICINE_SIGNALING_SECRET", secrets.token_hex(32))
os.environ.setdefault("TELEMEDICINE_TURN_SECRET", secrets.token_hex(32))
os.environ.setdefault("TELEMEDICINE_ALLOWED_ORIGINS", "http://localhost:3100,http://127.0.0.1:3100")
os.environ.pop("TELEMEDICINE_AUDIT_SECRET", None)  # Isolate tests from production audit

import tokens  # noqa: E402
import server  # noqa: E402


class TokenTests(unittest.TestCase):
    def setUp(self):
        self.secret = bytes.fromhex(os.environ["TELEMEDICINE_SIGNALING_SECRET"])
        self.room = secrets.token_urlsafe(20)

    def test_issue_verify_roles_and_expiry(self):
        token = tokens.issue(self.secret, self.room, "doctor", now=1000, ttl=120)
        self.assertEqual(tokens.verify(self.secret, token, now=1010)["room"], self.room)
        with self.assertRaises(tokens.TokenError):
            tokens.verify(self.secret, token, now=1120)

    def test_token_tampering_denied(self):
        token = tokens.issue(self.secret, self.room, "patient")
        with self.assertRaises(tokens.TokenError):
            tokens.verify(self.secret, token[:-2] + "xx")
        with self.assertRaises(ValueError):
            tokens.issue(self.secret, "room-one", "doctor")

    def test_turn_password_matches_coturn_rest_standard(self):
        entry = server.turn_ice_servers("doctor", self.room)[1]
        expected = __import__("base64").b64encode(hmac.new(
            os.environ["TELEMEDICINE_TURN_SECRET"].encode(),
            entry["username"].encode(), hashlib.sha1
        ).digest()).decode()
        self.assertEqual(entry["credential"], expected)
        self.assertEqual(len(entry["urls"]), 2)


class StoreTests(unittest.TestCase):
    def setUp(self):
        self.store = server.RoomStore(20)
        self.room = secrets.token_urlsafe(20)

    def test_join_offer_candidate_and_hangup(self):
        self.assertFalse(self.store.join(self.room, "doctor", "session-doc"))
        self.assertTrue(self.store.join(self.room, "patient", "session-pat"))
        messages = self.store.poll(self.room, "doctor", "session-doc", 0)
        self.assertEqual(messages["events"][0]["type"], "peer-ready")
        sdp = {"type": "offer", "sdp": "v=0\r\n"}
        self.store.emit(self.room, "doctor", "session-doc", "offer", sdp)
        received = self.store.poll(self.room, "patient", "session-pat", 0)
        self.assertEqual(received["events"][0]["type"], "offer")
        with self.assertRaises(ValueError):
            self.store.emit(self.room, "patient", "session-pat", "offer", sdp)
        self.store.leave(self.room, "patient", "session-pat")
        left = self.store.poll(self.room, "doctor", "session-doc", messages["next"])
        self.assertEqual(left["events"][0]["type"], "hangup")

    def test_room_capacity(self):
        limit = server.RoomStore(1)
        limit.join(secrets.token_urlsafe(20), "doctor", "session1")
        with self.assertRaises(RuntimeError):
            limit.join(secrets.token_urlsafe(20), "patient", "session2")

    def test_unjoined_cannot_emit_or_poll(self):
        with self.assertRaises(LookupError):
            self.store.poll(self.room, "doctor", "session-doc", 0)
        with self.assertRaises(LookupError):
            self.store.emit(self.room, "doctor", "session-doc", "hangup", None)


class HttpTests(unittest.TestCase):
    @classmethod
    def setUpClass(cls):
        cls.httpd = ThreadingHTTPServer(("127.0.0.1", 0), server.Handler)
        cls.worker = threading.Thread(target=cls.httpd.serve_forever, daemon=True)
        cls.worker.start()
        cls.base = f"http://127.0.0.1:{cls.httpd.server_port}"
        cls.room = secrets.token_urlsafe(20)
        cls.secret = bytes.fromhex(os.environ["TELEMEDICINE_SIGNALING_SECRET"])

    @classmethod
    def tearDownClass(cls):
        cls.httpd.shutdown()
        cls.httpd.server_close()
        cls.worker.join(timeout=3)

    def request(self, path, role="doctor", method="GET", data=None, token=None, origin=None, session=None):
        token = token or tokens.issue(self.secret, self.room, role)
        headers = {"Authorization": "Bearer " + token}
        if origin:
            headers["Origin"] = origin
        if session:
            headers["X-Telemedicine-Session"] = session
        body = None if data is None else json.dumps(data).encode()
        if data is not None:
            headers["Content-Type"] = "application/json"
        req = Request(self.base + path, data=body, method=method, headers=headers)
        try:
            with urlopen(req, timeout=4) as result:
                return result.status, json.loads(result.read())
        except HTTPError as err:
            return err.code, json.loads(err.read())

    def test_auth_and_origin(self):
        status, content = self.request("/api/config")
        self.assertEqual(status, 200)
        self.assertEqual(content["role"], "doctor")
        status, _ = self.request("/api/config", token="bad")
        self.assertEqual(status, 401)
        status, _ = self.request("/api/join", method="POST", data={}, origin="https://evil.example")
        self.assertEqual(status, 403)

    def test_endpoints(self):
        status, doctor = self.request("/api/join", method="POST", data={})
        self.assertEqual(status, 200)
        status, result = self.request("/api/join", role="patient", method="POST", data={})
        self.assertEqual(status, 200)
        self.assertTrue(result["peer_present"])
        status, _ = self.request("/api/signal", method="POST", session=doctor["session"], data={"type": "offer", "data": {"type": "offer", "sdp": "v=0\r\n"}})
        self.assertEqual(status, 200)
        # Event exists already so no long-poll blocking.
        status, result = self.request("/api/poll?after=0", role="patient", session=result["session"])
        self.assertEqual(status, 200)
        self.assertEqual(result["events"][0]["type"], "offer")
        status, _ = self.request("/api/leave", method="POST", session=doctor["session"], data={})
        self.assertEqual(status, 200)


if __name__ == "__main__":
    unittest.main()
