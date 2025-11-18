@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">
    
    <!-- Welcome Banner -->
    <div class="glass-effect rounded-2xl p-8 border border-gray-800/50 relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-primary-500/10 to-secondary-500/10"></div>
        <div class="relative z-10">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-white mb-2">Welcome back, {{ Auth::user()->name }}! 👋</h1>
                    <p class="text-gray-400">Here's what's happening with your portfolio today.</p>
                </div>
                <div class="hidden lg:flex items-center space-x-4">
                    <button class="btn-primary">
                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Deposit
                    </button>
                    <button class="btn-secondary">
                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                        </svg>
                        Trade
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="stats-grid">
        
        <!-- Total Balance -->
        <div class="stat-card">
            <div class="stat-icon bg-gradient-to-br from-primary-500 to-secondary-600">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="stat-label">Total Balance</div>
            <div class="stat-value">$45,678.90</div>
            <div class="stat-change text-success-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                </svg>
                <span>+12.5% (24h)</span>
            </div>
        </div>

        <!-- Today's P&L -->
        <div class="stat-card">
            <div class="stat-icon bg-gradient-to-br from-success-500 to-success-700">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
            <div class="stat-label">Today's P&L</div>
            <div class="stat-value">+$1,234.56</div>
            <div class="stat-change text-success-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                </svg>
                <span>+2.7% gain</span>
            </div>
        </div>

        <!-- Total Trades -->
        <div class="stat-card">
            <div class="stat-icon bg-gradient-to-br from-info-500 to-info-700">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                </svg>
            </div>
            <div class="stat-label">Total Trades</div>
            <div class="stat-value">156</div>
            <div class="stat-change text-gray-400">
                <span>23 today</span>
            </div>
        </div>

        <!-- Win Rate -->
        <div class="stat-card">
            <div class="stat-icon bg-gradient-to-br from-warning-500 to-warning-700">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                </svg>
            </div>
            <div class="stat-label">Win Rate</div>
            <div class="stat-value">68.5%</div>
            <div class="stat-change text-success-400">
                <span>Above average</span>
            </div>
        </div>

    </div>

    <!-- Portfolio & Market Data -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Portfolio Allocation -->
        <div class="lg:col-span-2 trading-card">
            <div class="trading-card-header">
                <h2 class="text-xl font-bold text-white">Portfolio Allocation</h2>
                <div class="flex items-center space-x-2">
                    <button class="chart-timeframe-btn active">1D</button>
                    <button class="chart-timeframe-btn">1W</button>
                    <button class="chart-timeframe-btn">1M</button>
                    <button class="chart-timeframe-btn">1Y</button>
                </div>
            </div>
            <div class="h-64 flex items-center justify-center">
                <canvas id="portfolioChart"></canvas>
            </div>
        </div>

        <!-- Top Assets -->
        <div class="trading-card">
            <div class="trading-card-header">
                <h2 class="text-xl font-bold text-white">Top Assets</h2>
            </div>
            <div class="space-y-3">
                
                <div class="flex items-center justify-between p-3 rounded-lg hover:bg-gray-800/30 transition">
                    <div class="flex items-center space-x-3">
                        <div class="crypto-icon crypto-icon-btc">₿</div>
                        <div>
                            <p class="text-sm font-semibold text-white">Bitcoin</p>
                            <p class="text-xs text-gray-400">0.5342 BTC</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-bold text-white">$22,567.89</p>
                        <p class="text-xs text-success-400">+2.5%</p>
                    </div>
                </div>

                <div class="flex items-center justify-between p-3 rounded-lg hover:bg-gray-800/30 transition">
                    <div class="flex items-center space-x-3">
                        <div class="crypto-icon crypto-icon-eth">Ξ</div>
                        <div>
                            <p class="text-sm font-semibold text-white">Ethereum</p>
                            <p class="text-xs text-gray-400">4.2156 ETH</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-bold text-white">$9,467.23</p>
                        <p class="text-xs text-success-400">+3.8%</p>
                    </div>
                </div>

                <div class="flex items-center justify-between p-3 rounded-lg hover:bg-gray-800/30 transition">
                    <div class="flex items-center space-x-3">
                        <div class="crypto-icon crypto-icon-bnb">B</div>
                        <div>
                            <p class="text-sm font-semibold text-white">BNB</p>
                            <p class="text-xs text-gray-400">34.5678 BNB</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-bold text-white">$10,903.45</p>
                        <p class="text-xs text-danger-400">-1.2%</p>
                    </div>
                </div>

                <div class="flex items-center justify-between p-3 rounded-lg hover:bg-gray-800/30 transition">
                    <div class="flex items-center space-x-3">
                        <div class="crypto-icon crypto-icon-sol">◎</div>
                        <div>
                            <p class="text-sm font-semibold text-white">Solana</p>
                            <p class="text-xs text-gray-400">89.1234 SOL</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-bold text-white">$8,801.34</p>
                        <p class="text-xs text-success-400">+5.6%</p>
                    </div>
                </div>

            </div>
        </div>

    </div>

    <!-- Market Overview & Recent Trades -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Trending Markets -->
        <div class="trading-card">
            <div class="trading-card-header">
                <h2 class="text-xl font-bold text-white">Trending Markets</h2>
                <a href="#" class="text-sm text-primary-400 hover:text-primary-300 transition">View All</a>
            </div>
            
            <div class="space-y-2">
                <div class="market-pair-card">
                    <div class="market-pair-header">
                        <div class="market-pair-name">
                            <div class="crypto-icon crypto-icon-btc text-xs">₿</div>
                            <span class="market-pair-symbol">BTC/USDT</span>
                            <span class="badge badge-success">+2.5%</span>
                        </div>
                        <div class="text-right">
                            <div class="market-pair-price">$42,156.32</div>
                        </div>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="market-pair-volume">Vol: $2.4B</span>
                        <span class="text-xs text-gray-400">High: $42,890</span>
                    </div>
                </div>

                <div class="market-pair-card">
                    <div class="market-pair-header">
                        <div class="market-pair-name">
                            <div class="crypto-icon crypto-icon-eth text-xs">Ξ</div>
                            <span class="market-pair-symbol">ETH/USDT</span>
                            <span class="badge badge-success">+3.2%</span>
                        </div>
                        <div class="text-right">
                            <div class="market-pair-price">$2,245.18</div>
                        </div>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="market-pair-volume">Vol: $1.8B</span>
                        <span class="text-xs text-gray-400">High: $2,289</span>
                    </div>
                </div>

                <div class="market-pair-card">
                    <div class="market-pair-header">
                        <div class="market-pair-name">
                            <div class="crypto-icon crypto-icon-sol text-xs">◎</div>
                            <span class="market-pair-symbol">SOL/USDT</span>
                            <span class="badge badge-success">+5.8%</span>
                        </div>
                        <div class="text-right">
                            <div class="market-pair-price">$98.75</div>
                        </div>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="market-pair-volume">Vol: $456M</span>
                        <span class="text-xs text-gray-400">High: $102</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="trading-card">
            <div class="trading-card-header">
                <h2 class="text-xl font-bold text-white">Recent Transactions</h2>
                <a href="#" class="text-sm text-primary-400 hover:text-primary-300 transition">View All</a>
            </div>
            
            <div class="space-y-1">
                <div class="trade-row">
                    <div class="flex items-center space-x-3">
                        <div class="status-completed">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-white">Buy BTC</p>
                            <p class="text-xs text-gray-400">2 hours ago</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-bold text-white">0.125 BTC</p>
                        <p class="text-xs text-gray-400">$5,269.54</p>
                    </div>
                </div>

                <div class="trade-row">
                    <div class="flex items-center space-x-3">
                        <div class="status-completed">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-white">Sell ETH</p>
                            <p class="text-xs text-gray-400">5 hours ago</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-bold text-white">2.5 ETH</p>
                        <p class="text-xs text-gray-400">$5,612.95</p>
                    </div>
                </div>

                <div class="trade-row">
                    <div class="flex items-center space-x-3">
                        <div class="status-pending">
                            <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-white">Buy SOL</p>
                            <p class="text-xs text-gray-400">Processing...</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-bold text-white">50 SOL</p>
                        <p class="text-xs text-gray-400">$4,937.50</p>
                    </div>
                </div>

                <div class="trade-row">
                    <div class="flex items-center space-x-3">
                        <div class="status-completed">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-white">Deposit USDT</p>
                            <p class="text-xs text-gray-400">Yesterday</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-bold text-white">10,000 USDT</p>
                        <p class="text-xs text-gray-400">Bank Transfer</p>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Quick Actions -->
    <div class="glass-effect rounded-2xl p-6 border border-gray-800/50">
        <h2 class="text-xl font-bold text-white mb-4">Quick Actions</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <button class="glass-effect hover:bg-gray-800/50 rounded-xl p-6 transition-all duration-300 group">
                <div class="w-12 h-12 rounded-xl bg-primary-500/20 text-primary-400 flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                </div>
                <p class="text-sm font-semibold text-white text-center">Deposit</p>
            </button>

            <button class="glass-effect hover:bg-gray-800/50 rounded-xl p-6 transition-all duration-300 group">
                <div class="w-12 h-12 rounded-xl bg-success-500/20 text-success-400 flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                    </svg>
                </div>
                <p class="text-sm font-semibold text-white text-center">Trade</p>
            </button>

            <button class="glass-effect hover:bg-gray-800/50 rounded-xl p-6 transition-all duration-300 group">
                <div class="w-12 h-12 rounded-xl bg-warning-500/20 text-warning-400 flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <p class="text-sm font-semibold text-white text-center">Staking</p>
            </button>

            <button class="glass-effect hover:bg-gray-800/50 rounded-xl p-6 transition-all duration-300 group">
                <div class="w-12 h-12 rounded-xl bg-info-500/20 text-info-400 flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <p class="text-sm font-semibold text-white text-center">Analytics</p>
            </button>
        </div>
    </div>

