import { createApp } from 'vue';
import ConsiliumModal from './vue/doctors/ConsiliumModal.vue';

function bootConsiliumModal() {
    const root =
        document.getElementById(
            'consilium-modal-root',
        );

    if (!root) {
        return;
    }

    const app =
        createApp(
            ConsiliumModal,
            {
                doctorsUrl:
                root.dataset.doctorsUrl,

                psyDoctorsUrl:
                root.dataset.psyDoctorsUrl,

                patientsUrl:
                root.dataset.patientsUrl,

                diagnosesUrl:
                root.dataset.diagnosesUrl,

                storeUrl:
                root.dataset.storeUrl,
            },
        );

    app.mount(root);
}

if (
    document.readyState ===
    'loading'
) {
    document.addEventListener(
        'DOMContentLoaded',
        bootConsiliumModal,
        {
            once: true,
        },
    );
} else {
    bootConsiliumModal();
}
