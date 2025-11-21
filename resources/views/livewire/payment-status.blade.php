<div wire:poll.5s="checkStatus" class="glass-effect border rounded-xl p-4 mb-6 animate-fade-in
    {{ $status === 'paid' ? 'border-success/30 bg-success/5' : 
       ($expired ? 'border-red-500/30 bg-red-500/5' : 'border-warning/30 bg-warning/5') }}">
    
    <div class="flex items-center space-x-3">
        <!-- Icon -->
        <div class="w-10 h-10 rounded-lg flex items-center justify-center
            {{ $status === 'paid' ? 'bg-success/20' : 
               ($expired ? 'bg-red-500/20' : 'bg-warning/20 animate-pulse') }}">
            
            @if($status === 'paid')
                <svg class="w-6 h-6 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            @elseif($expired)
                <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            @else
                <svg class="w-6 h-6 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            @endif
        </div>

        <!-- Content -->
        <div class="flex-1">
            @if($status === 'paid')
                <p class="text-sm font-semibold text-success">Payment Confirmed!</p>
                <p class="text-xs text-gray-300">Your trading bot has been activated</p>
            @elseif($expired)
                <p class="text-sm font-semibold text-red-500">Payment Expired</p>
                <p class="text-xs text-gray-300">Please create a new investment</p>
            @else
                <p class="text-sm font-semibold text-warning">Awaiting Payment</p>
                <p class="text-xs text-gray-300">Complete payment to activate your bot</p>
            @endif
        </div>

        <!-- Timer or Status -->
        @if($status === 'pending' && !$expired)
            <div class="text-right">
                <p class="text-xs text-gray-400">Expires in</p>
                <p class="text-sm font-bold text-warning">{{ $this->formattedTime }}</p>
            </div>
        @elseif($status === 'paid')
            <div class="px-3 py-1 bg-success/20 text-success rounded-full text-xs font-semibold">
                Active
            </div>
        @elseif($expired)
            <a href="{{ route('investments.plans.index') }}" 
               class="px-3 py-1 bg-primary/20 text-primary rounded-full text-xs font-semibold hover:bg-primary/30 transition">
                New Investment
            </a>
        @endif
    </div>

    <!-- Loading Indicator -->
    <div wire:loading wire:target="checkStatus" class="mt-2">
        <div class="flex items-center space-x-2 text-xs text-gray-400">
            <svg class="animate-spin h-3 w-3" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>Checking payment status...</span>
        </div>
    </div>
</div>