<?php

namespace App\Console\Commands;

use App\Models\Investment;
use App\Models\BotInstance;
use App\Models\Trade;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class GenerateReportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'report:generate {--email= : Email to send report}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate daily platform report';

    /**
     * Execute the console command.
     */
    public function handle()
    {
         $this->info('Generating Platform Report...');

        $report = [
            'date' => now()->format('Y-m-d'),
            'total_users' => User::count(),
            'active_investments' => Investment::where('status', 'active')->count(),
            'total_invested' => Investment::sum('amount'),
            'total_profit_generated' => Investment::sum('total_profit'),
            'active_bots' => BotInstance::where('is_active', true)->count(),
            'total_trades_today' => Trade::whereDate('created_at', today())->count(),
        ];

         $this->table(
            ['Metric', 'Value'],
            collect($report)->map(fn($value, $key) => [
                str_replace('_', ' ', ucfirst($key)),
                is_numeric($value) ? number_format($value, 2) : $value
            ])->toArray()
        );

        if ($email = $this->option('email')) {
            $this->info("Sending report to {$email}...");

            // Implementar envio de email
            Mail::raw(json_encode($report, JSON_PRETTY_PRINT), function ($message) use ($email) {
                $message->to($email)->subject('Daily Platform Report');
            });
        }
        
        return Command::SUCCESS;
    }
}
