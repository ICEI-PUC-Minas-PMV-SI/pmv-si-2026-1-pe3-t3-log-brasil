<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Helpers;
use App\Core\View;
use App\Models\Motorista;

/**
 * Gestão cadastral de motoristas próprios e terceirizados.
 */
final class MotoristasController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();

        View::render('motoristas/index', [
            'nav' => 'motoristas',
            'title' => 'Motoristas',
            'lista' => Motorista::listarTodos(),
        ]);
    }

    public function apiSalvar(?int $id = null): void
    {
        $this->requireLogin();
        $payload = json_decode((string) file_get_contents('php://input'), true) ?: [];
        $this->denyUnlessCsrf($payload['_csrf'] ?? null);

        $appSenha = trim((string) ($payload['app_senha'] ?? ''));

        $d = [
            'nome_completo' => trim((string) ($payload['nome_completo'] ?? '')),
            'cpf' => preg_replace('/\D/', '', (string) ($payload['cpf'] ?? '')),
            'cnh_numero' => (string) ($payload['cnh_numero'] ?? ''),
            'cnh_categoria' => (string) ($payload['cnh_categoria'] ?? ''),
            'telefone' => (string) ($payload['telefone'] ?? ''),
            'email' => (string) ($payload['email'] ?? ''),
            'empresa_terceira' => filter_var(
                $payload['empresa_terceira'] ?? false,
                FILTER_VALIDATE_BOOLEAN
            ),
            'nome_empresa_terceira' => (string) ($payload['nome_empresa_terceira'] ?? ''),
            'ativo' => array_key_exists('ativo', $payload)
                ? filter_var($payload['ativo'], FILTER_VALIDATE_BOOLEAN)
                : true,
        ];

        if ($appSenha !== '' && strlen($appSenha) < 8) {
            Helpers::jsonResponse(['ok' => false, 'message' => 'Senha do app: mínimo 8 caracteres (ou deixe em branco)'], 422);
        }

        if ($appSenha !== '') {
            $d['senha_hash'] = password_hash($appSenha, PASSWORD_DEFAULT);
        }

        if ($d['nome_completo'] === '') {
            Helpers::jsonResponse(['ok' => false, 'message' => 'Nome completo obrigatório'], 422);
        }

        if ($d['cpf'] === '') {
            $d['cpf'] = null;
        }

        if ($id === null) {
            $d['senha_hash'] ??= null;
            $d['foto_perfil'] ??= null;

            $novo = Motorista::criar($d);
            Helpers::jsonResponse(['ok' => true, 'id' => $novo]);
        }

        if ($appSenha === '') {
            unset($d['senha_hash']);
        }

        Helpers::jsonResponse(['ok' => Motorista::atualizar($id, $d)]);
    }

    public function apiRemover(int $id): void
    {
        $this->requireLogin();
        $payload = json_decode((string) file_get_contents('php://input'), true) ?: [];
        $this->denyUnlessCsrf($payload['_csrf'] ?? null);
        Helpers::jsonResponse(['ok' => Motorista::remover($id)]);
    }
}
