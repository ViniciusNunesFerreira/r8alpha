<?php

namespace App\Observers;

use App\Models\Profit; 
use App\Services\CommissionService;

class ProfitObserver
{
    protected $commissionService;

    public function __construct(CommissionService $commissionService)
    {
        $this->commissionService = $commissionService;
    }

    /**
     * Executado após um novo registro de Lucro ser criado.
     */
    public function created(Profit $profit): void
    {
        // O lucro sempre deve ter um user
        if ($profit->user && $profit->user->sponsor) {
            
            $this->commissionService->generateCommission(
                $profit->user, 
                $profit->amount, // O valor do lucro (base para comissão)
                $profit,
                'residual' // Tipo de comissão (Bônus Residual)
            );
        }
    }
}