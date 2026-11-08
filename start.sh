#!/bin/sh
set -eu

PROJECT_DIR="$(CDPATH= cd -- "$(dirname -- "$0")" && pwd)"
cd "$PROJECT_DIR"

ACTION=start
RESTART_VITE=0
NO_SHELL=0
MODE_REQUEST=auto
ENV_FILE=.env.telemedicine
BASE_COMPOSE=docker-compose.yml
MOBILE_COMPOSE=docker-compose.telemedicine.mobile.yml
LABORATORY_COMPOSE=docker-compose.laboratory-reverb.yml

# During startup, the terminal contains only two updating lines:
# 1) the current operation, 2) a progress bar. Detailed output goes to a private log.
LOG_FILE=
WARNINGS=
ERROR_REPORTED=0
UI_DRAWN=0
UI_TTY=0
UI_LAST_PERCENT=
UI_LAST_MESSAGE=
RUN_PID=
[ -t 1 ] && [ "${TERM:-dumb}" != dumb ] && UI_TTY=1

ui_update() {
    UI_PERCENT="$1"
    UI_MESSAGE="$2"

    if [ "$UI_TTY" -ne 1 ]; then
        # Redirection/CI cannot rewrite two terminal lines; print each phase only once.
        if [ "$UI_PERCENT" != "$UI_LAST_PERCENT" ] || [ "$UI_MESSAGE" != "$UI_LAST_MESSAGE" ]; then
            printf '[%3s%%] %s\n' "$UI_PERCENT" "$UI_MESSAGE"
            UI_LAST_PERCENT="$UI_PERCENT"
            UI_LAST_MESSAGE="$UI_MESSAGE"
        fi
        return
    fi

    UI_FILLED=$((UI_PERCENT * 30 / 100))
    UI_BAR=
    UI_I=0
    while [ "$UI_I" -lt 30 ]; do
        if [ "$UI_I" -lt "$UI_FILLED" ]; then
            UI_BAR="${UI_BAR}#"
        else
            UI_BAR="${UI_BAR}."
        fi
        UI_I=$((UI_I + 1))
    done

    # Keep the cursor on the progress line. The next refresh replaces both lines.
    if [ "$UI_DRAWN" -eq 1 ]; then
        printf '\033[1A\r'
    fi
    printf '\033[2K\rЛог: %s\n' "$UI_MESSAGE"
    printf '\033[2K\r[%s] %3s%%' "$UI_BAR" "$UI_PERCENT"
    UI_DRAWN=1
}

ui_finish() {
    if [ "$UI_DRAWN" -eq 1 ]; then
        printf '\n'
        UI_DRAWN=0
    fi
}

fail() {
    ERROR_REPORTED=1
    ui_finish
    printf '\nОШИБКА: %s\n' "$*" >&2
    if [ -n "$LOG_FILE" ] && [ -s "$LOG_FILE" ]; then
        printf '\nПоследние строки подробного лога:\n' >&2
        tail -n 25 "$LOG_FILE" >&2 || true
        printf '\nПолный лог: %s\n' "$LOG_FILE" >&2
    fi
    exit 1
}

warn() {
    [ -z "$LOG_FILE" ] || printf 'WARNING: %s\n' "$*" >> "$LOG_FILE"
    if [ -z "$WARNINGS" ]; then
        WARNINGS="$*"
    else
        WARNINGS="$WARNINGS
$*"
    fi
}

cleanup() {
    CLEANUP_CODE=$?
    ui_finish
    if [ "$CLEANUP_CODE" -eq 0 ]; then
        [ -z "$LOG_FILE" ] || rm -f -- "$LOG_FILE"
    elif [ "$ERROR_REPORTED" -eq 0 ] && [ -n "$LOG_FILE" ]; then
        printf '\nЗапуск прерван. Подробный лог: %s\n' "$LOG_FILE" >&2
    fi
}
trap cleanup 0
trap 'if [ -n "$RUN_PID" ]; then kill "$RUN_PID" 2>/dev/null || true; fi; exit 130' INT
trap 'if [ -n "$RUN_PID" ]; then kill "$RUN_PID" 2>/dev/null || true; fi; exit 143' TERM

