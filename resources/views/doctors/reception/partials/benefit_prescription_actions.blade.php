<div id="benefitPrescriptionActions" class="benefit-rx-actions" hidden>
    <span id="benefitPrescriptionBusy" class="benefit-rx-busy" role="status" hidden>
        <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
        <span>Сохранение…</span>
    </span>
    <button type="button" id="benefitSaveDraft" class="btn btn-outline-primary presc-btn"
            @disabled(empty($benefitPrescriptions['can_write']))>
        <i class="bi bi-save" aria-hidden="true"></i> Сохранить черновик
    </button>
    <button type="button" id="benefitIssue" class="btn btn-primary presc-btn"
            @disabled(empty($benefitPrescriptions['can_issue']))>
        <i class="bi bi-printer" aria-hidden="true"></i> Сохранить и открыть бланк
    </button>
    <button type="button" id="benefitNewDraft" class="btn btn-outline-primary presc-btn" hidden>
        <i class="bi bi-plus-lg" aria-hidden="true"></i> Новый льготный рецепт
    </button>
</div>
