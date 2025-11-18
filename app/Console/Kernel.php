<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        Commands\ScanArbitrageCommand::class,
        Commands\ProcessDailyProfitsCommand::class,
    ];

    protected function schedule(Schedule $schedule)
    {
        // Scan for arbitrage opportunities every minute
        $schedule->command('arbitrage:scan')->everyMinute();
        
        // Process daily profits at midnight
        $schedule->command('profits:process')->hourly();
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}