</div>

@push('scripts')
<script>
    // Portfolio Chart
    const ctx = document.getElementById('portfolioChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['00:00', '04:00', '08:00', '12:00', '16:00', '20:00', '24:00'],
                datasets: [{
                    label: 'Portfolio Value',
                    data: [42000, 43200, 41800, 44500, 45200, 44800, 45679],
                    borderColor: '#6366f1',
                    backgroundColor: 'rgba(99, 102, 241, 0.1)',
                    tension: 0.4,
                    fill: true,
                    borderWidth: 2,
                    pointRadius: 0,
                    pointHoverRadius: 6,
                    pointBackgroundColor: '#6366f1',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(17, 24, 39, 0.9)',
                        titleColor: '#fff',
                        bodyColor: '#d1d5db',
                        borderColor: 'rgba(99, 102, 241, 0.3)',
                        borderWidth: 1,
                        padding: 12,
                        displayColors: false,
                        callbacks: {
                            label: function(context) {
                                return '$' + context.parsed.y.toLocaleString();
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: false,
                        grid: {
                            color: 'rgba(255, 255, 255, 0.05)',
                            drawBorder: false
                        },
                        ticks: {
                            color: '#9ca3af',
                            callback: function(value) {
                                return '$' + (value / 1000) + 'K';
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false,
                            drawBorder: false
                        },
                        ticks: {
                            color: '#9ca3af'
                        }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
                }
            }
        });
    }
</script>
@endpush

@endsection