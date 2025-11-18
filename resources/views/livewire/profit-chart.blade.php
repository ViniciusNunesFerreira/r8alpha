<div class="glass-effect p-6 rounded-xl" wire:poll.30s="loadChartData">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h3 class="text-xl font-bold flex items-center space-x-2">
                <span>Profit Analytics</span>
                <span wire:loading wire:target="loadChartData" class="loading-dots">
                    <span></span>
                    <span></span>
                    <span></span>
                </span>
            </h3>
            <p class="text-sm text-gray-400 mt-1">Track your earnings over time</p>
        </div>

        <!-- Period Selector -->
        <div class="flex space-x-2 bg-white/5 rounded-lg p-1">
            <button 
                wire:click="setPeriod('week')"
                class="px-4 py-2 rounded-lg text-sm font-semibold transition {{ $period === 'week' ? 'bg-primary text-white' : 'text-gray-400 hover:text-white' }}">
                Week
            </button>
            <button 
                wire:click="setPeriod('month')"
                class="px-4 py-2 rounded-lg text-sm font-semibold transition {{ $period === 'month' ? 'bg-primary text-white' : 'text-gray-400 hover:text-white' }}">
                Month
            </button>
            <button 
                wire:click="setPeriod('year')"
                class="px-4 py-2 rounded-lg text-sm font-semibold transition {{ $period === 'year' ? 'bg-primary text-white' : 'text-gray-400 hover:text-white' }}">
                Year
            </button>
        </div>
    </div>

    <!-- Chart Container (CONDITIONAL RENDERING ADDED HERE) -->
    <div class="relative" style="height: 400px;">
        @if($hasData)
            <canvas id="profitChart"></canvas>
        @else
            <!-- Placeholder para quando não houver dados -->
            <div class="flex items-center justify-center h-full text-center p-4">
                <div class="text-gray-400">
                    <svg class="w-12 h-12 mx-auto text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <p class="mt-4 text-lg font-semibold text-white">Nenhum dado disponível para este período.</p>
                    <p class="text-sm">Inicie suas operações ou mude o seletor de período acima.</p>
                </div>
            </div>
        @endif
    </div>

    <!-- Chart Legend (REMOVED $chartData check, now dependent on $hasData logic) -->
    <div class="grid grid-cols-2 gap-4 mt-6">
        <div class="p-4 bg-white/5 rounded-lg">
            <div class="flex items-center space-x-2 mb-2">
                <!-- Alterado para usar a cor 'success' da sua paleta -->
                <div class="w-4 h-4 rounded" style="background-color: rgb(16, 185, 129);"></div>
                <span class="text-sm font-semibold text-gray-300">Total Profit</span>
            </div>
            <p class="text-2xl font-bold text-success" style="color: rgb(16, 185, 129);">
                +${{ number_format(collect($chartData['datasets'][0]['data'] ?? [])->sum(), 2) }}
            </p>
            <p class="text-xs text-gray-400 mt-1">
                @if($period === 'week')
                    Last 7 days
                @elseif($period === 'month')
                    Last 30 days
                @else
                    Last 12 months
                @endif
            </p>
        </div>

        <div class="p-4 bg-white/5 rounded-lg">
            <div class="flex items-center space-x-2 mb-2">
                <!-- Alterado para usar a cor 'primary' da sua paleta -->
                <div class="w-4 h-4 rounded" style="background-color: rgb(99, 102, 241);"></div>
                <span class="text-sm font-semibold text-gray-300">Total Trades</span>
            </div>
            <p class="text-2xl font-bold text-primary" style="color: rgb(99, 102, 241);">
                {{ collect($chartData['datasets'][1]['data'] ?? [])->sum() }}
            </p>
            <p class="text-xs text-gray-400 mt-1">
                Avg: {{ number_format(collect($chartData['datasets'][1]['data'] ?? [])->avg(), 1) }} per day
            </p>
        </div>
    </div>

    <!-- Performance Insights -->
    <div class="mt-6 p-4 bg-gradient-to-r from-primary/10 to-secondary/10 border border-primary/20 rounded-lg">
        <div class="flex items-start space-x-3">
            <svg class="w-6 h-6 text-primary flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="flex-1">
                <p class="font-semibold text-white mb-1">Performance Insight</p>
                <p class="text-sm text-gray-300">
                    @php
                        // Nota: Se $chartData estiver vazio, $data também estará. Os acessos com ?? [] evitam erros.
                        $data = collect($chartData['datasets'][0]['data'] ?? []);
                        $trend = $data->count() > 1 && $data->last() > $data->first() ? 'up' : 'down';
                        $change = $data->count() > 1 && $data->first() > 0 
                            ? (($data->last() - $data->first()) / $data->first()) * 100 
                            : 0;
                    @endphp
                    
                    @if($trend === 'up')
                        📈 Your profits are trending upward with a {{ number_format(abs($change), 1) }}% increase over this period. Keep up the great work!
                    @else
                        📊 Your trading activity is {{ $change < 0 ? 'stabilizing' : 'consistent' }}. Consider reviewing your bot configurations for optimization opportunities.
                    @endif
                </p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
{{-- IMPORTANTE: Certifique-se de que a biblioteca Chart.js está carregada antes de inicializar o gráfico. --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    // Configuração global para usar fontes brancas, ideal para temas escuros
    Chart.defaults.color = 'rgba(255, 255, 255, 0.7)';
    Chart.defaults.font.family = 'Inter, sans-serif';

    document.addEventListener('livewire:load', function () {
        let profitChartInstance = null;

        function initChart() {
            const ctx = document.getElementById('profitChart');
            // O gráfico só será inicializado se o elemento canvas existir no DOM.
            if (!ctx) return; 

            const chartData = @json($chartData);
            
            // Verifica se há datasets para desenhar o gráfico.
            // Esta verificação é um fallback, mas a variável $hasData do Livewire
            // é a principal responsável por renderizar ou não o canvas.
            if (!chartData || !chartData.datasets || chartData.datasets.length === 0) {
                 return; 
            }

            // Destroy existing chart if it exists
            if (profitChartInstance) {
                profitChartInstance.destroy();
            }

            profitChartInstance = new Chart(ctx.getContext('2d'), {
                type: 'line',
                data: chartData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            labels: {
                                color: 'rgba(255, 255, 255, 0.8)',
                                padding: 15,
                                font: {
                                    size: 12,
                                    weight: '600'
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(17, 24, 39, 0.95)',
                            titleColor: 'rgba(255, 255, 255, 0.9)',
                            bodyColor: 'rgba(255, 255, 255, 0.8)',
                            borderColor: 'rgba(99, 102, 241, 0.5)',
                            borderWidth: 1,
                            padding: 12,
                            displayColors: true,
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed.y !== null) {
                                        if (context.dataset.label.includes('Profit')) {
                                            label += '$' + context.parsed.y.toFixed(2);
                                        } else {
                                            label += context.parsed.y;
                                        }
                                    }
                                    return label;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(255, 255, 255, 0.05)',
                            },
                            ticks: {
                                color: 'rgba(255, 255, 255, 0.6)',
                                callback: function(value) {
                                    return '$' + value.toFixed(0);
                                }
                            }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            beginAtZero: true,
                            grid: {
                                drawOnChartArea: false,
                            },
                            ticks: {
                                color: 'rgba(255, 255, 255, 0.6)',
                            }
                        },
                        x: {
                            grid: {
                                color: 'rgba(255, 255, 255, 0.05)',
                            },
                            ticks: {
                                color: 'rgba(255, 255, 255, 0.6)',
                            }
                        }
                    }
                }
            });
        }

        // Initialize chart
        initChart();

        // Re-initialize on data update
        window.addEventListener('chartUpdated', () => {
            // Pequeno delay para garantir que o DOM seja atualizado
            setTimeout(() => {
                initChart();
            }, 100);
        });

        // Handle Livewire updates (for wire:poll and other actions)
        Livewire.hook('message.processed', (message, component) => {
            if (component.fingerprint.name === 'profit-chart') {
                setTimeout(() => {
                    initChart();
                }, 100);
            }
        });
    });
</script>
@endpush