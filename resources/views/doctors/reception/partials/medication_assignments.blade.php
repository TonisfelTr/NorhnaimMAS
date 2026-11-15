{{-- PATIENT_TREATMENT_WIZARD_V5_BUTTON --}}
<section class="medication-assignment-panel mb-4">
    <div class="medication-assignment-panel__head">
        <div>
            <div class="h5 mb-1">
                <i class="bi bi-capsule-pill text-primary me-1"></i>
                Назначения пациенту
            </div>
            <div class="small text-muted">
                Действующая схема лечения и инструкции, которые пациент видит в личном кабинете.
            </div>
        </div>

        <button id="medicationWizardOpenButton"
            type="button"
            class="btn btn-primary btn-sm"
            data-bs-toggle="modal"
            data-bs-target="#prescriptionModal"
        >
            <i class="bi bi-journal-medical me-1"></i>
            Рецепт и назначение
        </button>
    </div>

    <div class="medication-assignment-doctor-list">
        @forelse($medicationAssignments as $assignment)
            <article @class([
                'medication-assignment-doctor-card',
                'is-active' => $assignment->status === 'active',
                'is-muted' => $assignment->status !== 'active',
            ])>
                <div class="medication-assignment-doctor-card__main">
                    <div class="d-flex gap-2 align-items-center flex-wrap mb-1">
                        <strong>{{ $assignment->drug_name_snapshot }}</strong>
                        <span @class([
                            'badge',
                            'text-bg-success' => $assignment->status === 'active',
                            'text-bg-secondary' => $assignment->status === 'completed',
                            'text-bg-danger' => $assignment->status === 'cancelled',
                        ])>
                            {{ $assignment->status_label }}
                        </span>
                    </div>

                    <div class="small text-muted">
                        {{ $assignment->dosage }} · {{ $assignment->schedule }}
                    </div>

                    <div class="small mt-2">
                        <b>Показание:</b> {{ $assignment->indication_text }}
                    </div>

                    <div class="small mt-1">
                        <b>Пациенту:</b> {{ $assignment->doctor_instructions }}
                    </div>

                    <div class="medication-assignment-doctor-card__overdose">
                        <i class="bi bi-exclamation-octagon-fill"></i>
                        <span>
                            <b>Передозировка:</b>
                            {{ $assignment->overdose_actions }}
                        </span>
                    </div>

                    <div class="small text-muted mt-2">
                        {{ $assignment->clinic_name_snapshot ?: 'Клиника не указана' }}
                        · {{ $assignment->doctor_name_snapshot }}
                        · {{ $assignment->created_at?->format('d.m.Y H:i') }}
                    </div>
                </div>

                @if($assignment->status === 'active')
                    <div class="medication-assignment-doctor-card__actions">
                        <form
                            method="post"
                            action="{{ route('doctors.patients.medication-assignments.status', [$patient->id, $assignment->id]) }}"
                        >
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="completed">
                            <button type="submit" class="btn btn-sm btn-outline-success">
                                Завершить
                            </button>
                        </form>

                        <form
                            method="post"
                            action="{{ route('doctors.patients.medication-assignments.status', [$patient->id, $assignment->id]) }}"
                        >
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="cancelled">
                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                Отменить
                            </button>
                        </form>
                    </div>
                @endif
            </article>
        @empty
            <div class="medication-assignment-doctor-empty">
                Активных и завершённых назначений пока нет.
            </div>
        @endforelse
    </div>
</section>

<div
    class="modal fade mc-modal-ui mc-modal-ui--teal"
    id="medicationAssignmentModal"
    tabindex="-1"
    aria-hidden="true"
    data-drug-search-url="{{ route('api.drugs.search') }}"
    data-drug-reference-url="{{ route('doctors.patients.medication-assignments.reference', [$patient->id, '__DRUG__']) }}"
