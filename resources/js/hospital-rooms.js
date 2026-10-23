import { createApp } from 'vue';
import HospitalRoomsMap from './vue/doctors/HospitalRoomsMap.vue';

function mountHospitalRooms() {
    const root = document.getElementById('hospital-rooms-root');

    if (!root || root.__vue_app__) {
        return;
    }

    const fallbackText = root.querySelector('[data-hospital-rooms-loader-text]');
    const fallbackBar = root.querySelector('[data-hospital-rooms-loader-bar]');
    const fallbackPercent = root.querySelector('[data-hospital-rooms-loader-percent]');

    const setFallbackProgress = (value, text) => {
        const progress = Math.max(0, Math.min(100, Number(value) || 0));

        if (fallbackText && text) {
            fallbackText.textContent = text;
        }

        if (fallbackBar) {
            fallbackBar.style.width = `${progress}%`;
        }

        if (fallbackPercent) {
            fallbackPercent.textContent = `${Math.round(progress)}%`;
        }
    };

    try {
        setFallbackProgress(12, 'Подготавливаем интерфейс палат…');

        const app = createApp(HospitalRoomsMap, {
            bootstrapUrl: root.dataset.bootstrapUrl || '',
            roomStoreUrl: root.dataset.roomStoreUrl || '',
            roomUpdateUrlTemplate: root.dataset.roomUpdateUrlTemplate || '',
            roomDeleteUrlTemplate: root.dataset.roomDeleteUrlTemplate || '',
            roomLayoutUrlTemplate: root.dataset.roomLayoutUrlTemplate || '',
            bedAssignUrlTemplate: root.dataset.bedAssignUrlTemplate || '',
            bedReleaseUrlTemplate: root.dataset.bedReleaseUrlTemplate || '',
            patientSearchUrl: root.dataset.patientSearchUrl || '',
            patientCardUrlTemplate: root.dataset.patientCardUrlTemplate || '',
            clinicName: root.dataset.clinicName || '',
        });

        setFallbackProgress(24, 'Запускаем карту палат…');
        app.mount(root);
    } catch (error) {
        console.error('[hospital-rooms] Vue mount failed:', error);

        root.innerHTML = `
            <div class="hospital-rooms-boot-error" role="alert">
                <i class="bi bi-exclamation-triangle"></i>
                <strong>Не удалось запустить карту палат</strong>
                <span>Обновите страницу. Если ошибка повторится — проверьте сборку Vite в консоли браузера.</span>
            </div>
        `;
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', mountHospitalRooms, { once: true });
} else {
    mountHospitalRooms();
}
