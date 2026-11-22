'use strict';

(() => {
  const $ = id => document.getElementById(id);
  const startButton = $('startButton');
  const endButton = $('endButton');
  const muteButton = $('muteButton');
  const cameraButton = $('cameraButton');
  const localVideo = $('localVideo');
  const remoteVideo = $('remoteVideo');
  const remotePlaceholder = $('remotePlaceholder');
  const localPlaceholder = $('localPlaceholder');
  const channelText = $('channelText');
  const roleText = $('roleText');

  // /session?token=... is verified by the server before the HTML is returned.
  // The same signed invitation is still sent in Authorization for every API call.
  const invite = new URLSearchParams(window.location.search).get('token');
  let pc = null;
  let localStream = null;
  let running = false;
  let makingOffer = false;
  let offerSent = false;
  let seq = 0;
  let role = '';
  let sessionId = ''; // server-issued per-connection secret; never in URL or logs
  let pollAbortController = null;
  let statsTimer = null;
  let pendingCandidates = [];

  function setStatus(message, mode = '') {
    $('statusText').textContent = message;
    $('statusDot').className = `status-dot ${mode}`;
  }

  async function api(path, method = 'GET', body = null, signal = null) {
    const headers = { 'Authorization': `Bearer ${invite}` };
    if (method !== 'GET') headers['Content-Type'] = 'application/json';
    if (sessionId) headers['X-Telemedicine-Session'] = sessionId;
    const response = await fetch(path, {
      method, headers, cache: 'no-store',
      ...(body !== null ? { body: JSON.stringify(body) } : {}),
      ...(signal ? { signal } : {}),
    });
    let payload = {};
    try { payload = await response.json(); } catch (_) { /* safe default */ }
    if (!response.ok) throw new Error(payload.error || `HTTP ${response.status}`);
    return payload;
  }

  function resetControls() {
    startButton.disabled = running;
    endButton.disabled = !running;
    muteButton.disabled = !running;
    cameraButton.disabled = !running;
    muteButton.textContent = 'Выключить микрофон';
    cameraButton.textContent = 'Выключить камеру';
  }

  function releaseMedia() {
    if (pc) {
      pc.onicecandidate = null;
      pc.ontrack = null;
      pc.onconnectionstatechange = null;
      pc.close();
      pc = null;
    }
    if (localStream) {
      localStream.getTracks().forEach(track => track.stop());
      localStream = null;
    }
    localVideo.srcObject = null;
    remoteVideo.srcObject = null;
    remotePlaceholder.hidden = false;
    localPlaceholder.hidden = false;
    pendingCandidates = [];
    sessionId = '';
    offerSent = false;
    makingOffer = false;
    if (statsTimer !== null) clearInterval(statsTimer);
    statsTimer = null;
  }

  async function finish(notifyPeer = true) {
    if (!running) return;
    running = false;
    pollAbortController?.abort();
    pollAbortController = null;
    // Also unregister after receiving peer hangup (no stale role for 40 seconds).
    if (sessionId) {
      try { await api('/api/leave', 'POST', {}); } catch (_) { /* timeout cleanup */ }
    }
    releaseMedia();
    resetControls();
    channelText.textContent = '—';
    setStatus(notifyPeer ? 'Звонок завершён' : 'Собеседник завершил звонок');
  }

  async function send(kind, data) {
    if (!running) return;
    await api('/api/signal', 'POST', { type: kind, data });
  }

  async function flushCandidates() {
    while (pendingCandidates.length && pc?.remoteDescription) {
      const item = pendingCandidates.shift();
      try { await pc.addIceCandidate(item); }
      catch (err) { console.warn('[webrtc] ICE candidate rejected:', err.name); }
    }
  }

  async function offer() {
    if (!running || role !== 'doctor' || !pc || offerSent || makingOffer || pc.signalingState !== 'stable') return;
    makingOffer = true;
    try {
      const description = await pc.createOffer();
      await pc.setLocalDescription(description);
      offerSent = true;
      await send('offer', { type: pc.localDescription.type, sdp: pc.localDescription.sdp });
      setStatus('Ожидаем ответа пациента…');
    } finally {
      makingOffer = false;
    }
  }

  async function onMessage(item) {
    if (!running || !pc) return;
    if (item.type === 'peer-ready') {
      if (role === 'doctor') await offer();
      return;
    }
    if (item.type === 'hangup') {
      await finish(false);
      return;
    }
    if (item.type === 'offer' && role === 'patient') {
      await pc.setRemoteDescription(item.data);
      await flushCandidates();
      const description = await pc.createAnswer();
      await pc.setLocalDescription(description);
      await send('answer', { type: pc.localDescription.type, sdp: pc.localDescription.sdp });
      setStatus('Соединяемся с врачом…');
      return;
    }
    if (item.type === 'answer' && role === 'doctor') {
      await pc.setRemoteDescription(item.data);
      await flushCandidates();
      return;
    }
    if (item.type === 'candidate') {
      if (!pc.remoteDescription) pendingCandidates.push(item.data);
      else {
        try { await pc.addIceCandidate(item.data); }
        catch (err) { console.warn('[webrtc] ICE candidate rejected:', err.name); }
      }
    }
  }

  async function poll() {
    pollAbortController = new AbortController();
    while (running) {
      try {
        const result = await api(`/api/poll?after=${seq}`, 'GET', null, pollAbortController.signal);
        if (!running) break;
        for (const event of result.events || []) {
          if (!running) break;
          await onMessage(event);
        }
        seq = Math.max(seq, Number(result.next) || 0);
        if (running && role === 'doctor' && result.peer_present) await offer();
      } catch (error) {
        if (!running || error.name === 'AbortError') break;
        setStatus(`Нет связи с сигнализацией: ${error.message}`, 'error');
        if (error.message.includes('expired') || error.message.includes('Join the call')) {
          await finish(false); break;
        }
        await new Promise(resolve => setTimeout(resolve, 1500));
      }
    }
  }

  // Sent to signaling first; Laravel receives only a signed server event.
  // This is a CLIENT-REPORTED connection state, not independent proof of a call.
  function reportMedia(state) {
    if (running && sessionId) {
      void api('/api/media', 'POST', { state }).catch(() => { /* non-critical */ });
    }
  }

  async function showTransport() {
    if (!pc || !running || pc.connectionState !== 'connected') return;
    const stats = await pc.getStats();
    let chosen = null;
    stats.forEach(stat => {
      if (stat.type === 'transport' && stat.selectedCandidatePairId) chosen = stats.get(stat.selectedCandidatePairId);
    });
    if (!chosen) stats.forEach(stat => {
      if (stat.type === 'candidate-pair' && stat.nominated && stat.state === 'succeeded') chosen = stat;
    });
    if (!chosen) return;
    const local = stats.get(chosen.localCandidateId);
    const remote = stats.get(chosen.remoteCandidateId);
    const relay = local?.candidateType === 'relay' || remote?.candidateType === 'relay';
    channelText.textContent = relay ? 'TURN (через сервер)' : 'Прямой WebRTC (P2P)';
  }

  async function start() {
    if (running) return;
    if (!invite) { setStatus('Нет приглашения: создайте две ссылки скриптом make_demo_links.py', 'error'); return; }
    startButton.disabled = true;
    setStatus('Запрашиваем доступ к камере и микрофону…');
    try {
      const config = await api('/api/config');
      role = config.role;
      roleText.textContent = role === 'doctor' ? 'Врач (тест)' : 'Пациент (тест)';
      if (!navigator.mediaDevices?.getUserMedia) throw new Error('Нужен HTTPS или localhost для доступа к камере.');
      localStream = await navigator.mediaDevices.getUserMedia({ audio: { echoCancellation: true, noiseSuppression: true }, video: { width: { ideal: 1280 }, height: { ideal: 720 }, frameRate: { ideal: 24, max: 30 } } });
      localVideo.srcObject = localStream;
      localPlaceholder.hidden = true;
      pc = new RTCPeerConnection({ iceServers: config.iceServers, bundlePolicy: 'max-bundle' });
      localStream.getTracks().forEach(track => pc.addTrack(track, localStream));
      pc.ontrack = event => {
        remoteVideo.srcObject = event.streams[0];
        remotePlaceholder.hidden = true;
      };
      pc.onicecandidate = event => {
        if (event.candidate && running) {
          send('candidate', event.candidate.toJSON()).catch(() => {
            if (running) setStatus('Не удалось передать ICE-кандидат', 'error');
          });
        }
      };
      pc.onconnectionstatechange = () => {
        if (!pc || !running) return;
        if (pc.connectionState === 'connected') {
          setStatus('Видеосвязь установлена', 'active');
          reportMedia('connected');
          void showTransport();
        } else if (pc.connectionState === 'failed') {
          setStatus('Не удалось установить соединение. Проверьте TURN и ICE.', 'error');
          reportMedia('failed');
        } else if (pc.connectionState === 'disconnected') {
          setStatus('Соединение временно потеряно');
          reportMedia('disconnected');
        }
      };
      running = true;
      resetControls();
      setStatus('Ждём второго участника…');
      const state = await api('/api/join', 'POST', {});
      sessionId = state.session || '';
      if (!sessionId) throw new Error('Сервер не выдал сессию подключения.');
      void poll();
      if (role === 'doctor' && state.peer_present) await offer();
      statsTimer = setInterval(() => { void showTransport().catch(() => {}); }, 5000);
    } catch (error) {
      running = false;
      releaseMedia();
      resetControls();
      setStatus(`Не удалось начать: ${error.message}`, 'error');
    }
  }

  startButton.addEventListener('click', () => { void start(); });
  endButton.addEventListener('click', () => { void finish(); });
  muteButton.addEventListener('click', () => {
    if (!localStream) return;
    const tracks = localStream.getAudioTracks();
    const enabled = tracks.some(track => track.enabled);
    tracks.forEach(track => { track.enabled = !enabled; });
    muteButton.textContent = enabled ? 'Включить микрофон' : 'Выключить микрофон';
  });
  cameraButton.addEventListener('click', () => {
    if (!localStream) return;
    const tracks = localStream.getVideoTracks();
    const enabled = tracks.some(track => track.enabled);
    tracks.forEach(track => { track.enabled = !enabled; });
    cameraButton.textContent = enabled ? 'Включить камеру' : 'Выключить камеру';
  });
  window.addEventListener('pagehide', () => {
    // Best-effort explicit leave when closing the tab. Timeout cleanup remains a fallback.
    if (running && sessionId) {
      const headers = { 'Authorization': `Bearer ${invite}`, 'Content-Type': 'application/json', 'X-Telemedicine-Session': sessionId };
      void fetch('/api/leave', { method: 'POST', headers, body: '{}', keepalive: true }).catch(() => {});
    }
    releaseMedia();
  });
  resetControls();
  setStatus(invite ? 'Приглашение получено. Можно начать звонок.' : '');
})();

