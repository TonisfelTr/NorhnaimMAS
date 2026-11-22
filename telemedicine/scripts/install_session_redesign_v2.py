#!/usr/bin/env python3
"""NORHNAIM: session redesign v2, safe installer.

Updates WebRTC HTML, appends isolated CSS/JS enhancements, one server.py
GET route and HTTPS nginx redirect. Preserves original WebRTC JS logic, TURN,
signing secrets, Laravel and TLS certificates.
"""
from __future__ import annotations

import argparse
from datetime import datetime, timezone
import hashlib
import os
from pathlib import Path
import re
import shutil
import stat
import sys

ROOT = Path(__file__).resolve().parents[2]
DEST = ROOT / "telemedicine/signaling/index.html"
CSS = ROOT / "telemedicine/signaling/style.css"
JS = ROOT / "telemedicine/signaling/app.js"
CSS_PATCH = ROOT / "telemedicine/patches/session_v2/style.extra.css"
JS_PATCH = ROOT / "telemedicine/patches/session_v2/app.extra.js"
PAYLOAD = ROOT / "telemedicine/patches/session_v2/index.html"
SERVER = ROOT / "telemedicine/signaling/server.py"
NGINX = ROOT / "telemedicine/mobile/nginx.conf"
BACKUPS = ROOT / ".norhnaim-backups"
ORIGINAL_SHA256 = "4585b025b1c47ca83ce23c706090ba5817134bad8d69bf6ac64ce3f19bd5a8cf"
MARKER = "NORHNAIM_SESSION_REDESIGN_V2"
HOST = "video-norhnaim.duckdns.org"
V1_START = "# NORHNAIM_SESSION_REDESIGN_V1_BEGIN"
V1_END = "# NORHNAIM_SESSION_REDESIGN_V1_END"
V2_START = "# NORHNAIM_SESSION_REDESIGN_V2_BEGIN"
V2_END = "# NORHNAIM_SESSION_REDESIGN_V2_END"
REDIRECT = (
    "    " + V2_START + "\n"
    "    # Redirect old external /demo links to /session.\n"
    "    location = /demo {\n"
    "        return 302 /session$is_args$args;\n"
    "    }\n"
    "    " + V2_END + "\n"
)


def fail(message: str) -> None:
    raise RuntimeError(message)


def index_next(source: str, payload: str) -> str:
    if MARKER in source:
        if source != payload:
            fail("index.html уже содержит изменённый V2: ручные правки не перезаписываем")
        return source
    actual = hashlib.sha256(source.encode("utf-8")).hexdigest()
    if actual != ORIGINAL_SHA256:
        fail(
            "Локальный telemedicine/signaling/index.html отличается от присланного исходника. "
            "Чтобы не потерять изменения, установка остановлена. Пришли актуальный index.html."
        )
    expected_ids = (
        'id="remoteVideo"', 'id="localVideo"',
        'id="remotePlaceholder"', 'id="localPlaceholder"',
        'id="startButton"', 'id="muteButton"',
        'id="cameraButton"', 'id="endButton"',
        'id="statusDot"', 'id="statusText"',
        'id="roleText"', 'id="channelText"',
    )
    missing = [name for name in expected_ids if name not in payload]
    if missing:
        fail("В шаблоне ZIP отсутствуют ID, нужные app.js: " + ", ".join(missing))
    return payload


def append_patch(source: str, patch: str, marker: str, title: str) -> str:
    begin = marker + "_BEGIN"
    end = marker + "_END"
    if begin in source:
        if source.count(begin) != 1 or source.count(end) != 1:
            fail(f"Неполная вставка в {title}: требуется ручная проверка")
        return source
    if begin not in patch or end not in patch:
        fail(f"Повреждён файл патча {title}")
    return source.rstrip("\n") + "\n\n" + patch


def server_next(source: str) -> str:
    if re.search(r"if\s+path\s+in\s*\([^\n]*['\"]/session['\"][^\n]*\)\s*:", source):
        return source
    pattern = r"(?m)^([ \t]*)if[ \t]+path[ \t]+in[ \t]*\([ \t]*['\"]/['\"][ \t]*,[ \t]*['\"]/demo['\"][ \t]*\)[ \t]*:"
    matches = list(re.finditer(pattern, source))
    if len(matches) != 1:
        fail("Не найден ожидаемый обработчик '/' и '/demo' в server.py. Файл не меняли.")
    m = matches[0]
    replacement = m.group(1) + "if path in ('/', '/demo', '/session'):"
    return source[:m.start()] + replacement + source[m.end():]


def closing_brace(text: str, start: int) -> int:
    opening = text.find("{", start)
    if opening < 0:
        fail("В nginx.conf не найдена открывающая фигурная скобка")
    depth = 0
    quote = None
    escape = False
    comment = False
    for pos in range(opening, len(text)):
        c = text[pos]
        if comment:
            if c == "\n":
                comment = False
            continue
        if quote:
            if escape:
                escape = False
            elif c == "\\":
                escape = True
            elif c == quote:
                quote = None
            continue
        if c == "#":
            comment = True
        elif c in ("'", '"'):
            quote = c
        elif c == "{":
            depth += 1
        elif c == "}":
            depth -= 1
            if depth == 0:
                return pos
    fail("В nginx.conf не закрыта фигурная скобка")


