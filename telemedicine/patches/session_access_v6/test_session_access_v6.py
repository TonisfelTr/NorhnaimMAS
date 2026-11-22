#!/usr/bin/env python3
"""Offline HTTP integration tests for the NORHNAIM session auth v6 server."""
from __future__ import annotations
import importlib.util
import json
import os
from pathlib import Path
import secrets
import sys
import tempfile
import threading
import time
import types
import unittest
from urllib import request, error

ROOT = Path(__file__).resolve().parents[3] # telemedicine/patches/session_access_v6 -> root?
PAYLOAD = Path(__file__).resolve().parent / 'files/telemedicine/signaling'

# Avoid importing or contacting Laravel/audit systems in the isolated test.
audit = types.ModuleType('audit')
class FakeAudit:
    def __init__(self):
        self.url = None
        self.secret = None
    def emit(self, *args, **kwargs): pass
    def close(self): pass
audit.AuditEmitter = FakeAudit
sys.modules['audit'] = audit
appointment_guard = types.ModuleType('appointment_guard')
appointment_guard.assert_appointment_open = lambda *args, **kwargs: None
sys.modules['appointment_guard'] = appointment_guard

os.environ['TELEMEDICINE_SIGNALING_SECRET'] = 'aa' * 32
os.environ['TELEMEDICINE_TURN_SECRET'] = 'test-turn-key-' * 4
sys.path.insert(0, str(PAYLOAD))
sys.path.insert(0, str(Path(os.environ.get('TEST_TOKENS_DIR', ROOT / 'telemedicine/signaling'))))
spec = importlib.util.spec_from_file_location('server_v6_test', PAYLOAD / 'server.py')
server_module = importlib.util.module_from_spec(spec)
sys.modules[spec.name] = server_module
spec.loader.exec_module(server_module)
from tokens import issue
from http.server import ThreadingHTTPServer

class SessionAuthHTTPTests(unittest.TestCase):
    @classmethod
    def setUpClass(cls):
        cls.http = ThreadingHTTPServer(('127.0.0.1', 0), server_module.Handler)
        cls.http.daemon_threads = True
        cls.origin = 'http://127.0.0.1:' + str(cls.http.server_port)
        cls.thread = threading.Thread(target=cls.http.serve_forever,daemon=True)
        cls.thread.start()
        cls.room = secrets.token_urlsafe(20)
        cls.token = issue(server_module.SIGNING_KEY, cls.room, 'doctor', ttl=3600)
        cls.patient_token = issue(server_module.SIGNING_KEY, cls.room, 'patient', ttl=3600)

    @classmethod
    def tearDownClass(cls):
        cls.http.shutdown()
        cls.http.server_close()
        cls.thread.join(timeout=3)

    def fetch(self, path):
        url=self.origin+path
        try:
            with request.urlopen(url, timeout=5) as res:
                return res.status,res.headers,res.read()
        except error.HTTPError as ex:
            return ex.code,ex.headers,ex.read()

    def check_denied(self, path):
        status,headers,data=self.fetch(path)
        self.assertEqual(status, 403, path)
        self.assertEqual(headers.get('Cache-Control'), 'no-store')
        self.assertIn(b'403', str(status).encode())
        self.assertIn('text/html',headers.get('Content-Type',''))
        self.assertNotIn(b'id="remoteVideo"',data)
        self.assertNotIn(b'id="startButton"',data)
        return data

    def test_no_token_is_denied(self):
        self.check_denied('/session')
        self.check_denied('/session?utm_source=chatgpt.com')
        self.check_denied('/session?token=')
        self.check_denied('/session?token')
        self.check_denied('/session#token='+self.token)

    def test_invalid_or_duplicate_token_is_denied(self):
        self.check_denied('/session?token=invalid')
        self.check_denied('/session?token='+self.token[:-2]+'AA')
        self.check_denied('/session?token='+self.token+'&token='+self.token)

    def test_expired_token_is_denied(self):
        tok=issue(server_module.SIGNING_KEY,self.room,'doctor',ttl=60,now=int(time.time())-120)
        self.check_denied('/session?token='+tok)

    def test_valid_doctor_and_patient_token_allowed(self):
        for token in (self.token,self.patient_token):
            status,headers,body=self.fetch('/session?token='+token)
            self.assertEqual(status,200)
            self.assertEqual(headers.get('Cache-Control'),'no-store')
            self.assertEqual(headers.get('Referrer-Policy'),'no-referrer')
            self.assertIn(b'id="remoteVideo"',body)
            self.assertIn(b'id="startButton"',body)
            self.assertNotIn(token.encode(),body)
            self.assertNotIn(b'session-access-denied',body)

    def test_legacy_paths_not_accessible(self):
        self.check_denied('/')
        self.check_denied('/demo')
        self.check_denied('/demo?token='+self.token)

    def test_health_and_resources_unaffected(self):
        s,_,b=self.fetch('/health')
        self.assertEqual(s,200)
        self.assertEqual(json.loads(b)['ok'], True)
        s,_,b=self.fetch('/app.js')
        self.assertEqual(s,200)
        self.assertIn(b'new URLSearchParams(window.location.search)',b)
        s,_,b=self.fetch('/api/config')
        self.assertEqual(s,401)
        self.assertIn(b'Invitation invalid or expired',b)

if __name__=='__main__':
    unittest.main(verbosity=2)
