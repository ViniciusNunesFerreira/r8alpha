<?php

namespace App\Observers;

use App\Models\Investment; 
use App\Services\CommissionService;

class InvestmentObserver
{
    protected $commissionService;

    public function __construct(CommissionService $commissionService)
    {
        $this->commissionService = $commissionService;
    }

    /**
     * Executado quando um investimento é criado.
     * OBS: O ideal seria usar o método updated() para garantir que o status seja 'aprovado'.
     */
    public function created(Investment $investment): void
    {
        // Se o investimento for aprovado imediatamente na criação:
        if ($investment->user && $investment->user->sponsor && $investment->status === 'aprovado') {
            
            $this->commissionService->generateCommission(
                $investment->user, 
                $investment->amount, // Usar o valor total do investimento
                $investment,
                'investment' // Tipo de comissão (Comissão de Investimento)
            );
        }
    }

    /**
     * Executado quando o status de um investimento é alterado (Ex: de 'pendente' para 'aprovado').
     */
    public function updated(Investment $investment): void
    {
        // Verifica se o investimento foi APROVADO AGORA (transição de status)
        if ($investment->isDirty('status') && $investment->status === 'aprovado') {
            
            if ($investment->user && $investment->user->sponsor) {
                $this->commissionService->generateCommission(
                    $investment->user, 
                    $investment->amount, 
                    $investment,
                    'investment'
                );
            }
        }
    }
}