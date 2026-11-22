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
