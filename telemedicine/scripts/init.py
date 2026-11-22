#!/usr/bin/env python3
"""Generate strong secrets for the localhost-only telemedicine demo."""
from pathlib import Path
import secrets
import sys

ROOT = Path(__file__).resolve().parents[2]
DEST = ROOT / ".env.telemedicine"


def main() -> None:
    if DEST.exists():
        print(f"Already exists: {DEST}. Nothing overwritten.")
        return
    values = {
        "TELEMEDICINE_SIGNALING_SECRET": secrets.token_hex(32),
        "TELEMEDICINE_TURN_SECRET": secrets.token_hex(32),
        "TELEMEDICINE_TURN_HOST": "127.0.0.1",
        "TELEMEDICINE_TURN_PORT": "3478",
        "TELEMEDICINE_UI_PORT": "3100",
        "TELEMEDICINE_MAX_ROOMS": "20",
        "TELEMEDICINE_ALLOWED_ORIGINS": "http://localhost:3100,http://127.0.0.1:3100",
    }
    fd = DEST.open("x", encoding="utf-8")
    try:
        fd.write("# Local WebRTC test secrets. Never commit.\n")
        for name, value in values.items():
            fd.write(f"{name}={value}\n")
    finally:
        fd.close()
    DEST.chmod(0o600)
    ignore = ROOT / ".gitignore"
    text = ignore.read_text(encoding="utf-8") if ignore.exists() else ""
    if "/.env.telemedicine" not in text.splitlines():
        with ignore.open("a", encoding="utf-8") as stream:
            if text and not text.endswith("\n"):
                stream.write("\n")
            stream.write("\n# Telemedicine development secrets\n/.env.telemedicine\n")
    print("Created .env.telemedicine (permissions 0600). Git ignore rule added.")
    print("Do not copy these demo secrets to production or a public repository.")


if __name__ == "__main__":
    main()
