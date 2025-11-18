<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect('/login'); // Redireciona para o login se não estiver autenticado
        }

        // 2. Verifica se o usuário é um administrador
        // Você deve adaptar esta lógica para o seu modelo de User.
        // Exemplo: Usando uma coluna 'is_admin'
        if (auth()->user() && auth()->user()->is_admin) {
            return $next($request);
        }

        // 3. Nega o acesso se não for administrador
        abort(403, 'Access denied. You do not have permissions.');
    }
}
