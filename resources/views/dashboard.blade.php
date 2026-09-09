@extends('layouts.app')

@section('title', 'Painel de Controle')

@section('content')
<div class="row g-4">
    <!-- Cabeçalho com boas-vindas e Ação de Logout -->
    <div class="col-12">
        <div class="card card-custom bg-dark text-white p-4 shadow-sm border-0">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div>
                    <span class="badge bg-primary px-3 py-2 text-uppercase mb-2">Área Restrita Autenticada</span>
                    <h2 class="fw-bold mb-1 d-flex align-items-center gap-2">
                        <i class="bi bi-person-badge text-info"></i>
                        Bem-vindo, {{ $userData['username'] }}!
                    </h2>
                    <p class="text-secondary mb-0 small">
                        Autenticação estabelecida em <strong>{{ $userData['authenticated_at'] }}</strong> a partir do IP <code>{{ $userData['ip_address'] }}</code>.
                    </p>
                </div>
                <div>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-danger btn-lg shadow-sm px-4 d-flex align-items-center gap-2">
                            <i class="bi bi-box-arrow-right fs-5"></i>
                            <span>Encerrar Sessão (Logout)</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Informações da Aplicação e Sessão -->
    <div class="col-12 col-lg-4">
        <div class="card card-custom h-100 bg-white">
            <div class="card-header bg-white py-3 border-bottom">
                <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                    <i class="bi bi-cpu text-primary"></i>
                    Ambiente & Runtime
                </h5>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush small">
                    <li class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
                        <span class="text-muted">Aplicação</span>
                        <span class="fw-semibold">{{ $appInfo['name'] }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
                        <span class="text-muted">Ambiente (APP_ENV)</span>
                        <span class="badge bg-secondary">{{ $appInfo['env'] }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
                        <span class="text-muted">Modo Debug</span>
                        <span class="badge {{ config('app.debug') ? 'bg-warning text-dark' : 'bg-success' }}">{{ $appInfo['debug'] }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
                        <span class="text-muted">Laravel Framework</span>
                        <span class="badge bg-danger">v{{ $appInfo['laravel_version'] }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
                        <span class="text-muted">PHP Runtime</span>
                        <span class="badge bg-primary">v{{ $appInfo['php_version'] }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
                        <span class="text-muted">Esquema / Protocolo</span>
                        <span class="fw-semibold text-uppercase">{{ $appInfo['scheme'] }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
                        <span class="text-muted">TLS / HTTPS Reconhecido</span>
                        <span class="badge bg-success">{{ $appInfo['is_https'] }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
                        <span class="text-muted">Driver de Sessão</span>
                        <span class="badge bg-info text-dark">{{ $appInfo['session_driver'] }} (Sem Banco)</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
                        <span class="text-muted">Sessão ID (Ofuscado)</span>
                        <code>{{ $appInfo['session_id_masked'] }}</code>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
                        <span class="text-muted">Tempo Limite da Sessão</span>
                        <span>{{ $appInfo['session_lifetime'] }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Mecanismos de Segurança Implementados -->
    <div class="col-12 col-lg-8">
        <div class="card card-custom h-100 bg-white">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                    <i class="bi bi-shield-check text-success"></i>
                    Mecanismos Ativos de Segurança (Secure by Design)
                </h5>
                <span class="badge bg-success">10 Controles Ativos</span>
            </div>
            <div class="card-body p-3">
                <div class="row g-2">
                    @foreach($securityMechanisms as $mechanism)
                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded bg-light h-100">
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <h6 class="fw-bold mb-0 text-dark">{{ $mechanism['name'] }}</h6>
                                    <span class="badge bg-success badge-security">{{ $mechanism['status'] }}</span>
                                </div>
                                <span class="badge bg-secondary small mb-2">{{ $mechanism['category'] }}</span>
                                <p class="small text-muted mb-0">{{ $mechanism['description'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Tabela de Mitigações OWASP Top 10:2025 (Requisito 21) -->
    <div class="col-12">
        <div class="card card-custom bg-white shadow-sm">
            <div class="card-header bg-dark text-white py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 d-flex align-items-center gap-2">
                        <i class="bi bi-journal-check text-warning"></i>
                        Matriz de Mitigações OWASP Top 10:2025
                    </h5>
                    <span class="badge bg-warning text-dark fw-bold">Requisito Acadêmico Obrigatório</span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 15%;">Categoria OWASP</th>
                                <th style="width: 10%;">Severidade</th>
                                <th style="width: 35%;">Estratégia de Mitigação Implementada</th>
                                <th style="width: 20%;">Arquivos Responsáveis</th>
                                <th style="width: 20%;">Evidência / Teste Automatizado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($owaspMitigations as $mitigation)
                                <tr>
                                    <td>
                                        <span class="badge bg-dark">{{ $mitigation['id'] }}</span>
                                        <div class="fw-bold text-dark mt-1">{{ $mitigation['category'] }}</div>
                                    </td>
                                    <td>
                                        <span class="badge {{ $mitigation['risk'] === 'Crítico' ? 'bg-danger' : 'bg-warning text-dark' }}">
                                            {{ $mitigation['risk'] }}
                                        </span>
                                    </td>
                                    <td class="small text-secondary">
                                        {{ $mitigation['mitigation'] }}
                                    </td>
                                    <td>
                                        <code class="small text-break">{{ $mitigation['files'] }}</code>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            <i class="bi bi-check2-circle text-success me-1"></i>
                                            {{ $mitigation['test'] }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-light py-2 px-3 small text-muted">
                <i class="bi bi-info-circle me-1"></i> Categorias mapeadas em conformidade com o padrão oficial OWASP Top 10:2025 e testadas via PHPUnit.
            </div>
        </div>
    </div>
</div>
@endsection
