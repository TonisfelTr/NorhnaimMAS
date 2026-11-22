#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")/../.."
if [[ ! -f .env.telemedicine || ! -s telemedicine/certs/server.crt ]]; then
  echo 'Сначала запусти python3 telemedicine/scripts/init.py (если ещё не запускал), затем configure_mobile.py --ip LAN_IP' >&2
  exit 1
fi
# coturn in host-network mode must bind a real IP address of WSL.
LAN_IP="$(sed -n 's/^TELEMEDICINE_LAN_IP=//p' .env.telemedicine | tail -1)"
if [[ -z "$LAN_IP" ]]; then
  echo 'Не настроен TELEMEDICINE_LAN_IP; запусти configure_mobile.py --ip LAN_IP' >&2
  exit 1
fi
if command -v ip >/dev/null 2>&1; then
  if ! ip -o -4 addr show | awk '{split($4,a,"/"); print a[1]}' | grep -Fxq "$LAN_IP"; then
    echo "LAN IP $LAN_IP не найден на интерфейсах WSL." >&2
    echo 'Проверь ip -4 addr, сетевой режим WSL mirrored и IP из ipconfig Windows.' >&2
    echo 'В текущей конфигурации coturn network_mode: host не сможет привязаться к чужому IP.' >&2
    exit 1
  fi
fi
C=(docker compose -f docker-compose.yml -f docker-compose.telemedicine.mobile.yml --env-file .env --env-file .env.telemedicine --profile telemedicine)
"${C[@]}" config --quiet
# Do not affect Laravel/pgsql/redis/rabbitmq/ollama.
"${C[@]}" up -d --no-deps telemedicine-signaling telemedicine-turn
"${C[@]}" up -d --no-deps telemedicine-https
"${C[@]}" ps telemedicine-signaling telemedicine-turn telemedicine-https
