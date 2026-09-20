<script>
    (() => {
        const root = document.querySelector('[data-commission-root]');
        if (!root) return;

        const searchInput = document.getElementById('commissionPatientSearch');
        const resultsBox = document.getElementById('commissionPatientResults');
        const hint = document.getElementById('commissionPatientHint');
        const clearButton = document.getElementById('commissionPatientClear');
        const printButton = document.querySelector('[data-commission-print]');
        const searchUrl = root.dataset.patientSearchUrl || '';
        const pageUrl = root.dataset.pageUrl || window.location.pathname;

        let timer = null;
        let controller = null;

        const escapeHtml = (value) => String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');

        const patientName = (item) => {
            const explicit = String(item?.full_name || item?.text || '').trim();
            if (explicit) return explicit;

            return [item?.surname, item?.name, item?.patronym]
                .filter(Boolean)
                .join(' ')
                .trim();
        };

        const initials = (name) => String(name || '')
            .split(/\s+/)
            .filter(Boolean)
            .slice(0, 2)
            .map((part) => part.charAt(0).toUpperCase())
            .join('') || 'П';

        const closeResults = () => {
            if (!resultsBox) return;
            resultsBox.classList.remove('is-open');
            resultsBox.innerHTML = '';
        };

        const setHint = (text, error = false) => {
            if (!hint) return;
            hint.textContent = text;
            hint.classList.toggle('is-error', Boolean(error));
        };

        const renderResults = (items) => {
            if (!resultsBox) return;
            const list = Array.isArray(items) ? items : [];

            if (!list.length) {
                resultsBox.innerHTML = '<div class="p-3 text-center text-muted small">Пациенты не найдены</div>';
                resultsBox.classList.add('is-open');
                return;
            }

            resultsBox.innerHTML = list.slice(0, 12).map((item, index) => {
                const name = patientName(item);
                const birth = item?.birth_at || item?.birth_date || '';
                const snils = item?.snils || '';
                const meta = [birth, snils].filter(Boolean).join(' · ');

                return `
                    <button type="button" class="commission-patient-result" data-patient-index="${index}">
                        <span class="commission-patient-result__avatar">${escapeHtml(initials(name))}</span>
                        <span class="commission-patient-result__copy">
                            <strong>${escapeHtml(name || 'Пациент')}</strong>
                            <span>${escapeHtml(meta || 'Открыть карточку')}</span>
                        </span>
                        <i class="bi bi-chevron-right text-muted"></i>
                    </button>
                `;
            }).join('');

            resultsBox.classList.add('is-open');

            resultsBox.querySelectorAll('[data-patient-index]').forEach((button) => {
                button.addEventListener('click', () => {
                    const item = list[Number(button.dataset.patientIndex)] || null;
                    const id = Number(item?.id || item?.patient_id || 0);
                    if (!id) return;

                    const target = new URL(pageUrl, window.location.origin);
                    target.searchParams.set('patient_id', String(id));
                    window.location.assign(target.toString());
                });
            });
        };

        const searchPatients = async (term) => {
            const query = String(term || '').trim();
            if (!searchUrl || query.length < 1) {
                closeResults();
                return;
            }

            controller?.abort();
            controller = new AbortController();
            setHint('Ищем пациентов...');

            try {
                const url = new URL(searchUrl, window.location.origin);
                url.searchParams.set('q', query);

                const response = await fetch(url.toString(), {
                    credentials: 'same-origin',
                    signal: controller.signal,
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (!response.ok) throw new Error(`HTTP ${response.status}`);

                const payload = await response.json();
                const items = Array.isArray(payload)
                    ? payload
                    : (payload?.results || payload?.items || payload?.data || []);

                renderResults(items);
                setHint(`Найдено: ${Array.isArray(items) ? items.length : 0}. Выберите пациента.`);
            } catch (error) {
                if (error?.name === 'AbortError') return;
                console.error('[commission patient search]', error);
                closeResults();
                setHint('Не удалось выполнить поиск пациента.', true);
            }
        };

        searchInput?.addEventListener('input', () => {
            window.clearTimeout(timer);
            const query = searchInput.value.trim();

            if (!query) {
                closeResults();
                setHint('Начните вводить фамилию, имя или отчество.');
                return;
            }

            timer = window.setTimeout(() => searchPatients(query), 250);
        });

        searchInput?.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') closeResults();
        });

        clearButton?.addEventListener('click', () => {
            window.location.assign(pageUrl);
        });

        printButton?.addEventListener('click', () => window.print());

        document.addEventListener('click', (event) => {
            if (!event.target.closest('.commission-patient-search')) closeResults();
        });
    })();
</script>
