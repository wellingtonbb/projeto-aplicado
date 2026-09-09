<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Exibe a tela de login.
     */
    public function showLogin(): View
    {
        return view('auth.login');
    }

    /**
     * Processa a autenticação do usuário.
     *
     * Mitigações de Segurança:
     * - OWASP Top 10:2025: Identification and Authentication Failures:
     *   - Proteção contra força bruta via Rate Limiter por chave IP + Username.
     *   - Verificação criptográfica de senha com Bcrypt via password_verify (sem texto puro).
     *   - Mensagens de erro genéricas impedem enumeração de usuários (timing-resistant hash_equals).
     *   - Session Fixation Mitigation: session()->regenerate() após login bem-sucedido.
     * - OWASP Top 10:2025: Injection:
     *   - Validação estrita de tipos, regex e tamanho de entrada.
     */
    public function login(Request $request): RedirectResponse
    {
        // 1. Validação estrita de entrada (Mitigação de Injection e DoS)
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:50', 'alpha_dash'],
            'password' => ['required', 'string', 'min:8', 'max:100'],
        ], [
            'username.required' => 'O campo usuário é obrigatório.',
            'username.alpha_dash' => 'O formato do usuário é inválido (apenas letras, números, traços e underscores).',
            'username.max' => 'O usuário não pode ter mais que 50 caracteres.',
            'password.required' => 'O campo senha é obrigatório.',
            'password.min' => 'A senha deve conter no mínimo 8 caracteres.',
            'password.max' => 'A senha não pode exceder 100 caracteres.',
        ]);

        // 2. Chave de Rate Limiting por Username normalizado + IP de origem
        $throttleKey = Str::transliterate(Str::lower($validated['username']) . '|' . $request->ip());
        $maxAttempts = (int) config('auth.login_max_attempts', env('LOGIN_MAX_ATTEMPTS', 5));
        $decayMinutes = (int) config('auth.login_decay_minutes', env('LOGIN_DECAY_MINUTES', 1));

        // 3. Verificação de Bloqueio por Força Bruta
        if (RateLimiter::tooManyAttempts($throttleKey, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return back()
                ->withInput($request->only('username'))
                ->withErrors([
                    'login' => "Muitas tentativas incorretas detectadas. Por segurança, tente novamente em {$seconds} segundos.",
                ])
                ->withHeaders([
                    'Retry-After' => $seconds,
                ])
                ->setStatusCode(429);
        }

        // 4. Obtenção de credenciais seguras do ambiente (sem credenciais hardcoded)
        $configuredUser = (string) env('AUTH_USERNAME', 'admin');
        $configuredHash = (string) env('AUTH_PASSWORD_HASH', '');

        // 5. Verificação em tempo constante para mitigar timing attacks
        $isUsernameValid = hash_equals($configuredUser, $validated['username']);
        $isPasswordValid = password_verify($validated['password'], $configuredHash);

        if (! $isUsernameValid || ! $isPasswordValid) {
            // Incrementa contagem de falhas no Rate Limiter
            RateLimiter::hit($throttleKey, $decayMinutes * 60);

            // Mensagem intencionalmente genérica para não revelar existência de usuário
            return back()
                ->withInput($request->only('username'))
                ->withErrors([
                    'login' => 'Credenciais inválidas. Verifique seu usuário e senha.',
                ]);
        }

        // 6. Limpeza do contador de tentativas após sucesso
        RateLimiter::clear($throttleKey);

        // 7. Regeneração de ID de Sessão contra Session Fixation (Requisito 18)
        $request->session()->regenerate();

        // 8. Registro de sessão autenticada (armazenamento estritamente seguro em arquivo)
        $request->session()->put('authenticated_user', [
            'username' => $configuredUser,
            'authenticated_at' => now()->toIso8601String(),
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 120),
        ]);

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Encerra a sessão do usuário (Logout).
     *
     * Exclusivamente via método HTTP POST (Requisito 15).
     * Invalida a sessão e regenera o token CSRF.
     */
    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget('authenticated_user');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'Sessão encerrada com sucesso.');
    }
}
