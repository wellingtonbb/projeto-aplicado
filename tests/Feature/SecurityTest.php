<?php

namespace Tests\Feature;

use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        RateLimiter::clear('admin|127.0.0.1');
    }

    /**
     * OWASP A01:2025 - Broken Access Control
     * Teste: Usuário não autenticado tentando acessar /dashboard deve ser redirecionado para /login.
     */
    public function test_unauthenticated_user_cannot_access_dashboard(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors(['access']);
    }

    /**
     * OWASP A01:2025 - Broken Access Control
     * Teste: Usuário com sessão autenticada válida acessa /dashboard com sucesso.
     */
    public function test_authenticated_user_can_access_dashboard(): void
    {
        $response = $this->withSession([
            'authenticated_user' => [
                'username' => 'admin',
                'authenticated_at' => now()->toIso8601String(),
                'ip_address' => '127.0.0.1',
                'user_agent' => 'PHPUnit Test Agent',
            ],
        ])->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Painel de Controle');
        $response->assertSee('Bem-vindo, admin!');
        $response->assertSee('Matriz de Mitigações OWASP Top 10:2025');
    }

    /**
     * OWASP A07:2025 - Identification and Authentication Failures
     * Teste: Autenticação bem-sucedida com credenciais válidas e hash Bcrypt.
     */
    public function test_login_with_valid_credentials_succeeds(): void
    {
        $response = $this->post('/login', [
            'username' => 'admin',
            'password' => 'Admin@Secure2025!',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertTrue(session()->has('authenticated_user'));
        $this->assertEquals('admin', session('authenticated_user')['username']);
    }

    /**
     * OWASP A07:2025 - Identification and Authentication Failures
     * Teste: Falha com credenciais incorretas não autentica e retorna erro genérico.
     */
    public function test_login_with_invalid_credentials_fails(): void
    {
        $response = $this->post('/login', [
            'username' => 'admin',
            'password' => 'SenhaIncorreta123!',
        ]);

        $response->assertSessionHasErrors(['login']);
        $this->assertFalse(session()->has('authenticated_user'));
    }

    /**
     * OWASP A07:2025 - Identification and Authentication Failures
     * Teste: Rate Limiting bloqueia ataques de força bruta após tentativas excessivas.
     */
    public function test_rate_limiter_blocks_brute_force_attacks(): void
    {
        // Executa 5 tentativas falhas consecutivas (limite configurado)
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'username' => 'admin',
                'password' => 'SenhaErrada' . $i . '!',
            ]);
        }

        // A 6ª tentativa deve ser bloqueada por Rate Limiting (HTTP 429)
        $blockedResponse = $this->post('/login', [
            'username' => 'admin',
            'password' => 'TentativaAposLimite!',
        ]);

        $blockedResponse->assertStatus(429);
        $blockedResponse->assertSessionHasErrors(['login']);
        $this->assertFalse(session()->has('authenticated_user'));
    }

    /**
     * Requisito 16: CSRF Protection
     * Teste 1: O formulário de login renderiza obrigatoriamente o campo oculto _token com CSRF nativo.
     */
    public function test_login_form_contains_csrf_token_field(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('name="_token"', false);
    }

    /**
     * Requisito 16: CSRF Protection
     * Teste 2: O middleware ValidateCsrfToken rejeita requisições POST com token inválido/mismatch.
     */
    public function test_csrf_middleware_strictly_validates_and_rejects_invalid_tokens(): void
    {
        // Instancia o middleware CSRF forçando a checagem em ambiente de teste
        $middleware = new class($this->app, $this->app['encrypter']) extends \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken {
            protected function runningUnitTests(): bool
            {
                return false;
            }
        };

        $request = Request::create('/login', 'POST', [
            'username' => 'admin',
            'password' => 'Admin@Secure2025!',
            '_token' => 'token_forjado_ou_invalido',
        ]);

        $session = $this->app['session']->driver('array');
        $session->start();
        $session->put('_token', 'token_legitimo_da_sessao');
        $request->setLaravelSession($session);

        $this->expectException(TokenMismatchException::class);
        $this->expectExceptionMessage('CSRF token mismatch.');

        $middleware->handle($request, function () {
            return response('OK');
        });
    }

    /**
     * Requisito 15: Logout Seguro
     * Teste: Logout via POST invalida a sessão e revoga acesso ao dashboard.
     */
    public function test_logout_invalidates_session_and_revokes_access(): void
    {
        $session = [
            'authenticated_user' => [
                'username' => 'admin',
                'authenticated_at' => now()->toIso8601String(),
                'ip_address' => '127.0.0.1',
            ],
        ];

        // 1. Executa Logout via POST
        $logoutResponse = $this->withSession($session)->post('/logout');
        $logoutResponse->assertRedirect('/login');
        $this->assertFalse(session()->has('authenticated_user'));

        // 2. Confirma que novo acesso a /dashboard é bloqueado
        $dashboardResponse = $this->get('/dashboard');
        $dashboardResponse->assertRedirect('/login');
    }

    /**
     * Requisito 15: Logout Seguro
     * Teste: Logout via método GET é terminantemente proibido (HTTP 405 Method Not Allowed).
     */
    public function test_logout_cannot_be_performed_via_get(): void
    {
        $response = $this->get('/logout');
        $response->assertStatus(405);
    }

    /**
     * Requisito 19: Prevenção contra Cross-Site Scripting (XSS)
     * Teste: Payload XSS não é executado e é escapado pelo mecanismo Blade do Laravel.
     */
    public function test_xss_payload_is_escaped_and_not_executed(): void
    {
        $xssPayload = '<script>alert("XSS")</script>';

        $response = $this->post('/login', [
            'username' => $xssPayload,
            'password' => 'SenhaInvalida123!',
        ]);

        // Validação de que o formulário é recarregado com o input escapado
        $response->assertSessionHasErrors(['username']);

        // Testa a renderização da tela de login contendo o input com caracteres especiais
        $viewResponse = $this->withSession([
            '_old_input' => ['username' => $xssPayload],
        ])->get('/login');

        $viewResponse->assertStatus(200);
        // O script bruto NÃO deve estar presente de forma executável
        $content = $viewResponse->getContent();
        $this->assertStringNotContainsString('<script>alert("XSS")</script>', $content);
        $this->assertStringContainsString('&lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;', $content);
    }

    /**
     * OWASP A03:2025 - Injection
     * Teste: Validação estrita de tipos e limites no formulário de login.
     */
    public function test_login_input_validation_rules(): void
    {
        // Campos vazios
        $emptyResponse = $this->post('/login', [
            'username' => '',
            'password' => '',
        ]);
        $emptyResponse->assertSessionHasErrors(['username', 'password']);

        // Senha com menos de 8 caracteres
        $shortPassResponse = $this->post('/login', [
            'username' => 'admin',
            'password' => '123',
        ]);
        $shortPassResponse->assertSessionHasErrors(['password']);

        // Usuário com caracteres proibidos (ex: SQL injection quotes ou shell metacharacters)
        $badUserResponse = $this->post('/login', [
            'username' => 'admin\' OR 1=1 --',
            'password' => 'QualquerSenha123!',
        ]);
        $badUserResponse->assertSessionHasErrors(['username']);
    }

    /**
     * Requisito 32: Health Check
     * Teste: Endpoint /health retorna status ok em JSON e não vaza segredos.
     */
    public function test_health_check_returns_ok_without_information_disclosure(): void
    {
        $response = $this->get('/health');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJson([
            'status' => 'ok',
        ]);

        $body = $response->getContent();
        $this->assertStringNotContainsString('APP_KEY', $body);
        $this->assertStringNotContainsString('AUTH_PASSWORD_HASH', $body);
        $this->assertStringNotContainsString('database', $body);
    }
}
