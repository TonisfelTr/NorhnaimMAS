/* Norhnaim: guard Bootstrap's main and nested medical-card tabs.
 * Does not touch patient data, forms, or prescription actions.
 */
(function () {
    'use strict';

    function initialize() {
        const root = document.getElementById('medicalCardRoot');
        if (!root) return;

        const pane = root.querySelector('#pane-presc');
        const trigger = root.querySelector('[data-bs-target="#pane-presc"]');
        if (!pane || !trigger) return;

        const workspaceTabs = root.querySelector('#patientMedicationWorkspaceTabs');
        const workspace = root.querySelector('#patientMedicationWorkspaceContent');
        const infoPane = root.querySelector('#pane-patient-info');

        // A previous template edit can place the *whole workspace* outside its pane.
        // Reattach only the known container elements; no prescription rows are cloned.
        if (workspaceTabs && workspace &&
            (!pane.contains(workspaceTabs) || !pane.contains(workspace))) {
            pane.appendChild(workspaceTabs);
            pane.appendChild(workspace);
            console.warn('[Норхнейм] Контейнер вкладок назначений находился вне pane-presc; ' +
                'он возвращён внутрь нужной вкладки. Исправьте вложенность в исходном Blade.');
        }

        // Diagnose a malformed outer tab layout without moving other clinical sections.
        if (infoPane && pane.parentElement !== infoPane.parentElement) {
            console.error('[Норхнейм] pane-presc и pane-patient-info имеют разных родителей. ' +
                'Проверьте закрывающие </div> в medical_card.blade.php.');
        }

        function selected(tab) {
            return !!tab && tab.classList.contains('active') &&
                tab.getAttribute('aria-selected') !== 'false';
        }

        function sync() {
            pane.hidden = !selected(trigger);

            if (!workspace) return;
            workspace.querySelectorAll('.medication-workspace-pane').forEach(function (item) {
                const tab = workspaceTabs && workspaceTabs.querySelector(
                    '[data-bs-target="#' + item.id + '"]'
                );
                item.hidden = !selected(tab);
            });
        }

        document.addEventListener('show.bs.tab', function (event) {
            if (event.target === trigger) pane.hidden = false;
            if (workspace && workspaceTabs && workspaceTabs.contains(event.target)) {
                const id = event.target.getAttribute('data-bs-target');
                if (id && id.charAt(0) === '#') {
                    const target = document.getElementById(id.substring(1));
                    if (target && workspace.contains(target)) target.hidden = false;
                }
            }
        }, true);

        document.addEventListener('shown.bs.tab', sync, true);
        document.addEventListener('hidden.bs.tab', sync, true);

        sync();
        window.requestAnimationFrame(sync);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initialize, { once: true });
    } else {
        initialize();
    }
}());
