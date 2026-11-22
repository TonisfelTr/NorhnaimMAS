#!/usr/bin/env python3
"""Print two different signed local demo links: doctor and patient."""
import argparse
from pathlib import Path
import secrets
import sys
from urllib.parse import urlencode

ROOT = Path(__file__).resolve().parents[2]
sys.path.insert(0, str(ROOT / "telemedicine" / "signaling"))
from tokens import issue  # noqa: E402


def load_config() -> dict[str, str]:
    path = ROOT / ".env.telemedicine"
    if not path.exists():
        raise SystemExit("Run python3 telemedicine/scripts/init.py first")
    values = {}
    for line in path.read_text(encoding="utf-8").splitlines():
        if not line or line.lstrip().startswith("#") or "=" not in line:
            continue
        key, value = line.split("=", 1)
        values[key] = value
    return values


def main() -> None:
    parser = argparse.ArgumentParser(description="Create invitation links for one LOCAL demo room")
    parser.add_argument("--ttl-minutes", type=int, default=90, help="Invitation lifetime, 1-240 min (default 90)")
    args = parser.parse_args()
    if not 1 <= args.ttl_minutes <= 240:
        parser.error("Lifetime must be 1-240 min")
    config = load_config()
    secret = bytes.fromhex(config["TELEMEDICINE_SIGNALING_SECRET"])
    room = secrets.token_urlsafe(20)
    port = int(config.get("TELEMEDICINE_UI_PORT", "3100"))
    base = "https://video-norhnaim.duckdns.org/session"
    print("=== NORHNAIM WebRTC: ТЕСТОВАЯ КОНСУЛЬТАЦИЯ ===")
    for role, label in (("doctor", "ВРАЧ"), ("patient", "ПАЦИЕНТ")):
        token = issue(secret, room, role, ttl=args.ttl_minutes * 60)
        print(f"\n{label}:\n{base}?{urlencode({'token': token})}")
    print("\nСсылки содержат ключ доступа. Не публикуйте их и не используйте реальные данные пациентов.")


if __name__ == "__main__":
    main()
