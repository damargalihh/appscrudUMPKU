import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            // Point to the backend's public directory for build output & hot file
            publicDirectory: '../backend/public',
            buildDirectory: 'build',
            refresh: [
                '../backend/resources/views/**/*.blade.php',
                'resources/js/**',
                'resources/css/**',
            ],
        }),
    ],
    build: {
        emptyOutDir: true,
    },
    server: {
        cors: true,
    },
});