# Execute a command without printing its Docker/script output to the terminal.
# While the command is running, animate only the current log line (TTY mode).
run_step() {
    STEP_ERROR="$1"
    STEP_PERCENT="$2"
    STEP_MESSAGE="$3"
    shift 3

    ui_update "$STEP_PERCENT" "$STEP_MESSAGE"
    if [ "$UI_TTY" -eq 1 ]; then
        "$@" </dev/null >> "$LOG_FILE" 2>&1 &
        RUN_PID=$!
        STEP_SPIN=0
        while kill -0 "$RUN_PID" 2>/dev/null; do
            case "$STEP_SPIN" in
                0) STEP_SPIN_CHAR='|' ;;
                1) STEP_SPIN_CHAR='/' ;;
                2) STEP_SPIN_CHAR='-' ;;
                3) STEP_SPIN_CHAR='\' ;;
            esac
            ui_update "$STEP_PERCENT" "$STEP_MESSAGE  $STEP_SPIN_CHAR"
            STEP_SPIN=$(((STEP_SPIN + 1) % 4))
            sleep 0.3
        done
        if wait "$RUN_PID"; then
            RUN_PID=
        else
            RUN_PID=
            fail "$STEP_ERROR"
        fi
    else
        if "$@" </dev/null >> "$LOG_FILE" 2>&1; then
            :
        else
            fail "$STEP_ERROR"
        fi
    fi
    ui_update "$STEP_PERCENT" "$STEP_MESSAGE"
}

usage() {
cat <<'HELP'
NORHNAIM launcher
  ./start.sh                     Start Laravel, WebRTC, lab Reverb/worker if configured
  ./start.sh --mobile            Force local Wi-Fi HTTPS mode
  ./start.sh --basic             Start localhost-only WebRTC (no HTTPS proxy)
  ./start.sh --restart           Stop the project and start all again
  ./start.sh --vite              Restart Vite
  ./start.sh --down              Stop all project services, leave volumes intact
  ./start.sh --no-shell          Do not open a shell in the Laravel container
  ./start.sh --help

To change LAN IP: python3 telemedicine/scripts/configure_mobile.py --ip 192.168.1.50
                  or TELEMEDICINE_LAN_IP=192.168.1.50 ./start.sh --mobile
HELP
}

for arg in "$@"; do
    case "$arg" in
        --mobile) MODE_REQUEST=mobile ;;
        --basic) MODE_REQUEST=basic ;;
        --vite|-v) RESTART_VITE=1 ;;
        --restart|-r) ACTION=restart ;;
        --down|--stop) ACTION=down ;;
        --no-shell) NO_SHELL=1 ;;
        --help|-h) usage; exit 0 ;;
        *) fail "Unknown argument: $arg" ;;
    esac
done

LOG_FILE="$(mktemp "${TMPDIR:-/tmp}/norhnaim-start.XXXXXXXX")" || fail 'Could not create temporary log.'
chmod 600 "$LOG_FILE" || fail 'Could not secure temporary log.'
ui_update 3 'Проверка окружения'

command -v docker >/dev/null 2>&1 || fail 'Docker not found.'
docker compose version >> "$LOG_FILE" 2>&1 || fail 'Docker Compose v2 required.'
command -v python3 >/dev/null 2>&1 || fail 'python3 required.'
[ -f "$BASE_COMPOSE" ] || fail "$BASE_COMPOSE is missing."
[ -f .env ] || fail 'Laravel .env is missing.'

# Optional laboratory extension. Include it in the SAME Compose project.
LABORATORY_ENABLED=0
if [ -f "$LABORATORY_COMPOSE" ]; then
    LABORATORY_ENABLED=1
elif [ "$ACTION" != down ]; then
    warn "$LABORATORY_COMPOSE is missing: laboratory WebSocket and worker will not start."
fi

get_env_value() {
    [ -f "$ENV_FILE" ] || return 0
    sed -n "s/^${1}=//p" "$ENV_FILE" | tail -n 1
}

# Do not create credentials on --down.
if [ ! -f "$ENV_FILE" ] && [ "$ACTION" != down ]; then
    [ -f telemedicine/scripts/init.py ] || fail 'Missing telemedicine/scripts/init.py.'
    run_step 'Could not initialize telemedicine credentials.' 8 'Создание конфигурации WebRTC' \
        python3 telemedicine/scripts/init.py
fi
[ -f "$ENV_FILE" ] || fail "Missing $ENV_FILE."

LAN_IP="${TELEMEDICINE_LAN_IP:-$(get_env_value TELEMEDICINE_LAN_IP)}"
if [ -z "$LAN_IP" ] && command -v ip >/dev/null 2>&1; then
    LAN_IP="$(ip -4 route get 1.1.1.1 2>/dev/null | awk '{for(i=1;i<=NF;i++) if($i=="src") {print $(i+1); exit}}' || true)"
fi

MOBILE=0
if [ "$MODE_REQUEST" = mobile ]; then
    MOBILE=1
elif [ "$MODE_REQUEST" = auto ] && [ -f "$MOBILE_COMPOSE" ] && [ -n "$(get_env_value TELEMEDICINE_LAN_IP)" ]; then
    MOBILE=1
fi

