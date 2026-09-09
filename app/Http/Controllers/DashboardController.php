<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Exibe o painel de controle seguro (Dashboard).
     *
     * Acessível unicamente para usuários autenticados via middleware EnsureCustomAuthenticated.
     */
    public function index(Request $request): View
    {
        $userData = $request->session()->get('authenticated_user', [
            'username' => 'Usuário',
            'authenticated_at' => now()->toIso8601String(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $appInfo = [
            'name' => config('app.name', 'Secure Web Application'),
            'env' => config('app.env'),
            'debug' => config('app.debug') ? 'Ativo (Alerta: Inseguro em Produção)' : 'Desativado (Secure by Default)',
            'laravel_version' => app()->version(),
            'php_version' => PHP_VERSION,
            'server_software' => $request->server('SERVER_SOFTWARE', 'Apache/2.4 (Docker)'),
            'scheme' => $request->getScheme(),
            'is_https' => $request->isSecure() ? 'Sim (TLS Ativo via Reverse Proxy)' : 'Não (HTTP Interno)',
            'client_ip' => $request->ip(),
            'session_driver' => config('session.driver', 'file'),
            'session_lifetime' => config('session.lifetime') . ' minutos',
            'session_id_masked' => substr($request->session()->getId(), 0, 8) . '...' . substr($request->session()->getId(), -8),
        ];

        $securityMechanisms = [
            [
                'name' => 'Reverse Proxy com Terminação TLS',
                'category' => 'Infraestrutura Host',
                'description' => 'Apache 2.4 no Host atuando como Reverse Proxy e terminador SSL com Certbot Let\'s Encrypt.',
                'status' => 'Ativo',
            ],
            [
                'name' => 'Isolamento de Contêiner & Menor Privilégio',
                'category' => 'Docker / Virtualização',
                'description' => 'Contêiner Docker escuta estritamente em 127.0.0.1:8080 (loopback). Porta 8080 não exposta à Internet.',
                'status' => 'Ativo',
            ],
            [
                'name' => 'Proteção CSRF Nativa',
                'category' => 'Aplicação / Laravel',
                'description' => 'Tokens criptográficos verificados em todas as requisições de modificação de estado (POST/PUT/DELETE).',
                'status' => 'Ativo',
            ],
            [
                'name' => 'Rate Limiting (Anti-Brute Force)',
                'category' => 'Autenticação',
                'description' => 'Bloqueio progressivo temporário com cabeçalho Retry-After após 5 tentativas incorretas por IP/Username.',
                'status' => 'Ativo',
            ],
            [
                'name' => 'Hash Criptográfico de Senhas (Bcrypt)',
                'category' => 'Criptografia',
                'description' => 'Senhas nunca em texto puro. Verificação resistente a timing attacks via password_verify().',
                'status' => 'Ativo',
            ],
            [
                'name' => 'Regeneração & Invalidação de Sessão',
                'category' => 'Gestão de Sessão',
                'description' => 'Regeneração de ID de sessão após login previne Session Fixation; logout invalida a sessão completamente.',
                'status' => 'Ativo',
            ],
            [
                'name' => 'Cookies Seguros (HttpOnly, SameSite)',
                'category' => 'Navegador / Cookies',
                'description' => 'Cookies com flags HttpOnly (inacessíveis via JavaScript/XSS), SameSite=Lax e Secure sob HTTPS.',
                'status' => 'Ativo',
            ],
            [
                'name' => 'Trust Proxies Configurado',
                'category' => 'Rede / Proxy',
                'description' => 'Laravel configurado para confiar nos cabeçalhos X-Forwarded-Proto e X-Forwarded-For do Apache Host.',
                'status' => 'Ativo',
            ],
            [
                'name' => 'Security Headers (CSP, HSTS, X-Content-Type)',
                'category' => 'Cabeçalhos HTTP',
                'description' => 'Cabeçalhos estritos aplicados pelo Apache Host para prevenir MIME sniffing, clickjacking e XSS.',
                'status' => 'Ativo',
            ],
            [
                'name' => 'Firewall UFW & Fail2Ban',
                'category' => 'Hardening do Host',
                'description' => 'Apenas portas 22, 80 e 443 liberadas. Banimento automático de 24h após 4 tentativas falhas no SSH.',
                'status' => 'Ativo',
            ],
        ];

        $owaspMitigations = [
            [
                'id' => 'A01:2025',
                'category' => 'Broken Access Control',
                'risk' => 'Crítico',
                'mitigation' => 'Middleware EnsureCustomAuthenticated protege rotas privadas; tentativas de acesso não autenticado a /dashboard são interceptadas com redirecionamento para /login; logout restrito ao método POST com invalidação total de sessão.',
                'files' => 'app/Http/Middleware/EnsureCustomAuthenticated.php, routes/web.php',
                'test' => 'SecurityTest::test_unauthenticated_user_cannot_access_dashboard()',
            ],
            [
                'id' => 'A03:2025',
                'category' => 'Injection',
                'risk' => 'Alto',
                'mitigation' => 'Validação rigorosa de formulários com tipagem estrita (alpha_dash, min, max); eliminação total de interpolação de comandos de sistema ou SQL bruto; arquitetura sem banco de dados elimina SQL Injection.',
                'files' => 'app/Http/Controllers/AuthController.php',
                'test' => 'SecurityTest::test_login_input_validation_rules()',
            ],
            [
                'id' => 'A07:2025',
                'category' => 'Identification and Authentication Failures',
                'risk' => 'Alto',
                'mitigation' => 'Armazenamento de senhas via hash Bcrypt (custo 12) sem credenciais em texto puro; limitação de taxa (Rate Limiting de 5 tentativas/minuto); mensagens genéricas contra enumeração de contas; regeneração de ID de sessão após autenticação.',
                'files' => 'app/Http/Controllers/AuthController.php, config/session.php',
                'test' => 'SecurityTest::test_rate_limiter_blocks_brute_force_attacks()',
            ],
        ];

        return view('dashboard', compact('userData', 'appInfo', 'securityMechanisms', 'owaspMitigations'));
    }
}
