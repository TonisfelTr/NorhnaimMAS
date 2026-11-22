#!/usr/bin/env python3
"""Issue two local-test invitations that link to one EXISTING Laravel appointment.

Only authorized staff may operate this server-side script; CLI input alone does
not prove an appointment exists or that the operator is its assigned doctor.
For production, issue invitations in an authenticated Laravel controller after
checking registry_records, current user, clinic, consent and schedule window.
"""
from pathlib import Path
import argparse
import secrets
import sys
from urllib.parse import urlencode

ROOT = Path(__file__).resolve().parents[2]
sys.path.insert(0, str(ROOT / 'telemedicine' / 'signaling'))
from tokens import issue  # noqa: E402


def config_values(path: Path) -> dict[str, str]:
    return dict(
        (key.strip(), value.strip())
        for line in path.read_text(encoding='utf-8').splitlines()
        if line.strip() and not line.lstrip().startswith('#') and '=' in line
        for key, value in [line.split('=', 1)]
    )


def main() -> int:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--registry-record-id', required=True, type=int,
                        help='ID of an EXISTING telemedicine appointment, verified by staff')
    parser.add_argument('--ttl-minutes', type=int, default=90)
    parser.add_argument('--room-id', default=None, help='Existing room ID only for controlled test reissue')
    parser.add_argument('--base-url', default=None, help='Base URL used for both parties')
    parser.add_argument('--doctor-base-url', default=None, help='Override physician URL')
    parser.add_argument('--patient-base-url', default=None, help='Override patient URL (HTTPS LAN for phone)')
    args = parser.parse_args()
    if args.registry_record_id <= 0:
        parser.error('Appointment ID must be positive')
    if not 1 <= args.ttl_minutes <= 240:
        parser.error('TTL must be between 1 and 240 minutes')
    conf = config_values(ROOT / '.env.telemedicine')
    secret = bytes.fromhex(conf['TELEMEDICINE_SIGNALING_SECRET'])
    room = args.room_id or secrets.token_urlsafe(20)
    base = args.base_url or f"http://localhost:{conf.get('TELEMEDICINE_UI_PORT', '3100')}/demo"
    print(f'Appointment #{args.registry_record_id}, room {room}\nLOCAL TEST ONLY — not verified against the database!')
    print('Do not use with real patients: links are bearer tokens, not proof of medical identity.\n')
    for role, label in [('doctor', 'DOCTOR'), ('patient', 'PATIENT')]:
        token = issue(secret, room, role, ttl=args.ttl_minutes * 60,
                      registry_record_id=args.registry_record_id)
        use_base = (args.doctor_base_url if role == 'doctor' else args.patient_base_url) or base
        print(f'{label}: {use_base}#{urlencode({"token": token})}')
    return 0


if __name__ == '__main__':
    raise SystemExit(main())
