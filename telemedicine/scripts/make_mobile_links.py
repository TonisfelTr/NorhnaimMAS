#!/usr/bin/env python3
"""Issue independent doctor/patient demo links for a PC and a phone on the LAN."""
import argparse
import ipaddress
from pathlib import Path
import secrets
import sys
from urllib.parse import urlencode

ROOT = Path(__file__).resolve().parents[2]
sys.path.insert(0, str(ROOT / 'telemedicine' / 'signaling'))
from tokens import issue  # noqa: E402


def main():
    parser = argparse.ArgumentParser(description='Generate PC & Android test links')
    parser.add_argument('--ttl-minutes', type=int, default=60, help='Token lifetime (1–240)')
    args = parser.parse_args()
    if not 1 <= args.ttl_minutes <= 240:
        parser.error('Разрешено 1–240 минут')
    config_file = ROOT / '.env.telemedicine'
    if not config_file.exists():
        parser.error('Не найден .env.telemedicine')
    data = {}
    for line in config_file.read_text(encoding='utf-8').splitlines():
        if '=' in line and not line.lstrip().startswith('#'):
            key, value = line.split('=', 1)
            data[key.strip()] = value.strip().strip('"')
    try:
        ip = str(ipaddress.IPv4Address(data['TELEMEDICINE_LAN_IP']))
        port = int(data.get('TELEMEDICINE_HTTPS_PORT', '3443'))
        local_port = int(data.get('TELEMEDICINE_UI_PORT', '3100'))
        secret = bytes.fromhex(data['TELEMEDICINE_SIGNALING_SECRET'])
    except (KeyError, ValueError) as exc:
        parser.error(f'Неверная конфигурация. Сначала запусти configure_mobile.py: {exc}')
    room = secrets.token_urlsafe(20)
    common = {'ttl': args.ttl_minutes * 60}
    doctor = issue(secret, room, 'doctor', **common)
    patient = issue(secret, room, 'patient', **common)
    print('\nНОРХНЕЙМ — ТОЛЬКО ЛОКАЛЬНЫЙ ТЕСТ, БЕЗ РЕАЛЬНЫХ ПАЦИЕНТОВ')
    print('\nНА КОМПЬЮТЕРЕ (врач):')
    print(f'http://localhost:{local_port}/demo#{urlencode({"token": doctor})}')
    print('\nНА ТЕЛЕФОНЕ (пациент), тот же Wi-Fi:')
    print(f'https://{ip}:{port}/demo#{urlencode({"token": patient})}')
    print('\nЕсли телефон пишет, что сайт не защищён, сначала установи root-ca.crt на Android.')
    print('Ссылки являются токенами доступа; не передавай их посторонним.')


if __name__ == '__main__':
    main()
