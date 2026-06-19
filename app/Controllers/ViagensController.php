<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Helpers;
use App\Core\View;
use App\Models\Viagem;

/**
 * Acompanhamento operacional das viagens abertas e encerramento.
 */
final class ViagensController extends Controller
{
    /** Lista cards de viagens com status específico no banco de dados. */
    public function abertas(): void
    {
        $this->requireLogin();

        $lista = Viagem::listarAbertasParaPainel();
        foreach ($lista as &$row) {
            $row['_div_pend'] = Viagem::contarDivergenciasPendentesViagem((int) $row['id']);
            $row['_pode_fin'] = Viagem::podeFinalizarOperacional((int) $row['id']);
        }
        unset($row);

        View::render('viagens/abertas', [
            'nav' => 'viagens_abertas',
            'title' => 'Viagens em aberto',
            'lista' => $lista,
        ]);
    }

    public function finalizadas(): void
    {
        $this->requireLogin();

        View::render('viagens/finalizadas', [
            'nav' => 'viagens_final',
            'title' => 'Viagens finalizadas',
            'lista' => Viagem::listarPorStatus('finalizada'),
        ]);
    }

    public function apiPedidos(int $viagemId): void
    {
        $this->requireLogin();
        Helpers::jsonResponse([
            'ok' => true,
            'pedidos' => Viagem::pedidosDaViagem($viagemId),
        ]);
    }

    public function apiFinalizar(int $viagemId): void
    {
        $this->requireLogin();

        $payload = json_decode((string) file_get_contents('php://input'), true) ?: [];
        $this->denyUnlessCsrf($payload['_csrf'] ?? null);

        $v = Viagem::encontrar($viagemId);
        if ($v === null || $v['status'] !== 'aberta') {
            Helpers::jsonResponse(['ok' => false, 'message' => 'Viagem inexistente ou já encerrada'], 409);
        }

        try {
            Viagem::finalizar($viagemId);
        } catch (\RuntimeException $e) {
            Helpers::jsonResponse(['ok' => false, 'message' => $e->getMessage()], 422);
        }
        Helpers::jsonResponse(['ok' => true]);
    }

    /** Registra divergência operacional relacionada ao carregamento. */
    public function apiDivergencia(int $viagemId): void
    {
        $this->requireLogin();

        $payload = json_decode((string) file_get_contents('php://input'), true) ?: [];
        $this->denyUnlessCsrf($payload['_csrf'] ?? null);

        $texto = trim((string) ($payload['descricao'] ?? ''));
        $pid = isset($payload['pedido_id']) ? (int) $payload['pedido_id'] : null;

        if ($texto === '') {
            Helpers::jsonResponse(['ok' => false, 'message' => 'Descrição obrigatória'], 422);
        }

        Viagem::registrarDivergencia($viagemId, $pid, $texto, (int) $_SESSION['user']['id']);

        Helpers::jsonResponse(['ok' => true]);
    }

    /** Lista apenas divergências vinculadas à viagem. */
    public function apiListarDivergencias(int $viagemId): void
    {
        $this->requireLogin();
        Helpers::jsonResponse([
            'ok' => true,
            'divergencias' => Viagem::divergencias($viagemId),
        ]);
    }
}
