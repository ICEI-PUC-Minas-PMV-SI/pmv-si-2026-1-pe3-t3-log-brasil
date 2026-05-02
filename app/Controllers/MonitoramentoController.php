<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Helpers;
use App\Core\View;
use App\Models\Viagem;

final class MonitoramentoController extends Controller
{
    public function divergencias(): void
    {
        $this->requirePapel(['admin', 'gestor', 'monitoramento']);

        View::render('monitoramento/divergencias', [
            'nav' => 'monitoramento_div',
            'title' => 'Divergências pendentes',
            'lista' => Viagem::divergenciasPendentesPainel(),
        ]);
    }

    public function apiRevisar(): void
    {
        $this->requirePapel(['admin', 'gestor', 'monitoramento']);

        $payload = json_decode((string) file_get_contents('php://input'), true) ?: [];
        $this->denyUnlessCsrf($payload['_csrf'] ?? null);

        $id = (int) ($payload['divergencia_id'] ?? 0);
        if ($id <= 0) {
            Helpers::jsonResponse(['ok' => false, 'message' => 'ID inválido'], 422);
        }
        $aprovar = filter_var($payload['aprovar'] ?? false, FILTER_VALIDATE_BOOLEAN);

        $ok = Viagem::aprovarDivergencia($id, (int) $_SESSION['user']['id'], $aprovar);
        if (! $ok) {
            Helpers::jsonResponse(['ok' => false, 'message' => 'Registro não encontrado'], 404);
        }
        Helpers::jsonResponse(['ok' => true]);
    }
}
