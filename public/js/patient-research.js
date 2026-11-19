/**
 * Панель пациента — просмотр результатов лабораторных исследований.
 * Нативный <dialog> не зависит от Bootstrap и корректно закрывается по Esc.
 * Нет inline-JS в Blade и нет передачи пациентских данных через data-атрибуты.
 */
(function () {
    'use strict';

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
