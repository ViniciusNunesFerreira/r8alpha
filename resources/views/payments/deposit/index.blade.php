@extends('layouts.app')

@section('title', 'Deposit')
@section('header', 'Deposit')
@section('subheader', 'Add funds to your account securely and quickly.')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="glass-effect p-4 sm:p-6 rounded-xl">
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl sm:text-2xl font-bold mb-2">Deposit</h2>
                <p class="text-sm text-gray-400">Add funds to your account securely and quickly.</p>
            </div>
            
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
            </div>
        </div>
    </div>

    <!-- Formulário de Depósito -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div class="glass-effect col-span-full text-center px-8 py-16 rounded-xl overflow-hidden card-hover border border-white/10 hover:border-primary/30">
            <h3 class="text-2xl font-bold text-gray-400 mb-6">Make Deposit</h3>
            <form action="{{ route('deposit.create') }}" method="POST" id="depositForm">
                @csrf

                <!-- Campo de Valor com Máscara de Moeda -->
                <div class="mb-6">
                    <label for="amount" class="block text-sm font-semibold text-gray-400 mb-2">
                        Deposit Amount (USD)
                    </label>
                    <div class="relative">
                        <span class="absolute left-4 top-3 text-gray-500 text-lg font-semibold">$</span>
                        <input 
                            type="text" 
                            id="amount" 
                            data-currency-mask
                            step="0.01" 
                            min="{{ $minDepositUsd }}"
                            @if($maxDepositUsd) max="{{ $maxDepositUsd }}" @endif
                            value="{{ old('amount', $minDepositUsd) }}"
                            class="w-full pl-10 pr-4 py-3 bg-white/5 border-2 border-gray-300 rounded-lg transition text-lg text-white @error('amount') border-red-500 @enderror"
                            required
                            autocomplete="off"
                            inputmode="decimal"
                            name="amount"
                        >
                    </div>
                    @error('amount')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-sm text-gray-600 mt-2">
                        <i class="fas fa-info-circle"></i> 
                        Minimum value: <strong>${{ number_format($minDepositUsd, 2) }}</strong>
                        @if($maxDepositUsd)
                            | Maximum: <strong>${{ number_format($maxDepositUsd, 2) }}</strong>
                        @else
                            | No maximum limit
                        @endif
                    </p>
                </div>

                <!-- Método de Pagamento -->
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-400 mb-3">
                        Payment Method
                    </label>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- PIX -->
                        <label class="payment-method-card cursor-pointer">
                            <input 
                                type="radio" 
                                name="payment_method" 
                                value="pix" 
                                class="hidden payment-method-input"
                                onchange="updatePaymentMethod('pix')"
                                {{ old('payment_method') === 'pix' ? 'checked' : '' }}
                            >
                            <div class="border-2 border-gray-300 rounded-lg p-6 hover:border-blue-500 transition payment-method-content">
                                <div class="flex items-center justify-between mb-3">
                                    <img src="/images/pix-logo.png" alt="PIX" class="h-8" onerror="this.style.display='none'">
                                    <span class="text-2xl">🇧🇷</span>
                                </div>
                                <h3 class="text-lg font-bold text-gray-400 mb-2">PIX</h3>
                                <p class="text-sm text-gray-600 mb-3">Instant payment in Real (BRL)</p>
                                <div class="flex items-center text-green-600 text-sm font-semibold">
                                    <i class="fas fa-check-circle mr-2"></i>
                                    Quick confirmation
                                </div>
                            </div>
                        </label>

                        <!-- Criptomoeda -->
                        <label class="payment-method-card cursor-pointer">
                            <input 
                                type="radio" 
                                name="payment_method" 
                                value="crypto" 
                                class="hidden payment-method-input"
                                onchange="updatePaymentMethod('crypto')"
                                {{ old('payment_method') === 'crypto' ? 'checked' : '' }}
                            >
                            <div class="border-2 border-gray-300 rounded-lg p-6 hover:border-blue-500 transition payment-method-content">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-2xl">₮</span>
                                    <span class="text-xs bg-yellow-100 text-yellow-800 px-2 py-1 rounded font-semibold">BEP20</span>
                                </div>
                                <h3 class="text-lg font-bold text-gray-400 mb-2">USDT (BEP20)</h3>
                                <p class="text-sm text-gray-600 mb-3">USDT cryptocurrency on the BEP20 network</p>
                                <div class="flex items-center text-blue-600 text-sm font-semibold">
                                    <i class="fas fa-shield-alt mr-2"></i>
                                    Binance Smart Chain Network
                                </div>
                            </div>
                        </label>
                    </div>

                    @error('payment_method')
                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Informações de Conversão PIX -->
                <div id="pixConversionInfo" class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4" style="display: none;">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exchange-alt text-blue-600 text-xl mt-1"></i>
                        </div>
                        <div class="ml-3 flex-1">
                            <h4 class="font-semibold text-blue-900 mb-2">Amount in Brazilian Real (BRL)</h4>
                            <p class="text-2xl font-bold text-blue-600 mb-2" id="brlAmount">
                                BRL 0,00
                            </p>
                            <p class="text-xs text-blue-700">
                                <i class="fas fa-info-circle"></i> 
                                Conversion rate: USD 1.00 = BRL {{ number_format($usdToBrlRate, 2, ',', '.') }}
                            </p>
                            <p class="text-xs text-blue-600 mt-2">
                                <strong>Note:</strong> You will make the payment in BRL through PIX and the amount will be credited in USD to your account.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Avisos Cripto -->
                <div id="cryptoWarning" class="mb-6 bg-yellow-50 border-2 border-yellow-400 rounded-lg p-4" style="display: none;">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-triangle text-yellow-600 text-2xl"></i>
                        </div>
                        <div class="ml-3 flex-1">
                            <h4 class="font-bold text-yellow-900 mb-3">⚠️ CRITICAL: Check the network before sending!</h4>
                            <div class="space-y-2 text-sm text-yellow-900">
                                <p class="flex items-start">
                                    <i class="fas fa-check-circle text-green-600 mr-2 mt-1"></i>
                                    <span><strong>Accepted:</strong> USDT on the network <strong class="text-green-700">BEP20 (Binance Smart Chain)</strong></span>
                                </p>
                                <p class="flex items-start">
                                    <i class="fas fa-times-circle text-red-600 mr-2 mt-1"></i>
                                    <span><strong>NOT accepted:</strong> Other networks (ERC20, TRC20, Polygon ...)</span>
                                </p>
                                <div class="mt-4 bg-red-100 border border-red-400 rounded p-3">
                                    <p class="font-bold text-red-900">
                                        <i class="fas fa-radiation text-red-600 mr-2"></i>
                                        If you send USDT on a different network, YOU WILL PERMANENTLY LOSE YOUR FUNDS!
                                    </p>
                                </div>
                                <p class="mt-3 text-xs text-gray-700">
                                    <strong>How to check:</strong> When transferring funds from your wallet, make sure the BEP20 or BSC (Binance Smart Chain) network is selected.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botão de Submit -->
                <div class="mt-8">
                    <button 
                        type="submit" 
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 px-6 rounded-lg transition duration-200 flex items-center justify-center text-lg"
                    >
                        <i class="fas fa-lock mr-2"></i>
                        Continue to Payment
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Histórico de Depósitos -->
    <div class="glass-effect col-span-full text-center py-16 rounded-xl overflow-hidden card-hover border border-white/10 hover:border-primary/30">
        <h3 class="text-2xl font-bold text-gray-400 mb-6">Deposit History</h3>

        @if($deposits->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b-2 border-gray-200">
                            <th class="text-left py-3 px-4 font-semibold text-gray-700">Date</th>
                            <th class="text-left py-3 px-4 font-semibold text-gray-700">ID</th>
                            <th class="text-left py-3 px-4 font-semibold text-gray-700">Method</th>
                            <th class="text-right py-3 px-4 font-semibold text-gray-700">Amount</th>
                            <th class="text-center py-3 px-4 font-semibold text-gray-700">Status</th>
                            <th class="text-center py-3 px-4 font-semibold text-gray-700">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($deposits as $deposit)
                            <tr class="border-b border-gray-100 hover:bg-gray-50">
                                <td class="py-3 px-4 text-sm text-gray-600">
                                    {{ $deposit->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="py-3 px-4 text-sm font-mono text-gray-600">
                                    {{ substr($deposit->transaction_id, 0, 12) }}...
                                </td>
                                <td class="py-3 px-4 text-sm">
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold
                                        {{ $deposit->payment_method === 'pix' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                        {{ $deposit->payment_method_name }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right font-semibold text-gray-600">
                                    {{ $deposit->formatted_amount_usd }}
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold
                                        {{ $deposit->status === 'completed' ? 'bg-green-100 text-green-800' : '' }}
                                        {{ $deposit->status === 'paid' ? 'bg-green-100 text-green-800' : '' }}
                                        {{ $deposit->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                        {{ $deposit->status === 'processing' ? 'bg-blue-100 text-blue-800' : '' }}
                                        {{ in_array($deposit->status, ['failed', 'expired', 'cancelled']) ? 'bg-red-100 text-red-800' : '' }}">
                                        {{ $deposit->status_label }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <a href="{{ route('deposit.show', $deposit->transaction_id) }}" 
                                       class="text-blue-600 hover:text-blue-800 font-semibold text-sm">
                                        View Details
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $deposits->links() }}
            </div>
        @else
            <div class="text-center py-8 text-gray-400">
                <i class="fas fa-inbox text-4xl mb-3"></i>
                <p>You haven't made any deposits yet.</p>
            </div>
        @endif
    </div>
</div>

<style>
    .payment-method-input:checked + .payment-method-content {
        border-color: #3B82F6;
        background-color: #EFF6FF;
    }
</style>

@endsection

@push('scripts')
<script>
    const usdToBrlRate = {{ $usdToBrlRate }};

    function updateConversion() {
        // Obter valor do input oculto (valor numérico real)
        const hiddenInput = document.querySelector('.currency-hidden-value');
        const amount = hiddenInput ? parseFloat(hiddenInput.value) || 0 : 0;
        const brlAmount = amount * usdToBrlRate;
        
        const brlElement = document.getElementById('brlAmount');
        if (brlElement) {
            brlElement.textContent = 'BRL ' + brlAmount.toLocaleString('pt-BR', { 
                minimumFractionDigits: 2, 
                maximumFractionDigits: 2 
            });
        }
    }

    function updatePaymentMethod(method) {
        const pixInfo = document.getElementById('pixConversionInfo');
        const cryptoWarning = document.getElementById('cryptoWarning');
        
        if (method === 'pix') {
            pixInfo.style.display = 'block';
            cryptoWarning.style.display = 'none';
            updateConversion();
        } else if (method === 'crypto') {
            pixInfo.style.display = 'none';
            cryptoWarning.style.display = 'block';
        }
    }

    // Inicializa com o método selecionado
    document.addEventListener('DOMContentLoaded', function() {
        const selectedMethod = document.querySelector('input[name="payment_method"]:checked');
        if (selectedMethod) {
            updatePaymentMethod(selectedMethod.value);
        }

        // Escutar mudanças no valor para atualizar conversão
        const amountInput = document.getElementById('amount');
        if (amountInput) {
            amountInput.addEventListener('input', updateConversion);
            amountInput.addEventListener('blur', updateConversion);
        }

        // Mostrar notificações de sessão
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