if [ "$MOBILE" -eq 1 ]; then
    [ -f "$MOBILE_COMPOSE" ] || fail "Missing $MOBILE_COMPOSE."
    [ -n "$LAN_IP" ] || fail 'Missing LAN IP (TELEMEDICINE_LAN_IP).'
    [ -f telemedicine/scripts/configure_mobile.py ] || fail 'Missing configure_mobile.py.'
    [ -f telemedicine/scripts/create_mobile_certs.sh ] || fail 'Missing create_mobile_certs.sh.'
    if [ "$ACTION" != down ]; then
        # Update LAN values only; preserve both existing signing secrets.
        HTTPS_PORT="$(get_env_value TELEMEDICINE_HTTPS_PORT)"
        HTTPS_PORT="${HTTPS_PORT:-3443}"
        run_step 'Could not configure mobile HTTPS.' 12 'Настройка мобильного HTTPS' \
            python3 telemedicine/scripts/configure_mobile.py --ip "$LAN_IP" --https-port "$HTTPS_PORT"
        run_step 'Could not create mobile TLS certificates.' 18 'Проверка сертификатов HTTPS' \
            bash telemedicine/scripts/create_mobile_certs.sh "$LAN_IP"
    fi
fi

# Include laboratory overlay for start AND shutdown (do not remove its workers as orphans).
# File order: base, laboratory, optional mobile.
dc_base() {
    if [ "$LABORATORY_ENABLED" -eq 1 ]; then
        docker compose --env-file .env --env-file "$ENV_FILE" \
            -f "$BASE_COMPOSE" -f "$LABORATORY_COMPOSE" "$@"
    else
        docker compose --env-file .env --env-file "$ENV_FILE" \
            -f "$BASE_COMPOSE" "$@"
    fi
}
dc_all() {
    if [ "$MOBILE" -eq 1 ]; then
        if [ "$LABORATORY_ENABLED" -eq 1 ]; then
            docker compose --env-file .env --env-file "$ENV_FILE" \
                -f "$BASE_COMPOSE" -f "$LABORATORY_COMPOSE" -f "$MOBILE_COMPOSE" "$@"
        else
            docker compose --env-file .env --env-file "$ENV_FILE" \
                -f "$BASE_COMPOSE" -f "$MOBILE_COMPOSE" "$@"
        fi
    else
        dc_base "$@"
    fi
}

run_step 'Invalid Compose configuration.' 23 'Проверка Docker Compose' \
    dc_all --profile telemedicine config

if [ "$ACTION" = down ] || [ "$ACTION" = restart ]; then
    run_step 'Could not stop project services.' 30 'Остановка контейнеров (тома сохраняются)' \
        dc_all --profile telemedicine down --remove-orphans
    if [ "$ACTION" = down ]; then
        ui_update 100 'Все сервисы остановлены'
        ui_finish
        printf '\nNORHNAIM остановлен. Тома Docker сохранены.\n'
        exit 0
    fi
fi

[ -f telemedicine/scripts/start.sh ] || fail 'Missing telemedicine/scripts/start.sh.'
[ -n "$(get_env_value TELEMEDICINE_SIGNALING_SECRET)" ] || fail 'Signaling secret is empty.'
[ -n "$(get_env_value TELEMEDICINE_TURN_SECRET)" ] || fail 'TURN secret is empty.'

if [ "$LABORATORY_ENABLED" -eq 1 ]; then
    BASE_MESSAGE='Запуск Laravel, БД, Redis, RabbitMQ, Ollama и лаборатории'
else
    BASE_MESSAGE='Запуск Laravel, БД, Redis, RabbitMQ и Ollama'
fi
run_step 'Could not start main Docker services.' 38 "$BASE_MESSAGE" dc_base up -d

if [ "$MOBILE" -eq 1 ]; then
    run_step 'Could not start WebRTC services.' 55 'Запуск WebRTC (мобильный HTTPS)' \
        env TELEMEDICINE_MODE=mobile bash telemedicine/scripts/start.sh
else
    run_step 'Could not start WebRTC services.' 55 'Запуск WebRTC (локальный режим)' \
        env TELEMEDICINE_MODE=basic bash telemedicine/scripts/start.sh
fi

# The telemedicine launcher may use its own Compose files. Restore lab services afterwards.
if [ "$LABORATORY_ENABLED" -eq 1 ]; then
    run_step 'Could not start laboratory services.' 64 'Запуск Reverb и лабораторного worker' \
        dc_base up -d reverb laboratory-broadcast-worker
fi

get_status() {
    SERVICE="$1"
    CONTAINER_ID="$(dc_all --profile telemedicine ps -q "$SERVICE" 2>/dev/null || true)"
    [ -n "$CONTAINER_ID" ] || return 1
    docker inspect -f '{{if .State.Health}}{{.State.Health.Status}}{{else}}{{.State.Status}}{{end}}' \
        "$CONTAINER_ID" 2>/dev/null || true
}

