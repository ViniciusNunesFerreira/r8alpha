<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'R8-Alpha') }} - @yield('title', 'Access Platform')</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />
        <link href="https://fonts.bunny.net/css?family=jetbrains-mono:400,500,600&display=swap" rel="stylesheet" />

        <style>
            {!! Vite::content('resources/css/app.css') !!}
        </style>

        <script>
            {!! Vite::content('resources/js/app.js') !!}
        </script>

        @livewireStyles

        <style>
            /* Efeito de grade de pontos no fundo */
            .dot-grid-background {
                background-image: radial-gradient(circle at 1px 1px, rgba(99, 102, 241, 0.15) 1px, transparent 0);
                background-size: 40px 40px;
            }
        </style>
    </head>
    <body class="font-sans antialiased bg-gray-900 text-gray-100">
        
        <div class="fixed inset-0 bg-gradient-to-br from-gray-900 via-gray-900 to-primary-900/20 pointer-events-none"></div>
        <div class="fixed inset-0 opacity-30 pointer-events-none dot-grid-background"></div>

        <div class="relative min-h-screen flex flex-col items-center justify-center px-4 py-12 animate-fade-in">

            <a href="/" class="flex items-center space-x-3 group mb-8">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-primary-500 to-secondary-600 flex items-center justify-center shadow-lg group-hover:shadow-primary/50 transition-all duration-300 transform group-hover:scale-110">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-bold bg-gradient-to-r from-primary-400 to-secondary-400 bg-clip-text text-transparent">R8-Alpha</h1>
                    <p class="text-xs text-gray-500">Crypto Exchange</p>
                </div>
            </a>

            <main class="w-full max-w-md">
                {{ $slot }}
            </main>
        </div>
        
        @livewireScripts
        @stack('scripts')
    </body>
</html>
