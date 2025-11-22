<?php

namespace App\View\Composers;

use App\Models\BotInstance; // Certifique-se de que este caminho está correto
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class ActiveBotsComposer
{
    /**
     * Liga os dados à view 'navigation.blade.php'.
     *
     * @param  \Illuminate\View\View  $view
     * @return void
     */
    public function compose(View $view): void
    {
        // Garante que a consulta só é feita se houver um utilizador autenticado
        if (Auth::check()) {
            $userId = Auth::id();

            // Conta quantos robôs estão ativos para o utilizador atual.
            $activeBotsCount = BotInstance::where('user_id', $userId)
                                          ->where('is_active', true)
                                          ->count();
        } else {
            $activeBotsCount = 0;
        }

        // Injeta a variável $activeBotsCount na view.
        $view->with('activeBotsCount', $activeBotsCount);
    }
}