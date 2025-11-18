<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Auth\EloquentUserProvider;
use App\Models\Investment;
use App\Models\Profit;
use App\Observers\InvestmentObserver;
use App\Observers\ProfitObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Registro dos Observers
        Investment::observe(InvestmentObserver::class);
        Profit::observe(ProfitObserver::class);
    }
}
