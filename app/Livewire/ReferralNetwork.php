<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Investment;
use App\Models\BotInstance;

class ReferralNetwork extends Component
{
    public $referralsByLevel = [];
    public $commissionsGenerated = [];
    public $activeLevel = 1;

    public function mount()
    {
        $this->loadNetworkData();
    }

    public function loadNetworkData()
    {
        $user = Auth::user();

        // 1. Carrega as comissões
        $this->commissionsGenerated = DB::table('referral_commissions')
            ->where('user_id', $user->id)
            ->select('source_user_id', DB::raw('SUM(amount) as total_bonus'))
            ->groupBy('source_user_id')
            ->pluck('total_bonus', 'source_user_id')
            ->toArray();

        // 2. Carrega referrals
        $referrals = $user->referrals()->get();

        // 3. Carrega manualmente os users
        $userIds = $referrals->pluck('user_id')->filter()->unique();

        // 4. Carrega os users e investments separadamente
        $users = \App\Models\User::whereIn('id', $userIds)
            ->with('investments')
            ->get()
            ->keyBy('id');

        // 5. Carrega contagem de bots
        $botCounts = collect();
        if ($userIds->isNotEmpty()) {
            $botCounts = DB::table('bot_instances')
                ->whereIn('user_id', $userIds->toArray())
                ->where('is_active', true)
                ->select('user_id', DB::raw('COUNT(*) as count'))
                ->groupBy('user_id')
                ->pluck('count', 'user_id');
        }

        // 6. Monta a estrutura convertendo para array simples
        $referralsData = $referrals->map(function ($referral) use ($users, $botCounts) {
            $user = $users->get($referral->user_id);
            
            if (!$user) {
                return null;
            }

            return [
                'id' => $referral->id,
                'level' => $referral->level,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'username' => $user->username,
                    'first_investment_at' => $user->first_investment_at?->format('Y-m-d H:i:s'),
                    'total_invested' => $user->investments->sum('amount'),
                    'has_active_investment' => $user->investments->where('status', 'active')->isNotEmpty(),
                    'active_bots_count' => $botCounts->get($user->id, 0),
                ]
            ];
        })->filter()->values();

        // 7. Agrupa por nível e converte para array
        $this->referralsByLevel = $referralsData
            ->groupBy('level')
            ->map(function ($items) {
                return $items->values()->toArray();
            })
            ->toArray();
    }

    // Método para trocar o nível ativo
    public function setActiveLevel($level)
    {
        $this->activeLevel = (int) $level;
    }

    public function render()
    {
        return view('livewire.referral-network');
    }
}