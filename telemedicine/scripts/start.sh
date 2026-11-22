#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")/../.."
[[ -f .env && -f .env.telemedicine ]] || {
  echo 'Missing root Laravel .env or .env.telemedicine' >&2; exit 1;
}

MODE="${TELEMEDICINE_MODE:-auto}"
if [[ "$MODE" == auto ]]; then
  if [[ -f docker-compose.telemedicine.mobile.yml ]] && \
     grep -Eq '^TELEMEDICINE_LAN_IP=[0-9]' .env.telemedicine; then
    MODE=mobile
  else
    MODE=basic
  fi
fi

compose=(docker compose --env-file .env --env-file .env.telemedicine -f docker-compose.yml)
if [[ "$MODE" == mobile ]]; then
  [[ -f docker-compose.telemedicine.mobile.yml ]] || {
    echo 'Missing docker-compose.telemedicine.mobile.yml' >&2; exit 1;
  }
  [[ -f telemedicine/certs/mobile.crt && -f telemedicine/certs/mobile.key ]] || {
    echo 'Missing TLS certificate; run the root ./start.sh.old --mobile first' >&2; exit 1;
  }
  compose+=(-f docker-compose.telemedicine.mobile.yml)
fi
compose+=(--profile telemedicine)
"${compose[@]}" up -d --no-deps telemedicine-signaling telemedicine-turn
if [[ "$MODE" == mobile ]]; then
  # Compose waits for signaling to become healthy before starting nginx.
  "${compose[@]}" up -d telemedicine-https
fi
"${compose[@]}" ps telemedicine-signaling telemedicine-turn
if [[ "$MODE" == mobile ]]; then
  "${compose[@]}" ps telemedicine-https
fi
