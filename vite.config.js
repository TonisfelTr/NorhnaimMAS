import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import sass from 'sass';

export default defineConfig({
    server: {
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
                'resources/js/app.js',
                'resources/js/admin_app.js',
                'resources/js/selectize.js'
            ],
            refresh: true,
        }),
    ],
    resolve: {
        alias: {
            'vue': 'vue/dist/vue.esm-bundler.js'
        }
    }
});
