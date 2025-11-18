<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Private user channel - all user-specific events
Broadcast::channel('user.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

// Bot instance channel - bot-specific events
Broadcast::channel('bot.{botInstanceId}', function ($user, $botInstanceId) {
    $bot = \App\Models\BotInstance::find($botInstanceId);
    return $bot && (int) $bot->user_id === (int) $user->id;
});

// Investment channel - investment-specific events
Broadcast::channel('investment.{investmentId}', function ($user, $investmentId) {
    $investment = \App\Models\Investment::find($investmentId);
    return $investment && (int) $investment->user_id === (int) $user->id;
});

// Global market data channel (public)
Broadcast::channel('market.prices', function () {
    return true;
});
