<?php

namespace App\Console\Commands;

use App\Models\ArbitrageOpportunity;
use Illuminate\Console\Command;
use Carbon\Carbon;

class CleanupExpiredOpportunitiesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'arbitrage:cleanup {--days=7 : Days to keep}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cleanup old arbitrage opportunities';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = $this->option('days');
        $cutoffDate = Carbon::now()->subDays($days);
        $this->info("Cleaning up opportunities older than {$days} days...");
        $deleted = ArbitrageOpportunity::where('created_at', '<', $cutoffDate)
                    ->where('status', '!=', 'executed')
                    ->delete();
        $this->info("Deleted {$deleted} expired opportunities");
        
        return Command::SUCCESS;
    }
}
