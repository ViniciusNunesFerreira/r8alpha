@extends('layouts.app')

@section('title', 'Complete Payment')
@section('header', 'Complete Your Payment')
@section('subheader', 'Investment #' . $investment->id)

@section('content')
<div class="max-w-4xl mx-auto">
    
    <!-- Payment Status Alert -->
    @if($investment->payment_status === 'pending')
    <div class="glass-effect border border-warning/30 rounded-xl p-4 mb-6 animate-fade-in">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 rounded-lg bg-warning/20 flex items-center justify-center animate-pulse">
                <svg class="w-6 h-6 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="flex-1">
                <p class="text-sm font-semibold text-warning">Payment Pending</p>
                <p class="text-xs text-gray-300">Complete payment to activate your trading bot</p>
            </div>
            <div class="text-right">
                <p class="text-xs text-gray-400">Expires in</p>
                <p class="text-sm font-bold text-warning" id="countdown">29:59</p>
            </div>
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Payment Instructions -->
        <div class="lg:col-span-2 space-y-6">
            
            @if($investment->payment_method === 'pix')
            <!-- PIX Payment -->
            <div class="glass-effect p-6 rounded-xl">
                <div class="flex items-center space-x-3 mb-6">
                    <div class="w-12 h-12 rounded-lg bg-primary/20 flex items-center justify-center">
                        <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold">PIX Payment</h2>
                        <p class="text-sm text-gray-400">Scan QR Code or copy the PIX key</p>
                    </div>
                </div>

                <!-- QR Code -->
                <div class="mb-6 p-6 bg-white rounded-xl">
                    <img src="{{ $investment->payment_data['qr_code'] ?? '' }}" 
                         alt="PIX QR Code" 
                         class="w-64 h-64 mx-auto">
                </div>

                <!-- PIX Code -->
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-300 mb-2">
                        PIX Copia e Cola
                    </label>
                    <div class="flex items-center space-x-2">
                        <input 
                            type="text" 
                            id="pixCode"
                            value="{{ $investment->payment_data['pix_code'] ?? '' }}"
                            readonly
                            class="flex-1 px-4 py-3 bg-white/5 border border-white/10 rounded-lg text-white text-sm font-mono"
                        >
                        <button 
                            onclick="copyPixCode()"
                            class="px-4 py-3 bg-primary hover:bg-primary/80 rounded-lg transition flex items-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/>
                            </svg>
                            <span class="font-semibold">Copy</span>
                        </button>
                    </div>
                </div>

                <!-- Instructions -->
                <div class="p-4 bg-primary/10 border border-primary/20 rounded-lg">
                    <h3 class="font-semibold mb-3 flex items-center space-x-2">
                        <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>How to Pay with PIX</span>
                    </h3>
                    <ol class="space-y-2 text-sm text-gray-300">
                        <li class="flex items-start space-x-2">
                            <span class="font-bold text-primary">1.</span>
                            <span>Open your bank app and select "PIX"</span>
                        </li>
                        <li class="flex items-start space-x-2">
                            <span class="font-bold text-primary">2.</span>
                            <span>Choose "Pay with QR Code" or "Copy and Paste"</span>
                        </li>
                        <li class="flex items-start space-x-2">
                            <span class="font-bold text-primary">3.</span>
                            <span>Scan the QR Code above or paste the PIX key</span>
                        </li>
                        <li class="flex items-start space-x-2">
                            <span class="font-bold text-primary">4.</span>
                            <span>Confirm the payment of <strong>R$ {{ number_format($investment->amount, 2, ',', '.') }}</strong></span>
                        </li>
                        <li class="flex items-start space-x-2">
                            <span class="font-bold text-primary">5.</span>
                            <span>Wait for automatic confirmation (usually instant)</span>
                        </li>
                    </ol>
                </div>
            </div>
            @endif

            @if($investment->payment_method === 'crypto')
            <!-- Crypto Payment -->
            <div class="glass-effect p-6 rounded-xl">
                <div class="flex items-center space-x-3 mb-6">
                    <div class="w-12 h-12 rounded-lg bg-warning/20 flex items-center justify-center">
                        <svg class="w-6 h-6 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold">USDT Payment</h2>
                        <p class="text-sm text-gray-400">Send USDT to the address below</p>
                    </div>
                </div>

                <!-- Amount in USDT -->
                <div class="mb-6 p-4 bg-gradient-to-r from-warning/10 to-warning/5 border border-warning/20 rounded-lg">
                    <p class="text-xs text-gray-400 mb-1">Amount to Send</p>
                    <p class="text-3xl font-bold text-warning">{{ number_format($investment->amount, 2) }} USDT</p>
                    <p class="text-xs text-gray-400 mt-1">Network: TRC20 (Tron)</p>
                </div>

                <!-- Wallet Address -->
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-300 mb-2">
                        Wallet Address (TRC20)
                    </label>
                    <div class="flex items-center space-x-2">
                        <input 
                            type="text" 
                            id="walletAddress"
                            value="{{ $investment->payment_data['wallet_address'] ?? '' }}"
                            readonly
                            class="flex-1 px-4 py-3 bg-white/5 border border-white/10 rounded-lg text-white text-sm font-mono break-all"
                        >
                        <button 
                            onclick="copyWalletAddress()"
                            class="px-4 py-3 bg-warning hover:bg-warning/80 rounded-lg transition flex items-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/>
                            </svg>
                            <span class="font-semibold">Copy</span>
                        </button>
                    </div>
                </div>

                <!-- Warning -->
                <div class="p-4 bg-red-500/10 border border-red-500/30 rounded-lg mb-6">
                    <div class="flex items-start space-x-3">
                        <svg class="w-6 h-6 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <div>
                            <p class="font-semibold text-red-500 mb-1">Important Warning</p>
                            <ul class="text-sm text-gray-300 space-y-1">
                                <li>• Send ONLY USDT via TRC20 network</li>
                                <li>• Sending other tokens or wrong network will result in loss of funds</li>
                                <li>• Double-check the wallet address before sending</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Instructions -->
                <div class="p-4 bg-warning/10 border border-warning/20 rounded-lg">
                    <h3 class="font-semibold mb-3 flex items-center space-x-2">
                        <svg class="w-5 h-5 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>How to Pay with USDT</span>
                    </h3>
                    <ol class="space-y-2 text-sm text-gray-300">
                        <li class="flex items-start space-x-2">
                            <span class="font-bold text-warning">1.</span>
                            <span>Open your crypto wallet (Trust Wallet, Binance, etc.)</span>
                        </li>
                        <li class="flex items-start space-x-2">
                            <span class="font-bold text-warning">2.</span>
                            <span>Select USDT and choose TRC20 network</span>
                        </li>
                        <li class="flex items-start space-x-2">
                            <span class="font-bold text-warning">3.</span>
                            <span>Copy the wallet address above</span>
                        </li>
                        <li class="flex items-start space-x-2">
                            <span class="font-bold text-warning">4.</span>
                            <span>Send exactly <strong>{{ number_format($investment->amount, 2) }} USDT</strong></span>
                        </li>
                        <li class="flex items-start space-x-2">
                            <span class="font-bold text-warning">5.</span>
                            <span>Wait for blockchain confirmation (1-3 minutes)</span>
                        </li>
                    </ol>
                </div>
            </div>
            @endif

            <!-- Cancel Payment -->
            <div class="glass-effect p-4 rounded-xl">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-sm text-gray-400">Having trouble with payment?</p>
                    </div>
                    <button 
                        onclick="confirm('Are you sure you want to cancel this payment?') && window.location.href='{{ route('dashboard') }}'"
                        class="px-4 py-2 bg-red-500/20 text-red-500 hover:bg-red-500/30 rounded-lg transition text-sm font-semibold">
                        Cancel Payment
                    </button>
                </div>
            </div>

        </div>

        <!-- Order Summary -->
        <div class="space-y-6">
            
            <!-- Investment Details -->
            <div class="glass-effect p-6 rounded-xl">
                <h3 class="text-lg font-bold mb-4">Order Summary</h3>
                
                <div class="space-y-3">
                    <div class="flex items-center justify-between py-2 border-b border-white/10">
                        <span class="text-sm text-gray-400">Plan</span>
                        <span class="font-semibold">{{ $investment->investmentPlan->name }}</span>
                    </div>
                    
                    <div class="flex items-center justify-between py-2 border-b border-white/10">
                        <span class="text-sm text-gray-400">Duration</span>
                        <span class="font-semibold">{{ $investment->investmentPlan->duration_days }} Days</span>
                    </div>
                    
                    <div class="flex items-center justify-between py-2 border-b border-white/10">
                        <span class="text-sm text-gray-400">Daily Return</span>
                        <span class="font-semibold text-success">
                            {{ $investment->investmentPlan->daily_return_min }}-{{ $investment->investmentPlan->daily_return_max }}%
                        </span>
                    </div>
                    
                    <div class="flex items-center justify-between py-3 border-t border-white/10 mt-3">
                        <span class="text-sm font-semibold">Amount</span>
                        <span class="text-xl font-bold text-primary">${{ number_format($investment->amount, 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Estimated Returns -->
            <div class="glass-effect p-6 rounded-xl border border-success/30">
                <h3 class="text-lg font-bold mb-4 text-success">Estimated Returns</h3>
                
                <div class="space-y-4">
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Total Profit (Min)</p>
                        <p class="text-2xl font-bold text-success">
                            ${{ number_format($investment->amount * $investment->investmentPlan->daily_return_min / 100 * $investment->investmentPlan->duration_days, 2) }}
                        </p>
                    </div>
                    
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Total Profit (Max)</p>
                        <p class="text-2xl font-bold text-success">
                            ${{ number_format($investment->amount * $investment->investmentPlan->daily_return_max / 100 * $investment->investmentPlan->duration_days, 2) }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Support -->
            <div class="glass-effect p-6 rounded-xl">
                <h3 class="text-lg font-bold mb-3">Need Help?</h3>
                <p class="text-sm text-gray-400 mb-4">
                    Contact our support team if you have any questions.
                </p>
                <a href="#" class="flex items-center justify-center space-x-2 py-3 px-4 bg-primary/20 hover:bg-primary/30 text-primary rounded-lg transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                    <span class="font-semibold">Contact Support</span>
                </a>
            </div>

        </div>
    </div>
</div>

@push('scripts')
<script>
// Countdown timer
let timeLeft = 1800; // 30 minutes
const countdownEl = document.getElementById('countdown');

function updateCountdown() {
    const minutes = Math.floor(timeLeft / 60);
    const seconds = timeLeft % 60;
    
    if (countdownEl) {
        countdownEl.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
    }
    
    if (timeLeft <= 0) {
        window.location.href = '{{ route('dashboard') }}';
    } else {
        timeLeft--;
    }
}

setInterval(updateCountdown, 1000);

// Copy PIX code
function copyPixCode() {
    const pixCode = document.getElementById('pixCode');
    pixCode.select();
    document.execCommand('copy');
    
    showSuccessAlert('PIX code copied successfully!');
}

// Copy wallet address
function copyWalletAddress() {
    const walletAddress = document.getElementById('walletAddress');
    walletAddress.select();
    document.execCommand('copy');
    
    showSuccessAlert('Wallet address copied successfully!');
}

// Check payment status periodically
setInterval(() => {
    fetch('{{ route('investments.check-payment', $investment) }}')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'paid') {
                window.location.href = '{{ route('investments.show', $investment) }}';
            }
        });
}, 10000); // Check every 10 seconds
</script>
@endpush
@endsection