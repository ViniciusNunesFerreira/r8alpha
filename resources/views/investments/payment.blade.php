@extends('layouts.app')

@section('title', 'Payment Method')
@section('header', 'Choose Payment Method')
@section('subheader', 'Select how you want to pay for your investment')

@section('content')
<div class="max-w-5xl mx-auto">
    
    <!-- Investment Summary -->
    <div class="glass-effect p-4 sm:p-6 rounded-xl mb-6 animate-fade-in">
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div class="flex items-start space-x-4">
                <div class="w-12 h-12 sm:w-16 sm:h-16 rounded-xl animated-gradient flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6 sm:w-8 sm:h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg sm:text-xl font-bold mb-1">{{ $investment->investmentPlan->name }}</h2>
                    <p class="text-sm text-gray-400">{{ $investment->investmentPlan->duration_days }} days • {{ $investment->investmentPlan->daily_return_min }}-{{ $investment->investmentPlan->daily_return_max }}% daily</p>
                </div>
            </div>
            <div class="w-full md:w-auto text-left md:text-right">
                <p class="text-sm text-gray-400 mb-1">Investment Amount</p>
                <p class="text-2xl sm:text-3xl font-bold text-primary">${{ number_format($investment->amount, 2) }}</p>
            </div>
        </div>
    </div>

    <!-- Payment Methods -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        
        <!-- PIX Payment -->
        <button type="button" 
                onclick="showPaymentModal('pix')" 
                id="btn-pix"
                class="w-full h-full glass-effect p-6 rounded-xl border-2 border-white/10 hover:border-primary/50 transition-all duration-300 group text-left disabled:opacity-50 disabled:cursor-not-allowed touch-manipulation">
            <div class="flex items-start justify-between mb-4">
                <div class="w-14 h-14 rounded-xl bg-primary/20 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-8 h-8 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                    </svg>
                </div>
                <div class="px-3 py-1 bg-success/20 text-success rounded-full text-xs font-bold">
                    INSTANT
                </div>
            </div>
            
            <h3 class="text-xl font-bold mb-2 group-hover:text-primary transition">Pay with PIX</h3>
            <p class="text-sm text-gray-400 mb-4">Fast and secure payment via QR Code</p>
            
            <div class="space-y-2">
                <div class="flex items-center space-x-2 text-sm text-gray-300">
                    <svg class="w-4 h-4 text-success flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>Instant confirmation</span>
                </div>
                <div class="flex items-center space-x-2 text-sm text-gray-300">
                    <svg class="w-4 h-4 text-success flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>No fees</span>
                </div>
                <div class="flex items-center space-x-2 text-sm text-gray-300">
                    <svg class="w-4 h-4 text-success flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>Available 24/7</span>
                </div>
            </div>

            <div class="mt-6 pt-4 border-t border-white/10">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-400">Amount in BRL</span>
                    <span class="text-lg font-bold">R$ {{ number_format($investment->amount * config('payment.usd_to_brl_rate'), 2, ',', '.') }}</span>
                </div>
            </div>
        </button>

        <!-- Crypto Payment -->
        <button type="button"
                onclick="showPaymentModal('crypto')"
                id="btn-crypto"
                class="w-full h-full glass-effect p-6 rounded-xl border-2 border-white/10 hover:border-warning/50 transition-all duration-300 group text-left disabled:opacity-50 disabled:cursor-not-allowed touch-manipulation">
            <div class="flex items-start justify-between mb-4">
                <div class="w-14 h-14 rounded-xl bg-warning/20 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-8 h-8 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="px-3 py-1 bg-primary/20 text-primary rounded-full text-xs font-bold">
                    CRYPTO
                </div>
            </div>
            
            <h3 class="text-xl font-bold mb-2 group-hover:text-warning transition">Pay with USDT</h3>
            <p class="text-sm text-gray-400 mb-4">Cryptocurrency payment via BEP20 network</p>
            
            <div class="space-y-2">
                <div class="flex items-center space-x-2 text-sm text-gray-300">
                    <svg class="w-4 h-4 text-success flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>Fast confirmation (1-3 min)</span>
                </div>
                <div class="flex items-center space-x-2 text-sm text-gray-300">
                    <svg class="w-4 h-4 text-success flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>Low network fees</span>
                </div>
                <div class="flex items-center space-x-2 text-sm text-gray-300">
                    <svg class="w-4 h-4 text-success flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>International payments</span>
                </div>
            </div>

            <div class="mt-6 pt-4 border-t border-white/10">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-400">Amount in USDT</span>
                    <span class="text-lg font-bold text-warning">≈ {{ number_format($investment->amount, 2) }} USDT</span>
                </div>
            </div>
        </button>

    </div>

    <!-- Back Button -->
    <div class="flex items-center justify-center mb-6">
        <a href="{{ route('investments.plans.index') }}" 
           class="inline-flex items-center space-x-2 px-6 py-3 glass-effect hover:bg-white/10 rounded-lg transition text-gray-300 hover:text-white touch-manipulation">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span class="font-semibold">Back to Plans</span>
        </a>
    </div>

    <!-- Help Section -->
    <div class="glass-effect p-4 sm:p-6 rounded-xl">
        <div class="flex items-start space-x-3">
            <svg class="w-6 h-6 text-primary flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="flex-1">
                <h3 class="font-semibold mb-2">Payment Information</h3>
                <ul class="space-y-1 text-sm text-gray-400">
                    <li>• Your investment will be activated immediately after payment confirmation</li>
                    <li>• PIX payments are confirmed instantly</li>
                    <li>• Crypto payments require 1-3 blockchain confirmations</li>
                    <li>• You'll receive a notification once your bot starts trading</li>
                </ul>
            </div>
        </div>
    </div>

