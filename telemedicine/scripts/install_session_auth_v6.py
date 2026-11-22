#!/usr/bin/env python3
"""Safe installer for NORHNAIM session page server-auth v6, stdlib only.

Requires the local sources to match the user's captured November 2026 tree.
Never alters .env, Laravel, TURN, secrets or certificates.
"""
from __future__ import annotations

import argparse
from datetime import datetime, timezone
import hashlib
import json
import os
from pathlib import Path
import shutil
import stat
import sys
import tempfile

ROOT = Path(__file__).resolve().parents[2]
PACKAGE = ROOT / 'telemedicine/patches/session_access_v6'
PAYLOAD = PACKAGE / 'files'
MANIFEST = PACKAGE / 'manifest.json'
BACKUPS = ROOT / '.norhnaim-backups'


def digest(path: Path) -> str | None:
    return hashlib.sha256(path.read_bytes()).hexdigest() if path.is_file() else None


def load_manifest() -> dict:
    data = json.loads(MANIFEST.read_text(encoding='utf-8'))
    assert data['package'] == 'norhnaim_session_auth_v6'
    for name, item in data['files'].items():
        if '..' in Path(name).parts or Path(name).is_absolute():
            raise ValueError('Invalid package path')
        staged = PAYLOAD / name
        if not staged.is_file() or digest(staged) != item['installed_sha256']:
            raise ValueError(f'ПОВРЕЖДЁН ПАКЕТ: {name}')
        if name.endswith('.py'):
            compile(staged.read_text(encoding='utf-8'), str(staged), 'exec')
    return data


def status(data: dict) -> str:
    all_original = True
    all_new = True
    for name, item in data['files'].items():
        observed = digest(ROOT / name)
        if observed != item['original_sha256']:
            all_original = False
        if observed != item['installed_sha256']:
            all_new = False
        if observed != item['original_sha256'] and observed != item['installed_sha256']:
            raise ValueError(f'НЕСОВМЕСТИМЫЙ ФАЙЛ: {name}\n'
                             'Файл изменился после отправки исходников. '
                             'Не перезаписываю его — потребуется новый патч.')
    if all_original:
        return 'ready'
    if all_new:
        return 'installed'
    raise ValueError('Найдены смешанные версии исходников и патча. '
                     'Остановлено, чтобы не повредить работающий сервер.')


def atomic_write(target: Path, raw: bytes, mode: int) -> None:
    target.parent.mkdir(parents=True, exist_ok=True)
    fd, name = tempfile.mkstemp(prefix='.session-auth-v6-', dir=target.parent)
    try:
        with os.fdopen(fd, 'wb') as handle:
            handle.write(raw)
            handle.flush()
            os.fsync(handle.fileno())
        os.chmod(name, mode)
        os.replace(name, target)
    finally:
        if os.path.lexists(name):
            os.unlink(name)


def install(data: dict) -> None:
    result = status(data)
    if result == 'installed':
        print('Уже установлено — повторная установка не требуется.')
        return
    BACKUPS.mkdir(parents=True, exist_ok=True)
    stamp = datetime.now(timezone.utc).strftime('%Y%m%dT%H%M%S%fZ')
    backup = BACKUPS / f'session-auth-v6-{stamp}'
    backup.mkdir(mode=0o700)
    previous = {}
    for name, item in data['files'].items():
        src = ROOT / name
        previous[name] = {'sha256': digest(src), 'existed':src.is_file()}
        if src.is_file():
            target = backup / name
            target.parent.mkdir(parents=True,exist_ok=True)
            shutil.copy2(src,target)
    (backup/'backup_manifest.json').write_text(json.dumps(previous,ensure_ascii=False,indent=2)+'\n')
    written = []
    try:
        for name, item in data['files'].items():
            target = ROOT / name
            mode = stat.S_IMODE(target.stat().st_mode) & 0o755 if target.is_file() else item['mode']
            atomic_write(target,(PAYLOAD/name).read_bytes(),mode)
            written.append(name)
            if digest(target)!=item['installed_sha256']:
                raise OSError(f'Copy failed: {name}')
    except Exception:
        # Restore every changed path; all originals were saved before any change.
        for name in reversed(written):
            old = backup/name
            dest = ROOT/name
            if old.is_file():
                shutil.copy2(old,dest)
            else:
                dest.unlink(missing_ok=True)
        raise
    print('ГОТОВО: проверка токена выполняется сервером, без токена HTTP 403')
    print('ГОТОВО: URL приглашений теперь /session?token=...')
    print('ГОТОВО: обработка WebRTC, TURN и роли участников сохранена')
    print('ГОТОВО: Nginx не пишет URL /session с токеном в access-log')
    print('Резервная копия:',backup)
    print('Дальше: проверить Docker Compose и nginx -t, потом пересоздать signaling и HTTPS.')


def rollback(data: dict) -> None:
    candidates=sorted(BACKUPS.glob('session-auth-v6-*'),reverse=True) if BACKUPS.exists() else []
    if not candidates:
        raise ValueError('Нет резервных копий v6 для отката')
    backup=candidates[0]
    history=json.loads((backup/'backup_manifest.json').read_text())
    # Refuse to overwrite subsequent user edits.
    for name,item in data['files'].items():
        if digest(ROOT/name)!=item['installed_sha256']:
            raise ValueError(f'Откат остановлен: {name} уже изменён после v6')
    for name,old in history.items():
        dest=ROOT/name
        if old['existed']:
            saved=backup/name
            if digest(saved)!=old['sha256']:
                raise ValueError(f'Испорчена резервная копия: {name}')
            atomic_write(dest,saved.read_bytes(),stat.S_IMODE(saved.stat().st_mode))
        else:
            dest.unlink(missing_ok=True)
    print('ОТКАТ УСПЕШЕН. Восстановлены файлы из:',backup)
    print('Проверь Docker Compose и nginx -t перед перезапуском.')


def main() -> int:
    parser=argparse.ArgumentParser(description='NORHNAIM session auth v6')
    x=parser.add_mutually_exclusive_group(required=True)
    x.add_argument('--check',action='store_true')
    x.add_argument('--apply',action='store_true')
    x.add_argument('--rollback',action='store_true')
    args=parser.parse_args()
    try:
        data=load_manifest()
        if args.check:
            mode=status(data)
            print('ПРОВЕРКА УСПЕШНА:',
                  'совместимо, можно устанавливать' if mode=='ready' else 'версия v6 уже установлена')
        elif args.apply:
            install(data)
        else:
            rollback(data)
        return 0
    except (OSError, ValueError, AssertionError, SyntaxError) as exc:
        print('ОШИБКА:',exc,file=sys.stderr)
        return 1

if __name__=='__main__':
    sys.exit(main())
