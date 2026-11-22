/* Presentation enhancement only: app.js owns camera, mic, signaling and session lifecycle. */
(() => {
  'use strict';
  const ids = ['muteButton', 'cameraButton', 'endButton'];
  const sync = (button) => {
    const label = (button.textContent || '').replace(/\s+/g, ' ').trim();
    if (label) {
      if (button.getAttribute('aria-label') !== label) button.setAttribute('aria-label', label);
      if (button.getAttribute('title') !== label) button.setAttribute('title', label);
      if (button.id !== 'endButton') {
        button.dataset.controlOff = /^(?:включить|unmute|enable|turn on)/i.test(label) ? '1' : '0';
      }
    }
  };
  const init = () => {
    for (const id of ids) {
      const button = document.getElementById(id);
      if (!button) continue;
      sync(button);
      new MutationObserver(() => sync(button))
        .observe(button, {childList: true, characterData: true, subtree: true});
    }
  };
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init, {once: true});
  else init();
})();