>
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <form
            class="modal-content"
            method="post"
            action="{{ route('doctors.patients.medication-assignments.store', $patient->id) }}"
            id="medicationAssignmentForm"
        >
            @csrf

            <div class="modal-header">
                <div>
                    <h5 class="modal-title mb-1">Новое назначение пациенту</h5>
                    <div class="small text-muted">
                        Инструкция должна быть понятна пациенту без медицинской карты и устных пояснений.
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
            </div>

            <div class="modal-body">
                <div class="alert alert-warning d-flex gap-2 align-items-start">
                    <i class="bi bi-shield-exclamation mt-1"></i>
                    <div>
                        <b>Действия при передозировке обязательны.</b>
                        Не переносите справочную информацию автоматически в инструкцию пациенту:
                        подтвердите и отредактируйте текст для конкретного назначения.
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-xl-5">
                        <section class="medication-assignment-form-section">
                            <h6>Препарат и схема</h6>

                            <div class="mb-3 medication-drug-search">
                                <label class="form-label label-required" for="medicationDrugSearch">
                                    Препарат
                                </label>
                                <input
                                    type="search"
                                    id="medicationDrugSearch"
                                    class="form-control"
                                    autocomplete="off"
                                    placeholder="Начните вводить название или МНН"
                                >
                                <input type="hidden" name="drug_id" id="medicationDrugId" required>
                                <div
                                    id="medicationDrugResults"
                                    class="medication-drug-search__results"
                                    hidden
                                ></div>
                                <div id="medicationDrugSelected" class="form-text">
                                    Препарат не выбран
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label label-required">Начало</label>
                                    <input
                                        type="date"
                                        name="starts_on"
                                        class="form-control"
                                        value="{{ now()->format('Y-m-d') }}"
                                        required
                                    >
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Окончание</label>
                                    <input type="date" name="ends_on" class="form-control">
                                </div>
                            </div>

                            <div class="mt-3">
                                <label class="form-label label-required">Дозировка</label>
                                <input
                                    type="text"
                                    name="dosage"
                                    class="form-control"
                                    maxlength="255"
                                    placeholder="Например: 300 мг"
                                    required
                                >
                            </div>

                            <div class="mt-3">
                                <label class="form-label label-required">Режим приёма</label>
                                <textarea
                                    name="schedule"
                                    class="form-control"
                                    rows="3"
                                    placeholder="Например: утром и вечером после еды"
                                    required
                                ></textarea>
                            </div>
                        </section>

                        <section class="medication-reference-card mt-3">
                            <div class="medication-reference-card__title">
                                Справочник препарата
                            </div>
                            <div class="small text-muted mb-2">
                                Эти данные помогают врачу проверить назначение, но не заменяют индивидуальную инструкцию.
                            </div>
                            <div id="medicationReferenceState" class="small text-muted">
                                Выберите препарат.
                            </div>
                            <div id="medicationPatientWarnings" hidden></div>
                            <div id="medicationReferenceContent"></div>
                        </section>
                    </div>

                    <div class="col-xl-7">
                        <section class="medication-assignment-form-section">
                            <h6>Инструкция пациенту</h6>

                            <div class="mb-3">
                                <label class="form-label label-required">
                                    Индивидуальная инструкция врача
                                </label>
                                <textarea
                                    name="doctor_instructions"
                                    class="form-control"
                                    rows="4"
                                    placeholder="Как принимать препарат, важные ограничения и особенности именно для этого пациента"
                                    required
                                ></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label label-required">
                                    Показание — почему назначен препарат
                                </label>
                                <textarea
                                    name="indication_text"
                                    id="medicationIndicationText"
                                    class="form-control"
                                    rows="3"
                                    required
                                ></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label label-required">
                                    Противопоказания и ограничения
                                </label>
                                <textarea
                                    name="contraindications_text"
                                    id="medicationContraindicationsText"
                                    class="form-control"
                                    rows="4"
                                    placeholder="Укажите противопоказания и ограничения, которые должен учитывать пациент"
                                    required
                                ></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">
                                    Возможные нежелательные реакции
                                </label>
                                <textarea
                                    name="side_effects_text"
                                    id="medicationSideEffectsText"
                                    class="form-control"
                                    rows="3"
                                ></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">
                                    Если пропущен приём
                                </label>
                                <textarea
                                    name="missed_dose_instructions"
                                    class="form-control"
                                    rows="3"
                                    placeholder="Что делать пациенту при пропуске дозы"
                                ></textarea>
                            </div>

                            <div class="mb-3 medication-overdose-field">
                                <label class="form-label label-required">
                                    <i class="bi bi-exclamation-octagon-fill me-1"></i>
                                    Действия при подозрении на передозировку
                                </label>
                                <textarea
                                    name="overdose_actions"
                                    class="form-control"
                                    rows="4"
                                    placeholder="Чёткая последовательность действий пациента"
                                    required
                                ></textarea>
                                <div class="form-text">
                                    Не ограничивайтесь фразой «обратитесь к врачу» — укажите понятный план действий.
                                </div>
                            </div>

                            <div>
                                <label class="form-label">
                                    Симптомы/ситуации, при которых нужна срочная связь с врачом
                                </label>
                                <textarea
                                    name="warning_signs"
                                    class="form-control"
                                    rows="3"
                                ></textarea>
                            </div>
                        </section>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                    Отмена
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check2-circle me-1"></i>
                    Сохранить назначение
                </button>
            </div>
        </form>
    </div>
