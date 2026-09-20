import { EditorView, minimalSetup } from 'codemirror';
import { redo, undo } from '@codemirror/commands';
import { placeholder } from '@codemirror/view';

function createToolbarButton({ action, icon, label, modifier = '' }) {
    const button = document.createElement('button');
    button.type = 'button';
    button.className = `military-codemirror-toolbar__button${modifier ? ` ${modifier}` : ''}`;
    button.dataset.editorAction = action;
    button.title = label;
    button.setAttribute('aria-label', label);

    const iconElement = document.createElement('i');
    iconElement.className = `bi ${icon}`;
    iconElement.setAttribute('aria-hidden', 'true');
    button.append(iconElement);

    // Не даём кнопке забрать фокус до того, как обработаем выделение в редакторе.
    button.addEventListener('mousedown', (event) => event.preventDefault());

    return button;
}

function createToolbarSeparator() {
    const separator = document.createElement('span');
    separator.className = 'military-codemirror-toolbar__separator';
    separator.setAttribute('aria-hidden', 'true');

    return separator;
}

function replaceSelection(editor, value) {
    const selection = editor.state.selection.main;

    editor.dispatch({
        changes: {
            from: selection.from,
            to: selection.to,
            insert: value,
        },
        selection: {
            anchor: selection.from + value.length,
        },
        scrollIntoView: true,
    });

    editor.focus();
}

function selectedLines(editor) {
    const selection = editor.state.selection.main;
    const document = editor.state.doc;
    const firstLine = document.lineAt(selection.from);
    let lastLine = document.lineAt(selection.to);

    // Выделение до начала следующей строки не должно менять эту следующую строку.
    if (selection.to > selection.from && selection.to === lastLine.from) {
        lastLine = document.line(Math.max(firstLine.number, lastLine.number - 1));
    }

    const lines = [];

    for (let lineNumber = firstLine.number; lineNumber <= lastLine.number; lineNumber += 1) {
        lines.push(document.line(lineNumber));
    }

    return lines;
}

function toggleList(editor, type) {
    const lines = selectedLines(editor);
    const bulletPattern = /^\s*[•*-]\s+/u;
    const numberedPattern = /^\s*\d+[.)]\s+/u;
    const targetPattern = type === 'bullet' ? bulletPattern : numberedPattern;
    const meaningfulLines = lines.filter((line) => line.text.trim() !== '');
    const shouldRemove = meaningfulLines.length > 0
        && meaningfulLines.every((line) => targetPattern.test(line.text));

    let listNumber = 1;
    const changes = lines.map((line) => {
        const indentation = line.text.match(/^\s*/u)?.[0] || '';
        let content = line.text.slice(indentation.length);

        if (content.trim() === '') {
            return null;
        }

        if (shouldRemove) {
            content = type === 'bullet'
                ? content.replace(/^[•*-]\s+/u, '')
                : content.replace(/^\d+[.)]\s+/u, '');
        } else {
            content = content.replace(/^(?:[•*-]\s+|\d+[.)]\s+)/u, '');
            content = type === 'bullet'
                ? `• ${content}`
                : `${listNumber}. ${content}`;
            listNumber += 1;
        }

        return {
            from: line.from,
            to: line.to,
            insert: `${indentation}${content}`,
        };
    }).filter(Boolean);

    if (changes.length > 0) {
        editor.dispatch({
            changes,
            scrollIntoView: true,
        });
    }

    editor.focus();
}

function updateExpandControl(button, isExpanded) {
    const icon = button.querySelector('i');
    const label = isExpanded ? 'Свернуть редактор' : 'Развернуть редактор';

    if (icon) {
        icon.className = `bi ${isExpanded ? 'bi-arrows-angle-contract' : 'bi-arrows-angle-expand'}`;
    }

    button.classList.toggle('is-active', isExpanded);
    button.title = label;
    button.setAttribute('aria-label', label);
    button.setAttribute('aria-pressed', String(isExpanded));
}

