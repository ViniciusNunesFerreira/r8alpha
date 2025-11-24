<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Auth\EloquentUserProvider;
use App\Models\Investment;
use Illuminate\Support\Facades\View;
use App\View\Composers\ActiveBotsComposer;
use Illuminate\Support\Facades\URL;

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
        View::composer('layouts.navigation', ActiveBotsComposer::class);



        if($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