</div>

<script>
(() => {
    const modal = document.getElementById('medicationAssignmentModal');
    if (!modal) return;

    const searchInput = document.getElementById('medicationDrugSearch');
    const drugIdInput = document.getElementById('medicationDrugId');
    const results = document.getElementById('medicationDrugResults');
    const selected = document.getElementById('medicationDrugSelected');
    const referenceState = document.getElementById('medicationReferenceState');
    const referenceContent = document.getElementById('medicationReferenceContent');
    const patientWarnings = document.getElementById('medicationPatientWarnings');
    const searchUrl = modal.dataset.drugSearchUrl;
    const referenceTemplate = modal.dataset.drugReferenceUrl;

    let timer = null;
    let abort = null;

    const escapeHtml = (value) => String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');

    function listText(items, formatter) {
        if (!items || !items.length) return 'Нет данных в справочнике.';
        return items.map(formatter).join('\n');
    }

    function renderReference(data) {
        const indicationText = listText(
            data.indications,
            (item) => item.code + ' — ' + item.title
        );

        const contraindicationText = listText(
            data.contraindications,
            (item) => item.type_label + ': ' + item.name
                + (item.description ? ' — ' + item.description : '')
        );

        const sideEffectsText = listText(
            data.side_effects,
            (item) => item.name
                + (item.description ? ' — ' + item.description : '')
        );

        referenceState.hidden = true;

        referenceContent.innerHTML = `
            <div class="medication-reference-block">
                <div class="medication-reference-block__head">
                    <b>Показания</b>
                    <button type="button" data-copy-reference="indication">Вставить</button>
                </div>
                <pre>${escapeHtml(indicationText)}</pre>
            </div>
            <div class="medication-reference-block">
                <div class="medication-reference-block__head">
                    <b>Противопоказания</b>
                    <button type="button" data-copy-reference="contra">Вставить</button>
                </div>
                <pre>${escapeHtml(contraindicationText)}</pre>
            </div>
            <div class="medication-reference-block">
                <div class="medication-reference-block__head">
                    <b>Нежелательные реакции</b>
                    <button type="button" data-copy-reference="side">Вставить</button>
                </div>
                <pre>${escapeHtml(sideEffectsText)}</pre>
            </div>
        `;

        const map = {
            indication: [
                document.getElementById('medicationIndicationText'),
                indicationText,
            ],
            contra: [
                document.getElementById('medicationContraindicationsText'),
                contraindicationText,
            ],
            side: [
                document.getElementById('medicationSideEffectsText'),
                sideEffectsText,
            ],
        };

        referenceContent.querySelectorAll('[data-copy-reference]').forEach((button) => {
            button.addEventListener('click', () => {
                const pair = map[button.dataset.copyReference];
                if (!pair || !pair[0]) return;
                pair[0].value = pair[1] === 'Нет данных в справочнике.'
                    ? ''
                    : pair[1];
                pair[0].focus();
            });
        });

        if (data.patient_warnings && data.patient_warnings.length) {
            patientWarnings.hidden = false;
            patientWarnings.className = 'alert alert-danger py-2 px-3 small';
            patientWarnings.innerHTML = '<b>Совпадения с данными пациента:</b><ul class="mb-0 mt-1">'
                + data.patient_warnings.map((item) =>
                    '<li>' + escapeHtml(item.label) + ': '
                    + escapeHtml(item.message) + '</li>'
                ).join('')
                + '</ul>';
        } else {
            patientWarnings.hidden = true;
            patientWarnings.replaceChildren();
        }
    }

    async function loadReference(drugId) {
        referenceState.hidden = false;
        referenceState.textContent = 'Загружаем справочные данные…';
        referenceContent.replaceChildren();
        patientWarnings.hidden = true;

        const url = referenceTemplate.replace('__DRUG__', String(drugId));
        const response = await fetch(url, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.message || 'Не удалось загрузить справочные данные.');
        }

        renderReference(data);
    }

    function selectDrug(item) {
        drugIdInput.value = item.id;
        searchInput.value = item.name || item.text || '';
        selected.textContent = [
            item.name || item.text || '',
            item.latin_name ? 'МНН: ' + item.latin_name : '',
        ].filter(Boolean).join(' · ');
        results.hidden = true;
        results.replaceChildren();

        loadReference(item.id).catch((error) => {
            referenceState.hidden = false;
            referenceState.textContent = error.message;
        });
    }

    async function searchDrugs(term) {
        if (abort) abort.abort();
        abort = new AbortController();

        const params = new URLSearchParams({
            q: term,
            page: '1',
        });

        const response = await fetch(searchUrl + '?' + params.toString(), {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
            signal: abort.signal,
        });

        const data = await response.json();
        if (!response.ok) throw new Error(data.message || 'Ошибка поиска препаратов.');

        results.replaceChildren();

        (data.results || []).forEach((item) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'medication-drug-search__item';

            const name = document.createElement('strong');
            name.textContent = item.name || item.text;

            const latin = document.createElement('small');
            latin.textContent = item.latin_name
                ? 'МНН: ' + item.latin_name
                : '';

            button.append(name, latin);
            button.addEventListener('click', () => selectDrug(item));
            results.appendChild(button);
        });

        if (!(data.results || []).length) {
            const empty = document.createElement('div');
            empty.className = 'medication-drug-search__empty';
            empty.textContent = 'Препараты не найдены.';
            results.appendChild(empty);
        }

        results.hidden = false;
    }

    searchInput.addEventListener('input', () => {
        drugIdInput.value = '';
        selected.textContent = 'Препарат не выбран';
        referenceState.hidden = false;
        referenceState.textContent = 'Выберите препарат.';
        referenceContent.replaceChildren();
        patientWarnings.hidden = true;

        window.clearTimeout(timer);
        const term = searchInput.value.trim();

        if (term.length < 2) {
            results.hidden = true;
            results.replaceChildren();
            if (abort) abort.abort();
            return;
        }

        timer = window.setTimeout(() => {
            searchDrugs(term).catch((error) => {
                if (error.name === 'AbortError') return;
                results.hidden = false;
                results.innerHTML = '<div class="medication-drug-search__empty">'
                    + escapeHtml(error.message)
                    + '</div>';
            });
        }, 250);
    });

    document.addEventListener('click', (event) => {
        if (!event.target.closest('.medication-drug-search')) {
            results.hidden = true;
        }
    });
})();
</script>
