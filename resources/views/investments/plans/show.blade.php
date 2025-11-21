@extends('layouts.app')

@section('title', $plan->name)
@section('header', $plan->name)
@section('subheader', 'Complete your investment details')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    
    <!-- Main Content - Formulário -->
    <div class="lg:col-span-2 space-y-6">
        
        <!-- Plan Summary -->
        <div class="glass-effect p-6 rounded-xl">
            <div class="flex items-start justify-between mb-6">
                <div>
                    <h2 class="text-2xl font-bold mb-2">{{ $plan->name }}</h2>
                    <p class="text-gray-400">{{ $plan->description }}</p>
                </div>
                <div class="px-4 py-2 bg-success/20 text-success rounded-lg">
                    <p class="text-xs">Daily Returns</p>
                    <p class="font-bold">{{ $plan->daily_return_min }}-{{ $plan->daily_return_max }}%</p>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="grid grid-cols-3 gap-4">
                <div class="text-center p-3 bg-white/5 rounded-lg">
                    <p class="text-xs text-gray-400 mb-1">Duration</p>
                    <p class="font-bold">{{ $plan->duration_days }} Days</p>
                </div>
                <div class="text-center p-3 bg-white/5 rounded-lg">
                    <p class="text-xs text-gray-400 mb-1">Min Investment</p>
                    <p class="font-bold">${{ number_format($plan->min_amount, 0) }}</p>
                </div>
                <div class="text-center p-3 bg-white/5 rounded-lg">
                    <p class="text-xs text-gray-400 mb-1">Max Investment</p>
                    <p class="font-bold">${{ number_format($plan->max_amount, 0) }}</p>
                </div>
            </div>
        </div>

        <!-- Investment Form -->
        <div class="glass-effect p-6 rounded-xl">
            <h3 class="text-xl font-bold mb-6">Investment Details</h3>
            
            <form action="{{ route('investments.plans.subscribe', $plan) }}" method="POST" x-data="investmentForm">
                @csrf
                
                <!-- Amount Input -->
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-300 mb-2">
                        Investment Amount (USD)
                    </label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 font-bold">$</span>
                        <input 
                            type="text"
                            data-currency-mask
                            name="amount" 
                            min="{{ $plan->min_amount }}"
                            max="{{ $plan->max_amount }}"
                            step="0.01"
                            required
                            inputmode="decimal"
                            autocomplete="off"
                            class="w-full pl-8 pr-4 py-3 bg-white/5 border border-white/10 rounded-lg text-white text-lg font-bold focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                            placeholder="{{ number_format($plan->min_amount, 2) }}"
                        >
                    </div>

                    <div class="flex items-center justify-between mt-2">
                        <p class="text-xs text-gray-400">
                            Range: ${{ number_format($plan->min_amount, 0) }} - ${{ number_format($plan->max_amount, 0) }}
                        </p>
                        <div class="flex space-x-2">
                            <button type="button" @click="setAmount({{ $plan->min_amount }})" class="px-2 py-1 text-xs bg-white/5 hover:bg-white/10 rounded transition">
                                Min
                            </button>
                            <button type="button" @click="setAmount({{ ($plan->min_amount + $plan->max_amount) / 2 }})" class="px-2 py-1 text-xs bg-white/5 hover:bg-white/10 rounded transition">
                                Mid
                            </button>
                            <button type="button" @click="setAmount({{ $plan->max_amount }})" class="px-2 py-1 text-xs bg-white/5 hover:bg-white/10 rounded transition">
                                Max
                            </button>
                        </div>
                    </div>
                    @error('amount')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Estimated Returns -->
                <div class="mb-6 p-4 bg-gradient-to-r from-success/10 to-success/5 border border-success/20 rounded-lg">
                    <h4 class="text-sm font-semibold text-success mb-3">Estimated Returns</h4>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs text-gray-400 mb-1">Daily (Min)</p>
                            <p class="text-lg font-bold text-white">
                                $<span x-text="(amount * {{ $plan->daily_return_min }} / 100).toFixed(2)">0.00</span>
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 mb-1">Daily (Max)</p>
                            <p class="text-lg font-bold text-white">
                                $<span x-text="(amount * {{ $plan->daily_return_max }} / 100).toFixed(2)">0.00</span>
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 mb-1">Total (Min)</p>
                            <p class="text-xl font-bold text-success">
                                $<span x-text="(amount * {{ $plan->daily_return_min }} / 100 * {{ $plan->duration_days }}).toFixed(2)">0.00</span>
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 mb-1">Total (Max)</p>
                            <p class="text-xl font-bold text-success">
                                $<span x-text="(amount * {{ $plan->daily_return_max }} / 100 * {{ $plan->duration_days }}).toFixed(2)">0.00</span>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Payment Method -->
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-300 mb-3">
                        Payment Method
                    </label>
                    <div class="space-y-3">
                        <!-- Wallet Payment -->
                        <label class="flex items-center p-4 bg-white/5 border border-white/10 rounded-lg cursor-pointer hover:bg-white/10 transition"
                               :class="paymentMethod === 'wallet' ? 'border-primary bg-primary/10' : ''">
                            <input type="radio" name="payment_method" value="wallet" x-model="paymentMethod" class="text-primary focus:ring-primary">
                            <div class="ml-3 flex-1">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-3">
                                        <svg class="w-6 h-6 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                        </svg>
                                        <div>
                                            <p class="font-semibold">Account Wallet</p>
                                            <p class="text-xs text-gray-400">Instant activation</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-xs text-gray-400">Available</p>
                                        <p class="font-bold text-success">${{ number_format($wallet->balance ?? 0, 2) }}</p>
                                    </div>
                                </div>
                            </div>
                        </label>

                        <!-- PIX Payment -->
                        <label class="flex items-center p-4 bg-white/5 border border-white/10 rounded-lg cursor-pointer hover:bg-white/10 transition"
                               :class="paymentMethod === 'pix' ? 'border-primary bg-primary/10' : ''">
                            <input type="radio" name="payment_method" value="pix" x-model="paymentMethod" class="text-primary focus:ring-primary">
                            <div class="ml-3 flex-1">
                                <div class="flex items-center space-x-3">
                                    <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                                    </svg>
                                    <div>
                                        <p class="font-semibold">PIX</p>
                                        <p class="text-xs text-gray-400">Brazilian instant payment</p>
                                    </div>
                                </div>
                            </div>
                        </label>

                        <!-- Crypto Payment -->
                        <label class="flex items-center p-4 bg-white/5 border border-white/10 rounded-lg cursor-pointer hover:bg-white/10 transition"
                               :class="paymentMethod === 'crypto' ? 'border-primary bg-primary/10' : ''">
                            <input type="radio" name="payment_method" value="crypto" x-model="paymentMethod" class="text-primary focus:ring-primary">
                            <div class="ml-3 flex-1">
                                <div class="flex items-center space-x-3">
                                    <svg class="w-6 h-6 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <div>
                                        <p class="font-semibold">Cryptocurrency</p>
                                        <p class="text-xs text-gray-400">USDT (TRC20)</p>
                                    </div>
                                </div>
                            </div>
                        </label>
                    </div>
                    @error('payment_method')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Terms Checkbox -->
                <div class="mb-6">
                    <label class="flex items-start space-x-3 cursor-pointer">
                        <input type="checkbox" required class="mt-1 text-primary focus:ring-primary">
                        <span class="text-sm text-gray-400">
                            I agree to the <a href="#" class="text-primary hover:underline">Terms of Service</a> and understand that cryptocurrency trading involves risks. I confirm that this investment aligns with my financial goals.
                        </span>
                    </label>
                </div>

                <!-- Submit Button -->
                <button type="submit" 
                        class="w-full py-4 px-6 bg-gradient-primary text-white font-bold rounded-lg hover:shadow-glow transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed"
                        :disabled="amount < {{ $plan->min_amount }} || amount > {{ $plan->max_amount }} || !paymentMethod">
                    <span x-show="paymentMethod === 'wallet'">Invest Now - Instant Activation</span>
                    <span x-show="paymentMethod === 'pix'">Continue to PIX Payment</span>
                    <span x-show="paymentMethod === 'crypto'">Continue to Crypto Payment</span>
                </button>
            </form>
        </div>

    </div>

    <!-- Sidebar - Info -->
    <div class="space-y-6">
        
        <!-- What You Get -->
        <div class="glass-effect p-6 rounded-xl">
            <h3 class="text-lg font-bold mb-4">What You Get</h3>
            <div class="space-y-3">
                <div class="flex items-start space-x-3">
                    <svg class="w-5 h-5 text-success flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <div>
                        <p class="font-semibold text-sm">Automated Trading Bot</p>
                        <p class="text-xs text-gray-400">Your personal AI-powered trading assistant</p>
                    </div>
                </div>
                <div class="flex items-start space-x-3">
                    <svg class="w-5 h-5 text-success flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <div>
                        <p class="font-semibold text-sm">24/7 Market Scanning</p>
                        <p class="text-xs text-gray-400">Never miss a profitable opportunity</p>
                    </div>
                </div>
                <div class="flex items-start space-x-3">
                    <svg class="w-5 h-5 text-success flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <div>
                        <p class="font-semibold text-sm">Real-time Dashboard</p>
                        <p class="text-xs text-gray-400">Track every trade and profit</p>
                    </div>
                </div>
                <div class="flex items-start space-x-3">
                    <svg class="w-5 h-5 text-success flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <div>
                        <p class="font-semibold text-sm">Daily Payouts</p>
                        <p class="text-xs text-gray-400">Profits credited automatically</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Security Badge -->
        <div class="glass-effect p-6 rounded-xl border border-success/30">
            <div class="flex items-center space-x-3 mb-3">
                <svg class="w-8 h-8 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                <div>
                    <p class="font-bold">Secure Investment</p>
                    <p class="text-xs text-gray-400">Bank-level security</p>
                </div>
            </div>
            <p class="text-xs text-gray-400">
                Your funds are protected with industry-standard encryption and security protocols.
            </p>
        </div>

        <!-- Support -->
        <div class="glass-effect p-6 rounded-xl">
            <h3 class="text-lg font-bold mb-3">Need Help?</h3>
            <p class="text-sm text-gray-400 mb-4">
                Our support team is available 24/7 to assist you.
            </p>
            <a href="#" class="flex items-center justify-center space-x-2 py-3 px-4 bg-white/10 hover:bg-white/20 rounded-lg transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
                <span class="font-semibold">Contact Support</span>
            </a>
        </div>

    </div>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('investmentForm', () => ({
        amount: {{ $plan->min_amount }},
        paymentMethod: 'wallet',
        
        setAmount(value) {
            this.amount = value;
        }
    }));
});

</script>
@endpush
@endsection