<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('arbitrage:scan')->everyMinute();

// Comando para processar lucros, rodando de hora em hora
Schedule::command('profits:process')->everyMinute();