def nginx_next(source: str) -> str:
    servers = []
    for m in re.finditer(r"(?m)^[ \t]*server[ \t]*\{", source):
        end = closing_brace(source, m.start())
        body = source[m.start():end]
        if re.search(r"(?m)^[ \t]*server_name[ \t]+[^;\n]*\b" + re.escape(HOST) + r"\b[^;\n]*;", body):
            servers.append((m.start(), end, body))
    if len(servers) != 1:
        fail(f"Ожидается один HTTPS server_name {HOST} в nginx.conf; найдено {len(servers)}")
    start, end, body = servers[0]
    if V2_START in body:
        if V2_END not in body:
            fail("В nginx.conf обнаружен неполный V2-блок")
        return source
    if V1_START in body:
        if V1_END not in body:
            fail("В nginx.conf обнаружен неполный V1-блок")
        section = re.compile(re.escape(V1_START) + r"[\s\S]*?" + re.escape(V1_END))
        found = list(section.finditer(body))
        if len(found) != 1:
            fail("Количество старых блоков V1 не равно одному")
        old = found[0]
        updated = body[:old.start()] + REDIRECT.strip() + body[old.end():]
        return source[:start] + updated + source[end:]
    if re.search(r"(?m)^[ \t]*location[ \t]+=[ \t]*/demo(?:[ \t]|\{)", body):
        fail("Публичный nginx уже содержит пользовательский location = /demo — нужно посмотреть nginx.conf")
    if not re.search(r"(?m)^[ \t]*location[ \t]+/[ \t]*\{", body):
        fail("В публичном nginx не найден обычный location /; маршрут /session не проверен")
    return source[:end] + "\n" + REDIRECT + source[end:]


def proposed() -> tuple[dict[Path, str], dict[Path, bool]]:
    for p in (DEST, CSS, JS, SERVER, NGINX, PAYLOAD, CSS_PATCH, JS_PATCH):
        if not p.is_file():
            fail(f"Отсутствует необходимый файл: {p}")
    src = {p: p.read_text(encoding="utf-8") for p in (DEST, CSS, JS, SERVER, NGINX, PAYLOAD, CSS_PATCH, JS_PATCH)}
    if not all(token in src[JS] for token in ("remoteVideo", "localVideo", "muteButton", "cameraButton", "startButton")):
        fail("app.js не похож на ожидаемый клиент видеозвонка. Установка остановлена.")
    result = {
        DEST: index_next(src[DEST], src[PAYLOAD]),
        CSS: append_patch(src[CSS], src[CSS_PATCH], MARKER, "style.css"),
        JS: append_patch(src[JS], src[JS_PATCH], "// " + MARKER, "app.js"),
        SERVER: server_next(src[SERVER]),
        NGINX: nginx_next(src[NGINX]),
    }
    return result, {p: result[p] != src[p] for p in result}


def atomic_write(path: Path, data: str) -> None:
    temp = path.with_name(path.name + ".session_v2.tmp")
    if temp.exists():
        fail("Уже существует временный файл " + str(temp))
    try:
        temp.write_text(data, encoding="utf-8")
        os.chmod(temp, stat.S_IMODE(path.stat().st_mode))
        temp.replace(path)
    finally:
        if temp.exists():
            temp.unlink()


def apply() -> None:
    updates, changed = proposed()
    if not any(changed.values()):
        print("Уже установлено: изменений не требуется.")
        return
    stamp = datetime.now(timezone.utc).strftime("%Y%m%dT%H%M%SZ")
    backup = BACKUPS / f"session-redesign-v2-{stamp}"
    backup.mkdir(parents=True, exist_ok=False)
    for file in updates:
        shutil.copy2(file, backup / file.name)
    try:
        for file, contents in updates.items():
            if changed[file]:
                atomic_write(file, contents)
    except Exception:
        for file in updates:
            shutil.copy2(backup / file.name, file)
        raise
    print("Готово: index.html — два равных видеоокна, компактные кнопки слева")
    print("Готово: Python принимает /session, старый /demo оставлен для совместимости")
    print("Готово: публичный nginx перенаправляет /demo на /session")
    print("WebRTC app.js дополнен только оформлением; логика звонка сохранена")
    print("TURN, сертификаты и секреты НЕ изменены")
    print("Резервная копия:", backup)
    print("Теперь проверь Compose и nginx -t, затем пересоздай signaling и HTTPS")


def rollback(backup: Path) -> None:
    for name in ("index.html", "style.css", "app.js", "server.py", "nginx.conf"):
        if not (backup / name).is_file():
            fail("В резервной копии отсутствует: " + str(backup / name))
    for name, path in (("index.html", DEST), ("style.css", CSS), ("app.js", JS), ("server.py", SERVER), ("nginx.conf", NGINX)):
        shutil.copy2(backup / name, path)
    print("Восстановлено из:", backup)
    print("Далее проверь nginx -t и пересоздай signaling/HTTPS")


def main() -> None:
    parser = argparse.ArgumentParser(description="NORHNAIM: WebRTC /session redesign v2")
    mode = parser.add_mutually_exclusive_group(required=True)
    mode.add_argument("--check", action="store_true")
    mode.add_argument("--apply", action="store_true")
    mode.add_argument("--rollback", type=Path, metavar="BACKUP_PATH")
    args = parser.parse_args()
    try:
        if args.rollback:
            rollback(args.rollback)
        elif args.check:
            _, changes = proposed()
            print("Проверка успешна.")
            for file, needed in changes.items():
                print(file.relative_to(ROOT), "—", "будет обновлён" if needed else "уже актуален")
        else:
            apply()
    except (RuntimeError, ValueError, OSError) as exc:
        print("Установка остановлена:", exc, file=sys.stderr)
        sys.exit(1)


if __name__ == "__main__":
    main()
