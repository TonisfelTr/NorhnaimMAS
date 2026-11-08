import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

// This JS bundle is loaded only on /laboratory, not in the physician panel.
const root = document.getElementById('lab-realtime');

if (root) {
    const clinicId = Number(root.dataset.clinicId || 0);
    const token = root.dataset.csrfToken || '';
    const authUrl = root.dataset.channelAuthUrl || '';
    const fragmentUrl = root.dataset.fragmentUrl || '';
    const list = document.getElementById('lab-researches-list');
    const status = document.getElementById('lab-realtime-status');
    const notice = document.getElementById('lab-new-research-notice');
    const refreshButton = document.getElementById('lab-refresh-now');

    let inFlight = false;
    let needsRefresh = false;
    let refreshTimer = null;
    let refreshAfterHidden = false;

    function showConnection(message, style = 'secondary') {
        if (!status) return;
        status.textContent = message;
        status.className = `badge rounded-pill text-bg-${style}`;
    }

    async function refreshList() {
        if (!fragmentUrl || !list) return;
        if (inFlight) {
            needsRefresh = true;
            return;
        }
        if (document.visibilityState === 'hidden') {
            refreshAfterHidden = true;
            return;
        }

        inFlight = true;
        refreshAfterHidden = false;
        try {
            const url = new URL(fragmentUrl, window.location.origin);
            // Keep the user's search, status and pagination filters.
            url.search = window.location.search;

            const response = await fetch(url.toString(), {
                method: 'GET',
                credentials: 'same-origin',
                cache: 'no-store',
                headers: {
                    'Accept': 'text/html',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
            if (response.status === 401 || response.status === 403 || response.redirected) {
                showConnection('Войдите повторно', 'danger');
                return;
            }
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            const html = await response.text();
            // All HTML comes from the authenticated Laravel Blade fragment.
            list.innerHTML = html;
            list.setAttribute('data-last-update', new Date().toISOString());
        } catch (error) {
            showConnection('Не удалось обновить', 'warning');
            console.error('[Norhnaim Laboratory] List refresh failed', error);
        } finally {
            inFlight = false;
            if (needsRefresh) {
                needsRefresh = false;
                queueRefresh();
            }
        }
    }

    function queueRefresh() {
        clearTimeout(refreshTimer);
        refreshTimer = setTimeout(refreshList, 250);
    }

    refreshButton?.addEventListener('click', refreshList);
    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible' && refreshAfterHidden) {
            queueRefresh();
        }
    });

    const key = import.meta.env.VITE_REVERB_APP_KEY;
    const host = import.meta.env.VITE_REVERB_HOST || window.location.hostname;
    const scheme = import.meta.env.VITE_REVERB_SCHEME || window.location.protocol.replace(':', '');
    const useTls = scheme === 'https';
    const port = Number(import.meta.env.VITE_REVERB_PORT || (useTls ? 443 : 8081));

    if (!clinicId || !key || !Number.isInteger(port) || port < 1 || port > 65535 || !token || !authUrl) {
        showConnection('WebSocket не настроен', 'warning');
    } else {
        // Browser keys are public identifiers. The Reverb secret remains server-side.
        window.Pusher = Pusher;
        const echo = new Echo({
            broadcaster: 'reverb',
            key,
            wsHost: host,
            wsPort: port,
            wssPort: port,
            forceTLS: useTls,
            enabledTransports: useTls ? ['wss'] : ['ws'],
            authEndpoint: authUrl,
            // Pusher JS v8+ prefers channelAuthorization; authEndpoint/auth
            // are retained for Laravel Echo compatibility.
            channelAuthorization: {
                endpoint: authUrl,
                transport: 'ajax',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'X-Requested-With': 'XMLHttpRequest',
                },
            },
            auth: {
                headers: {
                    'X-CSRF-TOKEN': token,
                    'X-Requested-With': 'XMLHttpRequest',
                },
            },
        });

        echo.private(`laboratory.clinic.${clinicId}`)
            .listen('.laboratory.research.changed', (event) => {
                if (event?.change === 'created' && notice) {
                    const current = new URLSearchParams(window.location.search);
                    if ((current.get('q') || '') !== '' || (current.get('status') || '') !== '' || Number(current.get('page') || 1) > 1) {
                        notice.classList.remove('d-none');
                    }
                }
                queueRefresh();
            });

        const socket = echo.connector?.pusher?.connection;
        socket?.bind('connected', () => {
            showConnection('В реальном времени', 'success');
            // Reconcile events missed between initial render and WS connection,
            // or while the client was temporarily disconnected.
            queueRefresh();
        });
        for (const state of ['disconnected', 'unavailable', 'failed']) {
            socket?.bind(state, () => showConnection('Нет соединения · обновить вручную', 'warning'));
        }
        socket?.bind('connecting', () => showConnection('Подключение…', 'secondary'));
        socket?.bind('error', (error) => {
            console.warn('[Norhnaim Laboratory] WebSocket connection issue', error);
            showConnection('Ошибка WebSocket', 'warning');
        });
        echo.connector?.pusher?.channel(`private-laboratory.clinic.${clinicId}`)?.bind('pusher:subscription_error', () => {
            showConnection('Нет доступа к каналу', 'danger');
        });

        window.addEventListener('beforeunload', () => echo.disconnect(), { once: true });
    }
}