</div>

<!-- Modal de Confirmação Profissional -->
<div id="confirmationModal" class="fixed inset-0 z-50 hidden opacity-0 transition-opacity duration-300">
    <!-- Overlay com blur -->
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closePaymentModal()"></div>
    
    <!-- Modal Content -->
    <div class="relative min-h-screen flex items-center justify-center p-4">
        <div class="glass-effect rounded-2xl max-w-md w-full p-6 sm:p-8 border border-white/20 shadow-2xl transform transition-transform duration-300 scale-95" id="confirmationModalContent">
            
            <!-- Icon -->
            <div class="w-16 h-16 mx-auto mb-6 rounded-full flex items-center justify-center" id="modal-icon-container">
                <!-- Será preenchido dinamicamente -->
            </div>

            <!-- Title -->
            <h3 class="text-2xl font-bold text-center mb-2">Confirm Payment</h3>
            
            <!-- Subtitle -->
            <p class="text-center text-gray-400 mb-6">Please review your payment details</p>

            <!-- Payment Details -->
            <div class="space-y-4 mb-8">
                <div class="flex items-center justify-between p-4 bg-white/5 rounded-lg">
                    <span class="text-sm text-gray-400">Investment Plan</span>
                    <span class="font-semibold">{{ $investment->investmentPlan->name }}</span>
                </div>
                
                <div class="flex items-center justify-between p-4 bg-white/5 rounded-lg">
                    <span class="text-sm text-gray-400">Payment Method</span>
                    <span class="font-semibold" id="modal-method">PIX</span>
                </div>
                
                <div class="flex items-center justify-between p-4 bg-white/5 rounded-lg">
                    <span class="text-sm text-gray-400">Amount</span>
                    <span class="font-bold text-lg" id="modal-amount">$0.00</span>
                </div>

                <div class="flex items-center justify-between p-4 bg-primary/10 border border-primary/30 rounded-lg">
                    <span class="text-sm text-primary font-semibold">Duration</span>
                    <span class="font-bold text-primary">{{ $investment->investmentPlan->duration_days }} Days</span>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex flex-col sm:flex-row gap-3">
                <button 
                    type="button"
                    onclick="closePaymentModal()"
                    class="flex-1 px-6 py-3 bg-white/5 hover:bg-white/10 text-white rounded-lg transition font-semibold border border-white/10 touch-manipulation">
                    Cancel
                </button>
                <button 
                    type="button"
                    id="confirmPaymentBtn"
                    onclick="confirmPayment()"
                    class="flex-1 px-6 py-3 bg-gradient-primary text-white rounded-lg transition font-semibold hover:shadow-glow touch-manipulation">
                    Confirm Payment
                </button>
            </div>

        </div>
    </div>
</div>

