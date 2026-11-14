(() => {
    'use strict';

    const root = document.querySelector('.patient-identity-page');

    if (!root || root.dataset.autocompleteInitialized === '1') {
        return;
    }

    root.dataset.autocompleteInitialized = '1';

    const addressSuggestUrl = root.dataset.addressSuggestUrl || '';
    const insuranceSuggestUrl = root.dataset.insuranceSuggestUrl || '';

    const escapeHtml = value => String(value ?? '').replace(/[&<>"']/g, char => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;',
    }[char]));

    const localInsuranceOrganizations = [
        { value: 'АО «МАКС-М»', search: 'макс м makc max max-m макс-м максм' },
        { value: 'ООО «Капитал МС»', search: 'капитал мс капитал медицинское страхование' },
        { value: 'АО «СОГАЗ-Мед»', search: 'согаз мед согаз-мед sogaz' },
        { value: 'ООО «АльфаСтрахование-ОМС»', search: 'альфастрахование омс альфа страхование альфастрахование-омс' },
        { value: 'ООО «СК «Ингосстрах-М»', search: 'ингосстрах м ингосстрах-м ingos' },
        { value: 'ООО «РЕСО-Мед»', search: 'ресо мед ресо-мед reso' },
        { value: 'АО «Страховая компания «СОГАЗ-Мед»', search: 'страховая компания согаз мед' },
        { value: 'ООО «ВТБ Медицинское страхование»', search: 'втб медицинское страхование втб мс' },
        { value: 'ООО «СМК РЕСО-Мед»', search: 'смк ресо мед' },
        { value: 'ООО «МСК Медстрах»', search: 'мск медстрах медицинское страхование' },
    ];

    function onlyDigits(value, maxLength = null) {
        let digits = String(value || '').replace(/\D/g, '');

        if (maxLength) {
            digits = digits.slice(0, maxLength);
        }

        return digits;
    }

    function formatSnils(value) {
        const digits = onlyDigits(value, 11);
        const first = digits.slice(0, 3);
        const second = digits.slice(3, 6);
        const third = digits.slice(6, 9);
        const control = digits.slice(9, 11);
        let result = first;

        if (second) result += '-' + second;
        if (third) result += '-' + third;
        if (control) result += ' ' + control;

        return result;
    }

    function formatDepartmentCode(value) {
        const digits = onlyDigits(value, 6);

        if (digits.length <= 3) {
            return digits;
        }

        return digits.slice(0, 3) + '-' + digits.slice(3, 6);
    }

    function formatPhone(value) {
        let digits = onlyDigits(value, 11);

        if (digits.startsWith('8')) {
            digits = '7' + digits.slice(1);
        } else if (digits.startsWith('9')) {
            digits = '7' + digits;
        }

        if (!digits) {
            return '';
        }

        const country = digits.slice(0, 1);
        const code = digits.slice(1, 4);
        const first = digits.slice(4, 7);
        const second = digits.slice(7, 9);
        const third = digits.slice(9, 11);
        let result = '+' + country;

        if (code) {
            result += ' (' + code;
            if (code.length === 3) result += ')';
        }

        if (first) result += ' ' + first;
        if (second) result += '-' + second;
        if (third) result += '-' + third;

        return result;
    }

    function normalizeEmail(value) {
        return String(value || '').replace(/\s+/g, '').toLowerCase();
    }

    function normalizePolicy(value) {
        return String(value || '')
            .replace(/\s+/g, ' ')
            .replace(/[^0-9a-zA-Zа-яА-ЯёЁ№/\- ]/g, '')
            .trim()
            .slice(0, 32);
    }

    function bindMask(selector, formatter) {
        root.querySelectorAll(selector).forEach(field => {
            const apply = () => {
                const next = formatter(field.value, field);

                if (field.value !== next) {
                    field.value = next;
                }
            };

            field.addEventListener('input', apply);
            field.addEventListener('change', apply);
            field.addEventListener('blur', apply);
            apply();
        });
    }

    bindMask('.js-mask-snils', formatSnils);
    bindMask('.js-mask-phone', formatPhone);
    bindMask('.js-mask-email', normalizeEmail);
    bindMask('.js-mask-policy', normalizePolicy);
    bindMask('.js-mask-department-code', formatDepartmentCode);

    // Для российского паспорта серия и номер цифровые. Для иностранных
    // документов сохраняем буквы и другие допустимые символы.
    root.querySelectorAll('form').forEach(form => {
        const typeSelect = form.querySelector('[name="document_type"]');
        const series = form.querySelector('[name="document_series"]');
        const number = form.querySelector('[name="document_number"]');
        const departmentCode = form.querySelector('[name="document_department_code"]');

        if (!typeSelect) {
            return;
        }

        const applyPassportFormat = () => {
            const isRussianPassport = typeSelect.value === 'passport_rf';

            if (series) {
                series.maxLength = isRussianPassport ? 4 : 50;
                if (isRussianPassport) {
                    series.value = onlyDigits(series.value, 4);
                    series.inputMode = 'numeric';
                } else {
                    series.removeAttribute('inputmode');
                }
            }

            if (number) {
                number.maxLength = isRussianPassport ? 6 : 100;
                if (isRussianPassport) {
                    number.value = onlyDigits(number.value, 6);
                    number.inputMode = 'numeric';
                } else {
                    number.removeAttribute('inputmode');
                }
            }

            if (departmentCode && isRussianPassport) {
                departmentCode.value = formatDepartmentCode(departmentCode.value);
            }
        };

        typeSelect.addEventListener('change', applyPassportFormat);
        series?.addEventListener('input', applyPassportFormat);
        number?.addEventListener('input', applyPassportFormat);
        departmentCode?.addEventListener('input', applyPassportFormat);
        applyPassportFormat();
    });

    function buildSuggestUrl(baseUrl, query) {
        const url = new URL(baseUrl, window.location.origin);
        url.searchParams.set('q', query);
        url.searchParams.set('query', query);

        return url.toString();
    }

    async function fetchSuggestions(url, signal) {
        const response = await fetch(url, {
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            signal,
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        return response.json();
    }

    function getSuggestBox(field) {
        const selector = field.dataset.suggestBox || '';

        if (!selector) {
            return null;
        }

        try {
            return root.querySelector(selector);
        } catch {
            return null;
        }
    }

    function hideSuggestBox(box) {
        if (!box) {
            return;
        }

        box.classList.remove('is-visible');
        box.innerHTML = '';
    }

    function showSuggestBox(box, items, onSelect, getMeta = () => '') {
        if (!box || !items.length) {
            hideSuggestBox(box);
            return;
        }

        box.innerHTML = items.slice(0, 8).map((item, index) => {
            const meta = getMeta(item);

            return `
                <button
                    type="button"
                    class="patient-profile-autocomplete-suggest__item"
                    data-index="${index}"
                    role="option"
                >
                    <span>${escapeHtml(item.value)}</span>
                    ${meta ? `<span class="patient-profile-autocomplete-suggest__meta">${escapeHtml(meta)}</span>` : ''}
                </button>
            `;
        }).join('');

        box.classList.add('is-visible');

        box.querySelectorAll('button[data-index]').forEach(button => {
            button.addEventListener('mousedown', event => event.preventDefault());
            button.addEventListener('click', () => {
                const item = items[Number(button.dataset.index)];

                if (item) {
                    onSelect(item);
                }

                hideSuggestBox(box);
            });
        });
    }

    function normalizeAddressSuggestions(payload) {
        const rawItems = Array.isArray(payload)
            ? payload
            : (payload?.suggestions || payload?.data || payload?.items || []);

        return rawItems.map(item => {
            if (typeof item === 'string') {
                return {
                    value: item,
                    unrestricted_value: item,
                    postal_code: '',
                    fias_id: '',
                    city: '',
                    street: '',
                };
            }

            const data = item.data || {};
            const value = item.value
                || item.unrestricted_value
                || item.address
                || item.name
                || data.result
                || '';

            return {
                value,
                unrestricted_value: item.unrestricted_value || value,
                postal_code: item.postal_code || data.postal_code || '',
                fias_id: item.fias_id || data.fias_id || data.fias_code || '',
                city: item.city || data.city_with_type || data.settlement_with_type || '',
                street: item.street || data.street_with_type || '',
                region: item.region || data.region_with_type || '',
            };
        }).filter(item => item.value);
    }

    function addressWithPostalCode(address, postalCode) {
        const value = String(address || '').trim();
        const digits = String(postalCode || '').replace(/\D/g, '').slice(0, 6);

        if (!digits || value.startsWith(digits)) {
            return value;
        }

        return `${digits}, ${value}`;
    }

    function bindAddressAutocomplete(field) {
        const box = getSuggestBox(field);

        if (!box) {
            return;
        }

        let timer = null;
        let controller = null;

        const search = () => {
            const query = field.value.trim();
            clearTimeout(timer);

            if (query.length < 3 || !addressSuggestUrl) {
                hideSuggestBox(box);
                return;
            }

            timer = setTimeout(async () => {
                controller?.abort();
                controller = new AbortController();

                try {
                    const payload = await fetchSuggestions(
                        buildSuggestUrl(addressSuggestUrl, query),
                        controller.signal
                    );

                    const items = normalizeAddressSuggestions(payload);

                    showSuggestBox(
                        box,
                        items,
                        item => {
                            const selectedAddress = item.value || item.unrestricted_value;
                            field.value = addressWithPostalCode(selectedAddress, item.postal_code);
                            field.dataset.unrestrictedValue = item.unrestricted_value || selectedAddress;
                            field.dataset.postalCode = item.postal_code || '';
                            field.dataset.fiasId = item.fias_id || '';
                            field.dispatchEvent(new Event('change', { bubbles: true }));
                        },
                        item => [
                            item.postal_code,
                            item.city,
                            item.street,
                        ].filter(Boolean).join(' · ')
                    );
                } catch (error) {
                    if (error.name !== 'AbortError') {
                        hideSuggestBox(box);
                    }
                }
            }, 300);
        };

        field.addEventListener('input', search);
        field.addEventListener('focus', () => {
            if (field.value.trim().length >= 3) {
                search();
            }
        });
        field.addEventListener('blur', () => {
            setTimeout(() => hideSuggestBox(box), 180);
        });
    }

    function normalizeInsuranceSearch(value) {
        return String(value || '')
            .toLowerCase()
            .replace(/ё/g, 'е')
            .replace(/[«»"'`().,]/g, ' ')
            .replace(/[–—_]/g, '-')
            .replace(/\s+/g, ' ')
            .trim();
    }

    function localInsuranceSuggestions(query) {
        const normalized = normalizeInsuranceSearch(query);
        const compact = normalized.replace(/[\s-]+/g, '');

        if (normalized.length < 2) {
            return [];
        }

        return localInsuranceOrganizations
            .filter(item => {
                const haystack = normalizeInsuranceSearch(`${item.value} ${item.search}`);
                const compactHaystack = haystack.replace(/[\s-]+/g, '');

                return haystack.includes(normalized) || compactHaystack.includes(compact);
            })
            .map(item => ({
                value: item.value,
                unrestricted_value: item.value,
                inn: '',
                kpp: '',
                ogrn: '',
                status: '',
            }));
    }

    function defaultInsuranceSuggestions() {
        return localInsuranceOrganizations.slice(0, 8).map(item => ({
            value: item.value,
            unrestricted_value: item.value,
            inn: '',
            kpp: '',
            ogrn: '',
            status: '',
        }));
    }

    function normalizeInsuranceSuggestions(payload) {
        const rawItems = Array.isArray(payload)
            ? payload
            : (payload?.suggestions || payload?.items || payload?.data || payload?.results || []);

        return rawItems.map(item => {
            if (typeof item === 'string') {
                return {
                    value: item,
                    unrestricted_value: item,
                    inn: '',
                    kpp: '',
                    ogrn: '',
                    status: '',
                };
            }

            const data = item.data || {};
            const name = data.name || {};
            const value = item.value
                || item.unrestricted_value
                || item.name
                || name.short_with_opf
                || name.full_with_opf
                || name.short
                || name.full
                || '';

            return {
                value,
                unrestricted_value: item.unrestricted_value || name.full_with_opf || value,
                inn: item.inn || data.inn || '',
                kpp: item.kpp || data.kpp || '',
                ogrn: item.ogrn || data.ogrn || '',
                status: data.state?.status || item.status || '',
            };
        }).filter(item => item.value);
    }

    function insuranceMeta(item) {
        return [
            item.inn ? `ИНН ${item.inn}` : '',
            item.kpp ? `КПП ${item.kpp}` : '',
            item.status || '',
        ].filter(Boolean).join(' · ');
    }

    function bindInsuranceAutocomplete(field) {
        const box = getSuggestBox(field);

        if (!box) {
            return;
        }

        let timer = null;
        let controller = null;

        const search = () => {
            const query = field.value.trim();
            clearTimeout(timer);

            if (query.length < 2) {
                hideSuggestBox(box);
                return;
            }

            const localItems = localInsuranceSuggestions(query);

            timer = setTimeout(async () => {
                controller?.abort();
                controller = new AbortController();

                try {
                    if (!insuranceSuggestUrl) {
                        showSuggestBox(
                            box,
                            localItems,
                            item => {
                                field.value = item.value || item.unrestricted_value;
                                field.dataset.inn = item.inn || '';
                                field.dataset.kpp = item.kpp || '';
                                field.dispatchEvent(new Event('change', { bubbles: true }));
                            },
                            insuranceMeta
                        );
                        return;
                    }

                    const payload = await fetchSuggestions(
                        buildSuggestUrl(insuranceSuggestUrl, query),
                        controller.signal
                    );

                    const remoteItems = normalizeInsuranceSuggestions(payload);
                    const items = remoteItems.length ? remoteItems : localItems;

                    showSuggestBox(
                        box,
                        items,
                        item => {
                            field.value = item.value || item.unrestricted_value;
                            field.dataset.unrestrictedValue = item.unrestricted_value || field.value;
                            field.dataset.inn = item.inn || '';
                            field.dataset.kpp = item.kpp || '';
                            field.dataset.ogrn = item.ogrn || '';
                            field.dispatchEvent(new Event('change', { bubbles: true }));
                        },
                        insuranceMeta
                    );
                } catch (error) {
                    if (error.name !== 'AbortError') {
                        showSuggestBox(
                            box,
                            localItems,
                            item => {
                                field.value = item.value || item.unrestricted_value;
                                field.dispatchEvent(new Event('change', { bubbles: true }));
                            },
                            insuranceMeta
                        );
                    }
                }
            }, 300);
        };

        field.addEventListener('input', search);
        field.addEventListener('focus', () => {
            if (field.value.trim().length >= 2) {
                search();
            }
        });
        field.addEventListener('dblclick', () => {
            showSuggestBox(
                box,
                defaultInsuranceSuggestions(),
                item => {
                    field.value = item.value;
                    field.dispatchEvent(new Event('change', { bubbles: true }));
                },
                insuranceMeta
            );
        });
        field.addEventListener('blur', () => {
            setTimeout(() => hideSuggestBox(box), 180);
        });
    }

    root.querySelectorAll('.js-patient-address-autocomplete')
        .forEach(bindAddressAutocomplete);

    root.querySelectorAll('.js-patient-insurance-autocomplete')
        .forEach(bindInsuranceAutocomplete);

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            root.dataset.autocompleteInitialized = '1';
        }, { once: true });
    }
})();
