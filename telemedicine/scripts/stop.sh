#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")/../.."
[[ -f .env && -f .env.telemedicine ]] || {
  echo 'Missing root Laravel .env or .env.telemedicine' >&2; exit 1;
}
compose=(docker compose --env-file .env --env-file .env.telemedicine -f docker-compose.yml)
if [[ -f docker-compose.telemedicine.mobile.yml ]] && \
   grep -Eq '^TELEMEDICINE_LAN_IP=[0-9]' .env.telemedicine; then
    compose+=(-f docker-compose.telemedicine.mobile.yml)
    compose+=(--profile telemedicine)
    "${compose[@]}" stop telemedicine-https telemedicine-signaling telemedicine-turn
else
    compose+=(--profile telemedicine)
    "${compose[@]}" stop telemedicine-signaling telemedicine-turn
fi