function setExpandedEditor(shell, button, shouldExpand) {
    if (shouldExpand) {
        document.querySelectorAll('.military-codemirror-shell.is-expanded').forEach((expandedShell) => {
            if (expandedShell === shell) return;

            expandedShell.classList.remove('is-expanded');
            const expandedButton = expandedShell.querySelector('[data-editor-action="expand"]');

            if (expandedButton) {
                updateExpandControl(expandedButton, false);
            }
        });
    }

    shell.classList.toggle('is-expanded', shouldExpand);
    updateExpandControl(button, shouldExpand);
    document.body.classList.toggle(
        'has-expanded-medical-editor',
        Boolean(document.querySelector('.military-codemirror-shell.is-expanded')),
    );
}

function buildToolbar(editor, shell, label) {
    const toolbar = document.createElement('div');
    toolbar.className = 'military-codemirror-toolbar';
    toolbar.setAttribute('role', 'toolbar');
    toolbar.setAttribute('aria-label', `Инструменты поля «${label}»`);

    const undoButton = createToolbarButton({
        action: 'undo',
        icon: 'bi-arrow-counterclockwise',
        label: 'Отменить последнее изменение',
    });
    undoButton.addEventListener('click', () => {
        undo(editor);
        editor.focus();
    });

    const redoButton = createToolbarButton({
        action: 'redo',
        icon: 'bi-arrow-clockwise',
        label: 'Повторить отменённое изменение',
    });
    redoButton.addEventListener('click', () => {
        redo(editor);
        editor.focus();
    });

    const bulletButton = createToolbarButton({
        action: 'bullet-list',
        icon: 'bi-list-ul',
        label: 'Маркированный список',
    });
    bulletButton.addEventListener('click', () => toggleList(editor, 'bullet'));

    const numberedButton = createToolbarButton({
        action: 'numbered-list',
        icon: 'bi-list-ol',
        label: 'Нумерованный список',
    });
    numberedButton.addEventListener('click', () => toggleList(editor, 'number'));

    const dateButton = createToolbarButton({
        action: 'date',
        icon: 'bi-calendar3',
        label: 'Вставить текущую дату',
    });
    dateButton.addEventListener('click', () => {
        const currentDate = new Intl.DateTimeFormat('ru-RU').format(new Date());
        replaceSelection(editor, currentDate);
    });

    const expandButton = createToolbarButton({
        action: 'expand',
        icon: 'bi-arrows-angle-expand',
        label: 'Развернуть редактор',
    });
    expandButton.setAttribute('aria-pressed', 'false');
    expandButton.addEventListener('click', () => {
        const shouldExpand = !shell.classList.contains('is-expanded');
        setExpandedEditor(shell, expandButton, shouldExpand);
        editor.requestMeasure();
        editor.focus();
    });

    const clearButton = createToolbarButton({
        action: 'clear',
        icon: 'bi-trash3',
        label: 'Очистить поле',
        modifier: 'military-codemirror-toolbar__button--danger',
    });
    clearButton.addEventListener('click', () => {
        if (editor.state.doc.length === 0) {
            editor.focus();
            return;
        }

        if (!window.confirm(`Очистить содержимое поля «${label}»?`)) {
            editor.focus();
            return;
        }

        editor.dispatch({
            changes: {
                from: 0,
                to: editor.state.doc.length,
                insert: '',
            },
            selection: { anchor: 0 },
        });
        editor.focus();
    });

    toolbar.append(
        undoButton,
        redoButton,
        createToolbarSeparator(),
        bulletButton,
        numberedButton,
        createToolbarSeparator(),
        dateButton,
        createToolbarSeparator(),
        expandButton,
        clearButton,
    );

    return toolbar;
}

