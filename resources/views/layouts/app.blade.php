@use('Illuminate\Support\Facades\Vite')
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>R8-Alpha - @yield('title', 'Professional Trading Platform')</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link rel="stylesheet" href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" as="font" type="font/woff2" crossorigin/>
        <link rel="stylesheet" href="https://fonts.bunny.net/css?family=jetbrains-mono:400,500,600&display=swap" as="font" type="font/woff2" crossorigin />

        <style>
            {!! Vite::content('resources/css/app.css') !!}
        </style>

        <script>
            {!! Vite::content('resources/js/app.js') !!}
        </script>

        <script>
            window.userId = {{ auth()->user()->id }};
        </script>

        @livewireStyles

        @stack('styles')

    </head>

    <body class="font-sans antialiased bg-gray-900 text-gray-100 h-screen overflow-hidden">
        
        <!-- Background Effects -->
        <div class="fixed inset-0 bg-gradient-to-br from-gray-900 via-gray-900 to-primary-900/20 pointer-events-none"></div>
        <div class="fixed inset-0 opacity-30 pointer-events-none" style="background-image: radial-gradient(circle at 1px 1px, rgba(99, 102, 241, 0.15) 1px, transparent 0); background-size: 40px 40px;"></div>

         @livewire('crypto-ticker')

        <div class="h-screen flex overflow-hidden">
            <!-- Market Ticker Tape -->
            @include('layouts.navigation')

            <!-- Main Content Area -->
            <div class="flex-1 flex flex-col h-full min-w-0">

                <!-- Top Header Bar -->
                
                <header class="h-16 sm:h-20 bg-gray-950 border-b border-gray-800 flex flex-col md:flex-row flex-wrap items-start md:items-center justify-between gap-2 md:gap-4 p-3 sm:p-4 md:px-6 lg:px-8 sticky top-0 z-30 flex-shrink-0">
                    
                    <!-- Left Section -->
                    <div class="flex items-center space-x-3 sm:space-x-4 w-full md:w-auto min-w-0">
                        <!-- Mobile Menu Toggle -->
                        <button id="sidebarToggle" class="lg:hidden p-2 text-gray-400 hover:text-white transition touch-manipulation flex-shrink-0" aria-label="Toggle menu">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
                        </button>

                        <!-- Page Title -->
                        <div class="flex-1 min-w-0">
                            <h1 class="text-base sm:text-lg md:text-xl lg:text-2xl font-bold truncate">@yield('header', 'Dashboard')</h1>
                            <p class="text-xs sm:text-sm text-gray-400 truncate hidden sm:block">
                                @yield('subheader', 'Welcome back, ' . auth()->user()->name)
                            </p>
                        </div>
                    </div>

                    <!-- Right Section -->
                    <div class="flex items-center space-x-2 sm:space-x-3 md:space-x-4 w-full md:w-auto justify-end flex-shrink-0">
                        
                        <!-- Market Status Indicator -->
                        <div class="hidden sm:flex items-center space-x-2 px-2 sm:px-3 py-1.5 sm:py-2 glass-effect rounded-lg flex-shrink-0">
                            <span class="relative flex h-2 w-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-success-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-success-500"></span>
                            </span>
                            <span class="text-xs text-gray-400 hidden md:inline">Market</span>
                            <span class="text-xs font-semibold text-success-400">OPEN</span>
                        </div>

                        <!-- Notifications -->
                        @livewire('real-time-notification')

                        <!-- Wallet Quick View -->
                        <div class="glass-effect px-2 sm:px-3 md:px-4 py-1.5 sm:py-2 rounded-lg flex-shrink-0">
                            <div class="flex items-center space-x-1.5 sm:space-x-2">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-success flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <div class="min-w-0">
                                    <p class="text-xs text-gray-400 hidden sm:block">Balance</p>
                                    <p class="text-xs sm:text-sm font-bold truncate">
                                        ${{ number_format(auth()->user()->depositWallet->balance ?? 0, 2) }}
                                    </p>
                                </div>
                            </div>
                        </div>

                       
                        
                    </div>
                </header>

                <!-- Main Content -->
                <main class="flex-1 overflow-y-auto overflow-x-hidden p-4 sm:p-6 lg:p-8">
                    @yield('content')
                </main>
            </div>
        </div>

        <!-- Scripts Globais -->
        <script src="{{ asset('js/notification-system.js') }}"></script>
        <script src="{{ asset('js/currency-mask.js') }}"></script>
        <script src="{{ asset('js/deposit-status-checker.js') }}"></script>

        <script>
            // User Menu Toggle
            document.getElementById('userMenuButton')?.addEventListener('click', function(e) {
                e.stopPropagation();
                document.getElementById('userMenu').classList.toggle('hidden');
            });

            // Fechar menu ao clicar fora
            document.addEventListener('click', function(e) {
                const userMenu = document.getElementById('userMenu');
                const userMenuButton = document.getElementById('userMenuButton');
                
                if (userMenu && !userMenu.contains(e.target) && !userMenuButton.contains(e.target)) {
                    userMenu.classList.add('hidden');
                }
            });
        </script>

        <script>
        // Melhorias para dispositivos móveis
        document.addEventListener('DOMContentLoaded', function() {
            
            // Prevenir zoom duplo em iOS
            let lastTouchEnd = 0;
            document.addEventListener('touchend', function(event) {
                const now = Date.now();
                if (now - lastTouchEnd <= 300) {
                    event.preventDefault();
                }
                lastTouchEnd = now;
            }, false);
            
            // Fechar sidebar ao tocar fora (mobile)
            const sidebar = document.getElementById('sidebar');
            const sidebarToggle = document.getElementById('sidebarToggle');
            
            if (sidebar && sidebarToggle) {
                // Abrir/fechar sidebar
                sidebarToggle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    sidebar.classList.toggle('-translate-x-full');
                    document.body.classList.toggle('overflow-hidden');
                });
                
                // Fechar ao clicar fora (apenas mobile)
                document.addEventListener('click', function(event) {
                    if (window.innerWidth < 1024) {
                        const isClickInsideSidebar = sidebar.contains(event.target);
                        const isClickOnToggle = sidebarToggle.contains(event.target);
                        
                        if (!isClickInsideSidebar && !isClickOnToggle && !sidebar.classList.contains('-translate-x-full')) {
                            sidebar.classList.add('-translate-x-full');
                            document.body.classList.remove('overflow-hidden');
                        }
                    }
                });
                
                // Fechar sidebar ao clicar em um link (mobile)
                const sidebarLinks = sidebar.querySelectorAll('a');
                sidebarLinks.forEach(link => {
                    link.addEventListener('click', function() {
                        if (window.innerWidth < 1024) {
                            sidebar.classList.add('-translate-x-full');
                            document.body.classList.remove('overflow-hidden');
                        }
                    });
                });
            }
            
            // Ajustar altura do viewport em mobile (fix para barra de endereço)
            function setVH() {
                let vh = window.innerHeight * 0.01;
                document.documentElement.style.setProperty('--vh', `${vh}px`);
            }
            
            setVH();
            window.addEventListener('resize', setVH);
            window.addEventListener('orientationchange', setVH);
            
            // Smooth scroll performance
            if ('scrollBehavior' in document.documentElement.style) {
                document.documentElement.style.scrollBehavior = 'smooth';
            }


            @if(session('success'))
                Notify.success('{{ session('success') }}');
            @endif

            @if(session('error'))
                Notify.error('{{ session('error') }}');
            @endif

            @if(session('warning'))
                Notify.warning('{{ session('warning') }}');
            @endif

            @if(session('info'))
                Notify.info('{{ session('info') }}');
            @endif

            // Mostrar erros de validação do Laravel
            @if($errors->any())
                @foreach($errors->all() as $error)
                    Notify.error('{{ $error }}');
                @endforeach
            @endif


        });
        </script>

        @stack('scripts')

        @livewireScripts

    </body>
</html>