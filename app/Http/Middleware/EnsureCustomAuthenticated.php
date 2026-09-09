<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCustomAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * Mitiga OWASP Top 10:2025 - Broken Access Control:
     * Garante que apenas usuários com sessão ativa e autenticada
     * possam acessar rotas protegidas como /dashboard.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->session()->has('authenticated_user')) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Unauthenticated',
                    'message' => 'Acesso não autorizado. Autenticação obrigatória.',
                ], 401);
            }

            return redirect()->route('login')->withErrors([
                'access' => 'Acesso negado. Faça login para acessar esta área protegida.',
            ]);
        }

        return $next($request);
    }
}
