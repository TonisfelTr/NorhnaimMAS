#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")/../.."
if [[ ! -f .env.telemedicine ]]; then
  echo 'Файл .env.telemedicine не найден' >&2
  exit 1
fi
docker compose -f docker-compose.yml -f docker-compose.telemedicine.mobile.yml \
  --env-file .env --env-file .env.telemedicine --profile telemedicine \
  stop telemedicine-https telemedicine-signaling telemedicine-turn
