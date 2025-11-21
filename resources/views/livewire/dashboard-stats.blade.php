<div wire:poll.10s="loadStats" class="grid grid-cols-1 md:grid-cols-1 lg:grid-cols-4 gap-6">
    <!-- Total Invested -->
    <div class="glass-effect p-6 rounded-xl card-hover relative overflow-hidden">
        <div class="absolute top-0 right-0 w-32 h-32 bg-primary/10 rounded-full blur-3xl"></div>
        <div class="relative">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 rounded-lg bg-primary/20 flex items-center justify-center">
                    <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <span wire:loading wire:target="loadStats" class="loading-dots text-primary">
                    <span></span>
                    <span></span>
                    <span></span>
                </span>
            </div>
            <p class="text-sm text-gray-400 mb-1">Total Invested</p>
            <p class="text-3xl font-bold text-white mb-2">
                ${{ number_format($stats['total_invested'] ?? 0, 2) }}
            </p>
            <div class="flex items-center text-sm">
                <span class="text-gray-400">{{ $stats['active_investments'] ?? 0 }} active investments</span>
            </div>
        </div>
    </div>

    <!-- Current Balance -->
    <div class="glass-effect p-6 rounded-xl card-hover relative overflow-hidden">
        <div class="absolute top-0 right-0 w-32 h-32 bg-success/10 rounded-full blur-3xl"></div>
        <div class="relative">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 rounded-lg bg-success/20 flex items-center justify-center">
                    <svg class="w-6 h-6 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                </div>
                <span wire:loading wire:target="loadStats" class="loading-dots text-success">
                    <span></span>
                    <span></span>
                    <span></span>
                </span>
            </div>
            <p class="text-sm text-gray-400 mb-1">Current Balance Invests</p>
            <p class="text-3xl font-bold text-white mb-2">
                ${{ number_format($stats['current_balance'] ?? 0, 2) }}
            </p>
            <div class="flex items-center text-sm">
                <svg class="w-4 h-4 mr-1 {{ ($stats['profit_percentage'] ?? 0) >= 0 ? 'text-success' : 'text-red-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ ($stats['profit_percentage'] ?? 0) >= 0 ? 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6' : 'M13 17h8m0 0V9m0 8l-8-8-4 4-6-6' }}"/>
                </svg>
                <span class="{{ ($stats['profit_percentage'] ?? 0) >= 0 ? 'text-success' : 'text-red-500' }}">
                    {{ number_format($stats['profit_percentage'] ?? 0, 2) }}%
                </span>
            </div>
        </div>
    </div>

    <!-- Total Profit -->
    <div class="glass-effect p-6 rounded-xl card-hover relative overflow-hidden">
        <div class="absolute top-0 right-0 w-32 h-32 bg-warning/10 rounded-full blur-3xl"></div>
        <div class="relative">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 rounded-lg bg-warning/20 flex items-center justify-center">
                    <svg class="w-6 h-6 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                </div>
                <span wire:loading wire:target="loadStats" class="loading-dots text-warning">
                    <span></span>
                    <span></span>
                    <span></span>
                </span>
            </div>
            <p class="text-sm text-gray-400 mb-1">Total Profit</p>
            <p class="text-3xl font-bold text-success mb-2">
                +${{ number_format($stats['total_profit'] ?? 0, 2) }}
            </p>
            <div class="flex items-center text-sm">
                <span class="text-gray-400">Today: <span class="text-success">+${{ number_format($stats['profit_today'] ?? 0, 2) }}</span></span>
            </div>
        </div>
    </div>

    <!-- Active Bots -->
    <div class="glass-effect p-6 rounded-xl card-hover relative overflow-hidden">
        <div class="absolute top-0 right-0 w-32 h-32 bg-secondary/10 rounded-full blur-3xl"></div>
        <div class="relative">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 rounded-lg bg-secondary/20 flex items-center justify-center">
                    <svg class="w-6 h-6 text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/>
                    </svg>
                </div>
                <span wire:loading wire:target="loadStats" class="loading-dots text-secondary">
                    <span></span>
                    <span></span>
                    <span></span>
                </span>
            </div>
            <p class="text-sm text-gray-400 mb-1">Active Bots</p>
            <p class="text-3xl font-bold text-white mb-2">
                {{ $stats['active_bots'] ?? 0 }}<span class="text-xl text-gray-400">/{{ $stats['total_bots'] ?? 0 }}</span>
            </p>
            <div class="flex items-center text-sm">
                <span class="relative flex h-2 w-2 mr-2">
                    @if(($stats['active_bots'] ?? 0) > 0)
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-success opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-success"></span>
                    @else
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-gray-500"></span>
                    @endif
                </span>
                <span class="text-gray-400">{{ $stats['trades_today'] ?? 0 }} trades today</span>
            </div>
        </div>
    </div>

    <div class="col-span-full  overflow-hidden">

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Card 1: Sponsor -->
            <x-card class="glass-effect">
                <h3 class="text-sm font-semibold uppercase text-gray-400 mb-2">Sponsor</h3>
                <p class="text-xl font-bold text-cyan-400">
                    {{ auth()->user()->sponsor?->username ?? 'None' }}
                </p>
            </x-card>

            <!-- Card 2: Link -->
            <x-card x-data="{ link: '{{ url('/ref/' . auth()->user()->username) }}' }" class="glass-effect">
                <h3 class="text-sm font-semibold uppercase text-gray-400 mb-2">Your Link</h3>
                
                <!-- Code Field (Read Only) -->
                <code id="referral-link-input" class="block w-full bg-gray-900 text-xs p-3 rounded-lg text-gray-200 break-all overflow-hidden mb-3">
                    {{ url('/ref/' . auth()->user()->username) }}
                </code>
                
                <!-- Copy btn -->
                <button 
                    @click="copyToClipboard(link, 'Referral link copied successfully!')"
                    class="w-full flex items-center justify-center space-x-2 px-4 py-2 text-sm font-medium rounded-lg text-white bg-cyan-600 hover:bg-cyan-700 transition duration-150 ease-in-out shadow-md"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2v-2m-2 2h-4a2 2 0 01-2-2V7a2 2 0 012-2h4a2 2 0 012 2v10a2 2 0 01-2 2z"></path></svg>
                    <span>Copy Link</span>
                </button>
            </x-card>

            <!-- Card 3: Total Comission -->
            <x-card class="glass-effect">
                <h3 class="text-sm font-semibold uppercase text-gray-400 mb-2">Total Commission</h3>
                <p class="text-2xl font-extrabold text-yellow-400">
                    ${{ number_format($totalCommission, 2) }}
                </p>
            </x-card>
    
        </div>

    </div>
    

    <!-- Performance Metrics (Full Width) -->
    <div class="glass-effect p-6 rounded-xl col-span-full">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">

            <!-- Wallet Balance -->
            <div class="text-center">
                <div class="mb-3">
                    <div class="w-20 h-20 mx-auto rounded-full bg-gradient-to-r from-purple-500 to-pink-500 flex items-center justify-center">
                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                    </div>
                </div>
                <p class="text-sm font-semibold text-gray-300">Deposit Balance</p>
                <p class="text-xs text-primary mt-1">${{ number_format($stats['wallet_balance'] ?? 0, 2) }}</p>
            </div>

            <!-- Success Rate -->
            <div class="text-center">
                <div class="mb-3">
                    <div class="w-20 h-20 mx-auto rounded-full bg-gradient-to-r from-primary to-secondary flex items-center justify-center">
                        <span class="text-2xl font-bold text-white">{{ number_format($stats['average_success_rate'] ?? 0, 0) }}%</span>
                    </div>
                </div>
                <p class="text-sm font-semibold text-gray-300">Avg Success Rate</p>
                <p class="text-xs text-gray-500 mt-1">Across all bots</p>
            </div>

            <!-- Best Bot Performance -->
            <div class="text-center">
                <div class="mb-3">
                    <div class="w-20 h-20 mx-auto rounded-full bg-gradient-to-r from-success to-emerald-400 flex items-center justify-center">
                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                        </svg>
                    </div>
                </div>
                <p class="text-sm font-semibold text-gray-300">Best Bot Profit</p>
                <p class="text-xs text-success mt-1">+${{ number_format($stats['best_bot_profit'] ?? 0, 2) }}</p>
            </div>

            <!-- Total Trades Today -->
            <div class="text-center">
                <div class="mb-3">
                    <div class="w-20 h-20 mx-auto rounded-full bg-gradient-to-r from-warning to-yellow-400 flex items-center justify-center">
                        <span class="text-2xl font-bold text-white">{{ $stats['trades_today'] ?? 0 }}</span>
                    </div>
                </div>
                <p class="text-sm font-semibold text-gray-300">Trades Today</p>
                <p class="text-xs text-gray-500 mt-1">All active bots</p>
            </div>

           
        </div>
    </div>
