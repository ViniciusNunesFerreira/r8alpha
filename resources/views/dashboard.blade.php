@extends('layouts.app')

@section('title', 'Dashboard')
@section('header', 'Dashboard')
@section('subheader', 'Real-time overview of your crypto arbitrage trading')

@section('content')

    <div class="space-y-6">

        <!-- Welcome Banner -->
        <div class="glass-effect p-4 sm:p-6 md:p-8 rounded-lg md:rounded-xl relative overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 sm:w-48 sm:h-48 md:w-64 md:h-64 bg-gradient-to-br from-primary/20 to-secondary/20 rounded-full blur-3xl"></div>
            <div class="relative z-10">
                <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-3 sm:gap-4">
                    <div class="w-full md:w-auto">
                        <h2 class="text-xl sm:text-2xl md:text-3xl font-bold mb-2">
                            Welcome back, {{ auth()->user()->name }}! 👋
                        </h2>
                        <p class="text-sm sm:text-base text-gray-400">
                            Here's what's happening with your investments today.
                        </p>
                    </div>
                    <div class="w-full md:w-auto text-left md:text-right">
                        <p class="text-xs sm:text-sm text-gray-400 mb-1">Current Time</p>
                        <p class="text-lg sm:text-xl md:text-2xl font-bold" id="currentTime"></p>
                    </div>
                </div>
            </div>
        </div>



        <!-- Stats Cards -->
        @livewire('dashboard-stats')

        


        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column - Charts & Bots (2/3) -->
            <div class="lg:col-span-2 space-y-6">

                <!-- Active Bots List -->
                @livewire('active-bots-list')

                <!-- Profit Chart -->
                @livewire('profit-chart')


            </div>

            <!-- Right Column - Feed & Info (1/3) -->
            <div class="space-y-6">
                <!-- Recent Opportunities Feed -->
                @livewire('recent-opportunities-feed')

                <!-- Quick Actions Card -->
                <div class="glass-effect p-6 rounded-xl">
                    <h3 class="text-xl font-bold mb-4">Quick Actions</h3>
                    <div class="space-y-3">
                        <!-- Colocar route quando criado: route('investments.index') -->
                        <a href="#" class="flex items-center justify-between p-4 bg-gradient-to-r from-primary/20 to-primary/10 hover:from-primary/30 hover:to-primary/20 rounded-lg transition group">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 rounded-lg bg-primary flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-semibold">New Investment</p>
                                    <p class="text-xs text-gray-400">Create a new plan</p>
                                </div>
                            </div>
                            <svg class="w-5 h-5 text-gray-400 group-hover:text-primary transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>

                        <!-- Colocar route quando criado: route('wallet.index') -->
                        <a href="#" class="flex items-center justify-between p-4 bg-gradient-to-r from-success/20 to-success/10 hover:from-success/30 hover:to-success/20 rounded-lg transition group">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 rounded-lg bg-success flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-semibold">Deposit Funds</p>
                                    <p class="text-xs text-gray-400">Add to wallet</p>
                                </div>
                            </div>
                            <svg class="w-5 h-5 text-gray-400 group-hover:text-success transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>

                        <!-- Colocar route quando criado:route('analytics') -->
                        <a href="#" class="flex items-center justify-between p-4 bg-gradient-to-r from-warning/20 to-warning/10 hover:from-warning/30 hover:to-warning/20 rounded-lg transition group">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 rounded-lg bg-warning flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-semibold">View Analytics</p>
                                    <p class="text-xs text-gray-400">Detailed reports</p>
                                </div>
                            </div>
                            <svg class="w-5 h-5 text-gray-400 group-hover:text-warning transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- Market Status Card -->
                <div class="glass-effect p-6 rounded-xl">
                    <h3 class="text-xl font-bold mb-4">Market Status</h3>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-3 bg-white/5 rounded-lg">
                            <div class="flex items-center space-x-2">
                                <span class="relative flex h-3 w-3">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-success opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-3 w-3 bg-success"></span>
                                </span>
                                <span class="text-sm font-semibold">Binance API</span>
                            </div>
                            <span class="text-xs text-success">Connected</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-white/5 rounded-lg">
                            <div class="flex items-center space-x-2">
                                <span class="relative flex h-3 w-3">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-success opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-3 w-3 bg-success"></span>
                                </span>
                                <span class="text-sm font-semibold">Trading System</span>
                            </div>
                            <span class="text-xs text-success">Active</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-white/5 rounded-lg">
                            <div class="flex items-center space-x-2">
                                <span class="relative flex h-3 w-3">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-warning opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-3 w-3 bg-warning"></span>
                                </span>
                                <span class="text-sm font-semibold">Scanning</span>
                            </div>
                            <span class="text-xs text-warning">In Progress</span>
                        </div>
                    </div>
                </div>

                <!-- Performance Tips -->
                <div class="glass-effect p-6 rounded-xl">
                    <h3 class="text-xl font-bold mb-4">💡 Performance Tips</h3>
                    <div class="space-y-4 text-sm">
                        <div class="p-3 bg-primary/10 border border-primary/20 rounded-lg">
                            <p class="text-gray-300">
                                <strong class="text-primary">Tip:</strong> Diversify your base currencies for better arbitrage opportunities.
                            </p>
                        </div>
                        <div class="p-3 bg-success/10 border border-success/20 rounded-lg">
                            <p class="text-gray-300">
                                <strong class="text-success">Tip:</strong> Monitor your bots regularly and adjust profit thresholds based on market volatility.
                            </p>
                        </div>
                        <div class="p-3 bg-warning/10 border border-warning/20 rounded-lg">
                            <p class="text-gray-300">
                                <strong class="text-warning">Tip:</strong> Best arbitrage opportunities typically occur during high market volatility.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>



@endsection

    @push('scripts')
        <script>

            document.addEventListener('DOMContentLoaded', function() {
                // Update current time
                function updateTime() {
                    const now = new Date();
                    const timeString = now.toLocaleTimeString('en-US', { 
                        hour: '2-digit', 
                        minute: '2-digit',
                        second: '2-digit'
                    });

                    const timeElement = document.getElementById('currentTime');

                    if (timeElement) {
                        timeElement.textContent = timeString;
                    }

                }
                
                
                setInterval(updateTime, 1000);

            });


        </script>
    @endpush

