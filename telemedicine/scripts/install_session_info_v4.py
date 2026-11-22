#!/usr/bin/env python3
"""Small additive NORHNAIM /session information cleanup v4.1 over v2/v3.

Keeps app.js's roleText and channelText DOM nodes (hidden), mirrors the current
statusText into a visible connection status using a presentation-only observer.
No signaling, access tokens, TURN, Nginx, routes, TLS or Laravel changes.
"""
from __future__ import annotations

import argparse
from datetime import datetime, timezone
import json
import os
from pathlib import Path
import shutil
import stat
import sys

ROOT = Path(__file__).resolve().parents[2]
HTML = ROOT / 'telemedicine/signaling/index.html'
CSS = ROOT / 'telemedicine/signaling/style.css'
JS = ROOT / 'telemedicine/signaling/app.js'
PATCH = ROOT / 'telemedicine/patches/session_info_v4'
BACKUP_ROOT = ROOT / '.norhnaim-backups'

HTML_MARK = ''
CSS_BEGIN = ''
CSS_END = ''
JS_BEGIN = '// NORHNAIM_SESSION_INFO_V4_BEGIN'
JS_END = '// NORHNAIM_SESSION_INFO_V4_END'

OLD = '''    <section class="details" aria-label="Параметры сеанса">
      <div><span>Участник</span><strong id="roleText">—</strong></div>
      <div><span>Канал</span><strong id="channelText">—</strong></div>
      <div><span>Режим</span><strong>Один на один</strong></div>
    </section>'''
NEW = '''    
    <section class="details" aria-label="Сведения о соединении">
      <div><span>Ваша роль</span><strong id="roleDisplayText">—</strong></div>
      <div><span>Состояние соединения</span><strong id="connectionStateText" aria-live="polite">Ожидание</strong></div>
    </section>
    <!-- Kept for app.js compatibility. It may update these original IDs. -->
    <div class="session-info-legacy" hidden aria-hidden="true">
      <strong id="roleText">—</strong>
      <strong id="channelText">—</strong>
    </div>'''

def check_source(source: str, ids: tuple[str, ...], label: str) -> None:
    for element_id in ids:
        count = source.count(f'id="{element_id}"')
        if count != 1:
            raise RuntimeError(f'{label}: id="{element_id}" встречается {count} раз (ожидается 1)')

def html_result(source: str) -> str:
    if HTML_MARK in source:
        if source.count(HTML_MARK) != 1 or OLD in source or source.count('id="connectionStateText"') != 1:
            raise RuntimeError('HTML: обнаружена неполная установка v4, изменения не применены')
        check_source(source, ('roleText','channelText','statusText','roleDisplayText','connectionStateText'), 'HTML')
        return source
    if '' not in source:
        raise RuntimeError('HTML: сначала требуется установленный редизайн v2/v3')
    if source.count(OLD) != 1:
        raise RuntimeError('HTML: не найден ожидаемый блок Участник / Канал / Режим; нужен текущий исходник')
    result = source.replace(OLD, NEW, 1)
    check_source(result, ('roleText','channelText','statusText','roleDisplayText','connectionStateText',
                          'startButton','muteButton','cameraButton','endButton',
                          'remoteVideo','localVideo'), 'HTML')
    return result

def code_result(source: str, extra: str, begin: str, end: str, name: str) -> str:
    if begin in source or end in source:
        if source.count(begin) != 1 or source.count(end) != 1:
            raise RuntimeError(f'{name}: неполная установка v4')
        return source
    if not extra.startswith(begin) or not extra.rstrip().endswith(end):
        raise RuntimeError(f'{name}: файл дополнения повреждён')
    # The HTML and CSS independently prove a compatible v2/v3 installation.
    # Earlier local app.js revisions may omit the optional v2 presentation
    # marker; do not reject a working WebRTC client solely for that reason.
    if name == 'app.js':
        needed = ('roleText', 'channelText', 'statusText', 'startButton',
                  'muteButton', 'cameraButton', 'endButton')
        missing = [item for item in needed if item not in source]
        if missing:
            raise RuntimeError(
                'app.js: не найдены необходимые элементы WebRTC: ' + ', '.join(missing)
                + '. Нужен текущий исходник app.js, файл не изменён.'
            )
    elif 'NORHNAIM_SESSION_REDESIGN_V2_BEGIN' not in source:
        raise RuntimeError(f'{name}: не обнаружен ранее установленный редизайн v2')
    return source.rstrip('\n') + '\n\n' + extra.rstrip('\n') + '\n'

