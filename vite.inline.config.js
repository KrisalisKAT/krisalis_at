import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    build: {
        minify: false,
    },
    plugins: [
        laravel({
            input: [
                'resources/js/mtg-lister.js'
            ],
            refresh: true,
        }),
    ],
});
