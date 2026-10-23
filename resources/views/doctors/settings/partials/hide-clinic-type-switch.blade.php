{{--
    Убираем устаревший выбор «Амбулатория / Стационар» из интерфейса настроек.
    Текущий clinic_type определяется при регистрации организации и не меняется здесь.
    Входные поля оставляем в DOM (скрыты), чтобы не сломать унаследованные JS-обработчики.
    Серверное запрещение смены типа остаётся за LockClinicTypeMiddleware.
--}}
<style>
    /* Быстрое скрытие наиболее типичных обёрток (Firefox/Chromium поддерживают :has). */
    #clinicSettingsForm .clinic-type-switch,
    #clinicSettingsForm .clinic-type-selector,
    #clinicSettingsForm .clinic-settings__clinic-type,
    #clinicSettingsForm [data-clinic-type-selector] {
        display: none !important;
    }
</style>
<script>
    (function () {
        'use strict';

        function normalize(value) {
            return String(value || '').replace(/\s+/g, ' ').trim();
        }

        function hideClinicTypeSelector() {
            const form = document.getElementById('clinicSettingsForm');
            if (!form) return;

            const headings = Array.from(form.querySelectorAll(
                'label, .settings-label, h2, h3, h4, strong, .settings-panel__title, .form-label, span'
            )).filter(element => normalize(element.textContent) === 'Тип клиники');

            const inputs = Array.from(form.querySelectorAll('[name="clinic_type"]'));
            let target = null;

            for (const heading of headings) {
                let current = heading.parentElement;
                while (current && current !== form) {
                    const text = normalize(current.textContent);
                    const fields = Array.from(current.querySelectorAll('input, select, textarea'));
                    const onlyClinicTypeFields = fields.every(field => field.name === 'clinic_type');
                    const containsBothChoices = text.includes('Амбулатория') && text.includes('Стационар');

                    // Скрываем только компактный блок выбора, но не соседние настройки.
                    if (containsBothChoices && onlyClinicTypeFields && text.length <= 1500) {
                        target = current;
                        break;
                    }
                    current = current.parentElement;
                }
                if (target) break;
            }

            // Запасной поиск, если заголовок оформлен нетипично.
            if (!target && inputs.length) {
                let current = inputs[0].parentElement;
                while (current && current !== form) {
                    if (inputs.every(input => current.contains(input))) {
                        const fields = Array.from(current.querySelectorAll('input, select, textarea'));
                        const text = normalize(current.textContent);
                        if (fields.every(field => field.name === 'clinic_type') &&
                            text.includes('Амбулатория') && text.includes('Стационар') &&
                            text.length <= 1500) {
                            target = current;
                            break;
                        }
                    }
                    current = current.parentElement;
                }
            }

            if (!target || target === form) return;

            // Скрываем, но не удаляем из DOM: старый JS может читать :checked.
            target.setAttribute('hidden', '');
            target.setAttribute('aria-hidden', 'true');
            target.style.setProperty('display', 'none', 'important');
            target.querySelectorAll('input, select, button, textarea, a[href]').forEach(element => {
                element.tabIndex = -1;
            });

            // Заголовок/подсказка иногда являются соседями сетки карточек.
            // Удаляем только точные подписи этого переключателя.
            headings.forEach(heading => {
                if (!target.contains(heading)) heading.style.setProperty('display', 'none', 'important');
            });
            form.querySelectorAll('.settings-field-hint, .form-text, small, p').forEach(note => {
                const content = normalize(note.textContent);
                if (!target.contains(note) && content.startsWith('Выбор определяет структуру панели клиники')) {
                    note.style.setProperty('display', 'none', 'important');
                }
            });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', hideClinicTypeSelector, { once: true });
        } else {
            hideClinicTypeSelector();
        }
    })();
</script>
