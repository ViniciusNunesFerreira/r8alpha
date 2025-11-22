<?php

namespace App\Http\Controllers;

use App\Models\BotInstance;
use App\Jobs\ScanArbitrageOpportunities;
use App\Events\BotStatusChanged;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class BotController extends Controller
{
    /**
     * Exibe lista de bots do usuário
     */
    public function index()
    {
        $bots = auth()->user()->botInstances()
            ->with(['investment.investmentPlan'])
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('bots.index', compact('bots'));
    }

    /**
     * Exibe detalhes de um robô específico
     */
    public function show(BotInstance $bot)
    {
        // Verifica autorização
        Gate::authorize('view', $bot);

        // Carrega relacionamentos necessários
        $bot->load([
            'investment.investmentPlan',
            'arbitrageOpportunities' => function ($query) {
                $query->latest()->limit(20);
            },
            'trades' => function ($query) {
                $query->latest()->limit(50);
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
            'opportunities_today' => $bot->arbitrageOpportunities()
                ->whereDate('detected_at', today())
                ->count(),
            'profit_today' => $bot->trades()
                ->whereDate('created_at', today())
                ->where('status', 'completed')
                ->sum('profit') ?? 0,
        ];

        return view('bots.show', compact('bot', 'stats'));
    }

    /**
     * Ativa/Desativa um robô
     */
    public function toggle(BotInstance $bot)
    {
        Gate::authorize('update', $bot);

        try {
            $previousStatus = $bot->is_active;
            $bot->is_active = !$bot->is_active;
            $bot->save();

            // Broadcast mudança de status
            broadcast(new BotStatusChanged($bot))->toOthers();

            if ($bot->is_active) {
                // Se ativou, dispara job para escanear oportunidades
                ScanArbitrageOpportunities::dispatch($bot);
                
                Log::info('Bot activated', [
                    'bot_id' => $bot->id,
                    'user_id' => $bot->user_id,
                    'instance_id' => $bot->instance_id
                ]);

                return back()->with('success', 'Bot ativado com sucesso! Começando a escanear oportunidades...');
            } else {
                Log::info('Bot deactivated', [
                    'bot_id' => $bot->id,
                    'user_id' => $bot->user_id,
                    'instance_id' => $bot->instance_id
                ]);

                return back()->with('success', 'Bot pausado com sucesso.');
            }
        } catch (\Exception $e) {
            Log::error('Error toggling bot status', [
                'bot_id' => $bot->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Erro ao alterar status do bot. Tente novamente.');
        }
    }

    /**
     * Atualiza configurações do robô
     */
    public function updateConfig(Request $request, BotInstance $bot)
    {
        Gate::authorize('update', $bot);

        $validated = $request->validate([
            'base_currencies' => 'required|array|min:1',
            'base_currencies.*' => 'required|string|in:BTC,ETH,USDT,BNB,SOL,ADA,DOT,MATIC',
            'min_profit_percentage' => 'required|numeric|min:0.1|max:10',
            'max_investment_per_trade' => 'nullable|numeric|min:10',
            'auto_execute' => 'nullable|boolean',
        ]);

        try {
            $config = $bot->config ?? [];
            
            $config['base_currencies'] = $validated['base_currencies'];
            $config['min_profit_percentage'] = $validated['min_profit_percentage'];
            
            if (isset($validated['max_investment_per_trade'])) {
                $config['max_investment_per_trade'] = $validated['max_investment_per_trade'];
            }
            
            if (isset($validated['auto_execute'])) {
                $config['auto_execute'] = $validated['auto_execute'];
            }

            $bot->update(['config' => $config]);

            Log::info('Bot configuration updated', [
                'bot_id' => $bot->id,
                'user_id' => $bot->user_id,
                'config' => $config
            ]);

            // Se o bot está ativo, reinicia o scan com nova configuração
            if ($bot->is_active) {
                ScanArbitrageOpportunities::dispatch($bot);
            }

            return back()->with('success', 'Configurações atualizadas com sucesso!');
        } catch (\Exception $e) {
            Log::error('Error updating bot config', [
                'bot_id' => $bot->id,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Erro ao atualizar configurações. Tente novamente.');
        }
    }

    /**
     * Exporta dados do bot (CSV)
     */
    public function exportData(BotInstance $bot)
    {
        Gate::authorize('view', $bot);

        $trades = $bot->trades()
            ->with('arbitrageOpportunity')
            ->orderBy('created_at', 'desc')
            ->get();

        $filename = "bot_{$bot->instance_id}_trades_" . now()->format('Y-m-d') . ".csv";
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($trades) {
            $file = fopen('php://output', 'w');
            
            // Header
            fputcsv($file, [
                'Data/Hora',
                'Par',
                'Lado',
                'Quantidade',
                'Preço',
                'Total',
                'Taxa',
                'Lucro',
                'Status',
                'Sequência'
            ]);

            // Data
            foreach ($trades as $trade) {
                fputcsv($file, [
                    $trade->created_at->format('Y-m-d H:i:s'),
                    $trade->pair,
                    strtoupper($trade->side),
                    $trade->amount,
                    $trade->price,
                    $trade->total,
                    $trade->fee,
                    $trade->profit ?? 0,
                    $trade->status,
                    $trade->trade_sequence,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Deleta um bot (se não tiver operações ativas)
     */
    public function destroy(BotInstance $bot)
    {
        Gate::authorize('delete', $bot);

        try {
            // Verifica se há oportunidades sendo executadas
            $activeOpportunities = $bot->arbitrageOpportunities()
                ->where('status', 'detected')
                ->count();

            if ($activeOpportunities > 0) {
                return back()->with('error', 'Não é possível deletar um bot com oportunidades ativas. Pause o bot primeiro.');
            }

            // Verifica se está ativo
            if ($bot->is_active) {
                return back()->with('error', 'Não é possível deletar um bot ativo. Pause o bot primeiro.');
            }

            $instance_id = $bot->instance_id;
            $bot->delete();

            Log::info('Bot deleted', [
                'instance_id' => $instance_id,
                'user_id' => auth()->id()
            ]);

            return redirect()->route('bots.index')
                ->with('success', 'Bot deletado com sucesso.');
        } catch (\Exception $e) {
            Log::error('Error deleting bot', [
                'bot_id' => $bot->id,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Erro ao deletar bot. Tente novamente.');
        }
    }
}