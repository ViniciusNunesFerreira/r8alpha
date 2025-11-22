<?php

namespace App\Policies;

use App\Models\BotInstance;
use App\Models\User;

class BotInstancePolicy
{
    /**
     * Determine if the user can view any bots.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine if the user can view the bot.
     */
    public function view(User $user, BotInstance $bot): bool
    {
        return $user->id === $bot->user_id;
    }

    /**
     * Determine if the user can create bots.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine if the user can update the bot.
     */
    public function update(User $user, BotInstance $bot): bool
    {
        return $user->id === $bot->user_id;
    }

    /**
     * Determine if the user can delete the bot.
     */
    public function delete(User $user, BotInstance $bot): bool
    {
        return $user->id === $bot->user_id && !$bot->is_active;
    }

    /**
     * Determine if the user can toggle the bot status.
     */
    public function toggle(User $user, BotInstance $bot): bool
    {
        return $user->id === $bot->user_id;
    }
}