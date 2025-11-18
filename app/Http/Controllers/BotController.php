<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BotInstance;
use App\Jobs\ScanArbitrageOpportunities;
use App\Events\BotStatusChanged;

class BotController extends Controller
{
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
                ->avg('profit_percentage'),
            'best_opportunity' => $bot->arbitrageOpportunities()
                ->where('status', 'executed')
                ->orderByDesc('profit_percentage')
                ->first(),
        ];
        return view('bots.show', compact('bot', 'stats'));
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
        $bot->update(['is_active' => !$bot->is_active]);
        broadcast(new BotStatusChanged($bot) )->toOthers();
        if ($bot->is_active) {
            // Dispara job para escanear oportunidades
            ScanArbitrageOpportunities::dispatch($bot);
            
            $message = 'Bot activated successfully! Scanning for opportunities...';
        } else {
            $message = 'Bot deactivated successfully.';
        }
        return back()->with('success', $message);
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
            $request->validate([
                'base_currencies' => 'required|array',
                'min_profit_percentage' => 'required|numeric|min:0|max:100',
            ]);
            $config = $bot->config;
            $config['base_currencies'] = $request->base_currencies;
            $config['min_profit_percentage'] = $request->min_profit_percentage;
            $bot->update(['config' => $config]);
            return back()->with('success', 'Bot configuration updated successfully.');
    }

}