def proposed() -> dict[Path, bytes]:
    extra_css = PATCH / 'connection.extra.css'
    extra_js = PATCH / 'connection.extra.js'
    for p in (HTML, CSS, JS, extra_css, extra_js):
        if not p.is_file():
            raise RuntimeError('Не найден файл: ' + str(p))
    values = {
        HTML: html_result(HTML.read_text(encoding='utf-8')).encode('utf-8'),
        CSS: code_result(CSS.read_text(encoding='utf-8'), extra_css.read_text(encoding='utf-8'),
                         CSS_BEGIN, CSS_END, 'style.css').encode('utf-8'),
        JS: code_result(JS.read_text(encoding='utf-8'), extra_js.read_text(encoding='utf-8'),
                        JS_BEGIN, JS_END, 'app.js').encode('utf-8'),
    }
    return values

def atomic_write(path: Path, data: bytes) -> None:
    mode = stat.S_IMODE(path.stat().st_mode)
    target = path.with_name(path.name + '.session_info_v4_tmp')
    if target.exists():
        raise RuntimeError('Уже существует временный файл: ' + str(target))
    try:
        target.write_bytes(data)
        os.chmod(target, mode)
        os.replace(target, path)
    finally:
        if target.exists():
            target.unlink()

def apply() -> None:
    plan = proposed()
    changes = {path: data for path, data in plan.items() if path.read_bytes() != data}
    if not changes:
        print('Изменения v4 уже установлены, повторно ничего не изменяем.')
        return
    stamp = datetime.now(timezone.utc).strftime('%Y%m%dT%H%M%SZ')
    backup = BACKUP_ROOT / f'session-info-v4-{stamp}'
    if backup.exists():
        raise RuntimeError('Резервная копия с таким именем уже существует: ' + str(backup))
    backup.mkdir(parents=True)
    manifest = {}
    for path in plan:
        rel = path.relative_to(ROOT)
        manifest[str(rel)] = True
        dest = backup / rel
        dest.parent.mkdir(parents=True, exist_ok=True)
        shutil.copy2(path, dest)
    (backup / 'manifest.json').write_text(json.dumps(manifest, indent=2, ensure_ascii=False), encoding='utf-8')
    try:
        for path, data in changes.items():
            atomic_write(path, data)
    except Exception:
        for path in plan:
            shutil.copy2(backup / path.relative_to(ROOT), path)
        raise
    print('Готово v4.1: две карточки — «Ваша роль» и «Состояние соединения»')
    print('Готово: карточка «Режим» убрана')
    print('Готово: существующие roleText/channelText сохранены для app.js (скрыты)')
    print('Готово: состояние автоматически следует за статусом WebRTC')
    print('Видеосвязь, TURN, Laravel, HTTPS и сертификаты не изменены.')
    print('Резервная копия:', backup)
    print('Перезапусти только telemedicine-signaling')

def rollback(folder: str) -> None:
    backup = Path(folder).resolve(strict=True)
    try:
        backup.relative_to(BACKUP_ROOT.resolve())
    except ValueError as exc:
        raise RuntimeError('Откат разрешён только из .norhnaim-backups') from exc
    if not backup.name.startswith('session-info-v4-'):
        raise RuntimeError('Это не резервная копия session-info-v4')
    manifest_path = backup / 'manifest.json'
    manifest = json.loads(manifest_path.read_text(encoding='utf-8'))
    expected = {str(p.relative_to(ROOT)) for p in (HTML, CSS, JS)}
    if set(manifest) != expected:
        raise RuntimeError('Неверный manifest.json')
    for item in expected:
        src = backup / item
        if not src.is_file():
            raise RuntimeError('Файл в резервной копии отсутствует: ' + str(src))
    for item in expected:
        target = ROOT / item
        shutil.copy2(backup / item, target)
    print('Откат выполнен:', backup)
    print('Перезапусти telemedicine-signaling')

def main() -> None:
    parser = argparse.ArgumentParser(description='NORHNAIM: информация о соединении v4')
    group = parser.add_mutually_exclusive_group(required=True)
    group.add_argument('--check', action='store_true', help='Проверить совместимость; ничего не менять')
    group.add_argument('--apply', action='store_true', help='Сделать резервную копию и применить')
    group.add_argument('--rollback', metavar='BACKUP_DIR', help='Вернуть файлы из резервной копии')
    args = parser.parse_args()
    try:
        if args.check:
            plan = proposed()
            diff = [str(p.relative_to(ROOT)) for p, data in plan.items() if p.read_bytes() != data]
            print('Проверка совместимости v4.1: OK')
            print('Будут изменены:', ', '.join(diff) if diff else 'ничего, v4 уже установлен')
        elif args.apply:
            apply()
        else:
            rollback(args.rollback)
    except Exception as exc:
        print('ОШИБКА: ' + str(exc), file=sys.stderr)
        raise SystemExit(1)

if __name__ == '__main__':
    main()
