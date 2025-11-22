@extends('layouts.app')

@section('title', $deposit->payment_type === 'investment' ? 'Complete Payment' : 'Deposit Payment')
@section('header', $deposit->payment_type === 'investment' ? 'Complete Your Investment' : 'Complete Your Deposit')
@section('subheader', 'Transaction #' . substr($deposit->transaction_id, 0, 8))

@section('content')
<div class="max-w-5xl mx-auto">
    
    <!-- Payment Status Alert -->
    @if($deposit->isPending() && !$deposit->isExpired())
    <div class="glass-effect border border-warning/30 rounded-xl p-4 sm:p-6 mb-6 animate-fade-in">
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 sm:gap-4">
            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-lg bg-warning/20 flex items-center justify-center animate-pulse flex-shrink-0">
                <svg class="w-5 h-5 sm:w-6 sm:h-6 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm sm:text-base font-semibold text-warning">Awaiting Payment</p>
                <p class="text-xs sm:text-sm text-gray-300">Complete the payment to {{ $deposit->payment_type === 'investment' ? 'activate your trading bot' : 'credit your account' }}</p>
            </div>
            <div class="w-full sm:w-auto text-left sm:text-right">
                <p class="text-xs text-gray-400">Expires in</p>
                <p class="text-base sm:text-lg font-bold text-warning" id="countdown">--:--</p>
            </div>
        </div>
    </div>
    @endif

    @if($deposit->isExpired())
    <div class="glass-effect border border-red-500/30 rounded-xl p-4 sm:p-6 mb-6">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-lg bg-red-500/20 flex items-center justify-center">
                <svg class="w-5 h-5 sm:w-6 sm:h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-sm sm:text-base font-semibold text-red-500">Payment Expired</p>
                <p class="text-xs sm:text-sm text-gray-300">This payment link has expired. Please create a new {{ $deposit->payment_type }}.</p>
            </div>
        </div>
    </div>
    @endif

    @if($deposit->isCompleted())
    <div class="glass-effect border border-success/30 rounded-xl p-4 sm:p-6 mb-6">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-lg bg-success/20 flex items-center justify-center">
                <svg class="w-5 h-5 sm:w-6 sm:h-6 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <div>
                <p class="text-sm sm:text-base font-semibold text-success">Payment Confirmed!</p>
                <p class="text-xs sm:text-sm text-gray-300">{{ $deposit->payment_type === 'investment' ? 'Your bot is now active and trading' : 'Funds have been credited to your account' }}</p>
            </div>
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Payment Instructions -->
        <div class="lg:col-span-2 space-y-6">
            
            @if($deposit->payment_method === 'pix')
            <!-- PIX Payment -->
            <div class="glass-effect p-4 sm:p-6 rounded-xl">
                <div class="flex items-center space-x-3 mb-6">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-lg bg-primary/20 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg sm:text-xl font-bold">PIX Payment</h2>
                        <p class="text-xs sm:text-sm text-gray-400">Scan QR Code or copy the PIX key</p>
                    </div>
                </div>

                <!-- QR Code -->
                @if($deposit->qr_code_image && !$deposit->isExpired())
                <div class="mb-6 p-4 sm:p-6 bg-white rounded-xl">
                    {!! QrCode::size(256)->errorCorrection('H')->generate($deposit->qr_code_image); !!}
                </div>
                @endif

                <!-- PIX Code -->
                @if($deposit->pix_code && !$deposit->isExpired())
                <div class="mb-6">
                    <label class="block text-xs sm:text-sm font-semibold text-gray-300 mb-2">
                        PIX Copia e Cola
                    </label>
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                        <input 
                            type="text" 
                            id="pixCode"
                            value="{{ $deposit->pix_code }}"
                            readonly
                            class="flex-1 px-3 sm:px-4 py-2 sm:py-3 bg-white/5 border border-white/10 rounded-lg text-white text-xs sm:text-sm font-mono break-all"
                        >
                        <button 
                            onclick="copyToClipboard('pixCode', 'PIX code copied!')"
                            class="px-4 py-2 sm:py-3 bg-primary hover:bg-primary/80 rounded-lg transition flex items-center justify-center space-x-2 flex-shrink-0">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/>
                            </svg>
                            <span class="font-semibold text-sm">Copy</span>
                        </button>
                    </div>
                </div>
                @endif

                <!-- Instructions -->
                <div class="p-4 bg-primary/10 border border-primary/20 rounded-lg">
                    <h3 class="font-semibold mb-3 flex items-center space-x-2">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="text-sm sm:text-base">How to Pay with PIX</span>
                    </h3>
                    <ol class="space-y-2 text-xs sm:text-sm text-gray-300">
                        <li class="flex items-start space-x-2">
                            <span class="font-bold text-primary flex-shrink-0">1.</span>
                            <span>Open your bank app and select "PIX"</span>
                        </li>
                        <li class="flex items-start space-x-2">
                            <span class="font-bold text-primary flex-shrink-0">2.</span>
                            <span>Choose "Pay with QR Code" or "Copy and Paste"</span>
                        </li>
                        <li class="flex items-start space-x-2">
                            <span class="font-bold text-primary flex-shrink-0">3.</span>
                            <span>Scan the QR Code above or paste the PIX key</span>
                        </li>
                        <li class="flex items-start space-x-2">
                            <span class="font-bold text-primary flex-shrink-0">4.</span>
                            <span>Confirm the payment of <strong>R$ {{ number_format($deposit->amount_brl, 2, ',', '.') }}</strong></span>
                        </li>
                        <li class="flex items-start space-x-2">
                            <span class="font-bold text-primary flex-shrink-0">5.</span>
                            <span>Wait for automatic confirmation (usually instant)</span>
                        </li>
                    </ol>
                </div>
            </div>
            @endif

            @if($deposit->payment_method === 'crypto')
            <!-- Crypto Payment -->
            <div class="glass-effect p-4 sm:p-6 rounded-xl">
                <div class="flex items-center space-x-3 mb-6">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-lg bg-warning/20 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg sm:text-xl font-bold">USDT Payment</h2>
                        <p class="text-xs sm:text-sm text-gray-400">Send {{ $deposit->crypto_currency }} via {{ $deposit->crypto_network }}</p>
                    </div>
                </div>

                <!-- Amount in USDT -->
                @if($deposit->amount_crypto && !$deposit->isExpired())
                <div class="mb-6 p-4 bg-gradient-to-r from-warning/10 to-warning/5 border border-warning/20 rounded-lg">
                    <p class="text-xs text-gray-400 mb-1">Amount to Send</p>
                    <p class="text-2xl sm:text-3xl font-bold text-warning">{{ number_format($deposit->amount_crypto, 2) }} {{ $deposit->crypto_currency }}</p>
                    <p class="text-xs text-gray-400 mt-1">Network: {{ $deposit->crypto_network }}</p>
                </div>
                @endif

                <!-- Wallet Address -->
                @if($deposit->crypto_address && !$deposit->isExpired())
                <div class="mb-6">
                    <label class="block text-xs sm:text-sm font-semibold text-gray-300 mb-2">
                        Wallet Address ({{ $deposit->crypto_network }})
                    </label>
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                        <input 
                            type="text" 
                            id="walletAddress"
                            value="{{ $deposit->crypto_address }}"
                            readonly
                            class="flex-1 px-3 sm:px-4 py-2 sm:py-3 bg-white/5 border border-white/10 rounded-lg text-white text-xs sm:text-sm font-mono break-all"
                        >
                        <button 
                            onclick="copyToClipboard('walletAddress', 'Address copied!')"
                            class="px-4 py-2 sm:py-3 bg-warning hover:bg-warning/80 rounded-lg transition flex items-center justify-center space-x-2 flex-shrink-0">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/>
                            </svg>
                            <span class="font-semibold text-sm">Copy</span>
                        </button>
                    </div>
                </div>
                @endif

                <!-- Instructions -->
                <div class="p-4 bg-warning/10 border border-warning/20 rounded-lg">
                    <h3 class="font-semibold mb-3 flex items-center space-x-2">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <span class="text-sm sm:text-base">Important Instructions</span>
                    </h3>
                    <ol class="space-y-2 text-xs sm:text-sm text-gray-300">
                        <li class="flex items-start space-x-2">
                            <span class="font-bold text-warning flex-shrink-0">1.</span>
                            <span>Send <strong>exactly {{ number_format($deposit->amount_crypto, 2) }} {{ $deposit->crypto_currency }}</strong></span>
                        </li>
                        <li class="flex items-start space-x-2">
                            <span class="font-bold text-warning flex-shrink-0">2.</span>
                            <span>Use only <strong>{{ $deposit->crypto_network }} network</strong></span>
                        </li>
                        <li class="flex items-start space-x-2">
                            <span class="font-bold text-warning flex-shrink-0">3.</span>
                            <span>Copy the wallet address above</span>
                        </li>
                        <li class="flex items-start space-x-2">
                            <span class="font-bold text-warning flex-shrink-0">4.</span>
                            <span>Complete the transaction in your wallet app</span>
                        </li>
                        <li class="flex items-start space-x-2">
                            <span class="font-bold text-warning flex-shrink-0">5.</span>
                            <span>Wait for blockchain confirmation (1-3 minutes)</span>
                        </li>
                    </ol>
                </div>

                <!-- Warning -->
                <div class="mt-4 p-4 bg-red-500/10 border border-red-500/30 rounded-lg">
                    <div class="flex items-start space-x-2">
                        <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <p class="text-xs sm:text-sm text-red-400">
                            <strong>Warning:</strong> Sending to wrong network or wrong amount may result in permanent loss of funds!
                        </p>
                    </div>
                </div>
            </div>
            @endif

            <!-- Cancel Button -->
            @if($deposit->isPending() && !$deposit->isExpired())
            <div class="glass-effect p-4 rounded-xl">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                    <div class="flex items-center space-x-3">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-xs sm:text-sm text-gray-400">Having trouble with payment?</p>
                    </div>
                    <form action="{{ route('payment.cancel', $deposit->transaction_id) }}" method="POST" onsubmit="return confirm('Are you sure you want to cancel this payment?')">
                        @csrf
                        <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-red-500/20 text-red-500 hover:bg-red-500/30 rounded-lg transition text-xs sm:text-sm font-semibold">
                            Cancel Payment
                        </button>
                    </form>
                </div>
            </div>
            @endif

        </div>

        <!-- Summary Sidebar -->
        <div class="space-y-6">
            
            <!-- Transaction Details -->
            <div class="glass-effect p-4 sm:p-6 rounded-xl">
                <h3 class="text-base sm:text-lg font-bold mb-4">
                    {{ $deposit->payment_type === 'investment' ? 'Investment Summary' : 'Transaction Details' }}
                </h3>
                
                <div class="space-y-3">
                    @if($investment)
                    <div class="flex items-center justify-between py-2 border-b border-white/10">
                        <span class="text-xs sm:text-sm text-gray-400">Plan</span>
                        <span class="font-semibold text-sm">{{ $investment->investmentPlan->name }}</span>
                    </div>
                    
                    <div class="flex items-center justify-between py-2 border-b border-white/10">
                        <span class="text-xs sm:text-sm text-gray-400">Duration</span>
                        <span class="font-semibold text-sm">{{ $investment->investmentPlan->duration_days }} Days</span>
                    </div>
                    
                    <div class="flex items-center justify-between py-2 border-b border-white/10">
                        <span class="text-xs sm:text-sm text-gray-400">Daily Return</span>
                        <span class="font-semibold text-success text-sm">
                            {{ $investment->investmentPlan->daily_return_min }}-{{ $investment->investmentPlan->daily_return_max }}%
                        </span>
                    </div>
                    @endif

                    <div class="flex items-center justify-between py-2 border-b border-white/10">
                        <span class="text-xs sm:text-sm text-gray-400">Status</span>
                        <span class="px-2 py-1 rounded-full text-xs font-bold {{ $deposit->status === 'pending' ? 'bg-warning/20 text-warning' : ($deposit->status === 'confirmed' ? 'bg-success/20 text-success' : 'bg-gray-500/20 text-gray-400') }}">
                            {{ ucfirst($deposit->status) }}
                        </span>
                    </div>
                    
                    <div class="flex items-center justify-between py-3 border-t border-white/10 mt-3">
                        <span class="text-xs sm:text-sm font-semibold">Amount</span>
                        <span class="text-lg sm:text-xl font-bold text-primary">${{ number_format($deposit->amount_usd, 2) }}</span>
                    </div>

                    @if($deposit->amount_brl)
                    <div class="flex items-center justify-between py-2">
                        <span class="text-xs text-gray-400">In BRL</span>
                        <span class="text-sm font-semibold">R$ {{ number_format($deposit->amount_brl, 2, ',', '.') }}</span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Estimated Returns (for investments) -->
            @if($investment)
            <div class="glass-effect p-4 sm:p-6 rounded-xl border border-success/30">
                <h3 class="text-base sm:text-lg font-bold mb-4 text-success">Estimated Returns</h3>
                
                <div class="space-y-4">
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Total Profit (Min)</p>
                        <p class="text-xl sm:text-2xl font-bold text-success">
                            ${{ number_format($investment->amount * $investment->investmentPlan->daily_return_min / 100 * $investment->investmentPlan->duration_days, 2) }}
                        </p>
                    </div>
                    
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Total Profit (Max)</p>
                        <p class="text-xl sm:text-2xl font-bold text-success">
                            ${{ number_format($investment->amount * $investment->investmentPlan->daily_return_max / 100 * $investment->investmentPlan->duration_days, 2) }}
                        </p>
                    </div>
                </div>
            </div>
            @endif

            <!-- Support -->
            <div class="glass-effect p-4 sm:p-6 rounded-xl">
                <h3 class="text-base sm:text-lg font-bold mb-3">Need Help?</h3>
                <p class="text-xs sm:text-sm text-gray-400 mb-4">
                    Contact our support team if you have any questions.
                </p>
                <a href="https://chat.whatsapp.com/FgrnQYcPohr8tflTWenFV3?mode=hqrt3" class="flex items-center justify-center space-x-2 py-2 sm:py-3 px-4 bg-primary/20 hover:bg-primary/30 text-primary rounded-lg transition">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                    <span class="font-semibold text-xs sm:text-sm">Contact Support</span>
                </a>
            </div>

        </div>
    </div>
