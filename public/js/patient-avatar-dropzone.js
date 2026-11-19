// Внешний JS. Работает с существующей формой Laravel; новых маршрутов нет.
(() => {
    'use strict';

    const allowedTypes = new Set(['image/jpeg', 'image/png', 'image/webp']);
    const allowedExtensions = /\.(jpe?g|png|webp)$/i;

    function initialize(root) {
        if (root.dataset.avatarReady === '1') return;
        root.dataset.avatarReady = '1';

        const input = root.querySelector('[data-avatar-input]');
        const zone = root.querySelector('[data-avatar-dropzone]');
        const photo = root.querySelector('[data-avatar-photo]');
        const initials = root.querySelector('[data-avatar-initials]');
        const status = root.querySelector('[data-avatar-status]');
        const button = root.querySelector('[data-avatar-save]');
        const buttonText = root.querySelector('[data-avatar-save-text]');
        const form = root.closest('form');
        if (!input || !zone || !photo || !status || !button || !form) return;

        const maxBytes = Number(root.dataset.maxSize) || 5242880;
        const initialSrc = photo.getAttribute('src') || '';
        const hasInitialPhoto = Boolean(initialSrc);
        let selected = false;
        let currentUrl = null;
        let requestSequence = 0;
        let dragDepth = 0;

        function revokePreview() {
            if (currentUrl) URL.revokeObjectURL(currentUrl);
            currentUrl = null;
        }

        function restorePreview() {
            revokePreview();
            photo.src = initialSrc;
            photo.classList.toggle('d-none', !hasInitialPhoto);
            initials?.classList.toggle('d-none', hasInitialPhoto);
        }

        function renderError(message) {
            selected = false;
            input.value = '';
            button.disabled = true;
            root.classList.remove('has-file', 'is-checking');
            root.classList.add('has-error');
            status.textContent = message;
            restorePreview();
        }

        function renderStatus(message) {
            root.classList.remove('has-error');
            status.textContent = message;
        }

        function validateFile(file) {
            requestSequence += 1;
            const sequence = requestSequence;
            selected = false;
            button.disabled = true;
            root.classList.remove('has-file');

            if (!file) {
                renderError('Выберите фотографию.');
                return;
            }
            if (!allowedTypes.has(file.type) || !allowedExtensions.test(file.name)) {
                renderError('Выберите JPG, PNG или WEBP.');
                return;
            }
            if (file.size < 1 || file.size > maxBytes) {
                renderError('Размер фотографии должен быть от 1 байта до 5 МБ.');
                return;
            }

            root.classList.add('is-checking');
            renderStatus('Проверяем изображение…');

            // Сервер дополнительно требует размеры 128–4000 px по каждой стороне.
            const candidateUrl = URL.createObjectURL(file);
            const probe = new Image();
            probe.onload = () => {
                if (sequence !== requestSequence) {
                    URL.revokeObjectURL(candidateUrl);
                    return;
                }
                if (probe.naturalWidth < 128 || probe.naturalHeight < 128 ||
                    probe.naturalWidth > 4000 || probe.naturalHeight > 4000) {
                    URL.revokeObjectURL(candidateUrl);
                    renderError('Размер изображения: от 128×128 до 4000×4000 пикселей.');
                    return;
                }
                revokePreview();
                currentUrl = candidateUrl;
                photo.src = candidateUrl;
                photo.classList.remove('d-none');
                initials?.classList.add('d-none');
                selected = true;
                root.classList.remove('is-checking');
                root.classList.add('has-file');
                renderStatus(`${file.name} · ${(file.size / 1024 / 1024).toFixed(2)} МБ`);
                button.disabled = false;
            };
            probe.onerror = () => {
                URL.revokeObjectURL(candidateUrl);
                if (sequence === requestSequence) renderError('Не удалось прочитать изображение.');
            };
            probe.src = candidateUrl;
        }

        input.addEventListener('change', () => validateFile(input.files?.[0]));

        zone.addEventListener('dragenter', (event) => {
            event.preventDefault();
            dragDepth += 1;
            zone.classList.add('is-dragover');
        });
        zone.addEventListener('dragover', (event) => {
            event.preventDefault();
            if (event.dataTransfer) event.dataTransfer.dropEffect = 'copy';
        });
        zone.addEventListener('dragleave', (event) => {
            event.preventDefault();
            dragDepth = Math.max(0, dragDepth - 1);
            if (dragDepth === 0) zone.classList.remove('is-dragover');
        });
        zone.addEventListener('drop', (event) => {
            event.preventDefault();
            dragDepth = 0;
            zone.classList.remove('is-dragover');
            const file = event.dataTransfer?.files?.[0];
            if (!file) return;
            try {
                const transfer = new DataTransfer();
                transfer.items.add(file);
                input.files = transfer.files;
                validateFile(file);
            } catch (_error) {
                renderError('Не удалось обработать файл. Нажмите на область для выбора.');
            }
        });

        form.addEventListener('submit', (event) => {
            if (!selected || !input.files?.length) {
                event.preventDefault();
                renderStatus('Сначала выберите фотографию.');
                root.classList.add('has-error');
                return;
            }
            button.disabled = true;
            if (buttonText) buttonText.textContent = 'Загружаем…';
        });
        window.addEventListener('pagehide', revokePreview, { once: true });
    }

    function start() {
        document.querySelectorAll('[data-avatar-uploader]').forEach(initialize);
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start, { once: true });
    } else {
        start();
    }
})();
