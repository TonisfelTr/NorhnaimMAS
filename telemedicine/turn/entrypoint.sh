#!/bin/sh
set -eu

SECRET="${TELEMEDICINE_TURN_SECRET:-}"
TURN_PORT="${TELEMEDICINE_TURN_PORT:-3478}"
TURN_HOST="${TELEMEDICINE_TURN_HOST:-127.0.0.1}"

if [ "${#SECRET}" -lt 32 ]; then
    echo "[telemedicine-turn] Missing or too-short secret. Run python3 telemedicine/scripts/init.py" >&2
    exit 1
fi

case "$TURN_PORT" in
    ''|*[!0-9]*)
        echo "[telemedicine-turn] Invalid TELEMEDICINE_TURN_PORT" >&2
        exit 1
        ;;
esac

if [ "$TURN_PORT" -lt 1 ] || [ "$TURN_PORT" -gt 65535 ]; then
    echo "[telemedicine-turn] Invalid port" >&2
    exit 1
fi

if [ -z "$TURN_HOST" ]; then
    echo "[telemedicine-turn] TELEMEDICINE_TURN_HOST is empty" >&2
    exit 1
fi

echo "[telemedicine-turn] Starting TURN on ${TURN_HOST}:${TURN_PORT}"

exec /usr/bin/turnserver \
    -n \
    --log-file=stdout \
    --simple-log \
    --fingerprint \
    --use-auth-secret \
    "--static-auth-secret=${SECRET}" \
    --realm=norhnaim.local \
    "--listening-port=${TURN_PORT}" \
    "--listening-ip=${TURN_HOST}" \
    "--relay-ip=${TURN_HOST}" \
    --min-port=49160 \
    --max-port=49359 \
    --no-tls \
    --no-multicast-peers \
    --user-quota=4 \
    --total-quota=160
