"""Check a verified appointment-bound invitation against Laravel before joining.

Legacy local-demo invitations without registry_record_id are not patient visits.
No browser-supplied appointment ID, access token or identity is sent to Laravel.
"""
from __future__ import annotations
import hashlib
import hmac
import json
import os
import time
import urllib.error
import urllib.request
from urllib.parse import urlsplit
from tokens import TokenError

AUDIENCE = 'telemedicine-journal-v1'

class NoRedirect(urllib.request.HTTPRedirectHandler):
    def redirect_request(self, req, fp, code, msg, headers, newurl):
        return None  # Do not forward signed internal headers to a redirected host.

def assert_appointment_open(claim: dict, audit_url: str, secret: str, *, opener=None) -> None:
    appointment_id = claim.get('registry_record_id')
    if appointment_id is None:
        return
    if type(appointment_id) is not int or appointment_id <= 0 or len(secret) < 32:
        raise TokenError('Appointment authorization is unavailable')
    override = os.environ.get('TELEMEDICINE_APPOINTMENT_GUARD_URL', '')
    if override:
        endpoint = override
    elif audit_url.endswith('/audit/events'):
        endpoint = audit_url[:-len('/audit/events')] + '/journal/authorize'
    else:
        raise TokenError('Appointment authorization URL is not configured')
    parsed = urlsplit(endpoint)
    if parsed.scheme not in ('http', 'https') or not parsed.netloc or parsed.username or parsed.password:
        raise TokenError('Invalid appointment authorization URL')
    payload = {'audience': AUDIENCE, 'registry_record_id': appointment_id}
    # These values came from the verified HMAC invitation, never from browser JSON.
    appointment_start = claim.get('appointment_start')
    if appointment_start is not None:
        room = claim.get('room')
        if (type(appointment_start) is not int or appointment_start <= 0
                or not isinstance(room, str) or not 16 <= len(room) <= 100):
            raise TokenError('Invalid managed appointment invitation')
        payload['room'] = room
        payload['appointment_start'] = appointment_start
    raw = json.dumps(payload, separators=(',', ':')).encode('utf-8')
    stamp = str(int(time.time()))
    signature = hmac.new(secret.encode(), (AUDIENCE+'\n'+stamp+'.').encode()+raw,
                         hashlib.sha256).hexdigest()
    req = urllib.request.Request(endpoint, raw, headers={
        'Content-Type': 'application/json', 'Accept': 'application/json',
        'X-Telemedicine-Timestamp': stamp,
        'X-Telemedicine-Signature': 'sha256='+signature,
    }, method='POST')
    client = opener or urllib.request.build_opener(NoRedirect())
    try:
        with client.open(req, timeout=3) as response:
            payload = response.read(4097)
            if len(payload) > 4096 or response.status != 200:
                raise TokenError('Appointment authorization denied')
            result = json.loads(payload)
            if not isinstance(result, dict) or result.get('allowed') is not True:
                raise TokenError('Appointment cancelled or unavailable')
    except (OSError, TimeoutError, ValueError, urllib.error.URLError) as exc:
        raise TokenError('Appointment authorization is unavailable') from exc
