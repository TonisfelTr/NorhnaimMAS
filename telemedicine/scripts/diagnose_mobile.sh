#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")/../.."
[[ -f .env && -f .env.telemedicine && -f docker-compose.telemedicine.mobile.yml ]] || {
  echo 'Missing .env, .env.telemedicine, or mobile Compose override.' >&2
  exit 1
}
get_env() {
  sed -n "s/^${1}=//p" .env.telemedicine | tail -n 1
}
IP="$(get_env TELEMEDICINE_LAN_IP)"
HTTPS_PORT="$(get_env TELEMEDICINE_HTTPS_PORT)"
HTTPS_PORT="${HTTPS_PORT:-3443}"
[[ -n "$IP" ]] || { echo 'Missing TELEMEDICINE_LAN_IP' >&2; exit 1; }
compose=(docker compose --env-file .env --env-file .env.telemedicine -f docker-compose.yml -f docker-compose.telemedicine.mobile.yml --profile telemedicine)
printf '\nNORHNAIM mobile WebRTC diagnostics (no credentials shown)\n'
printf 'Target: https://%s:%s/health\n\n' "$IP" "$HTTPS_PORT"
"${compose[@]}" ps telemedicine-signaling telemedicine-turn telemedicine-https
if [[ -f telemedicine/certs/rootCA.crt && -f telemedicine/certs/mobile.crt ]]; then
  printf '\nTLS certificate IP check: '
  openssl x509 -in telemedicine/certs/mobile.crt -noout -checkip "$IP" || true
fi
if command -v curl >/dev/null 2>&1; then
  printf '\nLocal HTTPS /health (using trusted dev CA):\n'
  curl --silent --show-error --max-time 8 --cacert telemedicine/certs/rootCA.crt \
       --resolve "$IP:$HTTPS_PORT:127.0.0.1" \
       "https://$IP:$HTTPS_PORT/health" || true
  printf '\n'
fi
if command -v ss >/dev/null 2>&1; then
  printf '\nListeners on WSL host (absence can depend on Docker networking):\n'
  ss -lntu | grep -E '(:3100|:3443|:3478|:491[6-9][0-9]|:492[0-5][0-9])([[:space:]]|$)' || true
fi
printf '\nRecent sanitized SERVICE logs (may contain client IPs):\n'
"${compose[@]}" logs --tail=20 telemedicine-https telemedicine-signaling telemedicine-turn || true
