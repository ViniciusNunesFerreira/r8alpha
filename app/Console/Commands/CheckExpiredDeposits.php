<?php

namespace App\Console\Commands;

use App\Models\Deposit;
use Illuminate\Console\Command;

class CheckExpiredDeposits extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deposits:check-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica e marca depósitos expirados';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Verificando depósitos expirados...');

        $expiredDeposits = Deposit::where('status', 'pending')
            ->where('expires_at', '<', now())
            ->get();

        $count = 0;

        foreach ($expiredDeposits as $deposit) {
            $deposit->markAsExpired();
            $count++;
            
            $this->line("✓ Depósito {$deposit->transaction_id} marcado como expirado");
        }

        if ($count === 0) {
            $this->info('✓ Nenhum depósito expirado encontrado');
        } else {
            $this->info("✓ {$count} depósito(s) marcado(s) como expirado(s)");
        }

        return Command::SUCCESS;
    }
}