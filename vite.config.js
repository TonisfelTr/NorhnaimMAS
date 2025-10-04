import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import path from 'path';

export default defineConfig({
    server: {
        port: 5175,
        hmr: {
            host: 'localhost'
        }
    },
    plugins: [
        vue({
            transformAssetUrls: {
                base: null,
                includeAbsolute: false
            }
        }),
        laravel({
            input: [
                'resources/sass/admin.sass',
                'resources/sass/app.sass',
                'resources/sass/components.sass',
                'resources/sass/doctors.sass',
                'resources/js/app.js',
                'resources/js/admin_app.js',
                'resources/js/selectize.js',
                'resources/js/mass_delete.js',
                'resources/js/registry_script.js',
                'resources/js/medical_card.js'
            ],
            refresh: true,
        }),
    ],
    resolve: {
        alias: {
            'vue': 'vue/dist/vue.esm-bundler.js'
        }
    },
    optimizeDeps: {
        include: ['jquery']
    }
});
