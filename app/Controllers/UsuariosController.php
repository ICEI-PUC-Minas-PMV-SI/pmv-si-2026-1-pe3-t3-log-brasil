<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Helpers;
use App\Core\View;
use App\Models\Usuario;

final class UsuariosController extends Controller
{
    public function index(): void
    {
        $this->requirePapel(['admin']);

        View::render('usuarios/index', [
            'nav' => 'usuarios',
            'title' => 'Usuários',
            'lista' => Usuario::listarTodos(),
            'papeis' => Usuario::papeisLista(),
        ]);
    }

    public function apiCriar(): void
    {
        $this->requirePapel(['admin']);

        $payload = json_decode((string) file_get_contents('php://input'), true) ?: [];
        $this->denyUnlessCsrf($payload['_csrf'] ?? null);

        $email = mb_strtolower(trim((string) ($payload['email'] ?? '')), 'UTF-8');
        $nome = trim((string) ($payload['nome_completo'] ?? ''));
        $papel = (string) ($payload['papel'] ?? '');
        $senha = (string) ($payload['senha'] ?? '');
        $ativo = array_key_exists('ativo', $payload)
            ? filter_var($payload['ativo'], FILTER_VALIDATE_BOOLEAN)
            : true;
        $clienteCpfDigits = preg_replace('/\D/', '', (string) ($payload['cliente_cpf'] ?? ''));

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Helpers::jsonResponse(['ok' => false, 'message' => 'E-mail inválido'], 422);
        }
        if ($nome === '') {
            Helpers::jsonResponse(['ok' => false, 'message' => 'Nome completo obrigatório'], 422);
        }
        if (! in_array($papel, Usuario::papeisLista(), true)) {
            Helpers::jsonResponse(['ok' => false, 'message' => 'Papel inválido'], 422);
        }
        if (strlen($senha) < 8) {
            Helpers::jsonResponse(['ok' => false, 'message' => 'Senha com pelo menos 8 caracteres'], 422);
        }
        if ($papel === 'cliente' && strlen($clienteCpfDigits) !== 11) {
            Helpers::jsonResponse(['ok' => false, 'message' => 'Cliente: informe CPF com 11 dígitos'], 422);
        }

        if (Usuario::emailExisteOuOutroId($email, null)) {
            Helpers::jsonResponse(['ok' => false, 'message' => 'E-mail já cadastrado'], 409);
        }

        $id = Usuario::criar([
            'email' => $email,
            'nome_completo' => $nome,
            'papel' => $papel,
            'ativo' => $ativo,
            'senha_hash' => password_hash($senha, PASSWORD_DEFAULT),
            'acompanhar_cpf' => $papel === 'cliente' ? $clienteCpfDigits : '',
        ]);

        Helpers::jsonResponse(['ok' => true, 'id' => $id]);
    }
}
