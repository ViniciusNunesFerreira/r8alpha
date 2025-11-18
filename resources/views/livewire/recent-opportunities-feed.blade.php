<div wire:poll.10s="loadOpportunities" class="glass-effect p-6 rounded-xl">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h3 class="text-xl font-bold flex items-center space-x-2">
                <span>🔍 Live Arbitrage Feed</span>
                <span wire:loading wire:target="loadOpportunities" class="loading-dots">
                    <span></span>
                    <span></span>
                    <span></span>
                </span>
            </h3>
            <p class="text-sm text-gray-400 mt-1">Real-time trading opportunities detected by your bots</p>
        </div>
    </div>

    <!-- Status Filter -->
    <div class="flex space-x-2 bg-white/5 rounded-lg p-1 mb-6">
        <button 
            wire:click="setStatusFilter('all')"
            class="px-4 py-2 rounded-lg text-sm font-semibold transition {{ $statusFilter === 'all' ? 'bg-primary text-white' : 'text-gray-400 hover:text-white' }}">
            All
        </button>
        <button 
            wire:click="setStatusFilter('detected')"
            class="px-4 py-2 rounded-lg text-sm font-semibold transition {{ $statusFilter === 'detected' ? 'bg-warning text-white' : 'text-gray-400 hover:text-white' }}">
            Detected
        </button>
        <button 
            wire:click="setStatusFilter('executed')"
            class="px-4 py-2 rounded-lg text-sm font-semibold transition {{ $statusFilter === 'executed' ? 'bg-success text-white' : 'text-gray-400 hover:text-white' }}">
            Executed
        </button>
    </div>

    <!-- Opportunities List -->
    <div class="space-y-3 max-h-[600px] overflow-y-auto custom-scrollbar">
        @forelse($opportunities as $opportunity)
        <div class="p-4 rounded-lg border transition {{ $opportunity['status'] === 'executed' ? 'bg-success/5 border-success/20' : 'bg-gradient-to-r from-primary/10 to-secondary/10 border-primary/20 animate-pulse-slow' }}">
            <!-- Header -->
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center space-x-3">
                    <!-- Profit Badge -->
                    <div class="px-3 py-1 rounded-full font-bold {{ $opportunity['status'] === 'executed' ? 'bg-success/20 text-success' : 'bg-warning/20 text-warning' }}">
                        +{{ number_format($opportunity['profit_percentage'], 4) }}%
                    </div>
                    
                    <!-- Triangle Path -->
                    <div class="flex items-center space-x-2 text-sm">
                        <span class="px-2 py-1 bg-white/10 rounded font-semibold">{{ $opportunity['base_currency'] }}</span>
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                        <span class="px-2 py-1 bg-white/10 rounded font-semibold">{{ $opportunity['intermediate_currency'] }}</span>
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                        <span class="px-2 py-1 bg-white/10 rounded font-semibold">{{ $opportunity['quote_currency'] }}</span>
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                        <span class="px-2 py-1 bg-white/10 rounded font-semibold">{{ $opportunity['base_currency'] }}</span>
                    </div>
                </div>

                <!-- Status Badge -->
                <span class="px-3 py-1 text-xs rounded-full font-semibold {{ 
                    $opportunity['status'] === 'executed' ? 'bg-success/20 text-success' : 
                    ($opportunity['status'] === 'detected' ? 'bg-warning/20 text-warning' : 'bg-gray-600 text-gray-300') 
                }}">
                    {{ ucfirst($opportunity['status']) }}
                </span>
            </div>

            <!-- Price Details -->
            <div class="grid grid-cols-3 gap-3 mb-3">
                @foreach($opportunity['prices'] as $key => $priceData)
                <div class="p-2 bg-white/5 rounded text-center">
                    <p class="text-xs text-gray-400 mb-1">{{ $priceData['symbol'] }}</p>
                    <p class="text-sm font-bold">${{ number_format($priceData['price'], 8) }}</p>
                </div>
                @endforeach
            </div>

            <!-- Footer Info -->
            <div class="flex items-center justify-between text-sm pt-3 border-t border-white/10">
                <div class="flex items-center space-x-4">
                    <span class="text-gray-400">
                        Bot: <span class="text-white font-semibold">{{ $opportunity['bot_instance_id'] }}</span>
                    </span>
                    <span class="text-success">
                        Est. Profit: <span class="font-bold">${{ number_format($opportunity['estimated_profit'], 2) }}</span>
                    </span>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-gray-400 text-xs">
                        {{ \Carbon\Carbon::parse($opportunity['detected_at'])->diffForHumans() }}
                    </span>
                    @if($opportunity['status'] === 'detected')
                    <div class="flex items-center text-warning text-xs">
                        <svg class="w-4 h-4 mr-1 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Analyzing...
                    </div>
                    @elseif($opportunity['status'] === 'executed')
                    <div class="flex items-center text-success text-xs">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Executed
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="text-center py-16">
            <div class="mb-4">
                <svg class="w-20 h-20 mx-auto text-gray-600 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <h4 class="text-xl font-bold text-gray-400 mb-2">Scanning for Opportunities</h4>
            <p class="text-gray-500 mb-4">Your bots are actively monitoring market conditions</p>
            <div class="flex items-center justify-center space-x-2">
                <div class="w-2 h-2 bg-primary rounded-full animate-bounce" style="animation-delay: 0s"></div>
                <div class="w-2 h-2 bg-secondary rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                <div class="w-2 h-2 bg-success rounded-full animate-bounce" style="animation-delay: 0.4s"></div>
            </div>
        </div>
        @endforelse
    </div>

    <!-- Summary Footer -->
    @if(count($opportunities) > 0)
    <div class="mt-6 pt-4 border-t border-white/10">
        <div class="grid grid-cols-3 gap-4 text-center">
            <div>
                <p class="text-2xl font-bold text-warning">{{ collect($opportunities)->where('status', 'detected')->count() }}</p>
                <p class="text-sm text-gray-400">Detected</p>
            </div>
            <div>
                <p class="text-2xl font-bold text-success">{{ collect($opportunities)->where('status', 'executed')->count() }}</p>
                <p class="text-sm text-gray-400">Executed</p>
            </div>
            <div>
                <p class="text-2xl font-bold text-primary">{{ number_format(collect($opportunities)->avg('profit_percentage'), 4) }}%</p>
                <p class="text-sm text-gray-400">Avg Profit</p>
            </div>
        </div>
    </div>
    @endif
</div>
