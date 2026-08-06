            document.addEventListener('DOMContentLoaded', function () {
                const $ = window.jQuery;

                if (!$ || !$.fn || !$.fn.select2) {
                    console.error('Select2 не подключён.');
                    return;
                }

                const $doctorSelect = $('#dynamicsDoctorSelect');
                const $patientSelect = $('#dynamicsPatientSelect');

                function extractItems(payload) {
                    if (Array.isArray(payload)) {
                        return payload;
                    }

                    const candidates = [
                        payload?.results,
                        payload?.items,
                        payload?.patients,
                        payload?.doctors,
                        payload?.data,
                        payload?.data?.results,
                        payload?.data?.items,
                        payload?.data?.patients,
                        payload?.data?.doctors,
                        payload?.data?.data
                    ];

                    return candidates.find(Array.isArray) || [];
                }

                function itemId(item) {
                    if (item === null || item === undefined) {
                        return null;
                    }

                    if (typeof item !== 'object') {
                        return item;
                    }

                    return item.id
                        ?? item.value
                        ?? item.patient_id
                        ?? item.doctor_id
                        ?? item.user_id
                        ?? item.employee_id
                        ?? null;
                }

                function itemFullName(item) {
                    if (item === null || item === undefined) {
                        return '';
                    }

                    if (typeof item !== 'object') {
                        return String(item);
                    }

                    return item.text
                        || item.fio
                        || item.label
                        || item.full_name
                        || item.fullname
                        || item.patient_name
                        || item.doctor_name
                        || item.name_full
                        || [
                            item.surname || item.last_name,
                            item.name || item.first_name,
                            item.patronymic
                                || item.patronym
                                || item.middle_name
                        ].filter(Boolean).join(' ')
                        || String(itemId(item) || '');
                }

                function prepareResults(payload) {
                    const results = extractItems(payload)
                        .map(function (item) {
                            return {
                                id: itemId(item),
                                text: itemFullName(item)
                            };
                        })
                        .filter(function (item) {
                            return item.id !== undefined
                                && item.id !== null
                                && item.id !== ''
                                && item.text !== '';
                        });

                    return {
                        results: results,
                        pagination: {
                            more: Boolean(
                                payload?.pagination?.more
                                || payload?.more
                                || payload?.data?.pagination?.more
                            )
                        }
                    };
                }

                function destroySelect2($element) {
                    if ($element.length && $element.hasClass('select2-hidden-accessible')) {
                        $element.select2('destroy');
                    }
                }

                if ($doctorSelect.is('select')) {
                    destroySelect2($doctorSelect);

                    $doctorSelect.select2({
                        width: '100%',
                        placeholder: 'Введите ФИО врача',
                        allowClear: true,
                        minimumInputLength: 1,
                        ajax: {
                            url: $doctorSelect.data('search-url'),
                            dataType: 'json',
                            delay: 250,
                            cache: false,
                            data: function (params) {
                                const term = String(params.term || '').trim();

                                return {
                                    q: term,
                                    search: term,
                                    term: term,
                                    query: term,
                                    page: params.page || 1
                                };
                            },
                            processResults: function (payload) {
                                console.debug('Ответ поиска врачей:', payload);
                                return prepareResults(payload);
                            }
                        },
                        language: {
                            inputTooShort: function () {
                                return 'Введите хотя бы 1 символ';
                            },
                            noResults: function () {
                                return 'Врач не найден';
                            },
                            searching: function () {
                                return 'Поиск врача…';
                            },
                            errorLoading: function () {
                                return 'Не удалось загрузить список врачей';
                            }
                        }
                    });
                }

                destroySelect2($patientSelect);

                $patientSelect.select2({
                    width: '100%',
                    placeholder: 'Введите ФИО пациента',
                    allowClear: true,
                    minimumInputLength: 1,
                    ajax: {
                        url: $patientSelect.data('search-url'),
                        dataType: 'json',
                        delay: 250,
                        cache: false,
                        data: function (params) {
                            const term = String(params.term || '').trim();
                            const data = {
                                q: term,
                                search: term,
                                term: term,
                                query: term,
                                page: params.page || 1
                            };
                            const doctorId = $doctorSelect.val();

                            if (doctorId) {
                                data.doctor_id = doctorId;
                            }

                            return data;
                        },
                        processResults: function (payload) {
                            console.debug('Ответ поиска пациентов:', payload);
                            return prepareResults(payload);
                        }
                    },
                    language: {
                        inputTooShort: function () {
                            return 'Введите хотя бы 1 символ';
                        },
                        noResults: function () {
                            return 'Пациент не найден';
                        },
                        searching: function () {
                            return 'Поиск пациента…';
                        },
                        errorLoading: function () {
                            return 'Не удалось загрузить список пациентов';
                        }
                    }
                });

                if ($doctorSelect.is('select')) {
                    $doctorSelect.on('change', function () {
                        $patientSelect.val(null).trigger('change');
                    });
                }
            });
