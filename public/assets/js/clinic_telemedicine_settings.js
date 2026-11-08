(() => {
    'use strict';

    const initialize = () => {
        document.querySelectorAll('[data-telemedicine-settings]').forEach((form) => {
            const provider = form.querySelector('[data-tm-provider]');
            const mode = form.querySelector('[data-tm-telemost-mode]');

            if (!provider || !mode) return;

            const render = () => {
                form.querySelectorAll('[data-tm-panel]').forEach((panel) => {
                    panel.hidden = panel.dataset.tmPanel !== provider.value;
                });
                form.querySelectorAll('[data-tm-api-fields]').forEach((section) => {
                    section.hidden = mode.value !== 'api';
                });
                form.querySelectorAll('[data-tm-manual-help]').forEach((section) => {
                    section.hidden = mode.value !== 'manual';
                });
            };

            provider.addEventListener('change', render);
            mode.addEventListener('change', render);
            render();
        });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initialize, { once: true });
    } else {
        initialize();
    }
})();
