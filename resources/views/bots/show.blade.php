@extends('layouts.app')

@section('title', 'Bot Monitor - ' . $bot->instance_id)

@section('content')
<div class="space-y-6">
    
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-3xl font-bold">{{ $bot->instance_id }}</h2>
            <p class="text-gray-400 mt-1">Real-time arbitrage trading monitor</p>
        </div>
        <div class="flex space-x-4">
            <form action="{{ route('bots.toggle', $bot) }}" method="POST">
                @csrf
                <button type="submit" class="px-6 py-3 rounded-lg font-semibold transition {{ $bot->is_active ? 'bg-red-500 hover:bg-red-600' : 'bg-success hover:bg-green-600' }}">
                    {{ $bot->is_active ? 'Stop Bot' : 'Start Bot' }}
                </button>
            </form>
        </div>
    </div>

    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="glass-effect p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 {{ $bot->is_active ? 'bg-success/20' : 'bg-gray-500/20' }} rounded-lg">
                    <svg class="w-6 h-6 {{ $bot->is_active ? 'text-success' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
            </div>
            <h3 class="text-gray-400 text-sm">Status</h3>
            <p class="text-2xl font-bold mt-2">{{ $bot->is_active ? 'Active' : 'Inactive' }}</p>
            @if($bot->last_trade_at)
                <p class="text-gray-400 text-sm mt-2">Last trade: {{ $bot->last_trade_at->diffForHumans() }}</p>
            @else
                <p class="text-gray-400 text-sm mt-2">No trades yet</p>
            @endif
        </div>

        <div class="glass-effect p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 bg-primary/20 rounded-lg">
                    <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
            </div>
            <h3 class="text-gray-400 text-sm">Total Trades</h3>
            <p class="text-2xl font-bold mt-2">{{ $bot->total_trades }}</p>
            <p class="text-gray-400 text-sm mt-2">{{ $bot->successful_trades }} successful</p>
        </div>

        <div class="glass-effect p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 bg-success/20 rounded-lg">
                    <svg class="w-6 h-6 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                    </svg>
                </div>
            </div>
            <h3 class="text-gray-400 text-sm">Success Rate</h3>
            <p class="text-2xl font-bold mt-2">{{ number_format($bot->success_rate, 1) }}%</p>
            <p class="text-gray-400 text-sm mt-2">Performance metric</p>
        </div>

        <div class="glass-effect p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 bg-warning/20 rounded-lg">
                    <svg class="w-6 h-6 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
            <h3 class="text-gray-400 text-sm">Total Profit</h3>
            <p class="text-2xl font-bold mt-2">${{ number_format($bot->total_profit, 2) }}</p>
            @php
                $profitPercent = $bot->investment->amount > 0 ? ($bot->total_profit / $bot->investment->amount) * 100 : 0;
            @endphp
            <p class="text-success text-sm mt-2">+{{ number_format($profitPercent, 2) }}%</p>
        </div>
    </div>

    
    <livewire:bot-monitor :botId="$bot->id" />

    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <div class="glass-effect p-6">
            <h3 class="text-xl font-bold mb-4">Recent Arbitrage Opportunities</h3>
            <div class="space-y-4">
                @forelse($bot->arbitrageOpportunities()->latest()->take(5)->get() as $opportunity)
                    <div class="p-4 bg-white/5 rounded-lg border-l-4 {{ $opportunity->status === 'executed' ? 'border-success' : 'border-warning' }}">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <h4 class="font-semibold">{{ $opportunity->base_currency }} → {{ $opportunity->intermediate_currency }} → {{ $opportunity->quote_currency }}</h4>
                                <p class="text-sm text-gray-400">{{ $opportunity->detected_at->format('H:i:s') }}</p>
                            </div>
                            <span class="px-3 py-1 {{ $opportunity->status === 'executed' ? 'bg-success/20 text-success' : 'bg-warning/20 text-warning' }} text-sm rounded-full">
                                {{ ucfirst($opportunity->status) }}
                            </span>
                        </div>
                        <div class="grid grid-cols-2 gap-4 mt-4">
                            <div>
                                <p class="text-xs text-gray-400">Profit %</p>
                                <p class="font-semibold text-success">{{ number_format($opportunity->profit_percentage, 4) }}%</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400">Estimated Profit</p>
                                <p class="font-semibold">${{ number_format($opportunity->estimated_profit, 2) }}</p>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 text-gray-400">
                        <p>No opportunities detected yet</p>
                    </div>
                @endforelse
            </div>
        </div>

        
        <div class="glass-effect p-6">
            <h3 class="text-xl font-bold mb-4">Recent Trades</h3>
            <div class="space-y-4">
                @forelse($bot->trades()->latest()->take(10)->get() as $trade)
                    <div class="p-4 bg-white/5 rounded-lg">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <h4 class="font-semibold">{{ $trade->pair }}</h4>
                                <p class="text-sm text-gray-400">{{ $trade->trade_sequence }}</p>
                            </div>
                            <span class="px-3 py-1 {{ $trade->side === 'buy' ? 'bg-success/20 text-success' : 'bg-danger/20 text-danger' }} text-sm rounded-full">
                                {{ strtoupper($trade->side) }}
                            </span>
                        </div>
                        <div class="grid grid-cols-3 gap-4 mt-4">
                            <div>
                                <p class="text-xs text-gray-400">Amount</p>
                                <p class="font-semibold">{{ number_format($trade->amount, 8) }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400">Price</p>
                                <p class="font-semibold">{{ number_format($trade->price, 8) }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400">Total</p>
                                <p class="font-semibold">${{ number_format($trade->total, 2) }}</p>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 text-gray-400">
                        <p>No trades executed yet</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    
    <div class="glass-effect p-6">
        <h3 class="text-xl font-bold mb-4">Investment Details</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div>
                <p class="text-gray-400 text-sm">Plan</p>
                <p class="text-xl font-semibold mt-1">{{ $bot->investment->investmentPlan->name }}</p>
            </div>
            <div>
                <p class="text-gray-400 text-sm">Initial Investment</p>
                <p class="text-xl font-semibold mt-1">${{ number_format($bot->investment->amount, 2) }}</p>
            </div>
            <div>
                <p class="text-gray-400 text-sm">Current Balance</p>
                <p class="text-xl font-semibold mt-1">${{ number_format($bot->investment->current_balance, 2) }}</p>
            </div>
            <div>
                <p class="text-gray-400 text-sm">Total Profit</p>
                <p class="text-xl font-semibold text-success mt-1">${{ number_format($bot->investment->total_profit, 2) }}</p>
            </div>
        </div>
        <div class="mt-6 pt-6 border-t border-white/10">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <p class="text-gray-400 text-sm">Started</p>
                    <p class="text-lg font-semibold mt-1">{{ $bot->investment->started_at->format('M d, Y H:i') }}</p>
                </div>
                <div>
                    <p class="text-gray-400 text-sm">Expires</p>
                    <p class="text-lg font-semibold mt-1">{{ $bot->investment->expires_at->format('M d, Y H:i') }}</p>
                    <p class="text-sm text-gray-400 mt-1">{{ $bot->investment->expires_at->diffForHumans() }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Auto-refresh page every 30 seconds when bot is active
    @if($bot->is_active)
        setInterval(() => {
            window.location.reload();
        }, 30000);
    @endif
</script>

@endsection