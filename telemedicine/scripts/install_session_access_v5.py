#!/usr/bin/env python3
"""NORHNAIM WebRTC: decline /session in the browser when #token is absent.

Important: URL fragments are never sent to the HTTP server. This is a UX gate;
real access control must still be enforced by the signaling API on every join.
The browser navigates to an HTTPS nginx endpoint that returns HTTP 403.

Idempotent, refuses ambiguous markup, backs up changed files, supports rollback.
"""
from __future__ import annotations

import argparse
from datetime import datetime, timezone
import json
import os
from pathlib import Path
import re
import shutil
import stat
import sys

ROOT = Path(__file__).resolve().parents[2]
HTML = ROOT / 'telemedicine/signaling/index.html'
NGINX = ROOT / 'telemedicine/mobile/nginx.conf'
BACKUPS = ROOT / '.norhnaim-backups'
HOST = 'video-norhnaim.duckdns.org'
HTML_MARK = ''
NGINX_BEGIN = '# NORHNAIM_SESSION_ACCESS_V5_BEGIN'
NGINX_END = '# NORHNAIM_SESSION_ACCESS_V5_END'

GUARD = '''  
  <!-- The invitation is in the URL fragment, so nginx cannot read it on /session. -->
  <style>html.session-access-denied body { display: none !important; }</style>
  <script>
    (() => {
      'use strict';
      // Existing signed links use /session#token=..., and must keep working.
      const token = new URLSearchParams(window.location.hash.slice(1)).get('token');
      if (!token || !token.trim()) {
        document.documentElement.classList.add('session-access-denied');
        window.location.replace('/session-denied');
      }
    })();
  </script>'''

# No tokens in HTML, URL query strings, nginx logs or referrals.
DENIED_BODY = ('<!doctype html><html lang="ru"><head><meta charset="utf-8">'
               '<meta name="viewport" content="width=device-width,initial-scale=1">'
               '<meta name="referrer" content="no-referrer">'
               '<meta name="robots" content="noindex,nofollow">'
               '<title>Норхнейм — доступ запрещён</title>'
               '<style>body{margin:0;min-height:100vh;display:grid;place-items:center;'
               'font:16px/1.5 system-ui,-apple-system,sans-serif;background:#f4f8fa;color:#183a49}'
               'main{max-width:460px;margin:20px;padding:34px;border:1px solid #dde8ed;'
               'border-radius:18px;background:#fff;box-shadow:0 15px 50px rgba(14,42,53,.07)}'
               'h1{font-size:25px;line-height:1.25;margin:0 0 12px}'
               'p{margin:0;color:#526878}img{height:44px;margin-bottom:16px}</style></head>'
               '<body><main><img src="/brand/norhneim-viking-mark.png" alt="Норхнейм">'
               '<h1>Доступ к видеосеансу закрыт</h1>'
               '<p>Для подключения нужна персональная ссылка-приглашение. '
               'Откройте ссылку, полученную от клиники.</p>'
               '</main></body></html>')

NGINX_ROUTE = f'''    {NGINX_BEGIN}
    # Browser-side fragment gate redirects here; respond with real HTTP 403.
    # Do not put bearer tokens in query strings to enforce this guard.
    location = /session-denied {{
        default_type text/html;
        charset utf-8;
        add_header Cache-Control "no-store" always;
        add_header X-Robots-Tag "noindex, nofollow" always;
        return 403 '{DENIED_BODY}';
    }}
    {NGINX_END}
'''

def fail(message: str) -> None:
    raise RuntimeError(message)

def index_next(text: str) -> str:
    if HTML_MARK in text:
        if text.count(HTML_MARK) != 1 or text.count('window.location.replace(\'/session-denied\')') != 1:
            fail('index.html: обнаружена неполная установка v5')
        return text
    if text.count('</head>') != 1 or text.count('<head>') != 1:
        fail('index.html: не найден единственный блок <head>')
    if '' not in text:
        fail('index.html: сначала установи существующий редизайн v2/v3')
    if text.count('id="startButton"') != 1 or text.count('id="localVideo"') != 1:
        fail('index.html: не совпадают ID элементов видеозвонка')
    if not re.search(r'<script\s+src=[\'\"]/?app\.js\b', text):
        fail('index.html: не найден оригинальный /app.js; установка остановлена')
    return text.replace('</head>', GUARD + '\n</head>', 1)

def closing_brace(text: str, start: int) -> int:
    opening = text.find('{', start)
    if opening < 0:
        fail('nginx.conf: отсутствует открывающая скобка server')
    level = 0
    quote = None
    esc = False
    comment = False
    for i in range(opening, len(text)):
        ch = text[i]
        if comment:
            if ch == '\n':
                comment = False
            continue
        if quote:
            if esc:
                esc = False
            elif ch == '\\':
                esc = True
            elif ch == quote:
                quote = None
            continue
        if ch == '#':
            comment = True
        elif ch in "\"'":
            quote = ch
        elif ch == '{':
            level += 1
        elif ch == '}':
            level -= 1
            if level == 0:
                return i
    fail('nginx.conf: не закрыта фигурная скобка server')

