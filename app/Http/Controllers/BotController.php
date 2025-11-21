<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BotInstance;
use App\Jobs\ScanArbitrageOpportunities;
use App\Events\BotStatusChanged;
use App\Services\BotHealthCheck;
use App\Services\BotRiskManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

class BotController extends Controller
{
    protected $healthCheck;
    protected $riskManager;

    public function __construct(BotHealthCheck $healthCheck, BotRiskManager $riskManager)
    {
        $this->healthCheck = $healthCheck;
        $this->riskManager = $riskManager;
    }

    /**
     * Lista todos os bots do usuário
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = BotInstance::where('user_id', auth()->user()->id)
            ->with(['investment.investmentPlan']);

        // Filtros
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        if ($request->filled('search')) {
            $query->where('instance_id', 'like', '%' . $request->search . '%');
        }

        // Ordenação
        $sortBy = $request->input('sort', 'created_at');
        $sortOrder = $request->input('order', 'desc');
        
        $query->orderBy($sortBy, $sortOrder);

        $bots = $query->paginate(12);

        // Estatísticas gerais
        $stats = [
            'total_bots' => BotInstance::where('user_id', auth()->user()->id)->count(),
            'active_bots' => BotInstance::where('user_id', auth()->user()->id)->where('is_active', true)->count(),
            'total_profit' => BotInstance::where('user_id', auth()->user()->id)->sum('total_profit'),
            'total_trades' => BotInstance::where('user_id', auth()->user()->id)->sum('total_trades'),
        ];

        return view('bots.index', compact('bots', 'stats'));
    }

    /**
     * Exibe detalhes de um robô
     * 
     * @param BotInstance $bot
     * @return \Illuminate\View\View
     */
    public function show(BotInstance $bot)
    {
        // Verifica autorização
        $this->authorize('view', $bot);
        
        // Carrega relacionamentos
        $bot->load([
            'investment.investmentPlan',
            'arbitrageOpportunities' => function ($query) {
                $query->latest()->limit(10);
            },
            'trades' => function ($query) {
                $query->latest()->limit(20);
            }
        ]);
        
        // Estatísticas do robô
        $stats = [
            'total_opportunities' => $bot->arbitrageOpportunities()->count(),
            'executed_opportunities' => $bot->arbitrageOpportunities()
                ->where('status', 'executed')
                ->count(),
            'average_profit_percentage' => $bot->arbitrageOpportunities()
                ->where('status', 'executed')
                ->avg('profit_percentage') ?? 0,
            'best_opportunity' => $bot->arbitrageOpportunities()
                ->where('status', 'executed')
                ->orderByDesc('profit_percentage')
                ->first(),
            'worst_opportunity' => $bot->arbitrageOpportunities()
                ->where('status', 'executed')
                ->orderBy('profit_percentage')
                ->first(),
            'today_trades' => $bot->trades()
                ->whereDate('created_at', today())
                ->count(),
            'today_profit' => $bot->trades()
                ->whereDate('created_at', today())
                ->where('status', 'completed')
                ->sum('profit') ?? 0,
        ];

        // Health Check
        $healthStatus = $this->healthCheck->check($bot);

        // Risk Assessment
        $riskAssessment = $this->riskManager->assessRisk($bot);

        // Performance diária (últimos 7 dias)
        $dailyPerformance = $this->getDailyPerformance($bot, 7);

        return view('bots.show', compact('bot', 'stats', 'healthStatus', 'riskAssessment', 'dailyPerformance'));
    }

