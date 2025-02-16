import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/mtg-lister.js'
            ],
            refresh: true,
        }),
    ],
    server: {
        cors: {
            origin: [
                'http://krisalis_at.test',
                'http://krisalis.test',
                'http://kat.test',
            ],
        },
        // hmr: {
        //     host: 'kat.test',
        // },
        watch: {
            usePolling: true,
        }
    },
});
