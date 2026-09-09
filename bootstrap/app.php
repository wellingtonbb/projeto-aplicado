<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Configuração de Confiança no Reverse Proxy (Requisito 26 e 27)
        // Permite ao Laravel reconhecer conexões HTTPS originadas pelo Apache no Host
        $middleware->trustProxies(
            at: '*',
            headers: Request::HEADER_X_FORWARDED_FOR |
                     Request::HEADER_X_FORWARDED_HOST |
                     Request::HEADER_X_FORWARDED_PORT |
                     Request::HEADER_X_FORWARDED_PROTO
        );

        // Registro dos apelidos de middleware customizados para controle de acesso
        $middleware->alias([
            'custom.auth' => \App\Http\Middleware\EnsureCustomAuthenticated::class,
            'custom.guest' => \App\Http\Middleware\RedirectIfCustomAuthenticated::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