// Presentation only: WebRTC app.js still owns signaling, ICE, room roles and lifecycle.
(() => {
  'use strict';

  function initSessionInfo() {
    const roleSource = document.getElementById('roleText');
    const channelSource = document.getElementById('channelText');
    const statusSource = document.getElementById('statusText');
    const roleOutput = document.getElementById('roleDisplayText');
    const connectionOutput = document.getElementById('connectionStateText');
    if (!roleSource || !channelSource || !statusSource || !roleOutput || !connectionOutput) return;

    function syncRole() {
      const original = (roleSource.textContent || '').replace(/\s+/g, ' ').trim();
      // Only remove test suffix from the visual role; keep the original app-owned value.
      const label = original.replace(/\s*\((?:тест|test)\)\s*$/i, '').trim();
      roleOutput.textContent = label || '—';
    }

    function readableConnection(value) {
      const original = (value || '').replace(/\s+/g, ' ').trim();
      if (!original || original === '—') return 'Ожидание';
      const lower = original.toLocaleLowerCase('ru-RU');
      if (/нет приглашения|ожидаем приглашение|приглашение отсутствует/.test(lower)) return 'Нет приглашения';
      if (/звонок заверш|сеанс заверш|завершен|завершён|вызов окончен/.test(lower)) return 'Завершён';
      if (/потерян|разорван|отключён|отключен|disconnected|failed/.test(lower)) return 'Соединение потеряно';
      if (/соединение установлено|собеседник подключил|на связи|разговор идёт|разговор идет|connected/.test(lower)) return 'На связи';
      if (/ожидани|ожидаем|ожидаю|ждём|ждем/.test(lower)) return 'Ожидание';
      if (/запрашиваем|подключа|соединяем|устанавлива|присоединя|входим|checking|connecting/.test(lower)) return 'Подключение';
      // Preserve unknown states/errors verbatim; never claim a successful connection without evidence.
      return original;
    }

    function syncConnection() {
      const value = readableConnection(statusSource.textContent);
      if (connectionOutput.textContent !== value) connectionOutput.textContent = value;
    }

    syncRole();
    syncConnection();
    new MutationObserver(syncRole).observe(roleSource, {childList: true, characterData: true, subtree: true});
    new MutationObserver(syncConnection).observe(statusSource, {childList: true, characterData: true, subtree: true});
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initSessionInfo, {once: true});
  } else {
    initSessionInfo();
  }
})();