function initializeCommissariatSearch() {
    const root = document.getElementById('militaryCommissionFormRoot');
    const input = root?.querySelector('[data-commissariat-search]');
    const suggestions = root?.querySelector('[data-commissariat-suggestions]');
    const clearButton = root?.querySelector('[data-commissariat-clear]');
    const searchButton = root?.querySelector('[data-commissariat-submit]');
    const searchUrl = root?.dataset.commissariatSearchUrl;

    if (!root || !input || !suggestions || !clearButton || !searchButton || !searchUrl) return;
    if (input.dataset.commissariatReady === '1') return;

    input.dataset.commissariatReady = '1';

    const memoryCache = new Map();
    const cachePrefix = 'vvk-commissariat-search:v2:';
    let resultItems = [];
    let requestController = null;
    let lastRequestAt = 0;

    const closeSuggestions = () => {
        suggestions.classList.add('d-none');
        suggestions.replaceChildren();
        input.setAttribute('aria-expanded', 'false');
        resultItems = [];
    };

    const showMessage = (message) => {
        const node = document.createElement('div');
        node.className = 'military-suggestion-message';
        node.textContent = message;
        suggestions.replaceChildren(node);
        suggestions.classList.remove('d-none');
        input.setAttribute('aria-expanded', 'true');
    };

    const setBusy = (isBusy) => {
        searchButton.disabled = isBusy;
        searchButton.replaceChildren();

        const icon = document.createElement('i');
        icon.className = isBusy ? 'bi bi-hourglass-split' : 'bi bi-search';
        const label = document.createTextNode(isBusy ? ' Ищем…' : ' Найти');
        searchButton.append(icon, label);
    };

    const cacheKey = (query) => cachePrefix + query
        .trim()
        .toLocaleLowerCase('ru')
        .replace(/\s+/gu, ' ');

    const readCache = (query) => {
        const key = cacheKey(query);

        if (memoryCache.has(key)) {
            return memoryCache.get(key);
        }

        try {
            const value = window.sessionStorage.getItem(key);
            if (value === null) return null;

            const items = JSON.parse(value);
            if (!Array.isArray(items)) return null;

            memoryCache.set(key, items);
            return items;
        } catch (_) {
            return null;
        }
    };

    const writeCache = (query, items) => {
        const key = cacheKey(query);
        memoryCache.set(key, items);

        try {
            window.sessionStorage.setItem(key, JSON.stringify(items));
        } catch (_) {
            // Поиск продолжит работать с кэшем в памяти.
        }
    };

    const cleanText = (value) => {
        if (typeof value !== 'string' && typeof value !== 'number') {
            return '';
        }

        const text = String(value).trim();

        return text.toLocaleLowerCase('ru') === '[object object]'
            ? ''
            : text;
    };

    const normalizePlace = (place) => {
        const namedetails = place?.namedetails && typeof place.namedetails === 'object'
            ? place.namedetails
            : {};
        const displayName = cleanText(place?.display_name);
        const fallbackName = displayName.split(',')[0]?.trim() || '';
        const name = [
            namedetails['name:ru'],
            namedetails.name,
            place?.name,
            place?.title,
            fallbackName,
        ].map(cleanText).find(Boolean) || '';
        const addressData = place?.address && typeof place.address === 'object'
            ? place.address
            : {};
        const stringAddress = cleanText(place?.address);
        const fallbackAddress = [
            addressData.postcode,
            addressData.state || addressData.region,
            addressData.city
                || addressData.town
                || addressData.village
                || addressData.municipality,
            addressData.road,
            addressData.house_number,
        ].map(cleanText).filter(Boolean).join(', ');
        let address = [
            place?.address_label,
            displayName,
            stringAddress,
            fallbackAddress,
        ].map(cleanText).find(Boolean) || '';

        if (name && address.toLocaleLowerCase('ru').startsWith(name.toLocaleLowerCase('ru'))) {
            address = address.slice(name.length).replace(/^\s*[,—-]\s*/u, '').trim();
        }

        if (address === name) {
            address = '';
        }

        return {
            name,
            address,
            value: [name, address].filter(Boolean).join(' — '),
        };
    };

    const preparePlaces = (items) => {
        const uniquePlaces = new Map();

        items
            .map(normalizePlace)
            .filter((place) => place.name && place.value)
            .forEach((place) => {
                uniquePlaces.set(place.value.toLocaleLowerCase('ru'), place);
            });

        return [...uniquePlaces.values()].slice(0, 8);
    };

    const renderResults = (places) => {
        resultItems = preparePlaces(Array.isArray(places) ? places : []);

        if (!resultItems.length) {
            showMessage('Ничего не найдено. Уточните город или заполните поле вручную.');
            return;
        }

        const fragment = document.createDocumentFragment();

        resultItems.forEach((place, index) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'military-suggestion';
            button.dataset.commissariatIndex = String(index);
            button.setAttribute('role', 'option');

            const icon = document.createElement('span');
            icon.className = 'military-suggestion__icon';
            const iconNode = document.createElement('i');
            iconNode.className = 'bi bi-shield-check';
            icon.append(iconNode);

            const copy = document.createElement('span');
            copy.className = 'military-suggestion__copy';
            const name = document.createElement('strong');
            name.textContent = place.name;
            const address = document.createElement('span');
            address.textContent = place.address || 'Адрес не указан';
            copy.append(name, address);

            button.append(icon, copy);
            fragment.append(button);
        });

        suggestions.replaceChildren(fragment);
        suggestions.classList.remove('d-none');
        input.setAttribute('aria-expanded', 'true');
    };

    const selectCommissariat = (place) => {
        if (!place?.value) return;

        input.value = place.value;
        clearButton.classList.remove('d-none');
        closeSuggestions();
        input.dispatchEvent(new Event('change', { bubbles: true }));
        input.focus();
    };

    const waitForRateLimit = async () => {
        const remaining = 1000 - (Date.now() - lastRequestAt);

        if (remaining > 0) {
            await new Promise((resolve) => window.setTimeout(resolve, remaining));
        }

        lastRequestAt = Date.now();
    };

    const searchCommissariats = async () => {
        const query = input.value.trim();

        if (query.length < 2) {
            showMessage('Введите минимум два символа.');
            input.focus();
            return;
        }

        const cachedItems = readCache(query);

        if (Array.isArray(cachedItems)) {
            renderResults(cachedItems);
            return;
        }

        requestController?.abort();
        requestController = new AbortController();

        let timedOut = false;
        const timeoutId = window.setTimeout(() => {
            timedOut = true;
            requestController?.abort();
        }, 6000);

        setBusy(true);
        showMessage('Ищем военный комиссариат…');

        try {
            await waitForRateLimit();

            const url = new URL(searchUrl, window.location.origin);
            const searchQuery = /военком|военн/iu.test(query)
                ? query
                : 'военный комиссариат, ' + query;

            url.searchParams.set('format', 'jsonv2');
            url.searchParams.set('q', searchQuery);
            url.searchParams.set('addressdetails', '1');
            url.searchParams.set('namedetails', '1');
            url.searchParams.set('countrycodes', 'ru');
            url.searchParams.set('accept-language', 'ru');
            url.searchParams.set('dedupe', '1');
            url.searchParams.set('limit', '8');

            const response = await fetch(url.toString(), {
                method: 'GET',
                headers: { Accept: 'application/json' },
                signal: requestController.signal,
            });

            if (!response.ok) {
                throw new Error('HTTP ' + response.status);
            }

            const payload = await response.json();
            const places = preparePlaces(Array.isArray(payload) ? payload : []);

            writeCache(query, places);
            renderResults(places);
        } catch (error) {
            if (error.name !== 'AbortError' || timedOut) {
                console.error('[military-commission] commissariat search failed', error);
                showMessage(
                    timedOut
                        ? 'Поиск не ответил за 6 секунд. Заполните поле вручную или повторите попытку.'
                        : 'Не удалось выполнить поиск. Поле можно заполнить вручную.'
                );
            }
        } finally {
            window.clearTimeout(timeoutId);
            setBusy(false);
        }
    };

    clearButton.classList.toggle('d-none', input.value.trim() === '');

    input.addEventListener('input', () => {
        clearButton.classList.toggle('d-none', input.value.trim() === '');
        requestController?.abort();
        closeSuggestions();
    });

    input.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeSuggestions();
            return;
        }

        if (event.key === 'Enter') {
            event.preventDefault();
            searchCommissariats();
        }
    });

    searchButton.addEventListener('click', searchCommissariats);

    suggestions.addEventListener('click', (event) => {
        const button = event.target.closest('[data-commissariat-index]');
        if (!button) return;

        selectCommissariat(resultItems[Number(button.dataset.commissariatIndex)]);
    });

    clearButton.addEventListener('click', () => {
        requestController?.abort();
        input.value = '';
        clearButton.classList.add('d-none');
        closeSuggestions();
        input.dispatchEvent(new Event('change', { bubbles: true }));
        input.focus();
    });

    document.addEventListener('click', (event) => {
        if (!event.target.closest('.military-commissariat-search')) {
            closeSuggestions();
        }
    });
}

