(() => {
    const types = new Set(['image/jpeg', 'image/png', 'image/webp']);
    const extension = /\.(jpe?g|png|webp)$/i;

    document.querySelectorAll('[data-avatar-upload]').forEach((root) => {
        if (root.dataset.initialized === '1') return;
        root.dataset.initialized = '1';

        const input = root.querySelector('[data-avatar-file]');
        const zone = root.querySelector('[data-avatar-dropzone]');
        const image = root.querySelector('[data-avatar-preview-image]');
        const initials = root.querySelector('[data-avatar-preview-initials]');
        const status = root.querySelector('[data-avatar-status]');
        const submit = root.querySelector('[data-avatar-submit]');
        const max = Number(root.dataset.maxBytes) || 524288;
        if (!input || !zone || !image || !status || !submit) return;

        let objectUrl = null;
        let depth = 0;
        const initialSrc = image.getAttribute('src') || '';
        const hasExistingImage = !image.classList.contains('d-none') && !!initialSrc;

        const revoke = () => {
            if (objectUrl) URL.revokeObjectURL(objectUrl);
            objectUrl = null;
        };
        const error = (message) => {
            status.textContent = message;
            status.classList.add('is-error');
            zone.classList.add('is-invalid');
            submit.disabled = true;
            input.value = '';
            revoke();
            image.src = initialSrc;
            image.classList.toggle('d-none', !hasExistingImage);
            initials?.classList.toggle('d-none', hasExistingImage);
        };
        const choose = (file) => {
            if (!file) return;
            if (!types.has(file.type) || !extension.test(file.name)) {
                error('Поддерживаются только JPG, PNG или WEBP.');
                return;
            }
            if (file.size > max) {
                error('Файл слишком большой. Максимальный размер — 512 КБ.');
                return;
            }
            if (file.size === 0) {
                error('Нельзя загрузить пустой файл.');
                return;
            }
            revoke();
            objectUrl = URL.createObjectURL(file);
            image.src = objectUrl;
            image.classList.remove('d-none');
            initials?.classList.add('d-none');
            status.classList.remove('is-error');
            zone.classList.remove('is-invalid');
            status.textContent = `${file.name} · ${(file.size / 1024).toFixed(0)} КБ`;
            submit.disabled = false;
        };

        input.addEventListener('change', () => choose(input.files?.[0]));
        zone.addEventListener('dragenter', (event) => {
            event.preventDefault();
            depth++;
            zone.classList.add('is-dragover');
        });
        zone.addEventListener('dragover', (event) => {
            event.preventDefault();
            event.dataTransfer.dropEffect = 'copy';
        });
        zone.addEventListener('dragleave', (event) => {
            event.preventDefault();
            depth = Math.max(0, depth - 1);
            if (depth === 0) zone.classList.remove('is-dragover');
        });
        zone.addEventListener('drop', (event) => {
            event.preventDefault();
            depth = 0;
            zone.classList.remove('is-dragover');
            const file = event.dataTransfer?.files?.[0];
            if (!file) return;
            try {
                const transfer = new DataTransfer();
                transfer.items.add(file);
                input.files = transfer.files;
                choose(file);
            } catch {
                error('Не удалось выбрать файл. Нажмите «выберите файл».');
            }
        });

        const form = root.closest('form');
        form?.addEventListener('submit', (event) => {
            if (!input.files?.length || submit.disabled) {
                event.preventDefault();
                status.textContent = 'Сначала выберите фотографию.';
                status.classList.add('is-error');
                return;
            }
            submit.disabled = true;
            submit.textContent = 'Загрузка…';
        });
        window.addEventListener('pagehide', revoke, { once: true });
    });
})();