    /**
     * Ativa/Desativa um robô
     * 
     * @param BotInstance $bot
     * @return \Illuminate\Http\RedirectResponse
     */
    public function toggle(BotInstance $bot)
    {
        $this->authorize('update', $bot);

        // Se está ativando o bot, fazer health check primeiro
        if (!$bot->is_active) {
            $healthStatus = $this->healthCheck->check($bot);
            
            if (!$healthStatus['is_healthy']) {
                return back()->with('error', 'Cannot activate bot: ' . $healthStatus['error_message']);
            }

            // Verificar risk management
            $riskCheck = $this->riskManager->canActivateBot($bot);
            if (!$riskCheck['allowed']) {
                return back()->with('error', 'Risk check failed: ' . $riskCheck['reason']);
            }
        }

        DB::beginTransaction();
        try {
            $bot->update(['is_active' => !$bot->is_active]);

            // Log da ação
            $bot->activityLogs()->create([
                'action' => $bot->is_active ? 'activated' : 'deactivated',
                'details' => [
                    'user_id' => auth()->user()->id,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ],
            ]);

            broadcast(new BotStatusChanged($bot))->toOthers();

            if ($bot->is_active) {
                // Dispara job para escanear oportunidades
                ScanArbitrageOpportunities::dispatch($bot);
                
                $message = '✅ Bot activated successfully! Scanning for opportunities...';
            } else {
                $message = '⏸️ Bot deactivated successfully.';
            }

            DB::commit();
            return back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error toggling bot', [
                'bot_id' => $bot->id,
                'error' => $e->getMessage()
            ]);
            return back()->with('error', 'Error toggling bot: ' . $e->getMessage());
        }
    }

    /**
     * Atualiza configurações do robô
     * 
     * @param Request $request
     * @param BotInstance $bot
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateConfig(Request $request, BotInstance $bot)
    {
        $this->authorize('update', $bot);

        $validated = $request->validate([
            'base_currencies' => 'required|array|min:1',
            'base_currencies.*' => 'required|string|in:BTC,ETH,BNB,USDT,BUSD',
            'min_profit_percentage' => 'required|numeric|min:0.01|max:100',
            'max_trade_amount' => 'required|numeric|min:10|max:100000',
            'max_daily_loss' => 'required|numeric|min:1|max:10000',
            'stop_loss_percentage' => 'required|numeric|min:0.1|max:50',
            'take_profit_percentage' => 'nullable|numeric|min:0.1|max:100',
            'max_position_size' => 'required|numeric|min:1|max:100',
            'trading_pairs' => 'nullable|array',
            'paper_trading' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            $config = $bot->config ?? [];
            
            // Merge nova configuração
            $config = array_merge($config, [
                'base_currencies' => $validated['base_currencies'],
                'min_profit_percentage' => $validated['min_profit_percentage'],
                'max_trade_amount' => $validated['max_trade_amount'],
                'max_daily_loss' => $validated['max_daily_loss'],
                'stop_loss_percentage' => $validated['stop_loss_percentage'],
                'take_profit_percentage' => $validated['take_profit_percentage'] ?? null,
                'max_position_size' => $validated['max_position_size'],
                'trading_pairs' => $validated['trading_pairs'] ?? [],
                'paper_trading' => $validated['paper_trading'] ?? false,
                'updated_at' => now()->toDateTimeString(),
            ]);

            $bot->update(['config' => $config]);

            // Log da ação
            $bot->activityLogs()->create([
                'action' => 'config_updated',
                'details' => [
                    'user_id' => auth()->user()->id,
                    'changes' => $validated,
                ],
            ]);

            DB::commit();
            return back()->with('success', '⚙️ Bot configuration updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating bot config', [
                'bot_id' => $bot->id,
                'error' => $e->getMessage()
            ]);
            return back()->with('error', 'Error updating configuration: ' . $e->getMessage());
        }
    }

    /**
     * Exporta dados do robô
     * 
     * @param Request $request
     * @param BotInstance $bot
     * @return \Illuminate\Http\Response
     */
    public function exportData(Request $request, BotInstance $bot)
    {
        $this->authorize('view', $bot);

        $format = $request->input('format', 'csv');
        $period = $request->input('period', 'all'); // all, today, week, month

        // Filtrar trades por período
        $query = $bot->trades()->with('arbitrageOpportunity');

        switch ($period) {
            case 'today':
                $query->whereDate('created_at', today());
                break;
            case 'week':
                $query->where('created_at', '>=', now()->subWeek());
                break;
            case 'month':
                $query->where('created_at', '>=', now()->subMonth());
                break;
        }

        $trades = $query->orderBy('created_at', 'desc')->get();

        if ($format === 'csv') {
            return $this->exportCsv($bot, $trades);
        } elseif ($format === 'pdf') {
            return $this->exportPdf($bot, $trades);
        } else {
            return response()->json([
                'bot' => $bot,
                'trades' => $trades,
                'stats' => [
                    'total_trades' => $trades->count(),
                    'total_profit' => $trades->sum('profit'),
                    'avg_profit' => $trades->avg('profit'),
                ]
            ]);
        }
    }

    /**
     * Exporta dados em CSV
     */
    protected function exportCsv(BotInstance $bot, $trades)
    {
        $filename = "bot-{$bot->instance_id}-" . now()->format('Y-m-d') . ".csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($bot, $trades) {
            $file = fopen('php://output', 'w');
            
            // Headers
            fputcsv($file, [
                'Date', 'Time', 'Pair', 'Side', 'Amount', 
                'Price', 'Total', 'Profit', 'Status', 
                'Opportunity ID', 'Fees'
            ]);

            // Data
            foreach ($trades as $trade) {
                fputcsv($file, [
                    $trade->created_at->format('Y-m-d'),
                    $trade->created_at->format('H:i:s'),
                    $trade->pair,
                    $trade->side,
                    $trade->amount,
                    $trade->price,
                    $trade->total,
                    $trade->profit ?? 0,
                    $trade->status,
                    $trade->arbitrage_opportunity_id ?? 'N/A',
                    $trade->fees ?? 0,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Exporta dados em PDF
     */
    protected function exportPdf(BotInstance $bot, $trades)
    {
        $data = [
            'bot' => $bot,
            'trades' => $trades,
            'stats' => [
                'total_trades' => $trades->count(),
                'total_profit' => $trades->sum('profit'),
                'avg_profit' => $trades->avg('profit'),
                'successful_trades' => $trades->where('profit', '>', 0)->count(),
            ],
            'generated_at' => now(),
        ];

        $pdf = Pdf::loadView('bots.reports.pdf', $data);
        
        $filename = "bot-{$bot->instance_id}-report-" . now()->format('Y-m-d') . ".pdf";
        
        return $pdf->download($filename);
    }

    /**
     * Para todos os bots do usuário (emergency stop)
     * 
     * @return \Illuminate\Http\RedirectResponse
     */
    public function emergencyStopAll()
    {
        $bots = BotInstance::where('user_id', auth()->user()->id)
            ->where('is_active', true)
            ->get();

        $stopped = 0;
        foreach ($bots as $bot) {
            try {
                $bot->update(['is_active' => false]);
                
                $bot->activityLogs()->create([
                    'action' => 'emergency_stop',
                    'details' => [
                        'user_id' => auth()->user()->id,
                        'reason' => 'Emergency stop all',
                        'ip_address' => request()->ip(),
                    ],
                ]);

                $stopped++;
            } catch (\Exception $e) {
                Log::error('Error in emergency stop', [
                    'bot_id' => $bot->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return back()->with('success', "🚨 Emergency stop: {$stopped} bot(s) stopped.");
    }

    /**
     * Obtém performance diária
     */
    protected function getDailyPerformance(BotInstance $bot, int $days = 7)
    {
        $startDate = now()->subDays($days)->startOfDay();
        
        $dailyStats = DB::table('trades')
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as total_trades'),
                DB::raw('SUM(CASE WHEN profit > 0 THEN 1 ELSE 0 END) as successful_trades'),
                DB::raw('SUM(profit) as total_profit'),
                DB::raw('AVG(profit) as avg_profit')
            )
            ->where('bot_instance_id', $bot->id)
            ->where('created_at', '>=', $startDate)
            ->where('status', 'completed')
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        return $dailyStats;
    }

    /**
     * Testa conexão com a Binance
     * 
     * @param BotInstance $bot
     * @return \Illuminate\Http\JsonResponse
     */
    public function testConnection(BotInstance $bot)
    {
        $this->authorize('view', $bot);

        try {
            $result = $this->healthCheck->testBinanceConnection($bot);
            
            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'details' => $result['details'] ?? null,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Connection test failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtém health status via AJAX
     * 
     * @param BotInstance $bot
     * @return \Illuminate\Http\JsonResponse
     */
    public function getHealthStatus(BotInstance $bot)
    {
        $this->authorize('view', $bot);

        $healthStatus = $this->healthCheck->check($bot);
        
        return response()->json($healthStatus);
    }
}