<!-- Modal de Loading -->
<div id="loadingModal" class="fixed inset-0 z-50 hidden opacity-0 transition-opacity duration-300">
    <div class="absolute inset-0 bg-black/80 backdrop-blur-sm"></div>
    
    <div class="relative min-h-screen flex items-center justify-center p-4">
        <div class="glass-effect rounded-2xl max-w-sm w-full p-8 text-center border border-white/20 transform transition-transform duration-300 scale-95" id="loadingModalContent">
            
            <!-- Animated Icon -->
            <div class="w-20 h-20 mx-auto mb-6 relative">
                <div class="absolute inset-0 rounded-full border-4 border-primary/30"></div>
                <div class="absolute inset-0 rounded-full border-4 border-transparent border-t-primary animate-spin"></div>
                <div class="absolute inset-0 flex items-center justify-center">
                    <svg class="w-10 h-10 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24" id="loading-icon">
                        <!-- Será preenchido dinamicamente -->
                    </svg>
                </div>
            </div>

            <h3 class="text-xl font-bold mb-2">Processing Payment</h3>
            <p class="text-gray-400 mb-4">Please wait while we create your payment link...</p>
            
            <!-- Progress Bar -->
            <div class="w-full bg-white/10 rounded-full h-2 overflow-hidden">
                <div class="bg-gradient-primary h-full rounded-full animate-progress"></div>
            </div>

            <p class="text-xs text-gray-500 mt-4">This may take a few seconds</p>
        </div>
    </div>
</div>

<!-- Hidden Form -->
<form id="paymentForm" action="{{ route('payment.create') }}" method="POST" class="hidden">
    @csrf
    <input type="hidden" name="payment_type" value="investment">
    <input type="hidden" name="investment_id" value="{{ $investment->id }}">
    <input type="hidden" name="amount" value="{{ $investment->amount }}">
    <input type="hidden" name="payment_method" id="payment_method_input">
</form>

