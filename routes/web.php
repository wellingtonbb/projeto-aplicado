<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HealthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rotas Públicas e de Health Check
|--------------------------------------------------------------------------
*/

// Redirecionamento da raiz (contextual: dashboard se autenticado, login se convidado)
Route::get('/', function (Request $request) {
    if ($request->session()->has('authenticated_user')) {
        return redirect()->route('dashboard');
    }

    return redirect()->route('login');
});

// Endpoint de Health Check seguro (Requisito 32)
Route::get('/health', HealthController::class)->name('health');

/*
|--------------------------------------------------------------------------
| Rotas de Autenticação (Apenas Convidados)
|--------------------------------------------------------------------------
*/
Route::middleware('custom.guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

/*
|--------------------------------------------------------------------------
| Rotas Protegidas (Requer Autenticação Ativa)
|--------------------------------------------------------------------------
*/
Route::middleware('custom.auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
