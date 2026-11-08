/**
 * Presentation only: the server authorizes appointments and records attestation.
 * All PHP/data processing remains in the controller; no inline handlers in Blade.
 */
export function initMedicalVisitAudit() {
    const modal = document.getElementById('modalConfirmMedicalVisit');
    if (!modal) return;

    const form = modal.querySelector('[data-medical-visit-form]');
    const title = modal.querySelector('#modalConfirmMedicalVisitTitle');
    const caption = modal.querySelector('[data-medical-visit-caption]');
    const held = modal.querySelector('[data-medical-visit-held-fields]');
    const notHeld = modal.querySelector('[data-medical-visit-notheld-fields]');
    const correction = modal.querySelector('[data-medical-visit-correction-fields]');
    const attested = modal.querySelector('[data-medical-visit-attested]');
    const reason = modal.querySelector('[name="reason"]');
    const correctionReason = modal.querySelector('[name="correction_reason"]');

    if (!form || !reason || !attested || !correctionReason) return;

    let previous = '';
    let previousReason = '';

    const selectedOutcome = () => form.querySelector('input[name="outcome"]:checked')?.value || 'held';

    const updateControls = () => {
        const outcome = selectedOutcome();
        const showHeld = outcome === 'held';
        held?.classList.toggle('d-none', !showHeld);
        notHeld?.classList.toggle('d-none', showHeld);
        attested.required = showHeld;
        if (!showHeld) attested.checked = false;
        reason.required = !showHeld;
        if (showHeld) reason.value = '';

        const currentReason = !showHeld ? reason.value.trim() : '';
        const changing = previous !== '' &&
            (outcome !== previous || currentReason !== previousReason.trim());
        correction?.classList.toggle('d-none', !changing);
        correctionReason.required = changing;
        correctionReason.minLength = changing ? 10 : 0;
        if (!changing) correctionReason.value = '';
    };

    modal.addEventListener('show.bs.modal', event => {
        const button = event.relatedTarget?.closest?.('[data-medical-visit-confirm]');
        if (!button) return;

        const id = button.dataset.visitId || '';
        if (!/^\d+$/.test(id)) return;

        const template = form.dataset.actionTemplate || '';
        form.action = template.replace('__RECORD__', id);
        form.reset();
        previous = button.dataset.visitPreviousOutcome || '';
        previousReason = button.dataset.visitPreviousReason || '';
        const radio = form.querySelector(`input[name="outcome"][value="${previous || 'held'}"]`);
        if (radio) radio.checked = true;
        if (previous === 'not_held') reason.value = previousReason;

        title.textContent = `Результат приёма № ${id}`;
        caption.textContent = `${button.dataset.visitDate || 'Дата не указана'} · ${button.dataset.visitDoctor || 'Врач'}`;
        updateControls();
    });

    form.querySelectorAll('[data-medical-visit-outcome]').forEach(radio => {
        radio.addEventListener('change', updateControls);
    });
    reason.addEventListener('input', updateControls);
    form.addEventListener('submit', event => {
        updateControls();
        if (!form.reportValidity()) {
            event.preventDefault();
            return;
        }
        const submit = form.querySelector('[data-medical-visit-submit]');
        if (submit) {
            submit.disabled = true;
            submit.textContent = 'Сохранение…';
        }
    });

    // Deep-link opens the separate log tab and keeps an individual session expanded.
    const query = new URLSearchParams(window.location.search);
    if (query.get('tab') === 'telemedicine-log') {
        const button = document.querySelector('#cardTabs [data-bs-target="#pane-telemedicine-log"]');
        if (button && window.bootstrap?.Tab) {
            window.bootstrap.Tab.getOrCreateInstance(button).show();
        }
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initMedicalVisitAudit, { once: true });
} else {
    initMedicalVisitAudit();
}
