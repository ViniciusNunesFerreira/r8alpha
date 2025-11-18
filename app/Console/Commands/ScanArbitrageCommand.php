<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
 use App\Models\BotInstance;
 use App\Jobs\ScanArbitrageOpportunities;
  use Illuminate\Support\Facades\Log;

class ScanArbitrageCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature =  'arbitrage:scan 
                            {--bot= : ID específico de um robô}
                            {--force : Força scan mesmo se robô estiver inativo}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan for arbitrage opportunities for all active bots';

    /**
     * Execute the console command.
     */
    public function handle()
    {
         $this->info('Starting Arbitrage Scanner...');
         $this->newLine();

        try {

            $query = BotInstance::query();
            
            // Se ID específico foi fornecido
            if ($botId = $this->option('bot')) {

                $query->where('id', $botId);
                $this->info("Scanning specific bot: #{$botId}");

            } else {
                // Apenas robôs ativos, a menos que --force
                if (!$this->option('force')) {
                    $query->where('is_active', true);
                }
                $this->info(' Scanning all ' . ($this->option('force') ? '' : 'active ') . 'bots');
            }
            $bots = $query->with('investment')->get();

            if ($bots->isEmpty()) {
                $this->warn('No bots found matching criteria');
                return Command::SUCCESS;
            }

            $this->info("Found {$bots->count()} bot(s) to scan");
            $this->newLine();
            // Progress bar
            $bar = $this->output->createProgressBar($bots->count());
            $bar->start();
            $dispatched = 0;
            $skipped = 0;

            foreach ($bots as $bot) {
                if (!$bot->is_active && !$this->option('force')) {
                    $skipped++;
                    $bar->advance();
                    continue;
                }
                // Dispatch job
                ScanArbitrageOpportunities::dispatch($bot);
                $dispatched++;
                Log::info('Arbitrage scan dispatched', ['bot_instance_id' => $bot->instance_id, 'user_id' => $bot->user_id, 'investment_amount' => $bot->investment->amount]);
                $bar->advance();
            }
            
            $bar->finish();

            $this->newLine(2);

            $this->info('Scan completed successfully!');

            $this->table(
                ['Metric', 'Value'],
                [
                    ['Total Bots', $bots->count()],
                    ['Jobs Dispatched', $dispatched],
                    ['Skipped (inactive)', $skipped],
                ]
            );

            return Command::SUCCESS;

        } catch (\Exception $e) {

            $this->error('Error scanning for arbitrage opportunities');
            $this->error($e->getMessage());
            Log::error('Arbitrage scan command error', ['error' => $e->getMessage(),'trace' => $e->getTraceAsString()]);
            return Command::FAILURE;

        }
    }
}
