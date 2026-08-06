import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import path from 'path';

const VITE_HOST = process.env.VITE_HOST || '0.0.0.0';   // слушаем все интерфейсы в контейнере
const VITE_PORT = Number(process.env.VITE_PORT || 5175);

export default defineConfig({
    server: {
        host: VITE_HOST,         // важно для Docker
        port: VITE_PORT,
        strictPort: true,        // не прыгать на другой порт
        hmr: {
            host: 'localhost',     // домен, на котором открыт сайт в браузере
            port: VITE_PORT,
            protocol: 'ws',        // если сайт открываешь по HTTPS — поставь 'wss'
        },
        watch: {                 // чтобы авто-обновление работало в Docker/WSL
            usePolling: true,
            interval: 100,
        },
    },
    plugins: [
        vue({
            transformAssetUrls: { base: null, includeAbsolute: false },
        }),
        laravel({
            input: [
                'resources/sass/admin.sass',
                'resources/sass/app.sass',
                'resources/sass/components.sass',
                'resources/sass/doctors.sass',
                'resources/sass/medical_card.sass',
                'resource/sass/analyses.cass',
                'resources/js/app.js',
                'resources/js/admin_app.js',
                'resources/js/selectize.js',
                'resources/js/mass_delete.js',
                'resources/js/registry_script.js',
                'resources/js/medical_card.js',
                'resources/js/test-run.js'
            ],
            refresh: true,
        }),
    ],
    resolve: {
        alias: {
            vue: 'vue/dist/vue.esm-bundler.js',
            '@': path.resolve(__dirname, 'resources/js'),
        },
    },
    optimizeDeps: {
        include: ['jquery'],
    },
});
