import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    // Relative chunk and CSS preload URLs work at the domain root and in subdirectory deployments.
    base: './',
    plugins: [
        laravel({
            input: 'resources/js/app.ts',
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
    ],
});
