(() => {
    'use strict';

    const root = document.getElementById('medicalCommissionRoot');
    if (!root) {
        return;
    }

    const api = {
        patientSearch: root.dataset.patientSearchUrl || '',
        doctors: root.dataset.doctorsUrl || '',
        psyDoctors: root.dataset.psyDoctorsUrl || '',
    };

    const patientInput = root.querySelector('[data-patient-search-input]');
    const patientIdInput = root.querySelector('[data-commission-patient-id]');
    const patientSuggestions = root.querySelector('[data-patient-suggestions]');
    const patientSearchHint = root.querySelector('[data-patient-search-hint]');
    const patientClear = root.querySelector('[data-patient-clear]');
    const patientCard = root.querySelector('[data-patient-card]');
    const patientName = root.querySelector('[data-patient-name]');
    const patientBirth = root.querySelector('[data-patient-birth]');
    const patientDiagnosis = root.querySelector('[data-patient-diagnosis]');

    const doctorInput = root.querySelector('[data-doctor-search-input]');
    const doctorSuggestions = root.querySelector('[data-doctor-suggestions]');
    const doctorSearchHint = root.querySelector('[data-doctor-search-hint]');
    const membersList = root.querySelector('[data-members-list]');
    const membersEmpty = root.querySelector('[data-members-empty]');

    const meetingType = root.querySelector('[data-meeting-type]');
    const unscheduledWrap = root.querySelector('[data-unscheduled-basis-wrap]');

    const totalMembersNode = root.querySelector('[data-total-members]');
    const presentMembersNode = root.querySelector('[data-present-members]');
    const quorumRequiredNode = root.querySelector('[data-quorum-required]');
    const quorumStateNode = root.querySelector('[data-quorum-state]');
    const votesForNode = root.querySelector('[data-votes-for]');
    const votesRequiredNode = root.querySelector('[data-votes-required]');
    const decisionStateNode = root.querySelector('[data-decision-state]');

    let patientAbortController = null;
    let patientTimer = null;
    let doctorsPromise = null;
    let doctors = [];
    let members = readJson('commissionInitialMembers', []);

    function readJson(id, fallback) {
        const node = document.getElementById(id);
        if (!node) return fallback;

        try {
            return JSON.parse(node.textContent || 'null') ?? fallback;
        } catch (error) {
            console.error(`[medical-commissions] invalid JSON in #${id}`, error);
            return fallback;
        }
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function setSuggestionsVisible(node, visible) {
        if (!node) return;
        node.classList.toggle('d-none', !visible);
    }

    function patientLabel(patient) {
        return String(patient?.full_name || patient?.text || '').trim();
    }

    function patientBirthLabel(patient) {
        const value = patient?.birth_at_display || patient?.birth_at || '';
        return value ? `Дата рождения: ${value}` : 'Дата рождения не указана';
    }

    function patientDiagnosisLabel(patient) {
        if (patient?.diagnosis_label) {
            return `Диагноз: ${patient.diagnosis_label}`;
        }

        const code = String(patient?.diagnosis?.code || '').trim();
        const title = String(patient?.diagnosis?.title || '').trim();

        if (!code && !title) {
            return 'Диагноз: не установлен';
        }

        return `Диагноз: ${code}${code && title ? ' — ' : ''}${title}`;
    }

    function applyPatient(patient) {
        if (!patient) return;

        const name = patientLabel(patient);

        if (patientIdInput) patientIdInput.value = patient.id ?? '';
        if (patientInput) patientInput.value = name;
        if (patientName) patientName.textContent = name || 'Пациент не выбран';
        if (patientBirth) patientBirth.textContent = patientBirthLabel(patient);
        if (patientDiagnosis) patientDiagnosis.textContent = patientDiagnosisLabel(patient);

        patientCard?.classList.remove('is-empty');
        patientClear?.classList.remove('d-none');
        setSuggestionsVisible(patientSuggestions, false);

        if (patientSearchHint) {
            patientSearchHint.textContent = 'Пациент выбран.';
        }
    }

    function clearPatient() {
        if (patientIdInput) patientIdInput.value = '';
        if (patientInput) patientInput.value = '';
        if (patientName) patientName.textContent = 'Пациент не выбран';
        if (patientBirth) patientBirth.textContent = 'Выберите пациента из поиска';
        if (patientDiagnosis) patientDiagnosis.textContent = 'Диагноз: не установлен';

        patientCard?.classList.add('is-empty');
        patientClear?.classList.add('d-none');
        setSuggestionsVisible(patientSuggestions, false);

        if (patientSearchHint) {
            patientSearchHint.textContent = 'Поиск по фамилии, имени и отчеству.';
        }

        patientInput?.focus();
    }

    function renderPatientSuggestions(items) {
        if (!patientSuggestions) return;

        if (!items.length) {
            patientSuggestions.innerHTML = '<div class="commission-suggestion-state">Пациенты не найдены</div>';
            setSuggestionsVisible(patientSuggestions, true);
            return;
        }

        patientSuggestions.innerHTML = items.map((patient, index) => {
            const name = patientLabel(patient);
            const birth = patient?.birth_at_display || patient?.birth_at || 'дата рождения не указана';
            const diagnosis = patient?.diagnosis_label || 'диагноз не установлен';

            return `
                <button type="button" class="commission-suggestion" data-patient-index="${index}">
                    <span class="commission-suggestion__title">${escapeHtml(name)}</span>
                    <span class="commission-suggestion__meta">${escapeHtml(birth)} · ${escapeHtml(diagnosis)}</span>
                </button>
            `;
        }).join('');

        setSuggestionsVisible(patientSuggestions, true);

        patientSuggestions.querySelectorAll('[data-patient-index]').forEach((button) => {
            button.addEventListener('click', () => {
                const patient = items[Number(button.dataset.patientIndex)] || null;
                applyPatient(patient);
            });
        });
    }

    async function searchPatients(query) {
        query = String(query || '').trim();

        if (!api.patientSearch || !query) {
            setSuggestionsVisible(patientSuggestions, false);
            return;
        }

        patientAbortController?.abort();
        patientAbortController = new AbortController();

        if (patientSearchHint) {
            patientSearchHint.textContent = 'Поиск…';
        }

        try {
            const url = new URL(api.patientSearch, window.location.origin);
            url.searchParams.set('q', query);

            const response = await fetch(url.toString(), {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
                signal: patientAbortController.signal,
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const payload = await response.json();
            const items = Array.isArray(payload?.results) ? payload.results : [];

            // ВАЖНО: searchPatient() уже возвращает full_name,
            // birth_at_display, diagnosis и diagnosis_label.
            // Второго запроса patient-context здесь нет.
            renderPatientSuggestions(items);

            if (patientSearchHint) {
                patientSearchHint.textContent = items.length
                    ? `Найдено: ${items.length}`
                    : 'Пациенты не найдены.';
            }
        } catch (error) {
            if (error.name === 'AbortError') return;

            console.error('[medical-commissions] patient search failed', error);
            if (patientSearchHint) {
                patientSearchHint.textContent = 'Не удалось выполнить поиск пациента.';
            }
        }
    }

    patientInput?.addEventListener('input', () => {
        clearTimeout(patientTimer);
        const query = patientInput.value.trim();

        if (!query) {
            setSuggestionsVisible(patientSuggestions, false);
            if (patientSearchHint) {
                patientSearchHint.textContent = 'Поиск по фамилии, имени и отчеству.';
            }
            return;
        }

        patientTimer = setTimeout(() => searchPatients(query), 250);
    });

    patientClear?.addEventListener('click', clearPatient);

    async function loadDoctors() {
        if (doctorsPromise) return doctorsPromise;

        doctorsPromise = Promise.all(
            [api.doctors, api.psyDoctors]
                .filter(Boolean)
                .map(async (url) => {
                    const response = await fetch(url, {
                        headers: {
                            Accept: 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        credentials: 'same-origin',
                    });

                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}`);
                    }

                    const payload = await response.json();
                    return Array.isArray(payload?.items) ? payload.items : [];
                })
        ).then((groups) => {
            const unique = new Map();

            groups.flat().forEach((doctor) => {
                if (doctor?.id == null) return;
                unique.set(String(doctor.id), doctor);
            });

            doctors = Array.from(unique.values());
            return doctors;
        }).catch((error) => {
            console.error('[medical-commissions] doctors load failed', error);
            if (doctorSearchHint) {
                doctorSearchHint.textContent = 'Не удалось загрузить список врачей.';
            }
            return [];
        });

        return doctorsPromise;
    }

    function doctorFullName(doctor) {
        return [doctor?.surname, doctor?.name, doctor?.patronym]
            .filter(Boolean)
            .join(' ')
            .trim();
    }

    function normalizeDoctorForMember(doctor) {
        return {
            doctor_id: Number(doctor.id),
            name: doctorFullName(doctor),
            speciality: String(doctor.status || '').trim(),
            role: 'member',
            present: true,
            vote: null,
            special_opinion: '',
        };
    }

    function addDoctor(doctor) {
        const id = Number(doctor?.id);
        if (!id || members.some((member) => Number(member.doctor_id) === id)) {
            return;
        }

        members.push(normalizeDoctorForMember(doctor));
        renderMembers();
        setSuggestionsVisible(doctorSuggestions, false);
        if (doctorInput) doctorInput.value = '';
    }

    function renderDoctorSuggestions(items) {
        if (!doctorSuggestions) return;

        if (!items.length) {
            doctorSuggestions.innerHTML = '<div class="commission-suggestion-state">Врачи не найдены</div>';
            setSuggestionsVisible(doctorSuggestions, true);
            return;
        }

        doctorSuggestions.innerHTML = items.map((doctor, index) => `
            <button type="button" class="commission-suggestion" data-doctor-index="${index}">
                <span class="commission-suggestion__title">${escapeHtml(doctorFullName(doctor))}</span>
                <span class="commission-suggestion__meta">${escapeHtml(doctor.status || 'Специальность не указана')}</span>
            </button>
        `).join('');

        setSuggestionsVisible(doctorSuggestions, true);

        doctorSuggestions.querySelectorAll('[data-doctor-index]').forEach((button) => {
            button.addEventListener('click', () => {
                addDoctor(items[Number(button.dataset.doctorIndex)]);
            });
        });
    }

    doctorInput?.addEventListener('focus', loadDoctors);
    doctorInput?.addEventListener('input', async () => {
        const query = doctorInput.value.trim().toLocaleLowerCase('ru');
        const list = await loadDoctors();

        if (!query) {
            setSuggestionsVisible(doctorSuggestions, false);
            return;
        }

        const filtered = list.filter((doctor) => {
            const haystack = `${doctorFullName(doctor)} ${doctor.status || ''}`.toLocaleLowerCase('ru');
            return haystack.includes(query);
        }).slice(0, 12);

        renderDoctorSuggestions(filtered);
    });

    function roleOptions(value) {
        const options = [
            ['chairman', 'Председатель'],
            ['deputy', 'Заместитель председателя'],
            ['member', 'Член комиссии'],
        ];

        if (value && !options.some(([key]) => key === value)) {
            options.unshift([value, value]);
        }

        return options.map(([key, label]) =>
            `<option value="${escapeHtml(key)}" ${key === value ? 'selected' : ''}>${escapeHtml(label)}</option>`
        ).join('');
    }

    function voteOptions(value) {
        const options = [
            ['', 'Не указано'],
            ['for', 'За'],
            ['against', 'Против'],
            ['abstained', 'Воздержался'],
        ];

        if (value && !options.some(([key]) => key === value)) {
            options.push([value, value]);
        }

        return options.map(([key, label]) =>
            `<option value="${escapeHtml(key)}" ${String(key) === String(value || '') ? 'selected' : ''}>${escapeHtml(label)}</option>`
        ).join('');
    }

    function renderMembers() {
        if (!membersList) return;

        membersEmpty?.classList.toggle('d-none', members.length > 0);

        membersList.innerHTML = members.map((member, index) => `
            <article class="commission-member" data-member-index="${index}">
                <div class="commission-member__main">
                    <div class="commission-member__identity">
                        <strong>${escapeHtml(member.name || 'Врач')}</strong>
                        <span>${escapeHtml(member.speciality || 'Специальность не указана')}</span>
                    </div>
                    <button type="button" class="commission-member__remove" data-member-remove="${index}" aria-label="Удалить врача">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>

                <input type="hidden" name="members[${index}][doctor_id]" value="${escapeHtml(member.doctor_id)}">

                <div class="commission-member__controls">
                    <label>
                        <span>Роль</span>
                        <select name="members[${index}][role]" data-member-role="${index}">
                            ${roleOptions(member.role || 'member')}
                        </select>
                    </label>

                    <label class="commission-check commission-check--compact">
                        <input type="hidden" name="members[${index}][present]" value="0">
                        <input type="checkbox" name="members[${index}][present]" value="1" data-member-present="${index}" ${member.present ? 'checked' : ''}>
                        <span>Присутствует</span>
                    </label>

                    <label>
                        <span>Голос</span>
                        <select name="members[${index}][vote]" data-member-vote="${index}" ${member.present ? '' : 'disabled'}>
                            ${voteOptions(member.vote)}
                        </select>
                    </label>
                </div>

                <label class="commission-member__opinion">
                    <span>Особое мнение</span>
                    <textarea name="members[${index}][special_opinion]" rows="2" data-member-opinion="${index}">${escapeHtml(member.special_opinion || '')}</textarea>
                </label>
            </article>
        `).join('');

        membersList.querySelectorAll('[data-member-remove]').forEach((button) => {
            button.addEventListener('click', () => {
                members.splice(Number(button.dataset.memberRemove), 1);
                renderMembers();
            });
        });

        membersList.querySelectorAll('[data-member-role]').forEach((select) => {
            select.addEventListener('change', () => {
                const member = members[Number(select.dataset.memberRole)];
                if (member) member.role = select.value;
            });
        });

        membersList.querySelectorAll('[data-member-present]').forEach((checkbox) => {
            checkbox.addEventListener('change', () => {
                const index = Number(checkbox.dataset.memberPresent);
                const member = members[index];
                if (!member) return;

                member.present = checkbox.checked;
                if (!member.present) member.vote = null;
                renderMembers();
            });
        });

        membersList.querySelectorAll('[data-member-vote]').forEach((select) => {
            select.addEventListener('change', () => {
                const member = members[Number(select.dataset.memberVote)];
                if (member) {
                    member.vote = select.value || null;
                    updateSummary();
                }
            });
        });

        membersList.querySelectorAll('[data-member-opinion]').forEach((textarea) => {
            textarea.addEventListener('input', () => {
                const member = members[Number(textarea.dataset.memberOpinion)];
                if (member) member.special_opinion = textarea.value;
            });
        });

        updateSummary();
    }

    function setSummaryState(node, text, tone = 'neutral') {
        if (!node) return;

        node.textContent = text;
        node.classList.remove(
            'is-success',
            'is-danger',
            'is-warning'
        );

        if (tone !== 'neutral') {
            node.classList.add(`is-${tone}`);
        }
    }

    function updateSummary() {
        const total = members.length;
        const present = members.filter((member) => Boolean(member.present)).length;

        // UI-only indicator. Сервер остаётся источником истины при сохранении.
        const quorumRequired = total > 0 ? Math.floor(total / 2) + 1 : 0;
        const votesFor = members.filter((member) => member.present && member.vote === 'for').length;
        const votesRequired = present > 0 ? Math.floor(present / 2) + 1 : 0;

        if (totalMembersNode) totalMembersNode.textContent = String(total);
        if (presentMembersNode) presentMembersNode.textContent = String(present);
        if (quorumRequiredNode) quorumRequiredNode.textContent = String(quorumRequired);
        if (votesForNode) votesForNode.textContent = String(votesFor);
        if (votesRequiredNode) votesRequiredNode.textContent = String(votesRequired);

        if (!total) {
            setSummaryState(
                quorumStateNode,
                'Добавьте состав комиссии'
            );
        } else if (present >= quorumRequired) {
            setSummaryState(
                quorumStateNode,
                'Кворум есть',
                'success'
            );
        } else {
            setSummaryState(
                quorumStateNode,
                'Кворума нет',
                'danger'
            );
        }

        const presentMembers = members.filter(
            (member) => member.present
        );

        const allVoted =
            presentMembers.length > 0
            && presentMembers.every(
                (member) => Boolean(member.vote)
            );

        if (!allVoted) {
            setSummaryState(
                decisionStateNode,
                'Голосование не заполнено',
                presentMembers.length ? 'warning' : 'neutral'
            );
        } else if (votesFor >= votesRequired) {
            setSummaryState(
                decisionStateNode,
                'Решение принято',
                'success'
            );
        } else {
            setSummaryState(
                decisionStateNode,
                'Решение не принято',
                'danger'
            );
        }
    }

    function syncMeetingType() {
        if (!meetingType || !unscheduledWrap) return;
        unscheduledWrap.classList.toggle('d-none', meetingType.value !== 'unscheduled');
    }

    meetingType?.addEventListener('change', syncMeetingType);
    syncMeetingType();
    renderMembers();

    document.addEventListener('click', (event) => {
        if (!event.target.closest('[data-patient-search]')) {
            setSuggestionsVisible(patientSuggestions, false);
        }

        if (!event.target.closest('.commission-doctor-search')) {
            setSuggestionsVisible(doctorSuggestions, false);
        }
    });
})();
