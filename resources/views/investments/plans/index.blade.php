@extends('layouts.app')

@section('title', 'Investment Plans')
@section('header', 'Investment Plans')
@section('subheader', 'Choose the perfect plan for your trading goals')

@section('content')
<div class="space-y-6">
    
    <!-- Header com Saldo -->
    <div class="glass-effect p-4 sm:p-6 rounded-xl">
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl sm:text-2xl font-bold mb-2">Available Investment Plans</h2>
                <p class="text-sm text-gray-400">Start earning passive income with automated crypto arbitrage</p>
            </div>
            
            <!-- Saldo Disponível -->
            <div class="w-full md:w-auto glass-effect px-4 sm:px-6 py-3 sm:py-4 rounded-lg border border-success/30">
                <p class="text-xs text-gray-400 mb-1">Available Balance</p>
                <div class="flex items-center space-x-2">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-xl sm:text-2xl font-bold text-white">
                        ${{ number_format($wallet->balance ?? 0, 2) }}
                    </p>
                </div>
                <a href="{{route('deposit.index')}}" class="text-xs text-primary hover:text-primary/80 transition mt-2 inline-block">
                    + Add Funds
                </a>
            </div>
        </div>
    </div>

    <!-- Grid de Planos -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($plans as $plan)
        <div class="glass-effect rounded-xl overflow-hidden card-hover border border-white/10 hover:border-primary/30 transition-all duration-300 
            {{ $loop->index === 1 ? 'lg:scale-105 border-primary/50' : '' }}">
            
            <!-- Badge "Most Popular" -->
            @if($loop->index === 1)
            <div class="bg-gradient-primary px-4 py-2 text-center">
                <p class="text-white font-bold text-sm">⭐ MOST POPULAR</p>
            </div>
            @endif

            <div class="p-6">
                <!-- Header do Plano -->
                <div class="text-center mb-6">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-full animated-gradient flex items-center justify-center">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold mb-2">{{ $plan->name }}</h3>
                    <p class="text-sm text-gray-400">{{ $plan->description }}</p>
                </div>

                <!-- Range de Investimento -->
                <div class="mb-6 p-4 bg-white/5 rounded-lg">
                    <p class="text-xs text-gray-400 mb-2 text-center">Investment Range</p>
                    <div class="flex items-center justify-center space-x-2">
                        <p class="text-xl font-bold text-white">${{ number_format($plan->min_amount, 0) }}</p>
                        <span class="text-gray-400">-</span>
                        <p class="text-xl font-bold text-white">${{ number_format($plan->max_amount, 0) }}</p>
                    </div>
                </div>

                <!-- Retorno Diário -->
                <div class="mb-6 p-4 bg-gradient-to-r from-success/10 to-success/5 border border-success/20 rounded-lg">
                    <p class="text-xs text-gray-400 mb-2 text-center">Daily Returns</p>
                    <div class="flex items-center justify-center space-x-2">
                        <p class="text-2xl font-bold text-success">{{ $plan->daily_return_min }}%</p>
                        <span class="text-gray-400">to</span>
                        <p class="text-2xl font-bold text-success">{{ $plan->daily_return_max }}%</p>
                    </div>
                </div>

                <!-- Duração -->
                <div class="mb-6 text-center">
                    <div class="inline-flex items-center space-x-2 px-4 py-2 bg-primary/20 text-primary rounded-full">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="font-semibold">{{ $plan->duration_days }} Days</span>
                    </div>
                </div>

                <!-- Features -->
                <div class="space-y-3 mb-6">
                    <div class="flex items-center space-x-3">
                        <svg class="w-5 h-5 text-success flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span class="text-sm text-gray-300">Automated Trading Bot</span>
                    </div>
                    <div class="flex items-center space-x-3">
                        <svg class="w-5 h-5 text-success flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span class="text-sm text-gray-300">24/7 Market Scanning</span>
                    </div>
                    <div class="flex items-center space-x-3">
                        <svg class="w-5 h-5 text-success flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span class="text-sm text-gray-300">Daily Profit Distribution</span>
                    </div>
                    <div class="flex items-center space-x-3">
                        <svg class="w-5 h-5 text-success flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span class="text-sm text-gray-300">
                            @if($plan->is_capital_back)
                                Capital Returned
                            @else
                                Capital Reinvested
                            @endif
                        </span>
                    </div>
                    <div class="flex items-center space-x-3">
                        <svg class="w-5 h-5 text-success flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span class="text-sm text-gray-300">Real-time Dashboard</span>
                    </div>
                </div>

                <!-- Projeção de Retorno -->
                <div class="mb-6 p-4 bg-white/5 rounded-lg">
                    <p class="text-xs text-gray-400 mb-3 text-center">Estimated Total Return</p>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="text-center">
                            <p class="text-xs text-gray-500 mb-1">Min</p>
                            <p class="text-lg font-bold text-success">
                                +{{ number_format($plan->daily_return_min * $plan->duration_days, 0) }}%
                            </p>
                        </div>
                        <div class="text-center">
                            <p class="text-xs text-gray-500 mb-1">Max</p>
                            <p class="text-lg font-bold text-success">
                                +{{ number_format($plan->daily_return_max * $plan->duration_days, 0) }}%
                            </p>
                        </div>
                    </div>
                </div>

                <!-- CTA Button -->
                <a href="{{ route('investments.plans.show', $plan) }}" 
                   class="block w-full py-3 px-6 text-center font-bold rounded-lg transition-all duration-300 
                   {{ $loop->index === 1 
                      ? 'bg-gradient-primary text-white hover:shadow-glow' 
                      : 'bg-white/10 text-white hover:bg-white/20' }}">
                    Get Started
                    <svg class="w-5 h-5 inline-block ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </a>
            </div>
        </div>
        @empty
        <div class="glass-effect  col-span-full text-center py-16 rounded-xl overflow-hidden card-hover border border-white/10 hover:border-primary/30">
            <svg class="w-20 h-20 mx-auto mb-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
            </svg>
            <h3 class="text-xl font-bold text-gray-400 mb-2">No Plans Available</h3>
            <p class="text-gray-500">Check back soon for new investment opportunities</p>
        </div>
        @endforelse
    </div>

    <!-- FAQ Section -->
    <div class="glass-effect p-6 rounded-xl">
        <h3 class="text-xl font-bold mb-6">Frequently Asked Questions</h3>
        <div class="space-y-4">
            <details class="group">
                <summary class="flex items-center justify-between cursor-pointer p-4 bg-white/5 rounded-lg hover:bg-white/10 transition">
                    <span class="font-semibold">How does arbitrage trading work?</span>
                    <svg class="w-5 h-5 text-gray-400 group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </summary>
                <div class="p-4 text-sm text-gray-400">
                    Our bots monitor the Binance exchange market, searching for price differences in cryptocurrency pairs. When a profitable opportunity is detected, the bot automatically executes triangular arbitrage trades, taking advantage of these price inefficiencies.
                </div>
            </details>
            
            <details class="group">
                <summary class="flex items-center justify-between cursor-pointer p-4 bg-white/5 rounded-lg hover:bg-white/10 transition">
                    <span class="font-semibold">When do I receive my profits?</span>
                    <svg class="w-5 h-5 text-gray-400 group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </summary>
                <div class="p-4 text-sm text-gray-400">
                    Profits are calculated and distributed daily, exactly 24 hours after your investment activation time. You can track all earnings in real-time on your dashboard.
                </div>
            </details>
            
            <details class="group">
                <summary class="flex items-center justify-between cursor-pointer p-4 bg-white/5 rounded-lg hover:bg-white/10 transition">
                    <span class="font-semibold">Can I withdraw my investment before it expires?</span>
                    <svg class="w-5 h-5 text-gray-400 group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </summary>
                <div class="p-4 text-sm text-gray-400">
                    Yes, you can request early withdrawal at any time. However, an early withdrawal fee may apply. All accumulated profits will be credited to your wallet upon withdrawal.
                </div>
            </details>
        </div>
    </div>

</div>
@endsection