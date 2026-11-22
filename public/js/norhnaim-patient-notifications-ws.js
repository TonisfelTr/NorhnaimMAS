
(() => {
  'use strict';

  if (window.__norhnaimPatientNotificationsWsV1) return;
  window.__norhnaimPatientNotificationsWsV1 = true;

  const configUrl = '/patient/notifications/realtime-config';
  // Sidebar counts are GLOBAL. Tabs reflect the selected clinic.
  function clinicConfigUrl() {
    const uri = new URL(configUrl, location.origin);
    if (location.pathname.replace(/\/$/, '') === '/patient/notifications') {
      const id = new URL(location.href).searchParams.get('clinic_id');
      if (id && /^[1-9][0-9]*$/.test(id)) uri.searchParams.set('clinic_id', id);
    }
    return uri.toString();
  }
  function visibleCounts(data, unread, read) {
    const selection = data?.selectedCounts;
    if (selection && Number.isSafeInteger(Number(selection.unread)) && Number.isSafeInteger(Number(selection.read))) {
      return { unread: Number(selection.unread), read: Number(selection.read) };
    }
    // On WS messages only global counts arrive; keep currently rendered clinic tab values.
    const id = new URL(location.href).searchParams.get('clinic_id');
    if (id && /^[1-9][0-9]*$/.test(id) && location.pathname.replace(/\/$/, '') === '/patient/notifications') {
      return null;
    }
    return { unread, read };
  }
  let config = null;
  let socket = null;
  let retryDelay = 1000;
  let reconnectTimer = null;
  let refreshInProgress = false;
  let refreshAgain = false;
  let subscribed = false;
  let wsEventRevision = 0;

  function updateCounters(data) {
    const unread = Number(data?.unread);
    const read = Number(data?.read);
    if (!Number.isSafeInteger(unread) || unread < 0 || !Number.isSafeInteger(read) || read < 0) return;

    // Left sidebar menu badge: display only unread notifications.
    document.querySelectorAll('a.patient-sidebar__link').forEach((link) => {
      let url;
      try { url = new URL(link.getAttribute('href') || '', location.href); }
      catch (_) { return; }
      if (url.pathname.replace(/\/$/, '') !== '/patient/notifications') return;

      let badge = link.querySelector('.patient-notification-count');
      if (!badge) {
        badge = document.createElement('span');
        badge.className = 'patient-notification-count';
        link.appendChild(badge);
      }
      badge.textContent = unread > 99 ? '99+' : String(unread);
      badge.hidden = unread === 0;
      badge.style.display = unread === 0 ? 'none' : '';
      link.setAttribute('aria-label', `Уведомления, непрочитанных: ${unread}`);
    });

    const scoped = visibleCounts(data, unread, read);
    if (!scoped) return;
    const shownUnread = scoped.unread;
    const shownRead = scoped.read;
    // The "Все" tab in this project intentionally contains only READ items.
    document.querySelectorAll('[data-notification-count="read"]').forEach((badge) => {
      badge.textContent = String(shownRead);
    });
    document.querySelectorAll('[data-notification-count="unread"]').forEach((badge) => {
      badge.textContent = String(shownUnread);
    });
    document.querySelectorAll('.patient-notifications-tabs a').forEach((link) => {
      const query = new URL(link.href, location.href).searchParams;
      const isUnread = query.get('filter') === 'unread';
      link.setAttribute('aria-label', `${isUnread ? 'Непрочитанные' : 'Прочитанные'} уведомления: ${isUnread ? shownUnread : shownRead}`);
    });

    const form = document.querySelector('[data-notification-read-all-form]');
    if (form) {
      form.hidden = shownUnread === 0;
      form.style.display = shownUnread === 0 ? 'none' : '';
    }
  }

  async function refreshVisibleNotifications() {
    if (location.pathname.replace(/\/$/, '') !== '/patient/notifications') return;
    if (refreshInProgress) {
      refreshAgain = true;
      return;
    }
    refreshInProgress = true;
    try {
      const response = await fetch(location.href, {
        credentials: 'same-origin',
        cache: 'no-store',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
      });
      if (!response.ok) return;
      const html = await response.text();
      const doc = new DOMParser().parseFromString(html, 'text/html');
      for (const selector of [
        '.patient-notifications-toolbar',
        '.patient-notifications-list',
        '.patient-activity-pagination',
      ]) {
        const oldElement = document.querySelector(selector);
        const newElement = doc.querySelector(selector);
        if (oldElement && newElement) oldElement.replaceWith(document.importNode(newElement, true));
        else if (oldElement && !newElement && selector === '.patient-activity-pagination') oldElement.remove();
        else if (!oldElement && newElement && selector === '.patient-activity-pagination') {
          const list = document.querySelector('.patient-notifications-list');
          list?.insertAdjacentElement('afterend', document.importNode(newElement, true));
        }
      }
      // Server-rendered counts may be slightly behind a subsequent WS event;
      // use the latest delivered counters again if that happened.
      if (config?.lastCounts) updateCounters(config.lastCounts);
    } catch (error) {
      console.debug('[NORHNAIM notifications] Refresh postponed:', error);
    } finally {
      refreshInProgress = false;
      if (refreshAgain) {
        refreshAgain = false;
        void refreshVisibleNotifications();
      }
    }
  }

  function parseData(data) {
    if (typeof data === 'string') {
      try { return JSON.parse(data); } catch (_) { return null; }
    }
    return data && typeof data === 'object' ? data : null;
  }

  function authAndSubscribe(socketId, ws) {
    const body = new URLSearchParams({ socket_id: socketId, channel_name: config.channel });
    fetch(config.authUrl, {
      method: 'POST',
      credentials: 'same-origin',
      cache: 'no-store',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8',
        'X-CSRF-TOKEN': config.csrf,
        'X-Requested-With': 'XMLHttpRequest',
      },
      body,
    }).then((response) => {
      if (!response.ok) throw new Error(`Channel authorization failed: ${response.status}`);
      return response.json();
    }).then((data) => {
      if (ws.readyState !== WebSocket.OPEN || !data.auth) return;
      ws.send(JSON.stringify({ event: 'pusher:subscribe', data: { channel: config.channel, auth: data.auth } }));
    }).catch((error) => {
      console.warn('[NORHNAIM notifications]', error.message);
      ws.close();
    });
  }

  function scheduleReconnect() {
    if (reconnectTimer || !config) return;
    reconnectTimer = setTimeout(() => {
      reconnectTimer = null;
      connect();
    }, retryDelay);
    retryDelay = Math.min(30000, Math.round(retryDelay * 1.8));
  }

  function connect() {
    if (!config || !config.enabled) return;
    if (socket && (socket.readyState === WebSocket.OPEN || socket.readyState === WebSocket.CONNECTING)) return;
    const protocol = config.secure ? 'wss' : 'ws';
    if (location.protocol === 'https:' && protocol === 'ws') {
      console.warn('[NORHNAIM notifications] WSS proxy is required for HTTPS pages.');
      return;
    }

    const authority = config.host.includes(':') && !config.host.startsWith('[') ? `[${config.host}]` : config.host;
    const url = `${protocol}://${authority}:${config.port}/app/${encodeURIComponent(config.key)}?protocol=7&client=js&version=8.4.0&flash=false`;
    let ws;
    try { ws = new WebSocket(url); }
    catch (error) { console.warn('[NORHNAIM notifications]', error); scheduleReconnect(); return; }
    socket = ws;
    subscribed = false;

    ws.onopen = () => { retryDelay = 1000; };
    ws.onmessage = (message) => {
      let frame;
      try { frame = JSON.parse(message.data); } catch (_) { return; }
      if (frame.event === 'pusher:connection_established') {
        const data = parseData(frame.data);
        if (data?.socket_id) authAndSubscribe(data.socket_id, ws);
      } else if (frame.event === 'pusher_internal:subscription_succeeded') {
        subscribed = true;
        // A reconnect might have missed events; reconcile once via HTTP.
        void fetchConfigCounts();
      } else if (frame.event === 'pusher:ping') {
        if (ws.readyState === WebSocket.OPEN) ws.send(JSON.stringify({ event: 'pusher:pong', data: {} }));
      } else if (frame.event === config.event && frame.channel === config.channel) {
        const data = parseData(frame.data);
        if (!data) return;
        wsEventRevision += 1;
        config.lastCounts = data;
        updateCounters(data);
        // Fetch clinic-specific counts after a global WS event.
        void fetchConfigCounts();
        void refreshVisibleNotifications();
      }
    };
    ws.onclose = () => {
      if (socket === ws) socket = null;
      subscribed = false;
      if (!document.hidden) scheduleReconnect();
    };
    ws.onerror = () => { /* onclose handles reconnection */ };
  }

  async function fetchConfigCounts() {
    const currentRevision = wsEventRevision;
    try {
      const response = await fetch(clinicConfigUrl(), { credentials: 'same-origin', cache: 'no-store' });
      if (!response.ok) return;
      const payload = await response.json();
      if (payload.counts && wsEventRevision === currentRevision) {
        config.lastCounts = { ...payload.counts, selectedCounts: payload.selectedCounts };
        updateCounters(config.lastCounts);
      }
    } catch (_) { /* temporarily offline */ }
  }

  async function start() {
    try {
      const response = await fetch(clinicConfigUrl(), { credentials: 'same-origin', cache: 'no-store' });
      if (!response.ok) return;
      config = await response.json();
      if (config.counts) {
        config.lastCounts = { ...config.counts, selectedCounts: config.selectedCounts };
        updateCounters(config.lastCounts);
      }
      if (!config.enabled || !config.key) {
        console.warn('[NORHNAIM notifications] Reverb is not configured. Check BROADCAST_CONNECTION and REVERB_APP_KEY.');
        return;
      }
      connect();
    } catch (error) {
      console.debug('[NORHNAIM notifications] Could not initialize:', error);
    }
  }

  document.addEventListener('visibilitychange', () => {
    if (!document.hidden) {
      void fetchConfigCounts();
      if (!socket || socket.readyState === WebSocket.CLOSED) {
        if (reconnectTimer) { clearTimeout(reconnectTimer); reconnectTimer = null; }
        connect();
      }
    }
  });
  window.addEventListener('online', () => {
    void fetchConfigCounts();
    if (!socket || socket.readyState === WebSocket.CLOSED) connect();
  });

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start, { once: true });
  else void start();
})();
