@extends('layouts.app')

@section('title', 'Autenticação Segura')

@section('content')
<div class="row justify-content-center my-4">
    <div class="col-12 col-md-8 col-lg-5">
        <div class="card card-custom shadow-lg">
            <div class="card-header bg-dark text-white text-center py-4 border-bottom border-primary border-3 rounded-top">
                <div class="display-6 mb-2">
                    <i class="bi bi-shield-lock text-primary"></i>
                </div>
                <h4 class="fw-bold mb-1">Acesso Restrito</h4>
                <p class="text-secondary small mb-0">Autenticação Segura — Secure by Default</p>
            </div>

            <div class="card-body p-4 p-md-5">
                {{-- Alerta Geral de Erro / Rate Limiting --}}
                @if($errors->has('login'))
                    <div class="alert alert-danger d-flex align-items-center mb-4 shadow-sm" role="alert">
                        <i class="bi bi-exclamation-triangle-fill fs-5 me-3 flex-shrink-0"></i>
                        <div>{{ $errors->first('login') }}</div>
                    </div>
                @endif

                <form method="POST" action="{{ route('login.post') }}" novalidate>
                    @csrf

                    <!-- Usuário -->
                    <div class="mb-3">
                        <label for="username" class="form-label fw-semibold">
                            <i class="bi bi-person me-1 text-secondary"></i> Usuário
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted"><i class="bi bi-person-fill"></i></span>
                            <input 
                                type="text" 
                                class="form-control @error('username') is-invalid @enderror" 
                                id="username" 
                                name="username" 
                                value="{{ old('username') }}" 
                                placeholder="Digite seu usuário" 
                                required 
                                autofocus 
                                autocomplete="username"
                                maxlength="50"
                            >
                            @error('username')
                                <div class="invalid-feedback d-block">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>

                    <!-- Senha -->
                    <div class="mb-4">
                        <label for="password" class="form-label fw-semibold">
                            <i class="bi bi-key me-1 text-secondary"></i> Senha
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted"><i class="bi bi-lock-fill"></i></span>
                            <input 
                                type="password" 
                                class="form-control @error('password') is-invalid @enderror" 
                                id="password" 
                                name="password" 
                                placeholder="Digite sua senha" 
                                required 
                                autocomplete="current-password"
                                maxlength="100"
                            >
                            @error('password')
                                <div class="invalid-feedback d-block">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>

                    <!-- Botão de Login -->
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg shadow-sm fw-semibold">
                            <i class="bi bi-box-arrow-in-right me-2"></i> Autenticar
                        </button>
                    </div>
                </form>
            </div>

            <div class="card-footer bg-light py-3 px-4 rounded-bottom border-top">
                <div class="small text-muted d-flex align-items-center gap-2">
                    <i class="bi bi-info-circle text-primary"></i>
                    <span><strong>Mitigações:</strong> Proteção Anti-Brute Force (Rate Limit), Bcrypt Hash e CSRF ativo.</span>
                </div>
            </div>
        </div>

        <!-- Card Informativo Acadêmico -->
        <div class="card mt-4 border-0 bg-white shadow-sm">
            <div class="card-body p-3 small text-secondary">
                <h6 class="fw-bold text-dark mb-2"><i class="bi bi-shield-check text-success me-1"></i> Diretrizes de Segurança</h6>
                <ul class="mb-0 ps-3">
                    <li>Credenciais são validadas contra variáveis de ambiente (sem banco de dados).</li>
                    <li>Senhas armazenadas com Hash Criptográfico seguro (Bcrypt cost 12).</li>
                    <li>Após autenticação com sucesso, ocorre regeneração de ID de sessão.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
