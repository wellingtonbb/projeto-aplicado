<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Teste da rota raiz redirecionando usuário não autenticado para login sem erros.
     */
    public function test_root_route_redirects_to_login_for_guest(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/login');
        $response->assertSessionMissing('errors');
    }

    /**
     * Teste da rota raiz redirecionando usuário autenticado para dashboard.
     */
    public function test_root_route_redirects_to_dashboard_for_authenticated_user(): void
    {
        $response = $this->withSession([
            'authenticated_user' => [
                'username' => 'admin',
                'authenticated_at' => now()->toIso8601String(),
                'ip_address' => '127.0.0.1',
            ],
        ])->get('/');

        $response->assertRedirect('/dashboard');
    }
}
