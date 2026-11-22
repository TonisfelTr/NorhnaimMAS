/* Patient diagnoses: accessible tab panels, no Bootstrap JS dependency. */
(() => {
    'use strict';

    function init() {
        const root = document.querySelector('.patient-diagnoses');
        const tablist = root?.querySelector('.patient-diagnoses__tabs[role="tablist"]');
        if (!tablist) return;

        const tabs = Array.from(tablist.querySelectorAll('[role="tab"][data-diagnoses-tab]'));
        const panes = new Map();

        for (const tab of tabs) {
            const panelId = tab.getAttribute('aria-controls');
            const panel = panelId ? document.getElementById(panelId) : null;
            if (!panel || !root.contains(panel)) return;
            panes.set(tab.dataset.diagnosesTab, panel);
        }

        function activate(key, changeUrl = false) {
            if (!panes.has(key)) return;

            for (const tab of tabs) {
                const selected = tab.dataset.diagnosesTab === key;
                tab.classList.toggle('is-active', selected);
                tab.setAttribute('aria-selected', String(selected));
                tab.tabIndex = selected ? 0 : -1;
            }
            for (const [panelKey, panel] of panes) {
                panel.hidden = panelKey !== key;
            }

            if (changeUrl) {
                const url = new URL(window.location.href);
                url.searchParams.set('tab', key);
                window.history.replaceState(window.history.state, '', url.pathname + url.search + url.hash);
            }
        }

        for (const tab of tabs) {
            tab.addEventListener('click', (event) => {
                // Link fallback works without JS. With JS, update just the panels.
                if (event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
                event.preventDefault();
                activate(tab.dataset.diagnosesTab, true);
            });
        }

        tablist.addEventListener('keydown', (event) => {
            const selectedTab = event.target.closest?.('[role="tab"]');
            const index = tabs.indexOf(selectedTab);
            if (index < 0) return;

            let nextIndex;
            switch (event.key) {
                case 'ArrowRight': nextIndex = (index + 1) % tabs.length; break;
                case 'ArrowLeft': nextIndex = (index - 1 + tabs.length) % tabs.length; break;
                case 'Home': nextIndex = 0; break;
                case 'End': nextIndex = tabs.length - 1; break;
                default: return;
            }
            event.preventDefault();
            const target = tabs[nextIndex];
            activate(target.dataset.diagnosesTab, true);
            target.focus();
        });

        window.addEventListener('popstate', () => {
            const current = new URL(window.location.href).searchParams.get('tab') || 'current';
            activate(panes.has(current) ? current : 'current');
        });

        const requested = new URL(window.location.href).searchParams.get('tab') || 'current';
        activate(panes.has(requested) ? requested : 'current');
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init, {once: true});
    } else {
        init();
    }
})();
