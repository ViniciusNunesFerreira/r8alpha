@extends('layouts.app')

@section('title', 'Deposit Funds')
@section('header', 'Deposit Funds')
@section('subheader', 'Add funds to your trading wallet')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    
    <!-- Wallet Balance -->
    <div class="glass-effect p-4 sm:p-6 rounded-xl">
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl sm:text-2xl font-bold mb-2">Your Wallet</h2>
                <p class="text-sm text-gray-400">Manage your deposits and balance</p>
            </div>
            
            <div class="w-full md:w-auto glass-effect px-4 sm:px-6 py-3 sm:py-4 rounded-lg border border-success/30">
                <p class="text-xs text-gray-400 mb-1">Available Balance</p>
                <div class="flex items-center space-x-2">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-success flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-2xl sm:text-3xl font-bold text-white">
                        ${{ number_format($wallet->balance ?? 0, 2) }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Deposit Form -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <div class="lg:col-span-2">
            <div class="glass-effect p-4 sm:p-6 rounded-xl">
                <h3 class="text-lg sm:text-xl font-bold mb-6">New Deposit</h3>
                
                <form id="depositForm" class="space-y-6">
                    @csrf
                    
                    <!-- Amount Input -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-300 mb-2">
                            Deposit Amount (USD)
                        </label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 font-bold text-lg">$</span>
                            <input 
                                type="text"
                                data-currency-mask
                                id="depositAmount"
                                name="amount"
                                min="{{ $minDepositUsd }}"
                                max="{{ $maxDepositUsd }}"
                                step="0.01"
                                required
                                inputmode="decimal"
                                autocomplete="off"
                                class="w-full pl-10 pr-4 py-4 bg-white/5 border border-white/10 rounded-lg text-white text-xl font-bold focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                                placeholder="{{ number_format($minDepositUsd, 2) }}"
                            >
                        </div>

                        <div class="flex items-center justify-between mt-2 flex-wrap gap-2">
                            <p class="text-xs text-gray-400">
                                Min: ${{ number_format($minDepositUsd, 2) }}
                                @if($maxDepositUsd)
                                • Max: ${{ number_format($maxDepositUsd, 2) }}
                                @endif
                            </p>
                            <div class="flex space-x-2">
                                <button type="button" onclick="setDepositAmount({{ $minDepositUsd }})" 
                                    class="px-3 py-1 text-xs bg-white/5 hover:bg-white/10 rounded transition">
                                    Min
                                </button>
                                <button type="button" onclick="setDepositAmount(100)" 
                                    class="px-3 py-1 text-xs bg-white/5 hover:bg-white/10 rounded transition">
                                    $100
                                </button>
                                <button type="button" onclick="setDepositAmount(500)" 
                                    class="px-3 py-1 text-xs bg-white/5 hover:bg-white/10 rounded transition">
                                    $500
                                </button>
                                <button type="button" onclick="setDepositAmount(1000)" 
                                    class="px-3 py-1 text-xs bg-white/5 hover:bg-white/10 rounded transition">
                                    $1,000
                                </button>
                            </div>
                        </div>

                        <!-- Preview in BRL -->
                        <div id="brlPreview" class="mt-3 p-3 bg-primary/10 border border-primary/20 rounded-lg hidden">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-300">Amount in BRL:</span>
                                <span class="text-lg font-bold text-primary" id="brlAmount">R$ 0,00</span>
                            </div>
                            <p class="text-xs text-gray-400 mt-1">Exchange rate: R$ {{ number_format($usdToBrlRate, 2, ',', '.') }} per USD</p>
                        </div>
                    </div>

                    <!-- Payment Method Selection -->
                    <div class="space-y-4">
                        <label class="block text-sm font-semibold text-gray-300 mb-3">
                            Choose Payment Method
                        </label>

                        <!-- PIX Option -->
                        <button type="button" 
                            onclick="submitDeposit('pix')"
                            class="w-full p-4 glass-effect hover:bg-white/10 border-2 border-white/10 hover:border-primary/50 rounded-xl transition-all duration-300 group text-left">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-4">
                                    <div class="w-12 h-12 rounded-lg bg-primary/20 flex items-center justify-center group-hover:scale-110 transition-transform">
                                        <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-white group-hover:text-primary transition">Pay with PIX</h4>
                                        <p class="text-xs text-gray-400">Instant confirmation • No fees</p>
                                    </div>
                                </div>
                                <svg class="w-6 h-6 text-gray-400 group-hover:text-primary transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </div>
                        </button>

                        <!-- Crypto Option -->
                        <button type="button"
                            onclick="submitDeposit('crypto')"
                            class="w-full p-4 glass-effect hover:bg-white/10 border-2 border-white/10 hover:border-warning/50 rounded-xl transition-all duration-300 group text-left">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-4">
                                    <div class="w-12 h-12 rounded-lg bg-warning/20 flex items-center justify-center group-hover:scale-110 transition-transform">
                                        <svg class="w-6 h-6 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-white group-hover:text-warning transition">Pay with USDT</h4>
                                        <p class="text-xs text-gray-400">BEP20 Network • Fast confirmation</p>
                                    </div>
                                </div>
                                <svg class="w-6 h-6 text-gray-400 group-hover:text-warning transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </div>
                        </button>
                    </div>

                </form>
            </div>
        </div>

        <!-- Info Sidebar -->
        <div class="space-y-6">
            
            <!-- Deposit Info -->
            <div class="glass-effect p-4 sm:p-6 rounded-xl">
                <h3 class="font-bold mb-4 flex items-center space-x-2">
                    <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Deposit Information</span>
                </h3>
                <ul class="space-y-3 text-sm text-gray-300">
                    <li class="flex items-start space-x-2">
                        <svg class="w-4 h-4 text-success flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>PIX deposits are confirmed instantly</span>
                    </li>
                    <li class="flex items-start space-x-2">
                        <svg class="w-4 h-4 text-success flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>Crypto deposits take 1-3 minutes</span>
                    </li>
                    <li class="flex items-start space-x-2">
                        <svg class="w-4 h-4 text-success flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>No deposit fees</span>
                    </li>
                    <li class="flex items-start space-x-2">
                        <svg class="w-4 h-4 text-success flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>Secure and encrypted transactions</span>
                    </li>
                </ul>
            </div>

            <!-- Support -->
            <div class="glass-effect p-4 sm:p-6 rounded-xl">
                <h3 class="font-bold mb-3">Need Help?</h3>
                <p class="text-sm text-gray-400 mb-4">
                    Our support team is available 24/7 to assist you.
                </p>
                <a href="https://chat.whatsapp.com/FgrnQYcPohr8tflTWenFV3?mode=hqrt3" class="flex items-center justify-center space-x-2 py-3 px-4 bg-primary/20 hover:bg-primary/30 text-primary rounded-lg transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                    <span class="font-semibold">Contact Support</span>
                </a>
            </div>

        </div>
    </div>

    <!-- Recent Deposits -->
    @if($deposits->count() > 0)
    <div class="glass-effect p-4 sm:p-6 rounded-xl">
        <h3 class="text-lg sm:text-xl font-bold mb-6">Recent Deposits</h3>
        
        <div class="overflow-x-auto -mx-4 sm:-mx-6">
            <div class="inline-block min-w-full align-middle px-4 sm:px-6">
                <table class="min-w-full">
                    <thead>
                        <tr class="border-b border-white/10">
                            <th class="py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Date</th>
                            <th class="py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Amount</th>
                            <th class="py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Method</th>
                            <th class="py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="py-3 text-right text-xs font-semibold text-gray-400 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/10">
                        @foreach($deposits as $deposit)
                        <tr class="hover:bg-white/5 transition">
                            <td class="py-4 text-sm text-gray-300">
                                {{ $deposit->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="py-4 text-sm font-bold text-white">
                                ${{ number_format($deposit->amount_usd, 2) }}
                            </td>
                            <td class="py-4">
                                <span class="inline-flex items-center space-x-1 text-xs">
                                    @if($deposit->payment_method === 'pix')
                                    <span class="w-2 h-2 rounded-full bg-primary"></span>
                                    <span class="text-gray-300">PIX</span>
                                    @else
                                    <span class="w-2 h-2 rounded-full bg-warning"></span>
                                    <span class="text-gray-300">CRYPTO</span>
                                    @endif
                                </span>
                            </td>
                            <td class="py-4">
                                @php
                                    $statusColors = [
                                        'pending' => 'bg-warning/20 text-warning',
                                        'paid' => 'bg-blue-500/20 text-blue-400',
                                        'confirmed' => 'bg-success/20 text-success',
                                        'failed' => 'bg-red-500/20 text-red-400',
                                        'expired' => 'bg-gray-500/20 text-gray-400',
                                        'cancelled' => 'bg-gray-500/20 text-gray-400',
                                    ];
                                @endphp
                                <span class="px-2 py-1 rounded-full text-xs font-bold {{ $statusColors[$deposit->status] ?? 'bg-gray-500/20 text-gray-400' }}">
                                    {{ ucfirst($deposit->status) }}
                                </span>
                            </td>
                            <td class="py-4 text-right">
                                @if($deposit->isPending() && !$deposit->isExpired())
                                <a href="{{ route('payment.show', $deposit->transaction_id) }}" 
                                   class="text-primary hover:text-primary/80 text-sm font-semibold">
                                    Complete →
                                </a>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $deposits->links() }}
        </div>
    </div>
    @endif

</div>

@push('scripts')
<script>
const minDeposit = {{ $minDepositUsd }};
const maxDeposit = {{ $maxDepositUsd ?? 'null' }};
const usdToBrlRate = {{ $usdToBrlRate }};

// Variável de controle para prevenir múltiplas submissões
let isProcessing = false;

// Set deposit amount
function setDepositAmount(amount) {
    if (isProcessing) return;
    
    const input = document.getElementById('depositAmount');
    const event = new Event('input', { bubbles: true });
    
    // Format and set value
    input.value = amount.toFixed(2);
    input.dispatchEvent(event);
    
    // Update BRL preview
    updateBrlPreview(amount);
}

// Update BRL preview
function updateBrlPreview(usdAmount) {
    const brlPreview = document.getElementById('brlPreview');
    const brlAmountEl = document.getElementById('brlAmount');
    
    if (usdAmount >= minDeposit) {
        const brlAmount = usdAmount * usdToBrlRate;
        brlAmountEl.textContent = 'R$ ' + brlAmount.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        brlPreview.classList.remove('hidden');
    } else {
        brlPreview.classList.add('hidden');
    }
}

// Listen to amount input changes
document.getElementById('depositAmount').addEventListener('input', function(e) {
    const value = parseFloat(e.target.value.replace(/[^0-9.]/g, '')) || 0;
    updateBrlPreview(value);
});

// Submit deposit com proteção
function submitDeposit(paymentMethod) {
    // Previne múltiplos cliques
    if (isProcessing) {
        if (typeof Notify !== 'undefined') {
            Notify.warning('Please wait, processing your deposit request...');
        } else {
            alert('Please wait, processing your deposit request...');
        }
        return;
    }
    
    const amountInput = document.getElementById('depositAmount');
    const amount = parseFloat(amountInput.value.replace(/[^0-9.]/g, '')) || 0;
    
    // Validation
    if (amount < minDeposit) {
        if (typeof Notify !== 'undefined') {
            Notify.error(`Minimum deposit is $${minDeposit.toFixed(2)}`);
        } else {
            alert(`Minimum deposit is $${minDeposit.toFixed(2)}`);
        }
        return;
    }
    
    if (maxDeposit && amount > maxDeposit) {
        if (typeof Notify !== 'undefined') {
            Notify.error(`Maximum deposit is $${maxDeposit.toFixed(2)}`);
        } else {
            alert(`Maximum deposit is $${maxDeposit.toFixed(2)}`);
        }
        return;
    }
    
    // Confirmação
    const methodName = paymentMethod === 'pix' ? 'PIX' : 'USDT (Crypto)';
    const amountFormatted = paymentMethod === 'pix' 
        ? 'R$ ' + (amount * usdToBrlRate).toLocaleString('pt-BR', { minimumFractionDigits: 2 })
        : '$' + amount.toFixed(2);
    
    const confirmed = confirm(
        `Confirm Deposit\n\n` +
        `Method: ${methodName}\n` +
        `Amount: ${amountFormatted}\n\n` +
        `This will create a payment link. Continue?`
    );
    
    if (!confirmed) {
        return;
    }
    
    // Marca como processando
    isProcessing = true;
    
    // Desabilita ambos os botões visualmente
    const allButtons = document.querySelectorAll('button[onclick^="submitDeposit"]');
    allButtons.forEach(btn => {
        btn.disabled = true;
        btn.style.opacity = '0.5';
        btn.style.cursor = 'not-allowed';
    });
    
    // Mostra feedback visual
    if (typeof Notify !== 'undefined') {
        Notify.info('Processing your deposit request...');
    }
    
    // Create form and submit
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route('payment.create') }}';
    
    // CSRF Token
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = '{{ csrf_token() }}';
    form.appendChild(csrfInput);
    
    // Payment Type
    const typeInput = document.createElement('input');
    typeInput.type = 'hidden';
    typeInput.name = 'payment_type';
    typeInput.value = 'deposit';
    form.appendChild(typeInput);
    
    // Amount
    const amountInput2 = document.createElement('input');
    amountInput2.type = 'hidden';
    amountInput2.name = 'amount';
    amountInput2.value = amount;
    form.appendChild(amountInput2);
    
    // Payment Method
    const methodInput = document.createElement('input');
    methodInput.type = 'hidden';
    methodInput.name = 'payment_method';
    methodInput.value = paymentMethod;
    form.appendChild(methodInput);
    
    document.body.appendChild(form);
    
    // Submete após pequeno delay (previne double-click)
    setTimeout(() => {
        form.submit();
    }, 500);
    
    // Timeout de segurança
    setTimeout(() => {
        if (isProcessing) {
            isProcessing = false;
            allButtons.forEach(btn => {
                btn.disabled = false;
                btn.style.opacity = '1';
                btn.style.cursor = 'pointer';
            });
            
            if (typeof Notify !== 'undefined') {
                Notify.error('Request timeout. Please try again.');
            } else {
                alert('Request timeout. Please try again.');
            }
        }
    }, 10000); // 10 segundos
}

// Previne navegação acidental durante processamento
window.addEventListener('beforeunload', function(e) {
    if (isProcessing) {
        e.preventDefault();
        e.returnValue = 'Deposit is being processed. Are you sure you want to leave?';
        return e.returnValue;
    }
});
</script>
@endpush
@endsection