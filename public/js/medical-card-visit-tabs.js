/* Keep selected visit-workspace pane on refresh, pagination and form redirects. */
(() => {
    'use strict';
    function init() {
        const tabs = document.getElementById('mcVisitWorkspaceTabs');
        if (!tabs) return;

        tabs.addEventListener('shown.bs.tab', (event) => {
            const target = event.target;
            if (!(target instanceof Element) || !tabs.contains(target)) return;
            const selected = target.getAttribute('data-visit-workspace');
            if (selected !== 'history' && selected !== 'confirmation') return;
            const url = new URL(window.location.href);
            url.searchParams.set('tab', 'visits');
            url.searchParams.set('visit_section', selected);
            window.history.replaceState(window.history.state, '', url.pathname + url.search + url.hash);
        });
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init, {once: true});
    } else {
        init();
    }
})();