@push('styles')
<style>
@keyframes scaleIn {
    from {
        opacity: 0;
        transform: scale(0.9);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

@keyframes progress {
    0% { width: 0%; }
    50% { width: 70%; }
    100% { width: 100%; }
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

body.modal-open {
    overflow: hidden;
}
</style>
@endpush

@push('scripts')
<script>
// Variável de controle
let isProcessing = false;
let selectedMethod = null;

// Dados do investimento
const investmentData = {
    planName: '{{ $investment->investmentPlan->name }}',
    amount: {{ $investment->amount }},
    amountBRL: {{ $investment->amount * config('payment.usd_to_brl_rate') }},
    duration: {{ $investment->investmentPlan->duration_days }}
};

// Mostra modal de confirmação
function showPaymentModal(method) {
    if (isProcessing) {
        showNotification('Please wait, processing your request...', 'warning');
        return;
    }
    
    selectedMethod = method;
    const modal = document.getElementById('confirmationModal');
    const modalContent = document.getElementById('confirmationModalContent');
    const iconContainer = document.getElementById('modal-icon-container');
    const methodElement = document.getElementById('modal-method');
    const amountElement = document.getElementById('modal-amount');
    
    // Define ícone e cor baseado no método
    if (method === 'pix') {
        iconContainer.className = 'w-16 h-16 mx-auto mb-6 rounded-full bg-primary/20 flex items-center justify-center';
        iconContainer.innerHTML = `
            <svg class="w-8 h-8 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
            </svg>
        `;
        methodElement.textContent = 'PIX';
        amountElement.textContent = 'R$ ' + investmentData.amountBRL.toLocaleString('pt-BR', { minimumFractionDigits: 2 });
    } else {
        iconContainer.className = 'w-16 h-16 mx-auto mb-6 rounded-full bg-warning/20 flex items-center justify-center';
        iconContainer.innerHTML = `
            <svg class="w-8 h-8 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        `;
        methodElement.textContent = 'USDT (BEP20)';
        amountElement.textContent = '$' + investmentData.amount.toFixed(2) + ' USDT';
    }
    
    // Mostra modal com animação
    modal.classList.remove('hidden');
    document.body.classList.add('modal-open');
    
    // Trigger reflow
    modal.offsetHeight;
    
    // Anima entrada
    setTimeout(() => {
        modal.classList.remove('opacity-0');
        modalContent.classList.remove('scale-95');
        modalContent.classList.add('scale-100');
    }, 10);
}

// Fecha modal de confirmação
function closePaymentModal() {
    if (isProcessing) return;
    
    const modal = document.getElementById('confirmationModal');
    const modalContent = document.getElementById('confirmationModalContent');
    
    // Anima saída
    modal.classList.add('opacity-0');
    modalContent.classList.remove('scale-100');
    modalContent.classList.add('scale-95');
    
    setTimeout(() => {
        modal.classList.add('hidden');
        document.body.classList.remove('modal-open');
    }, 300);
    
    selectedMethod = null;
}

// Confirma pagamento
function confirmPayment() {
    if (isProcessing || !selectedMethod) return;
    
    isProcessing = true;
    
    // Fecha modal de confirmação
    const confirmModal = document.getElementById('confirmationModal');
    const confirmModalContent = document.getElementById('confirmationModalContent');
    
    confirmModal.classList.add('opacity-0');
    confirmModalContent.classList.remove('scale-100');
    confirmModalContent.classList.add('scale-95');
    
    setTimeout(() => {
        confirmModal.classList.add('hidden');
    }, 300);
    
    // Mostra loading
    showLoadingModal(selectedMethod);
    
    // Desabilita botões
    document.getElementById('btn-pix').disabled = true;
    document.getElementById('btn-crypto').disabled = true;
    
    // Preenche e submete formulário
    document.getElementById('payment_method_input').value = selectedMethod;
    
    // Log para debug
    console.log('Submitting payment:', {
        method: selectedMethod,
        amount: investmentData.amount,
        investmentId: {{ $investment->id }}
    });
    
    // IMPORTANTE: Desabilita beforeunload antes de submeter
    window.onbeforeunload = null;
    
    setTimeout(() => {
        const form = document.getElementById('paymentForm');
        console.log('Form action:', form.action);
        console.log('Form method:', form.method);
        form.submit();
    }, 800);
    
    // Timeout de segurança
    setTimeout(() => {
        if (isProcessing) {
            console.error('Payment submission timeout');
            hideLoadingModal();
            isProcessing = false;
            document.getElementById('btn-pix').disabled = false;
            document.getElementById('btn-crypto').disabled = false;
            showNotification('Request timeout. Please try again.', 'error');
            
            // Reativa beforeunload
            setupBeforeUnload();
        }
    }, 15000);
}

// Mostra loading
function showLoadingModal(method) {
    const modal = document.getElementById('loadingModal');
    const modalContent = document.getElementById('loadingModalContent');
    const icon = document.getElementById('loading-icon');
    
    icon.innerHTML = method === 'pix' 
        ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>'
        : '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>';
    
    modal.classList.remove('hidden');
    
    // Trigger reflow
    modal.offsetHeight;
    
    // Anima entrada
    setTimeout(() => {
        modal.classList.remove('opacity-0');
        modalContent.classList.remove('scale-95');
        modalContent.classList.add('scale-100');
    }, 10);
}

// Esconde loading
function hideLoadingModal() {
    const modal = document.getElementById('loadingModal');
    const modalContent = document.getElementById('loadingModalContent');
    
    modal.classList.add('opacity-0');
    modalContent.classList.remove('scale-100');
    modalContent.classList.add('scale-95');
    
    setTimeout(() => {
        modal.classList.add('hidden');
        document.body.classList.remove('modal-open');
    }, 300);
}

// Sistema de notificações
function showNotification(message, type = 'info') {
    if (typeof Notify !== 'undefined') {
        Notify[type](message);
        return;
    }
    
    const colors = {
        success: 'bg-success',
        error: 'bg-red-500',
        warning: 'bg-warning',
        info: 'bg-primary'
    };
    
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 ${colors[type]} text-white px-6 py-4 rounded-lg shadow-lg z-[60]`;
    notification.style.animation = 'scaleIn 0.3s ease-out';
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transform = 'translateY(-20px)';
        notification.style.transition = 'all 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Configura beforeunload (apenas se usuário tentar sair sem confirmar)
function setupBeforeUnload() {
    window.onbeforeunload = function(e) {
        // Só previne se tiver modal aberto E não estiver processando
        const confirmModalVisible = !document.getElementById('confirmationModal').classList.contains('hidden');
        
        if (confirmModalVisible && !isProcessing) {
            e.preventDefault();
            e.returnValue = 'You have an unsaved payment. Are you sure you want to leave?';
            return e.returnValue;
        }
    };
}

// Inicializa beforeunload
setupBeforeUnload();

// Fecha modal com ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && !isProcessing) {
        closePaymentModal();
    }
});

// Previne zoom duplo em iOS
let lastTouchEnd = 0;
document.addEventListener('touchend', function(event) {
    const now = Date.now();
    if (now - lastTouchEnd <= 300) {
        event.preventDefault();
    }
    lastTouchEnd = now;
}, false);

// Debug: Log quando a página carrega
console.log('Payment page loaded:', {
    investment: investmentData,
    csrfToken: '{{ csrf_token() }}',
    actionUrl: '{{ route("payment.create") }}'
});
</script>
@endpush
@endsection