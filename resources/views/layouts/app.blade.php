@use('Illuminate\Support\Facades\Vite')
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
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

        <div class="h-screen flex overflow-hidden">
            
            @include('layouts.navigation')

            <!-- Main Content Area -->
            <div class="flex-1 flex flex-col  h-full">

                <!-- Top Header Bar -->
                
                <header class="h-20 bg-gray-950 border-b border-gray-800 flex flex-col md:flex-row flex-wrap items-start md:items-center justify-between gap-3 md:gap-4 p-3 sm:p-4 md:px-8 sticky top-0 z-30">
                    
                    <!-- Left Section -->
                    <div class="flex items-center space-x-3 sm:space-x-4 w-full md:w-auto">
                        <!-- Mobile Menu Toggle -->
                        <button id="sidebarToggle" class="lg:hidden p-2 text-gray-400 hover:text-white transition touch-manipulation" aria-label="Toggle menu">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
                        </button>

                        <!-- Page Title -->
                        <div class="flex-1 min-w-0">
                            <h1 class="text-lg sm:text-xl md:text-2xl font-bold truncate">@yield('header', 'Dashboard')</h1>
                            <p class="text-xs sm:text-sm text-gray-400 truncate hidden sm:block">
                                @yield('subheader', 'Welcome back, ' . auth()->user()->name)
                            </p>
                        </div>
                    </div>

                    <!-- Right Section -->
                    <div class="flex items-center space-x-2 sm:space-x-3 md:space-x-4 w-full md:w-auto justify-end">
                        
                        <!-- Market Status Indicator -->
                        <div class="hidden sm:flex items-center space-x-2 px-2 sm:px-3 py-1.5 sm:py-2 glass-effect rounded-lg">
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
                        <div class="glass-effect px-2 sm:px-3 md:px-4 py-1.5 sm:py-2 rounded-lg">
                            <div class="flex items-center space-x-1.5 sm:space-x-2">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-success flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <div class="min-w-0">
                                    <p class="text-xs text-gray-400 hidden sm:block">Balance</p>
                                    <p class="text-xs sm:text-sm font-bold truncate">
                                        ${{ number_format(auth()->user()->wallet->balance ?? 0, 2) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </header>

                <!-- Market Ticker Tape -->
                <div class="h-7 sm:h-8 bg-gray-900/50 border-t border-gray-800/30 overflow-hidden">
                    <div class="flex items-center h-full ticker-tape">
                        <div class="flex items-center space-x-4 sm:space-x-6 md:space-x-8 px-2 sm:px-4">
                            <!-- BTC - sempre visível -->
                            <div class="flex items-center space-x-1 sm:space-x-2">
                                <span class="text-xs text-gray-400 whitespace-nowrap">BTC/USDT</span>
                                <span class="text-xs sm:text-sm font-semibold text-success-400">$42,156.32</span>
                                <span class="text-xs text-success-400">+2.5%</span>
                            </div>
                            <!-- ETH - sempre visível -->
                            <div class="flex items-center space-x-1 sm:space-x-2">
                                <span class="text-xs text-gray-400 whitespace-nowrap">ETH/USDT</span>
                                <span class="text-xs sm:text-sm font-semibold text-success-400">$2,245.18</span>
                                <span class="text-xs text-success-400">+3.2%</span>
                            </div>
                            <!-- BNB - visível a partir de sm -->
                            <div class="hidden sm:flex items-center space-x-1 sm:space-x-2">
                                <span class="text-xs text-gray-400 whitespace-nowrap">BNB/USDT</span>
                                <span class="text-xs sm:text-sm font-semibold text-danger-400">$315.42</span>
                                <span class="text-xs text-danger-400">-1.2%</span>
                            </div>
                            <!-- SOL - visível a partir de md -->
                            <div class="hidden md:flex items-center space-x-1 sm:space-x-2">
                                <span class="text-xs text-gray-400 whitespace-nowrap">SOL/USDT</span>
                                <span class="text-xs sm:text-sm font-semibold text-success-400">$98.75</span>
                                <span class="text-xs text-success-400">+5.8%</span>
                            </div>
                            <!-- XRP - visível a partir de lg -->
                            <div class="hidden lg:flex items-center space-x-1 sm:space-x-2">
                                <span class="text-xs text-gray-400 whitespace-nowrap">XRP/USDT</span>
                                <span class="text-xs sm:text-sm font-semibold text-success-400">$0.5642</span>
                                <span class="text-xs text-success-400">+1.8%</span>
                            </div>
                            <!-- Duplicados para scroll infinito (apenas desktop) -->
                            <div class="hidden xl:flex items-center space-x-1 sm:space-x-2">
                                <span class="text-xs text-gray-400 whitespace-nowrap">BTC/USDT</span>
                                <span class="text-xs sm:text-sm font-semibold text-success-400">$42,156.32</span>
                                <span class="text-xs text-success-400">+2.5%</span>
                            </div>
                            <!-- ... repita outros conforme necessário -->
                        </div>
                    </div>
                </div>
                <!-- Page Content -->
                <main class="flex-1 overflow-y-auto bg-gray-900 p-8 custom-scrollbar">

                    <!-- Alerts -->
                    @if (session('success'))
                    <div class="mb-6 p-4 bg-success/10 border border-success/30 rounded-lg flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <svg class="w-6 h-6 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="text-success">{{ session('success') }}</p>
                        </div>
                        <button onclick="this.parentElement.remove()" class="text-success hover:text-success/80">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    @endif

                    @if (session('error'))
                    <div class="mb-6 p-4 bg-red-500/10 border border-red-500/30 rounded-lg flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="text-red-500">{{ session('error') }}</p>
                        </div>
                        <button onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    @endif

                    @if (session('warning'))
                    <div class="mb-6 glass-effect border border-warning-500/30 rounded-xl p-4 animate-fade-in">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 rounded-lg bg-warning-500/20 flex items-center justify-center">
                                    <svg class="w-6 h-6 text-warning-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-warning-400">Warning</p>
                                    <p class="text-sm text-gray-300">{{ session('warning') }}</p>
                                </div>
                            </div>
                            <button onclick="this.parentElement.parentElement.remove()" class="text-gray-400 hover:text-white transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    @endif

                    @yield('content')
                

                    <!-- Footer -->
                    <footer class="glass-effect border-t border-gray-800/50 px-4 sm:px-6 lg:px-8 py-4 sm:py-4 w-full mt-6">
                        <div class="flex flex-col md:flex-row items-center justify-between gap-3 sm:gap-4 text-xs sm:text-sm text-gray-400">
                            <div class="flex flex-col sm:flex-row items-center space-y-1 sm:space-y-0 sm:space-x-4 text-gray-400">
                                <p class="text-center sm:text-left">&copy; {{ date('Y') }} R8-Alpha. All rights reserved.</p>
                                <span class="hidden sm:inline">•</span>
                                <span class="text-xs px-2 py-1 bg-primary-500/10 text-primary-400 rounded">v1.0.0</span>
                            </div>
                            <div class="flex flex-wrap items-center justify-center gap-x-2 sm:gap-x-4 gap-y-1 text-gray-400">
                                <a href="#" class="hover:text-primary-400 transition touch-manipulation">Terms</a>
                                <span class="hidden xs:inline">•</span>
                                <a href="#" class="hover:text-primary-400 transition touch-manipulation">Privacy</a>
                                <span class="hidden xs:inline">•</span>
                                <a href="#" class="hover:text-primary-400 transition touch-manipulation">Support</a>
                                <span class="hidden sm:inline">•</span>
                                <a href="#" class="hover:text-primary-400 transition touch-manipulation">API Docs</a>
                            </div>
                        </div>
                    </footer>

                </main>
            
            </div>

        </div>

        

        <!-- Chart.js -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>


        <script>


            // Livewire Notifications
            window.addEventListener('notification', event => {
                const data = event.detail;
                showNotification(data.type, data.message);
            });

            function showNotification(type, message) {
                const icons = {
                    success: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
                    error: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
                    warning: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>',
                    info: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>'
                };

                const colors = {
                    success: { bg: 'bg-success-500/20', border: 'border-success-500/30', text: 'text-success-400' },
                    error: { bg: 'bg-danger-500/20', border: 'border-danger-500/30', text: 'text-danger-400' },
                    warning: { bg: 'bg-warning-500/20', border: 'border-warning-500/30', text: 'text-warning-400' },
                    info: { bg: 'bg-primary-500/20', border: 'border-primary-500/30', text: 'text-primary-400' }
                };

                const color = colors[type] || colors.info;
                const icon = icons[type] || icons.info;

                const notification = document.createElement('div');
                notification.className = `fixed top-24 right-8 max-w-md glass-effect border ${color.border} rounded-xl p-4 shadow-2xl z-50 animate-slide-in`;
                notification.innerHTML = `
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-lg ${color.bg} flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 ${color.text}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                ${icon}
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold ${color.text} capitalize">${type}</p>
                            <p class="text-sm text-gray-300">${message}</p>
                        </div>
                        <button onclick="this.closest('.glass-effect').remove()" class="flex-shrink-0 text-gray-400 hover:text-white transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                `;
                document.body.appendChild(notification);
                setTimeout(() => notification.remove(), 5000);
            }

            // Smooth scroll behavior
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                });
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
        });
        </script>


        @stack('scripts')

        @livewireScripts

    </body>
</html>