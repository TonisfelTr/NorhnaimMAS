(function () {
    'use strict';

    const MONTHS = [
        'Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь',
        'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'
    ];
    const WEEKDAYS = ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс'];

    const pad = value => String(value).padStart(2, '0');

    function sameDay(a, b) {
        return a && b
            && a.getFullYear() === b.getFullYear()
            && a.getMonth() === b.getMonth()
            && a.getDate() === b.getDate();
    }

    function parseDateValue(value, mode) {
        if (!value) return null;

        const match = mode === 'datetime'
            ? String(value).match(/^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2})/)
            : String(value).match(/^(\d{4})-(\d{2})-(\d{2})/);

        if (!match) return null;

        const year = Number(match[1]);
        const month = Number(match[2]) - 1;
        const day = Number(match[3]);
        const hour = mode === 'datetime' ? Number(match[4]) : 0;
        const minute = mode === 'datetime' ? Number(match[5]) : 0;
        const result = new Date(year, month, day, hour, minute, 0, 0);

        return Number.isNaN(result.getTime()) ? null : result;
    }

    function parseDisplayValue(value, mode) {
        const normalized = String(value || '').trim();
        if (!normalized) return null;

        const match = mode === 'datetime'
            ? normalized.match(/^(\d{1,2})[.\/-](\d{1,2})[.\/-](\d{4})(?:\s*,?\s*)(\d{1,2}):(\d{2})$/)
            : normalized.match(/^(\d{1,2})[.\/-](\d{1,2})[.\/-](\d{4})$/);

        if (!match) return null;

        const day = Number(match[1]);
        const month = Number(match[2]) - 1;
        const year = Number(match[3]);
        const hour = mode === 'datetime' ? Number(match[4]) : 0;
        const minute = mode === 'datetime' ? Number(match[5]) : 0;

        if (
            year < 1900
            || month < 0
            || month > 11
            || day < 1
            || day > 31
            || hour < 0
            || hour > 23
            || minute < 0
            || minute > 59
        ) {
            return null;
        }

        const result = new Date(year, month, day, hour, minute, 0, 0);

        if (
            result.getFullYear() !== year
            || result.getMonth() !== month
            || result.getDate() !== day
            || result.getHours() !== hour
            || result.getMinutes() !== minute
        ) {
            return null;
        }

        return result;
    }

    function parseBoundary(value, mode, endOfDay = false) {
        if (!value) return null;
        if (value === 'today') {
            const today = new Date();
            return new Date(
                today.getFullYear(),
                today.getMonth(),
                today.getDate(),
                endOfDay ? 23 : 0,
                endOfDay ? 59 : 0,
                endOfDay ? 59 : 0,
                endOfDay ? 999 : 0
            );
        }

        const parsed = parseDateValue(value, mode);
        if (!parsed) return null;

        if (mode === 'date') {
            parsed.setHours(endOfDay ? 23 : 0, endOfDay ? 59 : 0, endOfDay ? 59 : 0, endOfDay ? 999 : 0);
        }

        return parsed;
    }

    function canonical(date, mode) {
        if (!date) return '';
        const datePart = `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`;
        return mode === 'datetime'
            ? `${datePart}T${pad(date.getHours())}:${pad(date.getMinutes())}`
            : datePart;
    }

    function displayValue(date, mode) {
        if (!date) return '';
        const datePart = `${pad(date.getDate())}.${pad(date.getMonth() + 1)}.${date.getFullYear()}`;
        return mode === 'datetime'
            ? `${datePart}, ${pad(date.getHours())}:${pad(date.getMinutes())}`
            : datePart;
    }

    function maskTemplate(mode) {
        return mode === 'datetime'
            ? '__.__.____, __:__'
            : '__.__.____';
    }

    function digitPositions(mode) {
        return mode === 'datetime'
            ? [0, 1, 3, 4, 6, 7, 8, 9, 12, 13, 15, 16]
            : [0, 1, 3, 4, 6, 7, 8, 9];
    }

    function replaceChar(value, index, char) {
        return value.slice(0, index) + char + value.slice(index + 1);
    }

    function createButton(html, className, title) {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = className;
        button.innerHTML = html;
        if (title) button.title = title;
        return button;
    }

    class ReceptionCalendarPicker {
        constructor(input, options = {}) {
            this.input = input;
            this.options = options;
            this.mode = options.mode === 'datetime' || options.enableTime ? 'datetime' : 'date';
            this.minuteIncrement = Number(options.minuteIncrement || 5);
            this.selected = parseDateValue(input.value, this.mode);
            this.view = this.selected
                ? new Date(this.selected.getFullYear(), this.selected.getMonth(), 1)
                : new Date(new Date().getFullYear(), new Date().getMonth(), 1);
            this.minDate = parseBoundary(options.minDate || input.min, this.mode, false);
            this.maxDate = parseBoundary(options.maxDate || input.max, this.mode, true);
            this.isOpen = false;
            this.build();
            this.syncDisplay();
            this.render();

            if (typeof options.onReady === 'function') {
                options.onReady(this.selected ? [this.selected] : [], this.input.value, this);
            }
        }

        build() {
            const originalClass = this.input.className;
            this.wrapper = document.createElement('div');
            this.wrapper.className = 'reception-calendar-field';

            this.altInput = document.createElement('input');
            this.altInput.type = 'text';
            this.altInput.readOnly = false;
            this.altInput.autocomplete = 'off';
            this.altInput.inputMode = this.mode === 'datetime' ? 'text' : 'numeric';
            this.altInput.spellcheck = false;
            this.altInput.className = `${originalClass} reception-date-display`.trim();
            this.altInput.placeholder = this.mode === 'datetime' ? 'дд.мм.гггг, чч:мм' : 'дд.мм.гггг';
            this.altInput.required = this.input.required;
            this.altInput.setAttribute('aria-haspopup', 'dialog');
            this.altInput.setAttribute('aria-expanded', 'false');

            this.input.parentNode.insertBefore(this.wrapper, this.input);
            this.wrapper.appendChild(this.input);
            this.wrapper.appendChild(this.altInput);
            this.input.type = 'hidden';

            this.popup = document.createElement('div');
            this.popup.className = 'reception-calendar-popover';
            this.popup.setAttribute('role', 'dialog');
            this.popup.setAttribute('aria-label', this.mode === 'datetime' ? 'Выбор даты и времени' : 'Выбор даты');
            this.wrapper.appendChild(this.popup);

            this.altInput.addEventListener('focus', () => this.open());
            this.altInput.addEventListener('click', () => this.open());
            this.altInput.addEventListener('input', () => {
                this.manualDirty = true;
                this.altInput.setCustomValidity('');
            });
            this.altInput.addEventListener('change', () => {
                this.commitTypedValue(true);
            });
            this.altInput.addEventListener('blur', () => {
                window.setTimeout(() => {
                    if (this.manualDirty) {
                        this.commitTypedValue(true);
                    }
                }, 0);
            });
            this.altInput.addEventListener('paste', event => {
                const clipboard = event.clipboardData || window.clipboardData;
                const text = clipboard ? clipboard.getData('text') : '';

                if (!text) return;

                event.preventDefault();
                this.pasteMaskedValue(text);
            });
            this.altInput.addEventListener('keydown', event => {
                const noModifier = !event.ctrlKey && !event.metaKey && !event.altKey;

                if (noModifier && /^\d$/.test(event.key)) {
                    event.preventDefault();
                    this.replaceDigitAtCaret(event.key);
                    return;
                }

                if (noModifier && (event.key === 'Backspace' || event.key === 'Delete')) {
                    event.preventDefault();
                    this.eraseDigitAtCaret(event.key === 'Backspace');
                    return;
                }

                if (event.key === 'Enter') {
                    event.preventDefault();
                    if (this.commitTypedValue(true)) {
                        this.close();
                    }
                } else if (event.key === 'ArrowDown' && event.altKey) {
                    event.preventDefault();
                    this.open();
                } else if (event.key === 'Escape') {
                    event.preventDefault();
                    this.manualDirty = false;
                    this.altInput.setCustomValidity('');
                    this.syncDisplay();
                    this.close();
                }
            });

            document.addEventListener('mousedown', event => {
                if (this.isOpen && !this.wrapper.contains(event.target)) {
                    this.close();
                }
            });
        }

        editableMaskValue() {
            const template = maskTemplate(this.mode);
            const current = this.altInput.value;

            if (!current) {
                return template;
            }

            if (current.length === template.length) {
                let normalized = current;
                for (let i = 0; i < template.length; i++) {
                    if (template[i] !== '_') {
                        normalized = replaceChar(normalized, i, template[i]);
                    }
                }
                return normalized;
            }

            const parsed = parseDisplayValue(current, this.mode);
            return parsed ? displayValue(parsed, this.mode) : template;
        }

        markManualEdit() {
            this.manualDirty = true;
            this.altInput.setCustomValidity('');
            this.altInput.dispatchEvent(new Event('input', {bubbles: true}));
        }

        clearSelectedDigitRange(value, start, end) {
            if (start === end) return value;

            let result = value;
            digitPositions(this.mode).forEach(position => {
                if (position >= start && position < end) {
                    result = replaceChar(result, position, '_');
                }
            });
            return result;
        }

        nextDigitPosition(from, includeCurrent = true) {
            const positions = digitPositions(this.mode);
            return positions.find(position => includeCurrent ? position >= from : position > from);
        }

        previousDigitPosition(from) {
            const positions = digitPositions(this.mode);
            for (let i = positions.length - 1; i >= 0; i--) {
                if (positions[i] < from) return positions[i];
            }
            return null;
        }

        setCaret(position) {
            window.requestAnimationFrame(() => {
                this.altInput.setSelectionRange(position, position);
            });
        }

        replaceDigitAtCaret(digit) {
            let value = this.editableMaskValue();
            let start = this.altInput.selectionStart ?? 0;
            const end = this.altInput.selectionEnd ?? start;

            value = this.clearSelectedDigitRange(value, start, end);

            const position = this.nextDigitPosition(start, true);
            if (position === undefined) return;

            value = replaceChar(value, position, digit);
            this.altInput.value = value;
            this.markManualEdit();

            const next = this.nextDigitPosition(position, false);
            this.setCaret(next === undefined ? position + 1 : next);
        }

        eraseDigitAtCaret(backward) {
            if (!this.altInput.value) return;

            let value = this.editableMaskValue();
            const start = this.altInput.selectionStart ?? 0;
            const end = this.altInput.selectionEnd ?? start;
            let caret = start;

            if (start !== end) {
                value = this.clearSelectedDigitRange(value, start, end);
                const first = this.nextDigitPosition(start, true);
                caret = first === undefined ? start : first;
            } else {
                const position = backward
                    ? this.previousDigitPosition(start)
                    : this.nextDigitPosition(start, true);

                if (position === null || position === undefined) return;

                value = replaceChar(value, position, '_');
                caret = position;
            }

            const hasDigit = /\d/.test(value);
            this.altInput.value = hasDigit ? value : '';
            this.markManualEdit();
            this.setCaret(hasDigit ? caret : 0);
        }

        pasteMaskedValue(text) {
            const directDisplay = parseDisplayValue(text, this.mode);
            const directCanonical = parseDateValue(text, this.mode);
            const direct = directDisplay || directCanonical;

            if (direct) {
                this.selected = direct;
                this.view = new Date(direct.getFullYear(), direct.getMonth(), 1);
                this.input.value = canonical(direct, this.mode);
                this.syncDisplay();
                this.fireChange();
                if (this.isOpen) this.render();
                return;
            }

            const digits = String(text).replace(/\D/g, '');
            if (!digits) return;

            const positions = digitPositions(this.mode);
            let value = this.editableMaskValue();
            const selectionStart = this.altInput.selectionStart ?? 0;
            const selectionEnd = this.altInput.selectionEnd ?? selectionStart;
            value = this.clearSelectedDigitRange(value, selectionStart, selectionEnd);

            let slotIndex = digits.length >= positions.length
                ? 0
                : positions.findIndex(position => position >= selectionStart);

            if (slotIndex < 0) slotIndex = positions.length - 1;

            let lastPosition = positions[slotIndex] ?? 0;
            for (const digit of digits) {
                if (slotIndex >= positions.length) break;
                const position = positions[slotIndex++];
                value = replaceChar(value, position, digit);
                lastPosition = position;
            }

            this.altInput.value = value;
            this.markManualEdit();
            const next = positions[slotIndex];
            this.setCaret(next === undefined ? lastPosition + 1 : next);
        }

        open() {
            document.querySelectorAll('.reception-calendar-popover.is-open').forEach(element => {
                if (element !== this.popup) element.classList.remove('is-open');
            });
            this.render();
            this.popup.classList.add('is-open');
            this.altInput.setAttribute('aria-expanded', 'true');
            this.isOpen = true;
        }

        close() {
            this.popup.classList.remove('is-open');
            this.altInput.setAttribute('aria-expanded', 'false');
            this.isOpen = false;
        }

        toggle() {
            this.isOpen ? this.close() : this.open();
        }

        set(key, value) {
            if (key === 'minDate') {
                this.minDate = parseBoundary(value, this.mode, false);
            } else if (key === 'maxDate') {
                this.maxDate = parseBoundary(value, this.mode, true);
            }
            this.render();
        }

        setDate(value, triggerChange = false) {
            const parsed = value instanceof Date ? new Date(value.getTime()) : parseDateValue(value, this.mode);
            if (!parsed) {
                this.clear(triggerChange);
                return;
            }
            this.selected = this.clamp(parsed);
            this.view = new Date(this.selected.getFullYear(), this.selected.getMonth(), 1);
            this.commit(triggerChange);
            this.render();
        }

        clear(triggerChange = false) {
            this.selected = null;
            this.input.value = '';
            this.syncDisplay();
            this.render();
            if (triggerChange) this.fireChange();
        }

        fireChange() {
            if (typeof this.options.onChange === 'function') {
                this.options.onChange(this.selected ? [this.selected] : [], this.input.value, this);
            }
        }

        manualValueError(date) {
            if (!date) {
                return this.mode === 'datetime'
                    ? 'Введите дату и время в формате ДД.ММ.ГГГГ, ЧЧ:ММ.'
                    : 'Введите дату в формате ДД.ММ.ГГГГ.';
            }

            if (this.minDate && date < this.minDate) {
                return this.mode === 'datetime'
                    ? `Дата и время не могут быть раньше ${displayValue(this.minDate, 'datetime')}.`
                    : `Дата не может быть раньше ${displayValue(this.minDate, 'date')}.`;
            }

            if (this.maxDate && date > this.maxDate) {
                return this.mode === 'datetime'
                    ? `Дата и время не могут быть позже ${displayValue(this.maxDate, 'datetime')}.`
                    : `Дата не может быть позже ${displayValue(this.maxDate, 'date')}.`;
            }

            return '';
        }

        commitTypedValue(triggerChange = true) {
            const raw = this.altInput.value.trim();

            if (!raw) {
                this.manualDirty = false;
                this.altInput.setCustomValidity('');
                this.clear(triggerChange);
                return !this.altInput.required;
            }

            const parsed = parseDisplayValue(raw, this.mode);
            const error = this.manualValueError(parsed);

            if (error) {
                this.altInput.setCustomValidity(error);
                return false;
            }

            this.altInput.setCustomValidity('');
            this.manualDirty = false;
            this.selected = parsed;
            this.view = new Date(parsed.getFullYear(), parsed.getMonth(), 1);
            this.input.value = canonical(parsed, this.mode);
            this.syncDisplay();

            if (triggerChange) this.fireChange();
            if (this.isOpen) this.render();

            return true;
        }

        commit(triggerChange = true) {
            if (!this.selected) return;
            this.selected = this.clamp(this.selected);
            this.input.value = canonical(this.selected, this.mode);
            this.syncDisplay();
            if (triggerChange) this.fireChange();
        }

        syncDisplay() {
            this.manualDirty = false;
            this.altInput.setCustomValidity('');
            this.altInput.value = displayValue(this.selected, this.mode);
        }

        clamp(date) {
            let result = new Date(date.getTime());
            if (this.minDate && result < this.minDate) result = new Date(this.minDate.getTime());
            if (this.maxDate && result > this.maxDate) result = new Date(this.maxDate.getTime());
            return result;
        }

        dayAllowed(date) {
            const start = new Date(date.getFullYear(), date.getMonth(), date.getDate(), 0, 0, 0, 0);
            const end = new Date(date.getFullYear(), date.getMonth(), date.getDate(), 23, 59, 59, 999);
            if (this.minDate && end < this.minDate) return false;
            if (this.maxDate && start > this.maxDate) return false;
            return true;
        }

        yearBounds() {
            const now = new Date().getFullYear();
            if (this.mode === 'datetime') {
                return [this.minDate ? this.minDate.getFullYear() : now, Math.max(now + 5, this.maxDate ? this.maxDate.getFullYear() : now + 5)];
            }
            return [1900, this.maxDate ? this.maxDate.getFullYear() : now];
        }

        render() {
            const year = this.view.getFullYear();
            const month = this.view.getMonth();
            const [minYear, maxYear] = this.yearBounds();

            this.popup.innerHTML = '';

            const header = document.createElement('div');
            header.className = 'reception-calendar-header';

            const prev = createButton('<span aria-hidden="true">‹</span>', 'reception-calendar-nav', 'Предыдущий месяц');
            const next = createButton('<span aria-hidden="true">›</span>', 'reception-calendar-nav', 'Следующий месяц');
            prev.addEventListener('click', () => {
                this.view = new Date(year, month - 1, 1);
                this.render();
            });
            next.addEventListener('click', () => {
                this.view = new Date(year, month + 1, 1);
                this.render();
            });

            const title = document.createElement('div');
            title.className = 'reception-calendar-title';

            const monthSelect = document.createElement('select');
            monthSelect.className = 'reception-calendar-select';
            MONTHS.forEach((label, index) => {
                const option = document.createElement('option');
                option.value = String(index);
                option.textContent = label;
                option.selected = index === month;
                monthSelect.appendChild(option);
            });
            monthSelect.addEventListener('change', () => {
                this.view = new Date(year, Number(monthSelect.value), 1);
                this.render();
            });

            const yearSelect = document.createElement('select');
            yearSelect.className = 'reception-calendar-select reception-calendar-year';
            for (let y = maxYear; y >= minYear; y--) {
                const option = document.createElement('option');
                option.value = String(y);
                option.textContent = String(y);
                option.selected = y === year;
                yearSelect.appendChild(option);
            }
            yearSelect.addEventListener('change', () => {
                this.view = new Date(Number(yearSelect.value), month, 1);
                this.render();
            });

            title.append(monthSelect, yearSelect);
            header.append(prev, title, next);
            this.popup.appendChild(header);

            const weekdays = document.createElement('div');
            weekdays.className = 'reception-calendar-weekdays';
            WEEKDAYS.forEach(label => {
                const item = document.createElement('div');
                item.textContent = label;
                weekdays.appendChild(item);
            });
            this.popup.appendChild(weekdays);

            const grid = document.createElement('div');
            grid.className = 'reception-calendar-grid';
            const first = new Date(year, month, 1);
            const mondayIndex = (first.getDay() + 6) % 7;
            const daysInMonth = new Date(year, month + 1, 0).getDate();
            const prevMonthDays = new Date(year, month, 0).getDate();
            const total = Math.ceil((mondayIndex + daysInMonth) / 7) * 7;
            const today = new Date();

            for (let cell = 0; cell < total; cell++) {
                let dayNumber;
                let cellMonth = month;
                let cellYear = year;
                let outside = false;

                if (cell < mondayIndex) {
                    dayNumber = prevMonthDays - mondayIndex + cell + 1;
                    cellMonth = month - 1;
                    outside = true;
                } else if (cell >= mondayIndex + daysInMonth) {
                    dayNumber = cell - (mondayIndex + daysInMonth) + 1;
                    cellMonth = month + 1;
                    outside = true;
                } else {
                    dayNumber = cell - mondayIndex + 1;
                }

                const cellDate = new Date(cellYear, cellMonth, dayNumber, 12, 0, 0, 0);
                const button = createButton(String(dayNumber), 'reception-calendar-day');
                if (outside) button.classList.add('is-outside');
                if (sameDay(cellDate, today)) button.classList.add('is-today');
                if (sameDay(cellDate, this.selected)) button.classList.add('is-selected');

                if (!this.dayAllowed(cellDate)) {
                    button.disabled = true;
                    button.classList.add('is-disabled');
                } else {
                    button.addEventListener('click', () => {
                        const previous = this.selected || new Date();
                        const nextDate = new Date(
                            cellDate.getFullYear(),
                            cellDate.getMonth(),
                            cellDate.getDate(),
                            this.mode === 'datetime' ? previous.getHours() : 0,
                            this.mode === 'datetime' ? previous.getMinutes() : 0,
                            0,
                            0
                        );
                        this.selected = this.clamp(nextDate);
                        this.view = new Date(cellDate.getFullYear(), cellDate.getMonth(), 1);
                        this.commit(true);
                        if (this.mode === 'date') this.close();
                        else this.render();
                    });
                }
                grid.appendChild(button);
            }
            this.popup.appendChild(grid);

            if (this.mode === 'datetime') {
                this.renderTime();
            }

            this.renderFooter();
        }

        renderTime() {
            const selected = this.selected || this.clamp(new Date());
            const time = document.createElement('div');
            time.className = 'reception-calendar-time';

            const icon = document.createElement('span');
            icon.className = 'reception-calendar-time-icon';
            icon.innerHTML = '<span aria-hidden="true">◷</span>';

            const hour = document.createElement('select');
            hour.className = 'reception-calendar-time-select';
            for (let h = 0; h < 24; h++) {
                const option = document.createElement('option');
                option.value = String(h);
                option.textContent = pad(h);
                option.selected = h === selected.getHours();
                hour.appendChild(option);
            }

            const minute = document.createElement('select');
            minute.className = 'reception-calendar-time-select';
            const minuteValues = [];
            for (let m = 0; m < 60; m += this.minuteIncrement) {
                minuteValues.push(m);
            }
            if (!minuteValues.includes(selected.getMinutes())) {
                minuteValues.push(selected.getMinutes());
                minuteValues.sort((a, b) => a - b);
            }
            minuteValues.forEach(m => {
                const option = document.createElement('option');
                option.value = String(m);
                option.textContent = pad(m);
                option.selected = m === selected.getMinutes();
                minute.appendChild(option);
            });

            const separator = document.createElement('span');
            separator.className = 'reception-calendar-time-separator';
            separator.textContent = ':';

            const applyTime = () => {
                const base = this.selected || selected;
                this.selected = this.clamp(new Date(
                    base.getFullYear(), base.getMonth(), base.getDate(),
                    Number(hour.value), Number(minute.value), 0, 0
                ));
                this.commit(true);
            };
            hour.addEventListener('change', applyTime);
            minute.addEventListener('change', applyTime);

            time.append(icon, hour, separator, minute);
            this.popup.appendChild(time);
        }

        renderFooter() {
            const footer = document.createElement('div');
            footer.className = 'reception-calendar-footer';

            const left = document.createElement('div');
            left.className = 'reception-calendar-footer-left';
            const todayButton = createButton('Сегодня', 'reception-calendar-link');
            const clearButton = createButton('Очистить', 'reception-calendar-link is-muted');
            todayButton.addEventListener('click', () => {
                const now = this.clamp(new Date());
                if (this.mode === 'date') now.setHours(0, 0, 0, 0);
                this.selected = now;
                this.view = new Date(now.getFullYear(), now.getMonth(), 1);
                this.commit(true);
                if (this.mode === 'date') this.close();
                else this.render();
            });
            clearButton.addEventListener('click', () => {
                this.clear(true);
                this.close();
            });
            left.append(todayButton, clearButton);
            footer.appendChild(left);

            if (this.mode === 'datetime') {
                const done = createButton('Готово', 'reception-calendar-done');
                done.addEventListener('click', () => {
                    if (this.manualDirty && !this.commitTypedValue(true)) {
                        this.altInput.reportValidity();
                        return;
                    }
                    if (!this.selected) {
                        this.selected = this.clamp(new Date());
                        this.commit(true);
                    }
                    this.close();
                });
                footer.appendChild(done);
            }

            this.popup.appendChild(footer);
        }
    }

    window.ReceptionCalendar = {
        create(input, options = {}) {
            return new ReceptionCalendarPicker(input, options);
        }
    };
})();
