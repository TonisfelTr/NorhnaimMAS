#!/usr/bin/env python3
"""Update only LAN-related NORHNAIM WebRTC demo variables (keep secrets intact)."""
import argparse
import ipaddress
import os
from pathlib import Path
import tempfile

ROOT = Path(__file__).resolve().parents[2]
ENV = ROOT / '.env.telemedicine'


def main():
    ap = argparse.ArgumentParser(description='Configure LAN IP for local HTTPS WebRTC testing')
    ap.add_argument('--ip', required=True, help='Windows/Wi-Fi LAN IPv4 address reachable from phone')
    ap.add_argument('--https-port', type=int, default=3443)
    ap.add_argument('--https-bind', default='0.0.0.0', help='Host listen IP; 0.0.0.0 recommended for WSL')
    args = ap.parse_args()
    try:
        ip = ipaddress.IPv4Address(args.ip)
        bind_ip = ipaddress.IPv4Address(args.https_bind)
    except ipaddress.AddressValueError as e:
        ap.error(str(e))
    if not ip.is_private or ip.is_loopback or ip.is_link_local or ip.is_multicast:
        ap.error('LAN IP must be a private routable IPv4 address (e.g. 192.168.1.50)')
    if not 1 <= args.https_port <= 65535:
        ap.error('HTTPS port must be within 1..65535')
    if not ENV.exists():
        ap.error('Missing .env.telemedicine; first run python3 telemedicine/scripts/init.py')

    contents = ENV.read_text(encoding='utf-8').splitlines()
    current = {}
    for line in contents:
        if not line.lstrip().startswith('#') and '=' in line:
            key, val = line.split('=', 1)
            current[key] = val
    uiport = current.get('TELEMEDICINE_UI_PORT', '3100')
    updates = {
        'TELEMEDICINE_LAN_IP': str(ip),
        'TELEMEDICINE_TURN_HOST': str(ip),
        'TELEMEDICINE_HTTPS_BIND_IP': str(bind_ip),
        'TELEMEDICINE_HTTPS_PORT': str(args.https_port),
        'TELEMEDICINE_ALLOWED_ORIGINS': (
            f'http://localhost:{uiport},http://127.0.0.1:{uiport},'
            f'https://{ip}:{args.https_port}'
        ),
    }
    out = []
    found = set()
    for line in contents:
        key = line.split('=', 1)[0] if '=' in line else ''
        if key in updates:
            if key not in found:
                out.append(f'{key}={updates[key]}')
                found.add(key)
        else:
            out.append(line)
    for key, val in updates.items():
        if key not in found:
            out.append(f'{key}={val}')

    # Never echo secrets or weaken the file permissions.
    fd, pathname = tempfile.mkstemp(dir=ENV.parent, prefix='.env.telemedicine-', text=True)
    try:
        with os.fdopen(fd, 'w', encoding='utf-8') as file:
            file.write('\n'.join(out) + '\n')
        os.chmod(pathname, 0o600)
        os.replace(pathname, ENV)
    finally:
        if os.path.exists(pathname):
            os.unlink(pathname)
    print(f'Mobile WebRTC configured: https://{ip}:{args.https_port} (TURN host: {ip})')
    print('Signing secrets were preserved.')


if __name__ == '__main__':
    main()
