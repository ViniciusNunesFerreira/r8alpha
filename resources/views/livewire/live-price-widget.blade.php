 <div wire:poll.5s="loadPrices" class="glass-effect p-4 rounded-xl">
    <div class="flex items-center justify-between mb-4">
        <h4 class="text-sm font-semibold text-gray-400 uppercase tracking-wider">
            Market Overview
        </h4>
        <div wire:loading wire:target="loadPrices" class="loading-dots">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </div>
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
        @foreach($prices as $symbol => $data)
            <div class="p-3 bg-white/5 rounded-lg hover:bg-white/10 transition group cursor-pointer">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold text-gray-400">{{ $data['symbol'] }}</span>
                    <span class="text-xs px-2 py-1 rounded {{ $data['change'] >= 0 ? 'bg-success/20 text-success' : 'bg-danger/20 text-danger' }}">
                        {{ $data['change'] >= 0 ? '+' : '' }}{{ $data['change'] }}%
                    </span>
                </div>
                <p class="text-lg font-bold mb-1">${{ $data['price'] }}</p>
                <div class="text-xs text-gray-400 space-y-1">
                    <div class="flex justify-between">
                        <span>24h High:</span>
                        <span class="text-success">${{ $data['high'] }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>24h Low:</span>
                        <span class="text-danger">${{ $data['low'] }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Volume:</span>
                        <span>{{ $data['volume'] }}</span>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
 </div>
