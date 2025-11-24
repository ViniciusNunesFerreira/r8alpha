<?php

namespace App\Services;

use App\Models\User;
use App\Models\ReferralCommission;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class CommissionService
{
    /**
     * Taxas de Bônus Residual
     * Chave = Nível, Valor = Porcentagem sobre o Lucro Bruto
     * Nota: O Nível 1 (0.20) já foi deduzido do usuário no InvestmentService, 
     * aqui apenas direcionamos esse valor. Os outros são bônus da empresa.
     */
    protected $residualRates = [
        1 => 0.20, // 20% (Direto - Deduzido do usuário)
        2 => 0.05, // 5% (Empresa paga - Requer $800 volume direto)
        4 => 0.03, // 3% (Empresa paga - Requer 5 indicados ativos)
        5 => 0.01, // 1% (Empresa paga - Requer 6 indicados ativos)
    ];

    /**
     * Processa a distribuição de residuais.
     * * @param User $originUser O usuário que gerou o lucro
     * @param float $grossProfitAmount O valor total do lucro gerado (base de cálculo)
     * @param Model $sourceModel O modelo Investment
     * @param float $deductedLevel1Amount O valor exato (20%) que já foi tirado do usuário para o nível 1
     */
    public function processResiduals(User $originUser, float $grossProfitAmount, Model $sourceModel, float $deductedLevel1Amount): void
    {
        $sponsor = $originUser->sponsor;
        $level = 1;

        // Percorre até o nível 5 (máximo definido nas regras)
        while ($sponsor && $level <= 5) {
            
            $commissionAmount = 0;
            $isEligible = false;
            $notes = "";

            // --- LÓGICA NÍVEL 1 (Dedução Direta) ---
            if ($level === 1) {
                // Nível 1 recebe os 20% retidos do usuário, sem critérios adicionais
                $commissionAmount = $deductedLevel1Amount;
                $isEligible = true; 
                $notes = "20% withheld from the referred employee's profit.";
            } 
            // --- LÓGICA NÍVEIS SUPERIORES (Bônus da Empresa) ---
            else {
                // Verifica se existe taxa configurada para este nível
                if (isset($this->residualRates[$level])) {
                    
                    $rate = $this->residualRates[$level];
                    
                    // Verifica critérios de elegibilidade
                    if ($this->checkEligibility($sponsor, $level)) {
                        $commissionAmount = $grossProfitAmount * $rate;
                        $isEligible = true;
                        $notes = "Network bonus level {$level}";
                    }
                }
            }

            // Se for elegível e valor > 0, paga
            if ($isEligible && $commissionAmount > 0) {
                $this->payCommission($sponsor, $originUser, $commissionAmount, $level, $sourceModel, $notes);
            } elseif ($level === 1 && !$sponsor) {
                // EDGE CASE: Se o usuário não tem patrocinador nível 1, os 20% ficam para a empresa (não devolve pro user)
                // Logar ou criar transação de "Quebra" para admin
            }

            // Sobe um nível na árvore
            $sponsor = $sponsor->sponsor;
            $level++;
        }
    }

    /**
     * Verifica se o patrocinador cumpre os requisitos para receber o bônus do nível.
     */
    protected function checkEligibility(User $sponsor, int $level): bool
    {
        switch ($level) {
            case 2:
                // Regra: receberá somente após somar 800$ dolares na sua rede direta
                // Usamos o Helper criado no User Model
                return $sponsor->direct_network_volume >= 800;

            case 4:
                // Regra: receberá somente após ter 5 indicados diretos ativos
                return $sponsor->active_directs_count >= 5;

            case 5:
                // Regra: receberá somente após ter 6 indicados diretos ativos
                return $sponsor->active_directs_count >= 6;

            default:
                // Para outros níveis definidos no array (se houver), assume sem critério ou critério padrão
                return true;
        }
    }

    /**
     * Efetua o pagamento na carteira de Referral.
     */
    protected function payCommission(User $receiver, User $sourceUser, float $amount, int $level, Model $sourceModel, string $notes)
    {
        // Usa a carteira exclusiva de Referral definida no User Model
        $wallet = $receiver->referralWallet;

        // Registra o log da comissão
        ReferralCommission::create([
            'user_id' => $receiver->id,
            'source_user_id' => $sourceUser->id,
            'source_id' => $sourceModel->id,
            'source_type' => get_class($sourceModel),
            'amount' => $amount,
            'level' => $level,
            'type' => 'residual',
        ]);

        // Credita saldo
        $balanceBefore = $wallet->balance;
        $wallet->increment('balance', $amount);
        $wallet->increment('total_profit', $amount); // Ou total_referral_bonus se tiver coluna específica

        // Transação
        Transaction::create([
            'user_id' => $receiver->id,
            'wallet_id' => $wallet->id,
            'type' => 'sponsored_deposit',
            'amount' => $amount,
            'balance_after' => $wallet->balance,
            'balance_before' => $balanceBefore,
            'description' => "Residual Level {$level} of {$sourceUser->name} - {$notes}",
            'status' => 'completed',
        ]);
    }
}