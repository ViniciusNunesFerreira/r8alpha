<?php

namespace App\Console\Commands;

use App\Models\Deposit;
use App\Services\NowPaymentsService;
use App\Services\StartCashPixService;
use Illuminate\Console\Command;

class SyncDepositStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deposits:sync-status {--pending-only : Sincronizar apenas depósitos pendentes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincroniza o status dos depósitos com os gateways de pagamento';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Sincronizando status dos depósitos...');

        $query = Deposit::whereNotIn('status', ['completed', 'cancelled', 'failed']);

        if ($this->option('pending-only')) {
            $query->where('status', 'pending');
        }

        $deposits = $query->get();

        if ($deposits->isEmpty()) {
            $this->info('✓ Nenhum depósito para sincronizar');
            return Command::SUCCESS;
        }

        $bar = $this->output->createProgressBar($deposits->count());
        $bar->start();

        $updated = 0;

        foreach ($deposits as $deposit) {
            try {
                if ($deposit->payment_method === 'pix') {
                    $updated += $this->syncPixDeposit($deposit);
                } else {
                    $updated += $this->syncCryptoDeposit($deposit);
                }
            } catch (\Exception $e) {
                $this->error("\nErro ao sincronizar {$deposit->transaction_id}: {$e->getMessage()}");
            }

            $bar->advance();
        }

        $bar->finish();

        $this->newLine(2);
        $this->info("✓ Sincronização concluída. {$updated} depósito(s) atualizado(s).");

        return Command::SUCCESS;
    }

    /**
     * Sincroniza depósito PIX
     */
    private function syncPixDeposit(Deposit $deposit): int
    {
        if (!$deposit->gateway_transaction_id) {
            return 0;
        }

        $pixService = new StartCashPixService();
        $status = $pixService->checkPaymentStatus($deposit->gateway_transaction_id);

        if (!$status) {
            return 0;
        }

        $previousStatus = $deposit->status;

        if (isset($status['status']) && $status['status'] === 'paid') {
            if (!$deposit->isCompleted()) {
                $deposit->markAsPaid();
                $deposit->markAsConfirmed();
                
                $this->newLine();
                $this->info("✓ PIX {$deposit->transaction_id}: {$previousStatus} → completed");
                return 1;
            }
        }

        return 0;
    }

    /**
     * Sincroniza depósito Crypto
     */
    private function syncCryptoDeposit(Deposit $deposit): int
    {
        if (!$deposit->gateway_transaction_id) {
            return 0;
        }

        $cryptoService = new NowPaymentsService();
        $status = $cryptoService->checkPaymentStatus($deposit->gateway_transaction_id);

        if (!$status) {
            return 0;
        }

        $previousStatus = $deposit->status;
        $paymentStatus = $status['payment_status'] ?? null;

        if (in_array($paymentStatus, ['confirmed', 'finished'])) {
            if (!$deposit->isCompleted()) {
                if ($deposit->isPending()) {
                    $deposit->markAsPaid();
                }
                $deposit->markAsConfirmed();
                
                $this->newLine();
                $this->info("✓ USDT {$deposit->transaction_id}: {$previousStatus} → completed");
                return 1;
            }
        } elseif ($paymentStatus === 'confirming') {
            if ($deposit->isPending()) {
                $deposit->markAsPaid();
                
                $this->newLine();
                $this->info("✓ USDT {$deposit->transaction_id}: pending → processing");
                return 1;
            }
        } elseif (in_array($paymentStatus, ['failed', 'expired'])) {
            $deposit->update(['status' => $paymentStatus]);
            
            $this->newLine();
            $this->warn("⚠ USDT {$deposit->transaction_id}: {$previousStatus} → {$paymentStatus}");
            return 1;
        }

        return 0;
    }
}