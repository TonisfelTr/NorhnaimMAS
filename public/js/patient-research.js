/**
 * Панель пациента — просмотр результатов лабораторных исследований.
 * Нативный <dialog> не зависит от Bootstrap и корректно закрывается по Esc.
 * Нет inline-JS в Blade и нет передачи пациентских данных через data-атрибуты.
 */
(function () {
    'use strict';


    // Переключение вкладок исследований без Bootstrap JS.
    const researchTabs = document.getElementById('patientResearchTabs');

    if (researchTabs) {
        const buttons = Array.from(
            researchTabs.querySelectorAll('[role="tab"][data-bs-target]')
        );

        const panes = Array.from(
            document.querySelectorAll(
                '.patient-research-tab-content > .tab-pane'
            )
        );

        function activateResearchTab(button) {
            const targetId = button.getAttribute('data-bs-target');
            const pane = targetId && targetId.startsWith('#')
                ? document.getElementById(targetId.slice(1))
                : null;

            if (!pane || !panes.includes(pane)) return;

            buttons.forEach(item => {
                const active = item === button;

                item.classList.toggle('active', active);
                item.setAttribute('aria-selected', String(active));
                item.tabIndex = active ? 0 : -1;
            });

            panes.forEach(item => {
                const active = item === pane;

                item.classList.toggle('active', active);
                item.classList.toggle('show', active);
                item.hidden = !active;
            });
        }

        researchTabs.addEventListener('click', event => {
            const button = event.target instanceof Element
                ? event.target.closest('[role="tab"]')
                : null;

            if (!button || !researchTabs.contains(button)) return;

            event.preventDefault();
            activateResearchTab(button);
        });

        researchTabs.addEventListener('keydown', event => {
            const current = event.target instanceof Element
                ? event.target.closest('[role="tab"]')
                : null;

            if (!current) return;

            const index = buttons.indexOf(current);
            if (index < 0) return;

            let nextIndex;

            switch (event.key) {
                case 'ArrowRight':
                    nextIndex = (index + 1) % buttons.length;
                    break;
                case 'ArrowLeft':
                    nextIndex = (index - 1 + buttons.length) % buttons.length;
                    break;
                case 'Home':
                    nextIndex = 0;
                    break;
                case 'End':
                    nextIndex = buttons.length - 1;
                    break;
                default:
                    return;
            }

            event.preventDefault();

            const next = buttons[nextIndex];
            activateResearchTab(next);
            next.focus();
        });

        const initial = buttons.find(
            button => button.classList.contains('active')
        ) || buttons[0];

        if (initial) activateResearchTab(initial);
    }

    document.addEventListener('click', function (event) {
        const target = event.target;
        if (!(target instanceof Element)) {
            return;
        }

        const openButton = target.closest('[data-research-modal-open]');
        if (openButton) {
            const modalId = openButton.getAttribute('data-research-modal-open');
            const dialog = modalId ? document.getElementById(modalId) : null;

            if (dialog instanceof HTMLDialogElement && !dialog.open) {
                dialog.showModal();
            }
            return;
        }

        const closeButton = target.closest('[data-research-modal-close]');
        if (closeButton) {
            const dialog = closeButton.closest('dialog.patient-research-modal');
            if (dialog instanceof HTMLDialogElement && dialog.open) {
                dialog.close();
            }
            return;
        }

        // Клик по затемнению за окном, но не по содержимому модалки.
        if (target.matches('dialog.patient-research-modal') && target.open) {
            const rect = target.getBoundingClientRect();
            const outside = event.clientX < rect.left || event.clientX > rect.right
                || event.clientY < rect.top || event.clientY > rect.bottom;

            if (outside) {
                target.close();
            }
        }
    });
})();
