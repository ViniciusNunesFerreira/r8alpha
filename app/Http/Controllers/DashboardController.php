<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    
    public function index()
    {
        $user = auth()->user();
        
        // Carrega dados do usuário
        $wallets = $user->wallets();
        
        // Investimentos com planos
        $investments = $user->investments()
            ->with('investmentPlan')
            ->orderByDesc('created_at')
            ->get();
        
        // Instâncias de robôs
        $botInstances = $user->botInstances()
            ->with('investment')
            ->orderByDesc('created_at')
            ->get();
        // Estatísticas
        $stats = [
            'total_invested' => $investments->sum('amount'),
            'total_profit' => optional($wallets->where('type', 'investment')->first())->total_profit,
            'active_bots' => $botInstances->where('is_active', true)->count(),
            'total_trades' => $botInstances->sum('total_trades'),
            'success_rate' => $this->calculateAverageSuccessRate($botInstances),
        ];
        return view('dashboard', compact(
            'user', 
            'wallets', 
            'investments', 
            'botInstances', 
            'stats'
        ));
    }


     /**
     * Calcula taxa média de sucesso dos robôs
     * 
     * @param \Illuminate\Database\Eloquent\Collection $botInstances
     * @return float
     */
    protected function calculateAverageSuccessRate($botInstances)
    {
        $activeBots = $botInstances->where('total_trades', '>', 0);
        
        if ($activeBots->isEmpty()) {
            return 0;
        }
        $totalSuccessRate = $activeBots->sum(function ($bot) {
            return $bot->success_rate;
        });
        return round($totalSuccessRate / $activeBots->count(), 2);
    }
    
}
