<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Helpers;
use App\Core\View;
use App\Models\Pedido;

/** Consulta pública de entregas por CPF. */
final class ClientePortalController extends Controller
{
    public function acompanhar(): void
    {
        View::render('cliente/acompanhar', [
            'title' => 'Acompanhar entregas',
        ], 'layouts/portal-cliente');
    }

    /** JSON dos pedidos agrupados (pendentes / realizadas). */
    public function apiRastrear(): void
    {
        $payload = json_decode((string) file_get_contents('php://input'), true) ?: [];
        $this->denyUnlessCsrf($payload['_csrf'] ?? null);

        $cpf = preg_replace('/\D/', '', (string) ($payload['cpf'] ?? ''));
        if (strlen($cpf) !== 11) {
            Helpers::jsonResponse([
                'ok' => false,
                'message' => 'Informe um CPF válido com 11 dígitos.',
            ], 422);
        }

        $dados = Pedido::rastrearPorCpfCliente($cpf);
        Helpers::jsonResponse(['ok' => true, 'dados' => $dados]);
    }
}