def nginx_next(text: str) -> str:
    servers = []
    for match in re.finditer(r'(?m)^[ \t]*server[ \t]*\{', text):
        end = closing_brace(text, match.start())
        block = text[match.start():end]
        host_re = re.compile(r'(?m)^[ \t]*server_name[ \t]+[^;\n]*\b' + re.escape(HOST) + r'\b[^;\n]*;')
        if host_re.search(block):
            servers.append((match.start(), end, block))
    if len(servers) != 1:
        fail(f'nginx.conf: ожидался один публичный server_name {HOST}, найдено {len(servers)}')
    _, end, block = servers[0]
    if NGINX_BEGIN in block or NGINX_END in block:
        if block.count(NGINX_BEGIN) != 1 or block.count(NGINX_END) != 1:
            fail('nginx.conf: обнаружена неполная установка v5')
        return text
    if re.search(r'(?m)^[ \t]*location[ \t]+=[ \t]*/session-denied(?:\s|\{)', block):
        fail('nginx.conf: /session-denied уже занят другим обработчиком')
    if not re.search(r'(?m)^[ \t]*location[ \t]+/[ \t]*\{', block):
        fail('nginx.conf: не найден обработчик location / публичного сервера')
    return text[:end] + '\n' + NGINX_ROUTE + text[end:]

def proposed() -> dict[Path, bytes]:
    for p in (HTML, NGINX):
        if not p.is_file():
            fail('Отсутствует файл: ' + str(p))
    return {
        HTML: index_next(HTML.read_text('utf-8')).encode('utf-8'),
        NGINX: nginx_next(NGINX.read_text('utf-8')).encode('utf-8'),
    }

def atomic_write(path: Path, contents: bytes) -> None:
    temp = path.with_name(path.name + '.session_access_v5.tmp')
    if temp.exists():
        fail('Временный файл уже существует: ' + str(temp))
    try:
        temp.write_bytes(contents)
        os.chmod(temp, stat.S_IMODE(path.stat().st_mode))
        os.replace(temp, path)
    finally:
        if temp.exists():
            temp.unlink()

def apply() -> None:
    plan = proposed()
    changed = {p: data for p, data in plan.items() if p.read_bytes() != data}
    if not changed:
        print('Доступ без приглашения уже блокируется; изменений нет.')
        return
    stamp = datetime.now(timezone.utc).strftime('%Y%m%dT%H%M%SZ')
    backup = BACKUPS / f'session-access-v5-{stamp}'
    backup.mkdir(parents=True, exist_ok=False)
    manifest = {'files': [str(p.relative_to(ROOT)) for p in plan]}
    for path in plan:
        target = backup / path.relative_to(ROOT)
        target.parent.mkdir(parents=True, exist_ok=True)
        shutil.copy2(path, target)
    (backup / 'manifest.json').write_text(json.dumps(manifest, indent=2, ensure_ascii=False), 'utf-8')
    try:
        for path, data in changed.items():
            atomic_write(path, data)
    except Exception:
        for path in plan:
            shutil.copy2(backup / path.relative_to(ROOT), path)
        raise
    print('Готово: без #token браузер сразу переходит на /session-denied')
    print('Готово: публичный nginx отвечает HTTP 403 и показывает страницу отказа')
    print('Готово: приглашения /session#token=... продолжают работать без изменений')
    print('Остались прежними: WebRTC, TURN, Laravel, сертификаты и генератор приглашений')
    print('Резервная копия:', backup)
    print('Далее проверь nginx -t и пересоздай signaling + HTTPS')

def rollback(folder: Path) -> None:
    original = folder.resolve(strict=True)
    if BACKUPS.resolve() not in original.parents or not original.name.startswith('session-access-v5-'):
        fail('Откат допускается только из резервной копии session-access-v5 в .norhnaim-backups')
    manifest = json.loads((original / 'manifest.json').read_text('utf-8'))
    expected = {'telemedicine/signaling/index.html', 'telemedicine/mobile/nginx.conf'}
    if set(manifest.get('files', [])) != expected:
        fail('В резервной копии неизвестный перечень файлов')
    for name in expected:
        src = original / name
        if not src.is_file():
            fail('В резервной копии отсутствует ' + name)
    for name in expected:
        shutil.copy2(original / name, ROOT / name)
    print('Восстановлено:', original)
    print('Пересоздай signaling + HTTPS после проверок.')

def main() -> None:
    parser = argparse.ArgumentParser(description='NORHNAIM: запрет /session без приглашения')
    group = parser.add_mutually_exclusive_group(required=True)
    group.add_argument('--check', action='store_true')
    group.add_argument('--apply', action='store_true')
    group.add_argument('--rollback', type=Path, metavar='BACKUP_DIR')
    args = parser.parse_args()
    try:
        if args.check:
            changes = proposed()
            for path, data in changes.items():
                print(str(path.relative_to(ROOT)) + ': ' + ('изменится' if path.read_bytes() != data else 'уже установлен'))
            print('Проверка совместимости: OK')
        elif args.apply:
            apply()
        else:
            rollback(args.rollback)
    except (RuntimeError, ValueError, OSError) as err:
        print('ОШИБКА:', err, file=sys.stderr)
        return 1
    return 0

if __name__ == '__main__':
    raise SystemExit(main())
