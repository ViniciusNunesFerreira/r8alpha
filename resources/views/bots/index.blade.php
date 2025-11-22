@extends('layouts.app')

@section('title', 'My Trading Bots')
@section('header', 'Arbitrage Bots')
@section('subheader', 'Manage your automated trading bots.')

@section('content')
<div class="space-y-4 sm:space-y-6">
    
    <!-- Stats Overview -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6">
        <!-- Total Bots -->
        <div class="glass-effect p-4 sm:p-6 rounded-lg sm:rounded-xl card-hover">
            <div class="flex items-center justify-between mb-3 sm:mb-4">
                <div class="p-2 sm:p-3 rounded-lg sm:rounded-xl bg-primary/20">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/>
                    </svg>
                </div>
            </div>
            <p class="text-xs sm:text-sm text-gray-400 mb-1">Total Bots</p>
            <p class="text-2xl sm:text-3xl font-bold">{{ $bots->total() }}</p>
        </div>

        <!-- Active Bots -->
        <div class="glass-effect p-4 sm:p-6 rounded-lg sm:rounded-xl card-hover">
            <div class="flex items-center justify-between mb-3 sm:mb-4">
                <div class="p-2 sm:p-3 rounded-lg sm:rounded-xl bg-success/20">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
            </div>
            <p class="text-xs sm:text-sm text-gray-400 mb-1">Active Bots</p>
            <p class="text-2xl sm:text-3xl font-bold text-success">{{ $bots->where('is_active', true)->count() }}</p>
        </div>

        <!-- Total Profit -->
        <div class="glass-effect p-4 sm:p-6 rounded-lg sm:rounded-xl card-hover">
            <div class="flex items-center justify-between mb-3 sm:mb-4">
                <div class="p-2 sm:p-3 rounded-lg sm:rounded-xl bg-warning/20">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <p class="text-xs sm:text-sm text-gray-400 mb-1">Total Profit</p>
            <p class="text-2xl sm:text-3xl font-bold text-success">${{ number_format($bots->sum('total_profit'), 2) }}</p>
        </div>

        <!-- Total Trades -->
        <div class="glass-effect p-4 sm:p-6 rounded-lg sm:rounded-xl card-hover">
            <div class="flex items-center justify-between mb-3 sm:mb-4">
                <div class="p-2 sm:p-3 rounded-lg sm:rounded-xl bg-secondary/20">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <p class="text-xs sm:text-sm text-gray-400 mb-1">Total Trades</p>
            <p class="text-2xl sm:text-3xl font-bold">{{ number_format($bots->sum('total_trades')) }}</p>
        </div>
    </div>

    <!-- Bots List -->
    <div class="glass-effect rounded-lg sm:rounded-xl overflow-hidden">
        <div class="p-4 sm:p-6 border-b border-white/10">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 sm:gap-4">
                <div>
                    <h2 class="text-xl sm:text-2xl font-bold">Your Trading Bots</h2>
                    <p class="text-gray-400 mt-1 text-sm sm:text-base">Manage and monitor all your bots.</p>
                </div>
                <a href="{{ route('investments.plans.index') }}" 
                   class="w-full sm:w-auto px-4 sm:px-6 py-2 sm:py-3 bg-gradient-to-r from-primary to-secondary hover:from-primary-600 hover:to-secondary-600 rounded-lg font-semibold transition flex items-center justify-center space-x-2 text-sm sm:text-base">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>New Bot</span>
                </a>
            </div>
        </div>

        <!-- Table Desktop -->
        <div class="hidden lg:block overflow-x-auto">
            <table class="w-full">
                <thead class="bg-white/5">
                    <tr>
                        <th class="px-4 sm:px-6 py-3 sm:py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Bot</th>
                        <th class="px-4 sm:px-6 py-3 sm:py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-4 sm:px-6 py-3 sm:py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Investiment</th>
                        <th class="px-4 sm:px-6 py-3 sm:py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Profit</th>
                        <th class="px-4 sm:px-6 py-3 sm:py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Trades</th>
                        <th class="px-4 sm:px-6 py-3 sm:py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Rate</th>
                        <th class="px-4 sm:px-6 py-3 sm:py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Last Op.</th>
                        <th class="px-4 sm:px-6 py-3 sm:py-4 text-right text-xs font-semibold text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($bots as $bot)
                    <tr class="hover:bg-white/5 transition">
                        <td class="px-4 sm:px-6 py-4">
                            <div>
                                <p class="font-semibold text-sm sm:text-base">{{ $bot->instance_id }}</p>
                                <p class="text-xs sm:text-sm text-gray-400">{{ $bot->investment->investmentPlan->name ?? 'N/A' }}</p>
                            </div>
                        </td>
                        <td class="px-4 sm:px-6 py-4">
                            @if($bot->is_active)
                                <span class="flex items-center space-x-2">
                                    <span class="relative flex h-3 w-3">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-success opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-3 w-3 bg-success"></span>
                                    </span>
                                    <span class="text-success font-semibold text-sm">Active</span>
                                </span>
                            @else
                                <span class="flex items-center space-x-2">
                                    <span class="relative inline-flex rounded-full h-3 w-3 bg-gray-500"></span>
                                    <span class="text-gray-400 font-semibold text-sm">Inactive</span>
                                </span>
                            @endif
                        </td>
                        <td class="px-4 sm:px-6 py-4">
                            <p class="font-semibold text-sm sm:text-base">${{ number_format($bot->investment->amount ?? 0, 2) }}</p>
                        </td>
                        <td class="px-4 sm:px-6 py-4">
                            <p class="font-semibold text-success text-sm sm:text-base">${{ number_format($bot->total_profit ?? 0, 2) }}</p>
                            @php
                                $profitPercent = $bot->investment->amount > 0 ? ($bot->total_profit / $bot->investment->amount) * 100 : 0;
                            @endphp
                            <p class="text-xs text-gray-400">{{ number_format($profitPercent, 2) }}%</p>
                        </td>
                        <td class="px-4 sm:px-6 py-4">
                            <p class="font-semibold text-sm sm:text-base">{{ number_format($bot->total_trades ?? 0) }}</p>
                            <p class="text-xs text-gray-400">{{ number_format($bot->successful_trades ?? 0) }} sucesso</p>
                        </td>
                        <td class="px-4 sm:px-6 py-4">
                            <p class="font-semibold text-sm sm:text-base">{{ number_format($bot->success_rate ?? 0, 1) }}%</p>
                        </td>
                        <td class="px-4 sm:px-6 py-4">
                            @if($bot->last_trade_at)
                                <p class="text-xs sm:text-sm">{{ $bot->last_trade_at->diffForHumans() }}</p>
                            @else
                                <p class="text-xs sm:text-sm text-gray-400">Never</p>
                            @endif
                        </td>
                        <td class="px-4 sm:px-6 py-4 text-right">
                            <div class="flex items-center justify-end space-x-2">
                                <a href="{{ route('bots.show', $bot['id']) }}" 
                                   class="p-2 hover:bg-white/10 rounded-lg transition" 
                                   title="Ver detalhes">
                                    <svg class="w-5 h-5 text-gray-400 hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                                <form action="{{ route('bots.toggle', $bot) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" 
                                            class="p-2 hover:bg-white/10 rounded-lg transition" 
                                            title="{{ $bot->is_active ? 'Pausar bot' : 'Ativar bot' }}">
                                        @if($bot->is_active)
                                            <svg class="w-5 h-5 text-red-400 hover:text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        @else
                                            <svg class="w-5 h-5 text-success hover:text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        @endif
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <svg class="w-20 h-20 text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/>
                                </svg>
                                <h3 class="text-xl font-bold text-gray-400 mb-2">No bots found</h3>
                                <p class="text-gray-500 mb-6">Make your first investment to get started.</p>
                                <a href="{{ route('investments.plans.index') }}" 
                                   class="px-6 py-3 bg-primary hover:bg-primary-600 rounded-lg font-semibold transition">
                                    Create First Bot
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Cards Mobile/Tablet -->
        <div class="lg:hidden divide-y divide-white/5">
            @forelse($bots as $bot)
                <div class="p-4 hover:bg-white/5 transition">
                    <!-- Header -->
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex-1 min-w-0">
                            <h3 class="font-semibold truncate">{{ $bot->instance_id }}</h3>
                            <p class="text-sm text-gray-400 truncate">{{ $bot->investment->investmentPlan->name ?? 'N/A' }}</p>
                        </div>
                        <div class="ml-3 flex-shrink-0">
                            @if($bot->is_active)
                                <span class="flex items-center space-x-1 text-success">
                                    <span class="relative flex h-2 w-2">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-success opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-2 w-2 bg-success"></span>
                                    </span>
                                    <span class="text-xs font-semibold">Active</span>
                                </span>
                            @else
                                <span class="flex items-center space-x-1 text-gray-400">
                                    <span class="relative inline-flex rounded-full h-2 w-2 bg-gray-500"></span>
                                    <span class="text-xs font-semibold">Inactive</span>
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Stats Grid -->
                    <div class="grid grid-cols-2 gap-3 mb-3">
                        <div class="bg-white/5 rounded-lg p-2">
                            <p class="text-xs text-gray-400 mb-1">Investiment</p>
                            <p class="font-semibold text-sm">${{ number_format($bot->investment->amount ?? 0, 2) }}</p>
                        </div>
                        <div class="bg-white/5 rounded-lg p-2">
                            <p class="text-xs text-gray-400 mb-1">Profit</p>
                            <p class="font-semibold text-sm text-success">${{ number_format($bot->total_profit ?? 0, 2) }}</p>
                        </div>
                        <div class="bg-white/5 rounded-lg p-2">
                            <p class="text-xs text-gray-400 mb-1">Trades</p>
                            <p class="font-semibold text-sm">{{ number_format($bot->total_trades ?? 0) }}</p>
                        </div>
                        <div class="bg-white/5 rounded-lg p-2">
                            <p class="text-xs text-gray-400 mb-1">Success Rate</p>
                            <p class="font-semibold text-sm">{{ number_format($bot->success_rate ?? 0, 1) }}%</p>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center space-x-2">
                        <a href="{{ route('bots.show', $bot) }}" 
                           class="flex-1 px-4 py-2 bg-white/10 hover:bg-white/20 rounded-lg font-semibold transition text-center text-sm">
                            View Details
                        </a>
                        <form action="{{ route('bots.toggle', $bot) }}" method="POST" class="flex-1">
                            @csrf
                            <button type="submit" 
                                    class="w-full px-4 py-2 rounded-lg font-semibold transition text-sm {{ $bot->is_active ? 'bg-red-500 hover:bg-red-600' : 'bg-success hover:bg-green-600' }}">
                                {{ $bot->is_active ? 'Pause' : 'Activate' }}
                            </button>
                        </form>
                    </div>

                    @if($bot->last_trade_at)
                        <p class="text-xs text-gray-400 mt-2 text-center">
                            Última operação: {{ $bot->last_trade_at->diffForHumans() }}
                        </p>
                    @endif
                </div>
            @empty
                <div class="p-8 text-center">
                    <svg class="w-16 h-16 text-gray-600 mb-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/>
                    </svg>
                    <h3 class="text-lg font-bold text-gray-400 mb-2">No bots found</h3>
                    <p class="text-sm text-gray-500 mb-4">Create your first investment</p>
                    <a href="{{ route('investments.plans.index') }}" 
                       class="inline-block px-6 py-3 bg-primary hover:bg-primary-600 rounded-lg font-semibold transition text-sm">
                        Create First Bot
                    </a>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($bots->hasPages())
        <div class="p-4 sm:p-6 border-t border-white/10">
            {{ $bots->links() }}
        </div>
        @endif
    </div>
</div>
@endsection