import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import Components from 'unplugin-vue-components/vite';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),

        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),

        Components({
            dirs: ['resources/js/components'],
            extensions: ['vue'],
            directoryAsNamespace: false,
        }),

        tailwindcss(),
    ],

    server: {
        host: '0.0.0.0',
        port: '5173',
        hmr: {
            host: '127.0.0.1'
        },
    },

});
