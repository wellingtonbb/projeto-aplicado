<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfCustomAuthenticated
{
    /**
     * Redireciona usuários já autenticados para o dashboard ao acessarem /login.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->session()->has('authenticated_user')) {
            return redirect()->route('dashboard');
        }

        return $next($request);
    }
}
