<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Helpers;
use App\Core\View;
use App\Models\Rota;

/**
 * Cadastro mestre das rotas, cidades inclusas e bairros vinculados.
 */
final class RotasController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();
        $rotas = Rota::listarTodos();

        foreach ($rotas as &$r) {
            $r['_cidades'] = Rota::cidadesPorRota((int) $r['id']);
            $r['_bairros'] = Rota::bairrosPorRota((int) $r['id']);
        }
        unset($r);

        View::render('rotas/index', [
            'nav' => 'rotas',
            'title' => 'Cadastro de rotas',
            'rotas' => $rotas,
        ]);
    }

    /** Cria/atualiza rota principal pelo JSON. */
    public function apiSalvar(?int $id = null): void
    {
        $this->requireLogin();
        $payload = json_decode((string) file_get_contents('php://input'), true) ?: [];
        $this->denyUnlessCsrf($payload['_csrf'] ?? null);

        $nome = trim((string) ($payload['nome'] ?? ''));
        if ($nome === '') {
            Helpers::jsonResponse(['ok' => false, 'message' => 'Nome obrigatório'], 422);
        }

        $ativo = array_key_exists('ativo', $payload) ? (bool) $payload['ativo'] : true;

        if ($id === null) {
            $novoId = Rota::criar($nome, $payload['observacao'] ?? null, $ativo);
            Helpers::jsonResponse(['ok' => true, 'id' => $novoId]);
        }

        Helpers::jsonResponse([
            'ok' => Rota::atualizar($id, $nome, $payload['observacao'] ?? null, $ativo),
        ]);
    }

    public function apiRemover(int $id): void
    {
        $this->requireLogin();
        $payload = json_decode((string) file_get_contents('php://input'), true) ?: [];
        $this->denyUnlessCsrf($payload['_csrf'] ?? null);
        Helpers::jsonResponse(['ok' => Rota::remover($id)]);
    }

    public function apiAddCidade(int $id): void
    {
        $this->requireLogin();
        $payload = json_decode((string) file_get_contents('php://input'), true) ?: [];
        $this->denyUnlessCsrf($payload['_csrf'] ?? null);

        try {
            Rota::adicionarCidade($id, trim((string) ($payload['cidade'] ?? '')), (string) ($payload['uf'] ?? ''));
            Helpers::jsonResponse(['ok' => true]);
        } catch (\Throwable $e) {
            Helpers::jsonResponse(['ok' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function apiDelCidade(int $idc): void
    {
        $this->requireLogin();
        $payload = json_decode((string) file_get_contents('php://input'), true) ?: [];
        $this->denyUnlessCsrf($payload['_csrf'] ?? null);
        Helpers::jsonResponse(['ok' => Rota::removerCidade($idc)]);
    }

    public function apiAddBairro(int $id): void
    {
        $this->requireLogin();
        $payload = json_decode((string) file_get_contents('php://input'), true) ?: [];
        $this->denyUnlessCsrf($payload['_csrf'] ?? null);

        try {
            Rota::adicionarBairro(
                $id,
                trim((string) ($payload['bairro'] ?? '')),
                trim((string) ($payload['cidade'] ?? '')),
                (string) ($payload['uf'] ?? '')
            );
            Helpers::jsonResponse(['ok' => true]);
        } catch (\Throwable $e) {
            Helpers::jsonResponse(['ok' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function apiDelBairro(int $idb): void
    {
        $this->requireLogin();
        $payload = json_decode((string) file_get_contents('php://input'), true) ?: [];
        $this->denyUnlessCsrf($payload['_csrf'] ?? null);
        Helpers::jsonResponse(['ok' => Rota::removerBairro($idb)]);
    }
}
