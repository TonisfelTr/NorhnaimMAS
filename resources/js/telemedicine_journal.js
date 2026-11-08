import { Modal, Tab } from 'bootstrap';
import '../sass/telemedicine_journal.sass';

export function initTelemedicineJournal() {
    const root = document.querySelector('.mc-audit-log');
    if (!root || root.dataset.tmInitialized === '1') return;
    root.dataset.tmInitialized = '1';
    const createModal = document.getElementById('modalCreateTelemedicineSession');
    const deleteModal = document.getElementById('modalDeleteTelemedicineSession');
    const deleteForm = deleteModal?.querySelector('[data-telemed-delete-form]');

    // Move dialogs outside the tab pane so a hidden/transformed ancestor cannot clip them.
    [createModal, deleteModal].filter(Boolean).forEach(modal => document.body.append(modal));
    root.addEventListener('click', event => {
        const create = event.target.closest('[data-telemed-create]');
        if (create && !create.disabled && createModal) {
            event.preventDefault();
            Modal.getOrCreateInstance(createModal).show(create);
            return;
        }
        const button = event.target.closest('[data-telemed-delete]');
        if (!button || !deleteForm || !deleteModal) return;
        event.preventDefault();
        event.stopPropagation();
        const target = new URL(button.dataset.deleteUrl, window.location.href);
        if (target.origin !== window.location.origin) return;
        deleteForm.reset();
        deleteForm.action = target.href;
        deleteForm.querySelector('[data-telemed-submit]').disabled = false;
        deleteModal.querySelector('[data-telemed-delete-caption]').textContent =
            `Сеанс № ${button.dataset.visitId} · ${button.dataset.visitDate} · ${button.dataset.visitDoctor}`;
        Modal.getOrCreateInstance(deleteModal).show(button);
    });
    [createModal, deleteModal].filter(Boolean).forEach(modal => {
        const form = modal.querySelector('[data-telemed-form]');
        const submit = form?.querySelector('[data-telemed-submit]');
        if (!form || !submit) return;
        const label = submit.textContent;
        form.addEventListener('submit', event => {
            if (!form.hasAttribute('action') || !form.reportValidity() || form.dataset.submitting === '1') {
                event.preventDefault();
                return;
            }
            form.dataset.submitting = '1';
            submit.disabled = true;
            submit.textContent = 'Сохранение…';
        });
        window.addEventListener('pageshow', () => {
            delete form.dataset.submitting;
            submit.disabled = !form.hasAttribute('action');
            submit.textContent = label;
        });
        modal.addEventListener('shown.bs.modal', () => {
            form.querySelector('select, textarea, input:not([type="hidden"])')?.focus();
        });
    });
    const params = new URLSearchParams(window.location.search);
    if (params.get('tab') === 'telemedicine-log') {
        const tab = document.getElementById('tab-telemedicine-log');
        if (tab) Tab.getOrCreateInstance(tab).show();
    }
    if (createModal?.dataset.openAfterError === '1') Modal.getOrCreateInstance(createModal).show();
}
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initTelemedicineJournal, { once: true });
} else {
    initTelemedicineJournal();
}