function initializeMedicalEditors() {
    const textareas = Array.from(document.querySelectorAll([
        'textarea[data-codemirror-medical]',
        'textarea#complaints',
        'textarea#examinationResults',
    ].join(', ')));

    textareas.forEach((textarea) => {
        if (textarea.dataset.codemirrorReady === '1') return;

        const shell = document.createElement('div');
        shell.className = 'military-codemirror-shell';

        const mount = document.createElement('div');
        mount.className = 'military-codemirror';
        mount.dataset.codemirrorMount = '';
        shell.append(mount);
        textarea.insertAdjacentElement('afterend', shell);

        const label = textarea
            .closest('.military-field')
            ?.querySelector('label')
            ?.textContent
            ?.replace('*', '')
            ?.trim() || 'Медицинские сведения';

        try {
            const editor = new EditorView({
                doc: textarea.value,
                parent: mount,
                extensions: [
                    minimalSetup,
                    EditorView.lineWrapping,
                    placeholder(textarea.placeholder || ''),
                    EditorView.contentAttributes.of({
                        'aria-label': label,
                        'aria-multiline': 'true',
                        spellcheck: 'true',
                        autocapitalize: 'sentences',
                    }),
                    EditorView.updateListener.of((update) => {
                        if (!update.docChanged) return;

                        textarea.value = update.state.doc.toString();
                        textarea.dispatchEvent(new Event('input', {
                            bubbles: true,
                        }));
                    }),
                ],
            });

            shell.prepend(buildToolbar(editor, shell, label));
            textarea.classList.add('is-codemirror-source');
            textarea.dataset.codemirrorReady = '1';
            textarea.setAttribute('aria-hidden', 'true');
            textarea.tabIndex = -1;

            textarea.form?.addEventListener('submit', () => {
                textarea.value = editor.state.doc.toString();
            });
        } catch (error) {
            shell.remove();
            console.error(`Не удалось инициализировать CodeMirror для поля ${textarea.name}.`, error);
        }
    });
}

document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') return;

    const shell = document.querySelector('.military-codemirror-shell.is-expanded');
    const button = shell?.querySelector('[data-editor-action="expand"]');

    if (!shell || !button) return;

    setExpandedEditor(shell, button, false);
    shell.querySelector('.cm-content')?.focus();
});

function initializeMilitaryFormEnhancements() {
    initializeMedicalEditors();
    initializeCommissariatSearch();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeMilitaryFormEnhancements, {
        once: true,
    });
} else {
    initializeMilitaryFormEnhancements();
}
