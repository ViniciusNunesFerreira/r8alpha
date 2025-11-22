<div class="glass-effect p-4 sm:p-6 rounded-lg sm:rounded-xl">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-4 sm:mb-6 gap-3">
        <div>
            <h3 class="text-lg sm:text-xl font-bold flex items-center space-x-2">
                <svg class="w-5 h-5 sm:w-6 sm:h-6 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                <span>Evolução de Lucros</span>
            </h3>
            <p class="text-xs sm:text-sm text-gray-400 mt-1">Acumulado no período</p>
        </div>

        <!-- Period Selector -->
        <div class="flex space-x-1 sm:space-x-2 bg-white/5 rounded-lg p-1 w-full sm:w-auto">
            <button 
                wire:click="setPeriod('24h')"
                class="flex-1 sm:flex-none px-3 sm:px-4 py-1.5 sm:py-2 rounded-lg text-xs sm:text-sm font-semibold transition touch-manipulation {{ $period === '24h' ? 'bg-primary text-white' : 'text-gray-400 hover:text-white' }}">
                24h
            </button>
            <button 
                wire:click="setPeriod('7d')"
                class="flex-1 sm:flex-none px-3 sm:px-4 py-1.5 sm:py-2 rounded-lg text-xs sm:text-sm font-semibold transition touch-manipulation {{ $period === '7d' ? 'bg-primary text-white' : 'text-gray-400 hover:text-white' }}">
                7 dias
            </button>
            <button 
                wire:click="setPeriod('30d')"
                class="flex-1 sm:flex-none px-3 sm:px-4 py-1.5 sm:py-2 rounded-lg text-xs sm:text-sm font-semibold transition touch-manipulation {{ $period === '30d' ? 'bg-primary text-white' : 'text-gray-400 hover:text-white' }}">
                30 dias
            </button>
        </div>
    </div>

    <!-- Chart Container -->
    <div class="relative" style="height: 250px;" wire:loading.class="opacity-50" wire:target="setPeriod">
        <canvas id="profitChart"></canvas>
        
        <!-- Loading Overlay -->
        <div class="absolute inset-0 flex items-center justify-center" wire:loading wire:target="setPeriod">
            <svg class="animate-spin h-8 w-8 text-primary" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="mt-4 sm:mt-6 grid grid-cols-2 sm:grid-cols-3 gap-3 sm:gap-4">
        <div class="p-3 sm:p-4 bg-white/5 rounded-lg">
            <p class="text-xs text-gray-400 mb-1">Lucro no Período</p>
            @if(count($chartData['data'] ?? []) > 0)
                <p class="text-base sm:text-lg font-bold text-success">
                    ${{ number_format(end($chartData['data']), 2) }}
                </p>
            @else
                <p class="text-base sm:text-lg font-bold text-gray-400">$0.00</p>
            @endif
        </div>

        <div class="p-3 sm:p-4 bg-white/5 rounded-lg">
            <p class="text-xs text-gray-400 mb-1">Média Diária</p>
            @if(count($chartData['data'] ?? []) > 0)
                @php
                    $days = $period === '24h' ? 1 : ($period === '7d' ? 7 : 30);
                    $avgDaily = end($chartData['data']) / $days;
                @endphp
                <p class="text-base sm:text-lg font-bold">${{ number_format($avgDaily, 2) }}</p>
            @else
                <p class="text-base sm:text-lg font-bold text-gray-400">$0.00</p>
            @endif
        </div>

        <div class="p-3 sm:p-4 bg-white/5 rounded-lg col-span-2 sm:col-span-1">
            <p class="text-xs text-gray-400 mb-1">Tendência</p>
            @if(count($chartData['data'] ?? []) > 1)
                @php
                    $firstValue = $chartData['data'][0];
                    $lastValue = end($chartData['data']);
                    $trend = $lastValue > $firstValue ? 'up' : ($lastValue < $firstValue ? 'down' : 'neutral');
                @endphp
                <div class="flex items-center space-x-2">
                    @if($trend === 'up')
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                        </svg>
                        <span class="text-base sm:text-lg font-bold text-success">Subindo</span>
                    @elseif($trend === 'down')
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 text-danger" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                        </svg>
                        <span class="text-base sm:text-lg font-bold text-danger">Caindo</span>
                    @else
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14"/>
                        </svg>
                        <span class="text-base sm:text-lg font-bold text-gray-400">Estável</span>
                    @endif
                </div>
            @else
                <p class="text-base sm:text-lg font-bold text-gray-400">N/A</p>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    let profitChart = null;

    function initProfitChart() {
        const ctx = document.getElementById('profitChart');
        if (!ctx) return;

        if (profitChart) {
            profitChart.destroy();
        }

        const chartData = @json($chartData);

        profitChart = new Chart(ctx.getContext('2d'), {
            type: 'line',
            data: {
                labels: chartData.labels || [],
                datasets: [{
                    label: 'Lucro Acumulado',
                    data: chartData.data || [],
                    borderColor: 'rgb(16, 185, 129)',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointRadius: window.innerWidth < 640 ? 2 : 4,
                    pointHoverRadius: window.innerWidth < 640 ? 4 : 6,
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
                        padding: window.innerWidth < 640 ? 8 : 12,
                        displayColors: false,
                        callbacks: {
                            label: function(context) {
                                return 'Lucro: $' + context.parsed.y.toFixed(2);
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
                            font: {
                                size: window.innerWidth < 640 ? 10 : 12
                            },
                            callback: function(value) {
                                return '$' + value.toFixed(0);
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
                            font: {
                                size: window.innerWidth < 640 ? 9 : 11
                            },
                            maxRotation: 0,
                            minRotation: 0,
                            autoSkip: true,
                            maxTicksLimit: window.innerWidth < 640 ? 6 : 12
                        },
                        border: {
                            display: false
                        }
                    }
                }
            }
        });
    }

    // Initialize on load
    document.addEventListener('DOMContentLoaded', initProfitChart);

    // Reinitialize after Livewire updates
    document.addEventListener('livewire:navigated', initProfitChart);
    
    // Update chart when data changes
    Livewire.on('refreshChart', () => {
        setTimeout(initProfitChart, 100);
    });

    // Responsive resize
    let resizeTimeout;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(initProfitChart, 250);
    });
</script>
@endpush