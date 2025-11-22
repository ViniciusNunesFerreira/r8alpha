<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\InvestmentPlan;

class InvestmentPlansSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limpa planos existentes (opcional - cuidado em produção!)
        // InvestmentPlan::truncate();

        $plans = [
            [
                'name' => 'Starter Plan',
                'description' => 'Perfect for beginners entering the crypto arbitrage world. Start small and learn the ropes.',
                'min_amount' => 10.00,
                'max_amount' => 99.99,
                'daily_return_min' => 2.0,
                'daily_return_max' => 10.0,
                'duration_days' => 45,
                'is_active' => true,
                'is_capital_back' => false, 
            ],
            [
                'name' => 'Professional Plan',
                'description' => 'Advanced trading for experienced investors. Higher returns with optimized algorithms.',
                'min_amount' => 100.00,
                'max_amount' => 10000.00,
                'daily_return_min' => 1.5,
                'daily_return_max' => 3.0,
                'duration_days' => 60,
                'is_active' => true,
                'is_capital_back' => false,
            ]
            
        ];

        foreach ($plans as $planData) {
            InvestmentPlan::create($planData);
            $this->command->info("✅ Plan created: {$planData['name']}");
        }

        $this->command->info("🎉 All investment plans seeded successfully!");
        $this->command->newLine();
        
        // Exibe resumo
        $this->displaySummary();
    }

    /**
     * Exibe resumo dos planos criados
     */
    protected function displaySummary(): void
    {
        $plans = InvestmentPlan::all();
        
        $this->command->table(
            ['ID', 'Name', 'Min Amount', 'Max Amount', 'Daily Return', 'Duration', 'Status'],
            $plans->map(function ($plan) {
                return [
                    $plan->id,
                    $plan->name,
                    '$' . number_format($plan->min_amount, 2),
                    '$' . number_format($plan->max_amount, 2),
                    $plan->daily_return_min . '% - ' . $plan->daily_return_max . '%',
                    $plan->duration_days . ' days',
                    $plan->is_active ? '✅ Active' : '❌ Inactive',
                ];
            })->toArray()
        );

        $this->command->newLine();
        $this->command->info("Total plans created: " . $plans->count());
        $this->command->info("Active plans: " . $plans->where('is_active', true)->count());
    }
}