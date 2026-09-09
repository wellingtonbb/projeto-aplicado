<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class HealthController extends Controller
{
    /**
     * Endpoint de verificação de integridade da aplicação (Health Check).
     *
     * Atende ao Requisito 32:
     * Retorna estritamente JSON {"status": "ok"} com código HTTP 200.
     * Não expõe dados de ambiente, credenciais, caminhos de arquivo ou segredos.
     */
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
        ], 200, [
            'Content-Type' => 'application/json',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
        ]);
    }
}