</div>

@push('scripts')
<script>
// Countdown timer
@if($deposit->isPending() && !$deposit->isExpired() && $deposit->expires_at)
let expiresAt = new Date('{{ $deposit->expires_at->toIso8601String() }}');
const countdownEl = document.getElementById('countdown');

function updateCountdown() {
    const now = new Date();
    const timeLeft = Math.max(0, Math.floor((expiresAt - now) / 1000));
    
    const minutes = Math.floor(timeLeft / 60);
    const seconds = timeLeft % 60;
    
    if (countdownEl) {
        countdownEl.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
    }
    
    if (timeLeft <= 0) {
        window.location.reload();
    }
}

updateCountdown();
setInterval(updateCountdown, 1000);
@endif

// Copy to clipboard function
function copyToClipboard(elementId, successMessage) {
    const element = document.getElementById(elementId);
    element.select();
    element.setSelectionRange(0, 99999); // For mobile devices
    
    document.execCommand('copy');
    
    if (typeof Notify !== 'undefined') {
        Notify.success(successMessage);
    } else {
        alert(successMessage);
    }
}

// Check payment status periodically
@if($deposit->isPending() && !$deposit->isExpired())
let checkCount = 0;
const maxChecks = 180; // 30 minutes (10s intervals)

function checkPaymentStatus() {
    if (checkCount >= maxChecks) {
        clearInterval(statusInterval);
        return;
    }
    
    fetch('{{ route('deposit.check-status-long', $deposit->transaction_id) }}')
        .then(response => response.json())
        .then(data => {
            if (data.is_completed) {
                if (typeof Notify !== 'undefined') {
                    Notify.success('Payment confirmed successfully!');
                }
                setTimeout(() => {
                    window.location.href = '{{ $deposit->payment_type === "investment" ? route("dashboard") : route("deposit.index") }}';
                }, 2000);
            } else if (data.is_expired) {
                window.location.reload();
            }
        })
        .catch(error => {
            console.error('Error checking status:', error);
        });
    
    checkCount++;
}

const statusInterval = setInterval(checkPaymentStatus, 10000); // Check every 10 seconds
checkPaymentStatus(); // Check immediately
@endif
</script>
@endpush
@endsection