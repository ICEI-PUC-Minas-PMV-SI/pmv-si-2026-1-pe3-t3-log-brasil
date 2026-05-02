<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Helpers;
use App\Core\View;
use App\Models\Veiculo;

/**
 * CRUD simplificado dos veículos da frota.
 */
final class VeiculosController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();

        View::render('veiculos/index', [
            'nav' => 'veiculos',
            'title' => 'Veículos',
            'lista' => Veiculo::listarTodos(),
        ]);
    }

    /** JSON create/update segundo presença de ID na URI. */
    public function apiSalvar(?int $id = null): void
    {
        $this->requireLogin();
        $payload = json_decode((string) file_get_contents('php://input'), true) ?: [];
        $this->denyUnlessCsrf($payload['_csrf'] ?? null);

        $d = [
            'placa' => mb_strtoupper(trim((string) ($payload['placa'] ?? '')), 'UTF-8'),
            'descricao' => (string) ($payload['descricao'] ?? ''),
            'marca_modelo' => (string) ($payload['marca_modelo'] ?? ''),
            'ano' => $payload['ano'] !== null && $payload['ano'] !== '' ? (int) $payload['ano'] : null,
            'capacidade_kg' => isset($payload['capacidade_kg']) ? (float) $payload['capacidade_kg'] : null,
            'tipo' => (string) ($payload['tipo'] ?? ''),
            'frota_interna' => (string) ($payload['frota_interna'] ?? ''),
            'ativo' => array_key_exists('ativo', $payload) ? (bool) $payload['ativo'] : true,
        ];

        if ($d['placa'] === '') {
            Helpers::jsonResponse(['ok' => false, 'message' => 'Placa obrigatória'], 422);
        }

        if ($id === null) {
            $novo = Veiculo::criar($d);
            Helpers::jsonResponse(['ok' => true, 'id' => $novo]);
        }

        Helpers::jsonResponse(['ok' => Veiculo::atualizar($id, $d)]);
    }

    public function apiRemover(int $id): void
    {
        $this->requireLogin();
        $payload = json_decode((string) file_get_contents('php://input'), true) ?: [];
        $this->denyUnlessCsrf($payload['_csrf'] ?? null);
        Helpers::jsonResponse(['ok' => Veiculo::remover($id)]);
    }
}
