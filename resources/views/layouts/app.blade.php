<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Projeto Web Seguro') - Secure by Design</title>
    
    <!-- Bootstrap 5.3.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
            --dark-surface: #0f172a;
            --card-surface: #ffffff;
        }
        body {
            background-color: #f8fafc;
            color: #1e293b;
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .navbar-brand-badge {
            background-color: rgba(255, 255, 255, 0.2);
            padding: 0.2rem 0.6rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .card-custom {
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .card-custom:hover {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.08);
        }
        .badge-security {
            font-size: 0.75rem;
            padding: 0.4em 0.8em;
            border-radius: 0.375rem;
            font-weight: 600;
        }
        footer {
            margin-top: auto;
            background-color: #0f172a;
            color: #94a3b8;
            font-size: 0.875rem;
        }
    </style>
    @stack('styles')
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark py-3 shadow-sm">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2 fw-bold" href="{{ url('/') }}">
                <i class="bi bi-shield-lock-fill text-primary fs-4"></i>
                <span>SecureApp</span>
                <span class="navbar-brand-badge">DevSecOps</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active fw-semibold' : '' }}" href="{{ route('dashboard') }}">
                            <i class="bi bi-speedometer2 me-1"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('health') }}" target="_blank">
                            <i class="bi bi-heart-pulse me-1"></i> Health Check
                        </a>
                    </li>
                </ul>
                <div class="d-flex align-items-center gap-3">
                    @if(session()->has('authenticated_user'))
                        <span class="navbar-text text-light">
                            <i class="bi bi-person-circle text-info me-1"></i>
                            <strong>{{ session('authenticated_user.username') }}</strong>
                        </span>
                        <form action="{{ route('logout') }}" method="POST" class="d-inline m-0">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger btn-sm px-3 d-flex align-items-center gap-1">
                                <i class="bi bi-box-arrow-right"></i> Sair
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-primary btn-sm px-3">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Login
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </nav>

    <main class="py-4 flex-grow-1">
        <div class="container">
            @if(session('status'))
                <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> {{ session('status') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if($errors->has('access'))
                <div class="alert alert-warning alert-dismissible fade show shadow-sm" role="alert">
                    <i class="bi bi-shield-exclamation me-2"></i> {{ $errors->first('access') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    <footer class="py-4 border-top border-secondary">
        <div class="container text-center">
            <div class="row align-items-center">
                <div class="col-md-6 text-md-start mb-2 mb-md-0">
                    <strong>Projeto Web Seguro</strong> — Secure by Design & Secure by Default
                    <div class="small text-secondary">Apache Host + Reverse Proxy + TLS + Docker + Laravel 12</div>
                </div>
                <div class="col-md-6 text-md-end small">
                    <span class="badge bg-secondary me-1">PHP 8.4</span>
                    <span class="badge bg-secondary me-1">Laravel 12</span>
                    <span class="badge bg-secondary me-1">Docker</span>
                    <span class="badge bg-secondary">Apache 2.4</span>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5.3.3 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    @stack('scripts')
</body>
</html>
