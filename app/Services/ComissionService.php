<?php

namespace App\Services;

use App\Models\User;
use App\Models\ReferralCommission;
use App\Models\Transaction; // Assumindo que você tem um modelo Transaction
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CommissionService
{
    // Taxas de comissão por nível (exemplo)
    protected $investmentRates = [
        1 => 0.10, // 10% do valor do investimento
        2 => 0.03,
        3 => 0.02,
        4 => 0.01,
        5 => 0.005,
    ];
    
    protected $residualRates = [
        1 => 0.02, // 2% do lucro residual
        2 => 0.01,
        3 => 0.005,
        4 => 0.003,
        5 => 0.001,
    ];

    /**
     * Gera e distribui comissões para a rede de indicados.
     *
     * @param User $sourceUser O usuário que gerou o evento (o indicado).
     * @param float $sourceAmount O valor base para o cálculo da comissão (Investimento ou Lucro).
     * @param Model $sourceModel O modelo da fonte (Investment ou Profit).
     * @param string $type O tipo de comissão ('investment' ou 'residual').
     */
    public function generateCommission(User $sourceUser, float $sourceAmount, Model $sourceModel, string $type): void
    {
        $currentSponsor = $sourceUser->sponsor;
        $currentLevel = 1;
        
        $rates = $type === 'investment' ? $this->investmentRates : $this->residualRates;
        $maxLevels = count($rates);

        // Percorre a cadeia de patrocinadores até o máximo de níveis
        while ($currentSponsor && $currentLevel <= $maxLevels) {
            
            $rate = $rates[$currentLevel] ?? 0;
            
            if ($rate > 0) {
                $commissionAmount = $sourceAmount * $rate;

                if ($commissionAmount > 0.001) {
                    
                    // Inicia uma transação de banco de dados para garantir atomicidade
                    DB::transaction(function () use ($currentSponsor, $sourceUser, $sourceModel, $commissionAmount, $currentLevel, $type) {
                        
                        // 1. REGISTRAR A COMISSÃO RECEBIDA
                        ReferralCommission::create([
                            'user_id'        => $currentSponsor->id, // Patrocinador que RECEBE
                            'source_user_id' => $sourceUser->id,     // Usuário que GEROU (indicado)
                            'source_id'      => $sourceModel->id,
                            'source_type'    => $sourceModel::class,
                            'amount'         => $commissionAmount,
                            'level'          => $currentLevel,
                            'type'           => $type,
                        ]);

                        // 2. CRÉDITO NA CARTEIRA DE REFERRAL (BUSCA A CARTEIRA DO PATROCINADOR)
                        $wallet = $currentSponsor->referralWallet;

                        // Se a carteira de referral não existir, ela deve ser criada no registro do usuário.
                        // Para segurança, criamos se não existir.
                        if (!$wallet) {
                            $wallet = $currentSponsor->wallets()->create(['type' => 'referral', 'balance' => 0.00]);
                        }
                        
                        $wallet->increment('balance', $commissionAmount);
                        
                        // 3. REGISTRAR A TRANSAÇÃO (Opcional, mas altamente recomendado para histórico)
                        // Assumindo que você tem uma coluna 'wallet_id' e 'type' na tabela transactions
                        Transaction::create([
                            'user_id' => $currentSponsor->id,
                            'wallet_id' => $wallet->id,
                            'amount' => $commissionAmount,
                            'type' => 'credit', // ou 'commission_credit'
                            'description' => "Nominating Committee Level {$currentLevel} ({$type}) de {$sourceUser->username}",
                            'status' => 'completed',
                            // Adicionar source_id/type na Transaction se necessário
                        ]);
                    });
                }
            }

            // Move para o próximo nível (Patrocinador do Patrocinador)
            $currentSponsor = $currentSponsor->sponsor;
            $currentLevel++;
        }
    }
}