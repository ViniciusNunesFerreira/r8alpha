<div wire:poll.15s="loadBots" class="glass-effect p-6 rounded-xl">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h3 class="text-xl font-bold flex items-center space-x-2">
                <span>Active Trading Bots</span>
                <span wire:loading wire:target="loadBots" class="loading-dots">
                    <span></span>
                    <span></span>
                    <span></span>
                </span>
            </h3>
            <p class="text-sm text-gray-400 mt-1">Manage your automated trading bots</p>
        </div>
        <!--  route('bots.index')  -->
        <a href="#" class="px-4 py-2 bg-primary hover:bg-primary/80 rounded-lg font-semibold transition">
            View All
        </a>
    </div>

    <!-- Filters and Sorting -->
    <div class="flex items-center justify-between mb-6 flex-wrap gap-4">
        <!-- Filter Tabs -->
        <div class="flex space-x-2 bg-white/5 rounded-lg p-1">
            <button 
                wire:click="setFilter('all')"
                class="px-4 py-2 rounded-lg text-sm font-semibold transition {{ $filter === 'all' ? 'bg-primary text-white' : 'text-gray-400 hover:text-white' }}">
                All ({{ count($bots) }})
            </button>
            <button 
                wire:click="setFilter('active')"
                class="px-4 py-2 rounded-lg text-sm font-semibold transition {{ $filter === 'active' ? 'bg-success text-white' : 'text-gray-400 hover:text-white' }}">
                Active
            </button>
            <button 
                wire:click="setFilter('inactive')"
                class="px-4 py-2 rounded-lg text-sm font-semibold transition {{ $filter === 'inactive' ? 'bg-gray-600 text-white' : 'text-gray-400 hover:text-white' }}">
                Inactive
            </button>
        </div>

        <!-- Sort Dropdown -->
        <div class="relative" x-data="{ open: false }">
            <button @click="open = !open" class="flex items-center space-x-2 px-4 py-2 bg-white/5 hover:bg-white/10 rounded-lg text-sm font-semibold transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"/>
                </svg>
                <span>Sort by: {{ ucfirst(str_replace('_', ' ', $sortBy)) }}</span>
            </button>
            <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 bg-gray-800 border border-gray-700 rounded-lg shadow-xl z-10" x-cloak>
                <button wire:click="setSorting('profit')" @click="open = false" class="w-full text-left px-4 py-2 hover:bg-gray-700 transition first:rounded-t-lg">
                    Total Profit
                </button>
                <button wire:click="setSorting('trades')" @click="open = false" class="w-full text-left px-4 py-2 hover:bg-gray-700 transition">
                    Total Trades
                </button>
                <button wire:click="setSorting('success_rate')" @click="open = false" class="w-full text-left px-4 py-2 hover:bg-gray-700 transition last:rounded-b-lg">
                    Success Rate
                </button>
            </div>
        </div>
    </div>

    <!-- Bots List -->
    <div class="space-y-4">
        @forelse($bots as $bot)
        <div class="p-4 bg-white/5 hover:bg-white/10 rounded-xl transition border border-white/10 hover:border-primary/30">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center space-x-4">
                    <!-- Bot Status Indicator -->
                    <div class="relative">
                        <div class="w-12 h-12 rounded-lg {{ $bot['is_active'] ? 'animated-gradient' : 'bg-gray-700' }} flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/>
                            </svg>
                        </div>
                        @if($bot['is_active'])
                        <span class="absolute -top-1 -right-1 flex h-3 w-3">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-success opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-success"></span>
                        </span>
                        @endif
                    </div>

                    <!-- Bot Info -->
                    <div>
                        <div class="flex items-center space-x-2 mb-1">
                            <h4 class="font-bold text-lg">{{ $bot['instance_id'] }}</h4>
                            <span class="px-2 py-1 text-xs rounded-full {{ $bot['is_active'] ? 'bg-success/20 text-success' : 'bg-gray-700 text-gray-400' }}">
                                {{ $bot['is_active'] ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                        <div class="flex items-center space-x-4 text-sm text-gray-400">
                            <span>Plan: {{ $bot['investment']['plan_name'] }}</span>
                            <span>•</span>
                            <span>Investment: ${{ number_format($bot['investment']['amount'], 2) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center space-x-2">
                    <button 
                        wire:click="toggleBot({{ $bot['id'] }})"
                        wire:loading.attr="disabled"
                        class="px-4 py-2 rounded-lg font-semibold text-sm transition {{ $bot['is_active'] ? 'bg-red-500/20 text-red-500 hover:bg-red-500/30' : 'bg-success/20 text-success hover:bg-success/30' }}">
                        {{ $bot['is_active'] ? 'Stop' : 'Start' }}
                    </button>
                    <!--  route('bots.show', $bot['id'])  -->
                    <a href="#" class="p-2 bg-white/5 hover:bg-white/10 rounded-lg transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </a>
                </div>
            </div>

            <!-- Bot Statistics -->
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                <!-- Total Profit -->
                <div class="text-center p-3 bg-white/5 rounded-lg">
                    <p class="text-xs text-gray-400 mb-1">Total Profit</p>
                    <p class="text-lg font-bold text-success">+${{ number_format($bot['total_profit'], 2) }}</p>
                </div>

                <!-- Current Balance -->
                <div class="text-center p-3 bg-white/5 rounded-lg">
                    <p class="text-xs text-gray-400 mb-1">Balance</p>
                    <p class="text-lg font-bold text-white">${{ number_format($bot['investment']['current_balance'], 2) }}</p>
                </div>

                <!-- Total Trades -->
                <div class="text-center p-3 bg-white/5 rounded-lg">
                    <p class="text-xs text-gray-400 mb-1">Total Trades</p>
                    <p class="text-lg font-bold text-white">{{ $bot['total_trades'] }}</p>
                </div>

                <!-- Success Rate -->
                <div class="text-center p-3 bg-white/5 rounded-lg">
                    <p class="text-xs text-gray-400 mb-1">Success Rate</p>
                    <div class="flex items-center justify-center">
                        <p class="text-lg font-bold text-white mr-1">{{ number_format($bot['success_rate'], 1) }}%</p>
                        <svg class="w-4 h-4 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>

                <!-- Last Trade -->
                <div class="text-center p-3 bg-white/5 rounded-lg">
                    <p class="text-xs text-gray-400 mb-1">Last Trade</p>
                    <p class="text-sm font-semibold text-white">
                        @if($bot['last_trade_at'])
                            {{ \Carbon\Carbon::parse($bot['last_trade_at'])->diffForHumans() }}
                        @else
                            Never
                        @endif
                    </p>
                </div>
            </div>

            <!-- Base Currencies -->
            @if(isset($bot['config']['base_currencies']) && count($bot['config']['base_currencies']) > 0)
            <div class="mt-4 pt-4 border-t border-white/10">
                <p class="text-xs text-gray-400 mb-2">Trading Pairs:</p>
                <div class="flex flex-wrap gap-2">
                    @foreach($bot['config']['base_currencies'] as $currency)
                    <span class="px-3 py-1 bg-primary/20 text-primary text-xs rounded-full font-semibold">
                        {{ $currency }}
                    </span>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
        @empty
        <div class="text-center py-16">
            <svg class="w-20 h-20 mx-auto mb-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/>
            </svg>
            <h4 class="text-xl font-bold text-gray-400 mb-2">No Bots Found</h4>
            <p class="text-gray-500 mb-6">Create your first investment to start automated trading</p>
            <!--  route('investments.index')  -->
            <a href="#" class="inline-flex items-center space-x-2 px-6 py-3 bg-primary hover:bg-primary/80 rounded-lg font-semibold transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Create Investment</span>
            </a>
        </div>
        @endforelse
    </div>

    <!-- Quick Stats Footer -->
    @if(count($bots) > 0)
    <div class="mt-6 pt-6 border-t border-white/10">
        <div class="grid grid-cols-3 gap-4 text-center">
            <div>
                <p class="text-2xl font-bold text-primary">{{ collect($bots)->where('is_active', true)->count() }}</p>
                <p class="text-sm text-gray-400">Active Bots</p>
            </div>
            <div>
                <p class="text-2xl font-bold text-success">+${{ number_format(collect($bots)->sum('total_profit'), 2) }}</p>
                <p class="text-sm text-gray-400">Total Profit</p>
            </div>
            <div>
                <p class="text-2xl font-bold text-warning">{{ collect($bots)->sum('total_trades') }}</p>
                <p class="text-sm text-gray-400">Total Trades</p>
            </div>
        </div>
    </div>
    @endif
</div>
