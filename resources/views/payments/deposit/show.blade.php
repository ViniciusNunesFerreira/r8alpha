@extends('layouts.app')

@section('title', 'Deposit Details')
@section('header', 'Deposit Details')
@section('subheader', 'Track your deposit status')

@section('content')
<div class="space-y-6">
   
    <div class="grid grid-cols-1 md:grid-cols-3 md:gap-6">

        <!-- Status Card -->
        <div class="h-full rounded-lg p-6 mb-6 glass-effect overflow-hidden card-hover border border-white/10 hover:border-primary/30 transition-all duration-300">
            
            @if($deposit->status === 'pending' && !$deposit->isExpired())
                <!-- Verificação Automática de Status -->
                <div class="mb-6" data-deposit-checker data-transaction-id="{{ $deposit->transaction_id }}" data-method="long-polling">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="font-bold text-gray-400 mb-1">Awaiting Payment</h3>
                            <p class="text-sm text-gray-600">Automatically checking status...</p>
                            <p class="text-xs text-gray-500 mt-1">
                                Last check: <span data-last-check>--:--:--</span>
                            </p>
                        </div>
                        <div class="animate-spin">
                            <i class="fas fa-sync-alt text-gray-400 text-2xl"></i>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Informações do Depósito -->
            <div>
                <!-- Status Badge -->
                <div class="mb-4">
                    <p class="text-sm text-gray-600 mb-2">Status</p>
                    <span data-status-badge class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold
                        {{ $deposit->status === 'completed' ? 'bg-green-100 text-green-800' : '' }}
                        {{ $deposit->status === 'paid' ? 'bg-green-100 text-green-800' : '' }}
                        {{ $deposit->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                        {{ $deposit->status === 'processing' ? 'bg-blue-100 text-blue-800' : '' }}
                        {{ in_array($deposit->status, ['failed', 'expired', 'cancelled']) ? 'bg-red-100 text-red-800' : '' }}">
                        {{ $deposit->status_label }}
                    </span>
                </div>
                
                <!-- Informações Principais -->
                <div class="grid grid-cols-2 gap-6 mb-6">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Transaction ID</p>
                        <p class="font-mono text-sm font-semibold text-gray-400 break-all">{{ $deposit->transaction_id }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Date and Time</p>
                        <p class="font-semibold text-gray-400">{{ $deposit->created_at->format('d/m/Y \à\s H:i') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Payment Method</p>
                        <p class="font-semibold text-gray-400">{{ $deposit->payment_method_name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Value</p>
                        <p class="text-2xl font-bold text-blue-600">{{ $deposit->formatted_amount_usd }}</p>
                    </div>
                </div>

                @if($deposit->expires_at && !$deposit->isCompleted() || $deposit->expires_at && !$deposit->isPaid )
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <div class="flex items-center">
                            <i class="fas fa-clock text-yellow-900 text-xl mr-3"></i>
                            <div>
                                <p class="text-sm font-semibold text-yellow-900">
                                    @if($deposit->isExpired())
                                        This deposit has expired.
                                    @else
                                        Expires in: <span id="countdown" class="font-bold"></span>
                                    @endif
                                </p>
                                <p class="text-xs text-yellow-700 mt-1">
                                    {{ $deposit->expires_at->format('d/m/Y H:i:s') }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

        </div>

        @if($deposit->status === 'pending' && !$deposit->isExpired())
            <!-- Instruções PIX -->
            @if($deposit->payment_method === 'pix')
                <div class="bg-white rounded-lg shadow-lg p-6 mb-6 col-span-2 z-30 h-full">
                    <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                        <span class="bg-green-100 text-green-600 rounded-full p-2 mr-3">
                            <i class="fas fa-qrcode text-xl"></i>
                        </span>
                        Pay with PIX
                    </h2>

                    <!-- Valor em BRL -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6 text-center">
                        <p class="text-sm text-blue-700 mb-2">Amount to Pay in BRL</p>
                        <p class="text-4xl font-bold text-blue-900">{{ $deposit->formatted_amount_brl }}</p>
                        <p class="text-xs text-blue-600 mt-2">
                            Conversion: {{ $deposit->formatted_amount_usd }} × BRL {{ number_format($deposit->conversion_rate, 2, ',', '.') }}
                        </p>
                    </div>

                    <!-- QR Code -->
                    @if($deposit->qr_code_image)
                        <div class="text-center mb-6">
                            <p class="text-sm font-semibold text-gray-700 mb-3">1. Scan the QR Code</p>
                            <div class="inline-block bg-white p-4 rounded-lg border-2 border-gray-300">
                                {!! QrCode::size(256)->errorCorrection('H')->generate($deposit->qr_code_image); !!}
                            </div>
                            <p class="text-xs text-gray-600 mt-3">
                                Open your bank's app and scan this QR code.
                            </p>
                        </div>
                    @endif

                    <!-- PIX Code -->
                    @if($deposit->pix_code)
                        <div class="mb-6">
                            <p class="text-sm font-semibold text-gray-700 mb-3">2. Or use the PIX Code Copy and Paste</p>
                            <div class="flex items-center">
                                <input 
                                    type="text" 
                                    id="pixCode" 
                                    value="{{ $deposit->pix_code }}" 
                                    readonly
                                    class="flex-1 bg-gray-100 border border-gray-300 rounded-l-lg px-4 py-3 font-mono text-sm text-gray-400"
                                >
                                <button 
                                    onclick="copyToClipboard('pixCode', 'PIX code copied successfully!')"
                                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-r-lg font-semibold transition"
                                >
                                    <i class="fas fa-copy mr-2"></i>Copy
                                </button>
                            </div>
                            <p class="text-xs text-gray-600 mt-2">
                                <i class="fas fa-info-circle"></i> Copy this code into your bank's app using the "Pix Copy and Paste" option.
                            </p>
                        </div>
                    @endif

                    <!-- Instruções -->
                    <div class="bg-gray-50 rounded-lg p-6">
                        <h3 class="font-bold text-gray-900 mb-3">📱 How to pay:</h3>
                        <ol class="space-y-2 text-sm text-gray-700">
                            <li class="flex items-start">
                                <span class="font-bold mr-2">1.</span>
                                <span>Open your bank's app.</span>
                            </li>
                            <li class="flex items-start">
                                <span class="font-bold mr-2">2.</span>
                                <span>Choose the "Pix" or "QR Code" option.</span>
                            </li>
                            <li class="flex items-start">
                                <span class="font-bold mr-2">3.</span>
                                <span>Scan the QR code above OR copy and paste the PIX code.</span>
                            </li>
                            <li class="flex items-start">
                                <span class="font-bold mr-2">4.</span>
                                <span>Confirm the payment of <strong>{{ $deposit->formatted_amount_brl }}</strong></span>
                            </li>
                            <li class="flex items-start">
                                <span class="font-bold mr-2">5.</span>
                                <span>After payment, wait for automatic confirmation (usually instant).</span>
                            </li>
                        </ol>
                    </div>
                </div>
            @endif

            <!-- Instruções Cripto -->
            @if($deposit->payment_method === 'crypto')
                <div class="bg-white rounded-lg shadow-lg p-6 mb-6 col-span-2 z-30 h-full">
                    <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                        <span class="bg-yellow-100 text-yellow-600 rounded-full p-2 mr-3">
                            <i class="fas fa-coins text-xl"></i>
                        </span>
                        Pay with USDT (BEP20)
                    </h2>

                    <!-- Valor em USDT -->
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 mb-6 text-center">
                        <p class="text-sm text-yellow-700 mb-2">Amount to Send</p>
                        <p class="text-4xl font-bold text-yellow-900">{{ $deposit->formatted_amount_crypto }}</p>
                        <p class="text-xs text-yellow-600 mt-2">
                            <strong>Network:</strong> BEP20 (Binance Smart Chain)
                        </p>
                    </div>

                    <!-- Endereço da Wallet -->
                    @if($deposit->crypto_address)
                        <div class="mb-6">
                            <p class="text-sm font-semibold text-gray-700 mb-3">Destination Address (BEP20)</p>
                            <div class="flex items-center mb-4">
                                <input 
                                    type="text" 
                                    id="cryptoAddress" 
                                    value="{{ $deposit->crypto_address }}" 
                                    readonly
                                    class="flex-1 bg-gray-100 border border-gray-300 rounded-l-lg px-4 py-3 font-mono text-sm text-gray-400 break-all"
                                >
                                <button 
                                    onclick="copyToClipboard('cryptoAddress', 'BEP20 address copied! Remember to check the network before sending.')"
                                    class="bg-yellow-600 hover:bg-yellow-700 text-white px-6 py-3 rounded-r-lg font-semibold transition"
                                >
                                    <i class="fas fa-copy mr-2"></i>Copy
                                </button>
                            </div>

                            <!-- QR Code Cripto -->
                            @if($deposit->qr_code_image)
                                <div class="text-center">
                                    <p class="text-sm font-semibold text-gray-700 mb-3">Or scan the QR Code</p>
                                    <div class="inline-block bg-white p-4 rounded-lg border-2 border-gray-300">
                                        {!! QrCode::size(200)->errorCorrection('H')->generate($deposit->crypto_address); !!}
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- Aviso Crítico -->
                    <div class="bg-red-50 border-2 border-red-500 rounded-lg p-6 mb-6">
                        <div class="flex items-start">
                            <i class="fas fa-exclamation-triangle text-red-600 text-3xl mr-4 flex-shrink-0"></i>
                            <div>
                                <h3 class="font-bold text-red-900 mb-3 text-lg">⚠️ CRITICAL WARNING!</h3>
                                <div class="space-y-2 text-sm text-red-900">
                                    <p class="font-semibold">
                                        Send ONLY <strong class="text-lg">USDT</strong> on the network <strong class="text-lg">BEP20 (BSC)</strong>
                                    </p>
                                    <p>
                                        ❌ DO NOT send on other networks (ERC20, TRC20, Polygon, Solana, etc.)
                                    </p>
                                    <p class="font-bold text-base mt-3">
                                        IF YOU SEND ON THE WRONG NETWORK, YOU WILL PERMANENTLY LOSE YOUR FUNDS!
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Instruções Passo a Passo -->
                    <div class="bg-gray-50 rounded-lg p-6 mb-6">
                        <h3 class="font-bold text-gray-900 mb-4">📝 Step by Step Instructions:</h3>
                        <ol class="space-y-3 text-sm text-gray-700">
                            <li class="flex items-start">
                                <span class="bg-blue-600 text-white rounded-full w-6 h-6 flex items-center justify-center font-bold mr-3 flex-shrink-0">1</span>
                                <span>Open your cryptocurrency wallet (Binance, Trust Wallet, MetaMask, etc.)</span>
                            </li>
                            <li class="flex items-start">
                                <span class="bg-blue-600 text-white rounded-full w-6 h-6 flex items-center justify-center font-bold mr-3 flex-shrink-0">2</span>
                                <span><strong>IMPORTANT:</strong> Select USDT as the currency.</span>
                            </li>
                            <li class="flex items-start">
                                <span class="bg-blue-600 text-white rounded-full w-6 h-6 flex items-center justify-center font-bold mr-3 flex-shrink-0">3</span>
                                <span><strong>CHECK THE NETWORK:</strong> Make sure the network is selected <strong class="text-yellow-600">BEP20</strong> or <strong class="text-yellow-600">BSC</strong></span>
                            </li>
                            <li class="flex items-start">
                                <span class="bg-blue-600 text-white rounded-full w-6 h-6 flex items-center justify-center font-bold mr-3 flex-shrink-0">4</span>
                                <span>Paste the address above or scan the QR code.</span>
                            </li>
                            <li class="flex items-start">
                                <span class="bg-blue-600 text-white rounded-full w-6 h-6 flex items-center justify-center font-bold mr-3 flex-shrink-0">5</span>
                                <span>Enter the value: <strong>{{ $deposit->formatted_amount_crypto }}</strong></span>
                            </li>
                            <li class="flex items-start">
                                <span class="bg-blue-600 text-white rounded-full w-6 h-6 flex items-center justify-center font-bold mr-3 flex-shrink-0">6</span>
                                <span><strong>Please confirm the BEP20 network again before sending</strong></span>
                            </li>
                            <li class="flex items-start">
                                <span class="bg-blue-600 text-white rounded-full w-6 h-6 flex items-center justify-center font-bold mr-3 flex-shrink-0">7</span>
                                <span>Confirm the submission and wait for blockchain confirmations (usually 10-15 minutes).</span>
                            </li>
                        </ol>
                    </div>

                    <!-- Checklist de Segurança -->
                    <div class="bg-yellow-50 border border-yellow-300 rounded-lg p-4">
                        <h4 class="font-bold text-yellow-900 mb-3">✅ Checklist before sending:</h4>
                        <div class="space-y-2 text-sm text-yellow-800">
                            <label class="flex items-center cursor-pointer">
                                <input type="checkbox" class="mr-3 w-5 h-5">
                                <span>I confirmed that the selected currency is <strong>USDT</strong></span>
                            </label>
                            <label class="flex items-center cursor-pointer">
                                <input type="checkbox" class="mr-3 w-5 h-5">
                                <span>I confirmed that the selected network is <strong>BEP20 or BSC</strong></span>
                            </label>
                            <label class="flex items-center cursor-pointer">
                                <input type="checkbox" class="mr-3 w-5 h-5">
                                <span>I checked the destination address.</span>
                            </label>
                            <label class="flex items-center cursor-pointer">
                                <input type="checkbox" class="mr-3 w-5 h-5">
                                <span>I checked the amount to send: {{ $deposit->formatted_amount_crypto }}</span>
                            </label>
                        </div>
                    </div>
                </div>
            @endif

        @endif

        @if($deposit->status === 'completed' || $deposit->status === 'paid')
            <div class="bg-green-50 border-2 border-green-500 rounded-lg p-8 text-center col-span-2 z-30">
                <i class="fas fa-check-circle text-green-600 text-6xl mb-4"></i>
                <h2 class="text-2xl font-bold text-green-900 mb-2">Payment Confirmed!</h2>
                <p class="text-green-700 mb-4">Your balance has been successfully credited.</p>
                <p class="text-sm text-green-600">
                    Confirmed on: {{ $deposit->confirmed_at->format('d/m/Y \à\s H:i:s') }}
                </p>
            </div>
        @endif

        @if(in_array($deposit->status, ['failed', 'expired', 'cancelled']))
            <div class="bg-red-50 border-2 border-red-500 rounded-lg p-8 text-center col-span-2 z-30">
                <i class="fas fa-times-circle text-red-600 text-6xl mb-4"></i>
                <h2 class="text-2xl font-bold text-red-900 mb-2">
                    @if($deposit->status === 'expired') Deposit Expired
                    @elseif($deposit->status === 'cancelled') Deposit Canceled
                    @else Deposit Failed
                    @endif
                </h2>
                <p class="text-red-700 mb-4">
                    @if($deposit->status === 'expired') 
                        The time to make the payment has expired.
                    @elseif($deposit->status === 'cancelled')
                        This deposit has been cancelled.
                    @else
                        An error occurred while processing the payment.
                    @endif
                </p>
                <a href="{{ route('deposit.index') }}" 
                   class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition">
                    Make New Deposit
                </a>
            </div>
        @endif
    </div>

</div>
@endsection

@push('scripts')
<script>
    // Função global para copiar para clipboard
    function copyToClipboard(elementId, successMessage) {
        const element = document.getElementById(elementId);
        
        // Método moderno
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(element.value).then(() => {
                Notify.success(successMessage);
            }).catch(err => {
                console.error('Clipboard error:', err);
                fallbackCopy(element, successMessage);
            });
        } else {
            // Fallback para navegadores antigos
            fallbackCopy(element, successMessage);
        }
    }

    function fallbackCopy(element, successMessage) {
        element.select();
        element.setSelectionRange(0, 99999); // Para mobile
        
        try {
            document.execCommand('copy');
            Notify.success(successMessage);
        } catch (err) {
            Notify.error('Failed to copy. Please copy manually.');
        }
    }

    // Countdown timer
    @if($deposit->expires_at && !$deposit->isExpired() && !$deposit->isCompleted())
        const expiresAt = new Date('{{ $deposit->expires_at->toIso8601String() }}').getTime();
        
        const countdown = setInterval(function() {
            const now = new Date().getTime();
            const distance = expiresAt - now;
            
            if (distance < 0) {
                clearInterval(countdown);
                document.getElementById('countdown').innerHTML = 'EXPIRED';
                Notify.warning('Deposit has expired');
                setTimeout(() => window.location.reload(), 2000);
                return;
            }
            
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);
            
            document.getElementById('countdown').innerHTML = minutes + 'm ' + seconds + 's';
        }, 1000);
    @endif

    // Escutar evento de atualização de status
    document.addEventListener('deposit:status-updated', function(e) {
        console.log('Deposit status updated:', e.detail);
        
        // Você pode adicionar lógica customizada aqui
        // Por exemplo, atualizar outros elementos da UI
    });

    // Mostrar notificações de sessão
    document.addEventListener('DOMContentLoaded', function() {
        @if(session('success'))
            Notify.success('{{ session('success') }}');
        @endif

        @if(session('error'))
            Notify.error('{{ session('error') }}');
        @endif

        @if(session('warning'))
            Notify.warning('{{ session('warning') }}');
        @endif

        @if(session('info'))
            Notify.info('{{ session('info') }}');
        @endif
    });
</script>
@endpush