<div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
        {{-- Bot Status --}}
        <div class="glass-effect p-4 rounded-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-400 mb-1">Status</p>
                    <div class="flex items-center space-x-2">
                        <span class="relative flex h-3 w-3">
                            @if($bot->is_active)
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-success opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-3 w-3 bg-success"></span>
                            @else
                                <span class="relative inline-flex rounded-full h-3 w-3 bg-gray-500"></span>
                            @endif
                        </span>
                        <span class="text-lg font-bold">{{ $bot->is_active ? 'Online' : 'Offline' }}</span>
                    </div>
                </div>
                <div class="p-3 rounded-lg bg-primary/10">
                    <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path>
                    </svg>
                </div>
            </div>
        </div>

        
        <div class="glass-effect p-4 rounded-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-400 mb-1">Total Profit</p>
                    <p class="text-lg font-bold text-success">${{ number_format($stats['total_profit'], 2) }}</p>
                </div>
                <div class="p-3 rounded-lg bg-success/10">
                    <svg class="w-6 h-6 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Success Rate --}}
        <div class="glass-effect p-4 rounded-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-400 mb-1">Success Rate</p>
                    <p class="text-lg font-bold">{{ number_format($stats['success_rate'], 1) }}%</p>
                </div>
                <div class="p-3 rounded-lg bg-secondary/10">
                    <svg class="w-6 h-6 text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Total Trades --}}
        <div class="glass-effect p-4 rounded-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-400 mb-1">Total Trades</p>
                    <p class="text-lg font-bold">{{ number_format($stats['total_trades']) }}</p>
                </div>
                <div class="p-3 rounded-lg bg-primary/10">
                    <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                    </svg>
                </div>
            </div>
        </div>

       
        <div class="glass-effect p-4 rounded-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-400 mb-1">Opportunities Today</p>
                    <p class="text-lg font-bold">{{ number_format($stats['opportunities_today']) }}</p>
                </div>
                <div class="p-3 rounded-lg bg-warning/10">
                    <svg class="w-6 h-6 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <div class="lg:col-span-2 space-y-6">
            {{-- Performance Chart --}}
            <div class="glass-effect p-6 rounded-lg" wire:poll.10s="loadBotData">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold flex items-center space-x-2">
                        <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        <span>Performance (24h)</span>
                    </h3>
                    <button 
                        wire:click="refreshMonitor"
                        class="p-2 rounded-lg bg-white/5 hover:bg-white/10 transition"
                        title="Atualizar dados">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                    </button>
                </div>

                <div class="h-64">
                    <canvas id="performanceChart"></canvas>
                </div>

                <div class="mt-4 grid grid-cols-2 gap-4">
                    <div class="p-3 bg-white/5 rounded-lg">
                        <p class="text-sm text-gray-400 mb-1">Average Profit/Trade</p>
                        <p class="text-lg font-bold text-success">${{ number_format($stats['avg_profit_per_trade'], 2) }}</p>
                    </div>
                    <div class="p-3 bg-white/5 rounded-lg">
                        <p class="text-sm text-gray-400 mb-1">Last Operation</p>
                        <p class="text-lg font-bold">{{ $bot->last_trade_at ? $bot->last_trade_at->diffForHumans() : 'N/A' }}</p>
                    </div>
                </div>
            </div>

           
            <div class="glass-effect p-6 rounded-lg">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold flex items-center space-x-2">
                        <svg class="w-6 h-6 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        <span>Live Opportunities</span>
                    </h3>
                    <div class="flex items-center space-x-2">
                        <span class="px-3 py-1 bg-success/20 text-success text-sm rounded-full font-semibold">
                            {{ $this->activeOpportunities }} Actives
                        </span>
                    </div>
                </div>

                <div class="space-y-3 max-h-96 overflow-y-auto custom-scrollbar">
                    @forelse($recentOpportunities as $opportunity)
                        <div class="p-4 rounded-lg transition-all duration-300 {{ $opportunity->status === 'detected' ? 'bg-gradient-to-r from-primary/10 to-secondary/10 border border-primary/20 animate-pulse-slow' : 'bg-white/5' }}">
                            <div class="flex justify-between items-start mb-3">
                                <div class="flex items-center space-x-2">
                                    <span class="px-3 py-1 {{ $opportunity->status === 'executed' ? 'bg-success/20 text-success' : 'bg-warning/20 text-warning' }} text-sm rounded-full font-bold">
                                        +{{ number_format($opportunity->profit_percentage, 4) }}%
                                    </span>
                                    @if($opportunity->status === 'executed')
                                        <span class="px-2 py-1 bg-success/10 text-success text-xs rounded-full">
                                            ✓ Executed
                                        </span>
                                    @endif
                                </div>
                                <span class="text-xs text-gray-400">
                                    {{ $opportunity->detected_at->format('d/m H:i:s') }}
                                </span>
                            </div>
                            
                            <div class="mb-3">
                                <p class="text-sm font-semibold mb-2">Arbitration Route:</p>
                                <div class="flex items-center space-x-2">
                                    <span class="px-3 py-1 bg-primary/20 text-primary rounded-lg font-semibold">
                                        {{ $opportunity->base_currency }}
                                    </span>
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                    </svg>
                                    <span class="px-3 py-1 bg-secondary/20 text-secondary rounded-lg font-semibold">
                                        {{ $opportunity->intermediate_currency }}
                                    </span>
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                    </svg>
                                    <span class="px-3 py-1 bg-warning/20 text-warning rounded-lg font-semibold">
                                        {{ $opportunity->quote_currency }}
                                    </span>
                                </div>
                            </div>

                            @if(isset($opportunity->prices) && is_array($opportunity->prices))
                                <div class="grid grid-cols-3 gap-2 mb-3">
                                    @foreach($opportunity->prices as $priceData)
                                        <div class="text-center p-2 bg-white/5 rounded-lg">
                                            <p class="text-xs text-gray-400 mb-1">{{ $priceData['symbol'] }}</p>
                                            <p class="text-sm font-semibold">${{ number_format($priceData['price'], 8) }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <div class="flex justify-between items-center pt-3 border-t border-white/10">
                                <span class="text-sm text-gray-400">Estimated Profit:</span>
                                <span class="text-lg font-bold text-success">
                                    ${{ number_format($opportunity->estimated_profit, 2) }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12">
                            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-white/5 mb-4">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                            <p class="text-gray-400">Awaiting arbitration opportunities....</p>
                            <p class="text-sm text-gray-500 mt-2">The bot is continuously monitoring the market.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        
        <div class="space-y-6">
            {{-- Bot Controls --}}
            <div class="glass-effect p-6 rounded-lg">
                <h3 class="text-xl font-bold mb-4 flex items-center space-x-2">
                    <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                    </svg>
                    <span>Bot Controls</span>
                </h3>

                <div class="space-y-4">
                    <div class="p-4 bg-white/5 rounded-lg">
                        <p class="text-sm text-gray-400 mb-1">Instance ID</p>
                        <p class="text-sm font-mono">{{ $bot->instance_id }}</p>
                    </div>

                    <div class="p-4 bg-white/5 rounded-lg">
                        <p class="text-sm text-gray-400 mb-1">Investiment</p>
                        <p class="text-lg font-bold">${{ number_format($bot->investment->amount ?? 0, 2) }}</p>
                    </div>

                    <button 
                        wire:click="toggleBot"
                        wire:loading.attr="disabled"
                        class="w-full py-3 rounded-lg font-bold transition-all duration-300 flex items-center justify-center space-x-2 {{ $bot->is_active ? 'bg-red-500 hover:bg-red-600 active:scale-95' : 'bg-success hover:bg-green-600 active:scale-95' }}">
                        <span wire:loading.remove wire:target="toggleBot">
                            @if($bot->is_active)
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"></path>
                                </svg>
                            @else
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            @endif
                        </span>
                        <span wire:loading wire:target="toggleBot">
                            <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                        <span>{{ $bot->is_active ? 'Pause Bot' : 'Active Bot' }}</span>
                    </button>

                    <p class="text-xs text-center text-gray-400">
                        Last updated: {{ now()->format('H:i:s') }}
                    </p>
                </div>
            </div>

            {{-- Recent Trades --}}
            <div class="glass-effect p-6 rounded-lg">
                <h3 class="text-xl font-bold mb-4 flex items-center space-x-2">
                    <svg class="w-6 h-6 text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    <span>Recent Trades</span>
                </h3>

                <div class="space-y-2 max-h-96 overflow-y-auto custom-scrollbar">
                    @forelse($recentTrades as $trade)
                        <div class="p-3 bg-white/5 rounded-lg hover:bg-white/10 transition-all duration-200 cursor-pointer">
                            <div class="flex justify-between items-center mb-2">
                                <div class="flex items-center space-x-2">
                                    <span class="px-2 py-1 text-xs rounded-lg font-bold {{ $trade->side === 'buy' ? 'bg-success/20 text-success' : 'bg-danger/20 text-danger' }}">
                                        {{ strtoupper($trade->side) }}
                                    </span>
                                    <span class="font-semibold text-sm">{{ $trade->pair }}</span>
                                </div>
                                @if(isset($trade->profit))
                                    <span class="text-xs {{ $trade->profit > 0 ? 'text-success' : 'text-danger' }}">
                                        {{ $trade->profit > 0 ? '+' : '' }}${{ number_format($trade->profit, 2) }}
                                    </span>
                                @endif
                            </div>
                            
                            <div class="flex justify-between items-center text-xs text-gray-400">
                                <span>{{ number_format($trade->amount, 8) }} @ ${{ number_format($trade->price, 8) }}</span>
                                <span>{{ $trade->created_at->format('H:i:s') }}</span>
                            </div>

                            @if($trade->status)
                                <div class="mt-2">
                                    <span class="px-2 py-1 text-xs rounded {{ $trade->status === 'completed' ? 'bg-success/10 text-success' : 'bg-warning/10 text-warning' }}">
                                        {{ ucfirst($trade->status) }}
                                    </span>
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="text-center py-12">
                            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-white/5 mb-4">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                            </div>
                            <p class="text-gray-400">No trades executed yet</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>


<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('livewire:init', function () {
        let chart = null;
        
        function initChart() {
            const ctx = document.getElementById('performanceChart');
            if (!ctx) return;

            if (chart) {
                chart.destroy();
            }

            chart = new Chart(ctx.getContext('2d'), {
                type: 'line',
                data: {
                    labels: @json($performanceData['labels'] ?? []),
                    datasets: [{
                        label: 'Accumulated Profit ($)',
                        data: @json($performanceData['data'] ?? []),
                        borderColor: 'rgb(16, 185, 129)',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        tension: 0.4,
                        fill: true,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        pointBackgroundColor: 'rgb(16, 185, 129)',
                        pointBorderColor: 'rgba(255, 255, 255, 0.8)',
                        pointBorderWidth: 2,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        intersect: false,
                        mode: 'index',
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleColor: 'rgb(255, 255, 255)',
                            bodyColor: 'rgb(16, 185, 129)',
                            borderColor: 'rgb(16, 185, 129)',
                            borderWidth: 1,
                            padding: 12,
                            displayColors: false,
                            callbacks: {
                                label: function(context) {
                                    return 'Profit: $' + context.parsed.y.toFixed(2);
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(255, 255, 255, 0.05)',
                                drawBorder: false,
                            },
                            ticks: {
                                color: 'rgba(255, 255, 255, 0.6)',
                                callback: function(value) {
                                    return '$' + value.toFixed(2);
                                }
                            },
                            border: {
                                display: false
                            }
                        },
                        x: {
                            grid: {
                                color: 'rgba(255, 255, 255, 0.05)',
                                drawBorder: false,
                            },
                            ticks: {
                                color: 'rgba(255, 255, 255, 0.6)',
                                maxRotation: 45,
                                minRotation: 45
                            },
                            border: {
                                display: false
                            }
                        }
                    }
                }
            });
        }

        // Initialize chart on load
        initChart();

        // Update chart when bot data is updated
        Livewire.on('botUpdated', (event) => {
            if (chart && event[0].profit !== undefined) {
                const now = new Date();
                const timeLabel = now.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
                
                // Add new data point
                chart.data.labels.push(timeLabel);
                chart.data.datasets[0].data.push(event[0].profit);
                
                // Keep only last 20 points
                if (chart.data.labels.length > 20) {
                    chart.data.labels.shift();
                    chart.data.datasets[0].data.shift();
                }
                
                chart.update('none'); // Update without animation for better performance
            }
        });

        // Reinitialize chart after Livewire updates
        Livewire.hook('morph.updated', () => {
            initChart();
        });
    });
</script>

<style>
    @keyframes pulse-slow {
        0%, 100% {
            opacity: 1;
        }
        50% {
            opacity: 0.8;
        }
    }

    .animate-pulse-slow {
        animation: pulse-slow 3s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }

    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
    }

    .custom-scrollbar::-webkit-scrollbar-track {
        background: rgba(255, 255, 255, 0.05);
        border-radius: 3px;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: rgba(99, 102, 241, 0.5);
        border-radius: 3px;
        transition: background 0.2s;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: rgba(99, 102, 241, 0.7);
    }

    /* Glass effect */
    .glass-effect {
        background: rgba(255, 255, 255, 0.05);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.1);
    }
</style>

</div>