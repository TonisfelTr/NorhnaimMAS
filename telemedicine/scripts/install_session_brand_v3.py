#!/usr/bin/env python3
"""Apply NORHNAIM's existing viking branding to the already-installed WebRTC /session v2.

Reads the actual original images in public/assets/images/home, serves copies via
an explicit allowlist of signaling routes, and updates only HTML/CSS/server.py.
No changes to WebRTC JS, nginx, Docker Compose, TLS, TURN or Laravel.
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
CSS = ROOT / 'telemedicine/signaling/style.css'
SERVER = ROOT / 'telemedicine/signaling/server.py'
PATCH = ROOT / 'telemedicine/patches/session_brand_v3/brand.extra.css'
BACKUPS = ROOT / '.norhnaim-backups'

# Source images from the user's existing Laravel public directory.
ASSETS = {
    'brand/norhneim-viking-mark.png': 'public/assets/images/home/norhneim-viking-mark.png',
    'brand/favicon-32x32.png': 'public/assets/images/home/favicon/favicon-32x32.png',
    'brand/favicon-16x16.png': 'public/assets/images/home/favicon/favicon-16x16.png',
}
HTML_MARKER = ''
CSS_BEGIN = ''
CSS_END = ''
SERVER_BEGIN = '# NORHNAIM_SESSION_BRAND_V3_BEGIN'
SERVER_END = '# NORHNAIM_SESSION_BRAND_V3_END'
PNG = b'\x89PNG\r\n\x1a\n'

def fail(message: str) -> None:
    raise RuntimeError(message)

def exactly_once(source: str, old: str, new: str, title: str) -> str:
    if source.count(old) != 1:
        fail(f'Не найден ожидаемый фрагмент в {title}: {old[:80]!r}. Существующие изменения не затронуты.')
    return source.replace(old, new, 1)

def next_html(source: str) -> str:
    if HTML_MARKER in source:
        if source.count(HTML_MARKER) != 1 or '/brand/norhneim-viking-mark.png' not in source:
            fail('HTML содержит неполную установку V3')
        return source
    if '' not in source:
        fail('Требуется установленный редизайн v2 в telemedicine/signaling/index.html')

    # Explicit textual substitutions are intentional: stop on drift, never overwrite other edits.
    out = exactly_once(
        source,
        '  <title>НОРХНЕЙМ · Видеосеанс</title>',
        '  <title>Норхнейм — Видеосвязь</title>\n'
        f'  {HTML_MARKER}\n'
        '  <link rel="icon" type="image/png" sizes="32x32" href="/brand/favicon-32x32.png?v=3">\n'
        '  <link rel="icon" type="image/png" sizes="16x16" href="/brand/favicon-16x16.png?v=3">',
        'index.html / заголовок и favicon',
    )
    out = exactly_once(
        out,
        '      <div class="logo" aria-hidden="true">Н<span>·</span></div>',
        '      <div class="logo" aria-hidden="true">'
        '<img src="/brand/norhneim-viking-mark.png?v=3" alt="" width="54" height="54"></div>',
        'index.html / логотип',
    )
    out = exactly_once(
        out,
        '      <div class="masthead-text"><b>НОРХНЕЙМ</b><span>Сеанс видеосвязи WebRTC</span></div>',
        '      <div class="masthead-text"><b>Норхнейм</b><span>КЛИНИЧЕСКАЯ СИСТЕМА</span></div>',
        'index.html / текст шапки',
    )
    out = exactly_once(
        out,
        '      <div class="stage">Тестовая среда</div>\n',
        '',
        'index.html / тестовая среда',
    )
    old_warning = ('    <p class="warning"><b>Тестовый режим.</b> Эта страница пока не интегрирована '
                   'с авторизацией Laravel, записью на приём и медицинскими документами. '
                   'Не используйте реальные персональные или медицинские данные.</p>\n')
    out = exactly_once(out, old_warning, '', 'index.html / тестовое предупреждение')
    ids = ('remoteVideo','localVideo','remotePlaceholder','localPlaceholder',
           'startButton','muteButton','cameraButton','endButton',
           'statusDot','statusText','roleText','channelText')
    for id_ in ids:
        if out.count('id="'+id_+'"') != 1:
            fail('Отсутствует или дублируется элемент WebRTC '+id_)
    return out

def next_css(source: str, patch: str) -> str:
    if CSS_BEGIN in source:
        if source.count(CSS_BEGIN) != 1 or source.count(CSS_END) != 1:
            fail('CSS содержит неполную установку V3')
        return source
    if 'NORHNAIM_SESSION_REDESIGN_V2_BEGIN' not in source:
        fail('Требуется CSS редизайна v2')
    if not patch.startswith(CSS_BEGIN) or CSS_END not in patch:
        fail('Повреждён файл дополнительных стилей')
    return source.rstrip('\n') + '\n\n' + patch.rstrip('\n') + '\n'

def next_server(source: str) -> str:
    if SERVER_BEGIN in source:
        if source.count(SERVER_BEGIN) != 1 or source.count(SERVER_END) != 1:
            fail('server.py содержит неполную установку V3')
        return source
    # Add exact routes after the already-existing /style.css static handler.
    pat = re.compile(
        r'(?m)^([ \t]*)if path == [\'"]/style\.css[\'"]:[ \t]*\n'
        r'([ \t]+)return self\._file\([\'"]style\.css[\'"],[ \t]*'
        r'[\'"]text/css; charset=utf-8[\'"]\)[ \t]*\n'
    )
    hits = list(pat.finditer(source))
    if len(hits) != 1:
        fail('В server.py не найден ровно один стандартный обработчик /style.css')
    match = hits[0]
    indent = match.group(1)
    return_indent = match.group(2)
    if len(return_indent.expandtabs()) <= len(indent.expandtabs()):
        fail('Неверные отступы в server.py')
    for route in ASSETS:
        if f"/{route}" in source:
            fail('В server.py уже есть маршрут /'+route+' без маркера V3 — проверь вручную')
    lines = [f'{indent}{SERVER_BEGIN}']
    for path in ASSETS:
        lines.append(f"{indent}if path == '/{path}':")
        lines.append(f"{return_indent}return self._file('{path}', 'image/png')")
    lines.append(f'{indent}{SERVER_END}')
    snippet = '\n'.join(lines) + '\n'
    return source[:match.end()] + snippet + source[match.end():]

def png_data(path: Path) -> bytes:
    data = path.read_bytes()
    if not data.startswith(PNG) or len(data) < 32:
        fail('Оригинальный файл не является PNG: ' + str(path))
    if len(data) > 5 * 1024 * 1024:
        fail('Слишком большой брендовый PNG (больше 5 МБ): ' + str(path))
    return data

def proposed() -> dict[Path, bytes]:
    for p in (HTML, CSS, SERVER, PATCH):
        if not p.is_file():
            fail('Файл не найден: ' + str(p))
    for src in ASSETS.values():
        if not (ROOT / src).is_file():
            fail('Оригинальная иконка не найдена: ' + src)
    result = {
        HTML: next_html(HTML.read_text(encoding='utf-8')).encode('utf-8'),
        CSS: next_css(CSS.read_text(encoding='utf-8'), PATCH.read_text(encoding='utf-8')).encode('utf-8'),
        SERVER: next_server(SERVER.read_text(encoding='utf-8')).encode('utf-8'),
    }
    for dest, source in ASSETS.items():
        result[ROOT / 'telemedicine/signaling' / dest] = png_data(ROOT / source)
    return result

def changed_targets(plan: dict[Path, bytes]) -> dict[Path, bool]:
    return {p: not p.is_file() or p.read_bytes() != value for p, value in plan.items()}

def atomic_write(path: Path, data: bytes, src_mode: int | None) -> None:
    path.parent.mkdir(parents=True, exist_ok=True)
    temp = path.with_name(path.name+'.brand_v3.tmp')
    if temp.exists():
        fail('Временный файл уже существует: ' + str(temp))
    try:
        temp.write_bytes(data)
        os.chmod(temp, src_mode if src_mode is not None else 0o644)
        os.replace(temp, path)
    finally:
        if temp.exists():
            temp.unlink()

def apply() -> None:
    plan = proposed()
    changed = changed_targets(plan)
    if not any(changed.values()):
        print('Брендинг v3 уже установлен; ничего делать не нужно.')
        return
    stamp = datetime.now(timezone.utc).strftime('%Y%m%dT%H%M%SZ')
    backup = BACKUPS / f'session-brand-v3-{stamp}'
    if backup.exists():
        fail('Каталог резервной копии уже существует: '+str(backup))
    backup.mkdir(parents=True)
    manifest: dict[str, bool] = {}
    for path in plan:
        rel = path.relative_to(ROOT)
        exists = path.is_file()
        manifest[str(rel)] = exists
        if exists:
            dest = backup / rel
            dest.parent.mkdir(parents=True, exist_ok=True)
            shutil.copy2(path, dest)
    (backup / 'manifest.json').write_text(json.dumps(manifest, indent=2, ensure_ascii=False), encoding='utf-8')
    try:
        for path, data in plan.items():
            if changed[path]:
                mode = stat.S_IMODE(path.stat().st_mode) if path.exists() else None
                atomic_write(path, data, mode)
    except Exception:
        restore(backup)
        raise
    print('Готово: оригинальный викинг в шапке страницы /session')
    print('Готово: фирменный favicon 16x16 и 32x32')
    print('Готово: удалены «Тестовая среда» и текст «Тестовый режим»')
    print('Сохранены: равные видеоокна, кнопки-иконки, все WebRTC обработчики')
    print('TURN, сертификаты, nginx, Laravel и секреты НЕ изменены')
    print('Резервная копия:', backup)
    print('Перезапускать требуется только telemedicine-signaling')

def restore(backup: Path) -> None:
    try:
        real_backup = backup.resolve(strict=True)
    except OSError:
        fail('Резервная копия не найдена')
    if BACKUPS.resolve() not in real_backup.parents or not real_backup.name.startswith('session-brand-v3-'):
        fail('Откат разрешён только из резервной копии V3 в .norhnaim-backups')
    m = real_backup / 'manifest.json'
    if not m.is_file():
        fail('Отсутствует manifest.json в резервной копии')
    manifest = json.loads(m.read_text(encoding='utf-8'))
    if not isinstance(manifest, dict) or not manifest:
        fail('Некорректная резервная копия')
    for rel, existed in manifest.items():
        relpath = Path(rel)
        if relpath.is_absolute() or '..' in relpath.parts:
            fail('Некорректный путь в резервной копии')
        if not isinstance(existed, bool):
            fail('Некорректный manifest.json')
        if existed and not (real_backup / relpath).is_file():
            fail('Не хватает файла в резервной копии: '+rel)
    for rel, existed in manifest.items():
        target = ROOT / rel
        if existed:
            target.parent.mkdir(parents=True, exist_ok=True)
            shutil.copy2(real_backup / rel, target)
        elif target.exists():
            target.unlink()
    print('Файлы восстановлены из:', real_backup)
    print('Перезапусти telemedicine-signaling')

def main() -> None:
    parser = argparse.ArgumentParser(description='NORHNAIM /session: оригинальный викинг и favicon')
    group = parser.add_mutually_exclusive_group(required=True)
    group.add_argument('--check', action='store_true')
    group.add_argument('--apply', action='store_true')
    group.add_argument('--rollback', metavar='BACKUP_PATH', type=Path)
    args = parser.parse_args()
    try:
        if args.rollback is not None:
            restore(args.rollback)
        elif args.check:
            plan = proposed()
            changed = changed_targets(plan)
            print('Проверка успешна; файлы готовы к установке.')
            for p, yes in changed.items():
                print(str(p.relative_to(ROOT)), '— обновится' if yes else '— без изменений')
        else:
            apply()
    except (RuntimeError, OSError, ValueError) as exc:
        print('Установка остановлена:', exc, file=sys.stderr)
        sys.exit(1)

if __name__ == '__main__':
    main()
