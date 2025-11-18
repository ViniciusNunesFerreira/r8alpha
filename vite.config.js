import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
            ],
            
            buildDirectory: 'build',

            refresh: [
                'resources/views/**/*.blade.php',
                'resources/js/**/*.js',
                'app/Http/Livewire/**/*.php',
            ],
        }),
    ],
    
    resolve: {
        alias: {
            '@': '/resources/js',
            '@css': '/resources/css',
        },
    },
    
    build: {

        outDir: 'public/build',
        rollupOptions: {
            output: {
                manualChunks: {
                    'vendor': [
                        'laravel-echo',
                        'pusher-js',
                    ],
                    'charts': [
                        'chart.js',
                    ],
                },
            },
        },
    },
    
    server: {
        hmr: {
            host: 'localhost',
        },
        watch: {
            usePolling: true,
        },
    },
});