</div>

<script>
    
    function showSuccessAlert(message) {
        const alertHtml = `
            <div id="dynamic-success-alert" class="fixed top-10 right-10 z-50 p-4 bg-success/10 border border-success/30 rounded-lg flex items-center justify-between shadow-xl animate-fade-in" style="min-width: 300px;">
                <div class="flex items-center space-x-3">
                    <svg class="w-6 h-6 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-success">${message}</p>
                </div>
                <button onclick="this.closest('#dynamic-success-alert').remove()" class="text-success hover:text-success/80">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        `;
        
        const oldAlert = document.getElementById('dynamic-success-alert');
        if (oldAlert) {
            oldAlert.remove();
        }

        document.body.insertAdjacentHTML('beforeend', alertHtml);
        setTimeout(() => {
            const currentAlert = document.getElementById('dynamic-success-alert');
            if (currentAlert) {
                currentAlert.classList.remove('animate-fade-in');
                currentAlert.classList.add('animate-fade-out');
                setTimeout(() => currentAlert.remove(), 500); 
            }
        }, 5000);
    }



    function copyToClipboard(textToCopy, successMessage) {
        
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(textToCopy).then(() => {
                showSuccessAlert(successMessage);
            }).catch(err => {
                
                fallbackCopyTextToClipboard(textToCopy, successMessage);
            });
        } else {
            
            fallbackCopyTextToClipboard(textToCopy, successMessage);
        }
    }


    function fallbackCopyTextToClipboard(textToCopy, successMessage) {
        const textArea = document.createElement("textarea");
        textArea.value = textToCopy;
        textArea.style.position = "fixed";
        textArea.style.left = "-999999px";
        textArea.style.top = "-999999px";

        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();

        try {
            document.execCommand('copy');
            showSuccessAlert(successMessage);
        } catch (err) {
           
            console.error('Failed to copy the text: ', err);
            alert('We were unable to copy the link. Try selecting and copying it manually.');
        } finally {
            document.body.removeChild(textArea);
        }
    }
</script>