wait_service() {
    SERVICE="$1"
    EXPECTED="$2"
    COUNT="$3"
    PERCENT="$4"
    UI_NAME="$5"
    ORIGINAL_COUNT="$COUNT"

    ui_update "$PERCENT" "Ожидание: $UI_NAME"
    while [ "$COUNT" -gt 0 ]; do
        STATUS="$(get_status "$SERVICE" || true)"
        if [ "$STATUS" = "$EXPECTED" ]; then
            ui_update "$PERCENT" "Готово: $UI_NAME"
            return 0
        fi
        COUNT=$((COUNT - 1))
        if [ "$UI_TTY" -eq 1 ]; then
            ui_update "$PERCENT" "Ожидание: $UI_NAME (${STATUS:-не создан}, $((ORIGINAL_COUNT - COUNT)) с)"
        fi
        sleep 1
    done
    dc_all --profile telemedicine logs --tail=80 "$SERVICE" >> "$LOG_FILE" 2>&1 || true
    fail "$SERVICE failed to become $EXPECTED (last status: $STATUS)."
}

wait_service telemedicine-signaling healthy 45 70 'WebRTC signaling'
wait_service telemedicine-turn running 20 75 'TURN'
if [ "$MOBILE" -eq 1 ]; then
    wait_service telemedicine-https healthy 30 80 'HTTPS proxy'
fi
if [ "$LABORATORY_ENABLED" -eq 1 ]; then
    # No Docker HEALTHCHECK on these two services; expect the running state.
    wait_service reverb running 30 84 'Reverb'
    wait_service laboratory-broadcast-worker running 30 88 'Лабораторный worker'
fi

# No room or invitation is created on startup.
# Appointment-specific sessions and signed links must be generated separately.
ui_update 90 'WebRTC готов к работе'

vite_running() {
    dc_base exec -T laravel.test sh -lc \
        'ps -eo args 2>/dev/null | grep -E "[v]ite|[n]pm run dev" >/dev/null 2>&1'
}
stop_vite() {
    dc_base exec -T laravel.test sh -lc '
        if command -v pkill >/dev/null 2>&1; then
            pkill -f "[v]ite" 2>/dev/null || true
            pkill -f "[n]pm run dev" 2>/dev/null || true
        fi' || true
}
start_vite() { dc_base exec -d laravel.test npm run dev; }

if [ "$RESTART_VITE" -eq 1 ]; then
    run_step 'Could not stop Vite.' 93 'Остановка Vite' stop_vite
    sleep 1
    run_step 'Could not restart Vite.' 97 'Перезапуск Vite' start_vite
elif vite_running >> "$LOG_FILE" 2>&1; then
    ui_update 97 'Vite уже запущен'
else
    run_step 'Could not start Vite.' 97 'Запуск Vite' start_vite
fi

# Save optional container listings to the private log instead of dumping tables to the UI.
dc_all --profile telemedicine ps telemedicine-signaling telemedicine-turn >> "$LOG_FILE" 2>&1 || true
if [ "$MOBILE" -eq 1 ]; then
    dc_all --profile telemedicine ps telemedicine-https >> "$LOG_FILE" 2>&1 || true
fi
if [ "$LABORATORY_ENABLED" -eq 1 ]; then
    dc_base ps reverb laboratory-broadcast-worker >> "$LOG_FILE" 2>&1 || true
fi

ui_update 100 'NORHNAIM успешно запущен'
ui_finish

printf '\n================ NORHNAIM WebRTC ================\n'
printf 'Laravel:          http://localhost:%s\n' "${APP_PORT:-8080}"
printf 'WebRTC:           готов, видеосеансы при запуске не создаются\n'
if [ "$MOBILE" -eq 1 ]; then
    printf 'Phone TLS root CA: telemedicine/certs/rootCA.crt\n'
    printf 'Для телефона: доверять root CA и подключиться к той же Wi-Fi сети.\n'
else
    warn 'Basic HTTP mode: phone camera/mic will be blocked on LAN IP.'
fi
if [ "$LABORATORY_ENABLED" -eq 1 ]; then
    REVERB_LOCAL_PORT="${REVERB_PUBLIC_PORT:-$(sed -n 's/^REVERB_PUBLIC_PORT=//p' .env | tail -n 1)}"
    REVERB_LOCAL_PORT="${REVERB_LOCAL_PORT:-8081}"
    printf 'Reverb:           ws://localhost:%s\n' "$REVERB_LOCAL_PORT"
fi
printf 'Ссылки пациенту и врачу должны создаваться отдельно для каждого приёма.\n'
printf '==================================================\n'
if [ -n "$WARNINGS" ]; then
    printf '\nПредупреждения:\n%s\n' "$WARNINGS"
fi
printf '\n'

# Keep the original default behavior: enter the Laravel container after startup.
if [ "$NO_SHELL" -eq 0 ]; then
    dc_base exec laravel.test bash
fi
