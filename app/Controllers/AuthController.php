<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\DatabaseConnectionException;
use App\Core\Helpers;
use App\Core\View;
use App\Models\Usuario;

/**
 * Fluxo login/logout e proteção inicial de sessão.
 * Mensagens de credencial incorreta/inexistente ficam deliberadamente neutras para não revelar cadastros.
 */
final class AuthController extends Controller
{
    public static function landingPath(string $papel): string
    {
        return match ($papel) {
            'cliente' => '/acompanhar',
            default => '/inicio',
        };
    }

    /** Texto único quando e-mail não existe, senha errada ou usuário inativo. */
    private const MSG_CREDENCIAL_NEGADA = 'Não foi possível validar o acesso neste momento. Tente novamente em instantes.';

    public function loginForm(): void
    {
        if (! empty($_SESSION['user'])) {
            Helpers::redirect(AuthController::landingPath((string) ($_SESSION['user']['papel'] ?? '')));
            return;
        }
        View::render('auth/login', [
            'title' => 'LogBrasil — Entrar',
        ], 'layouts/guest');
    }

    public function loginAttempt(): void
    {
        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $senha = isset($_POST['senha']) ? (string) $_POST['senha'] : '';

        $debugOn = self::debugAtivo();

        $tokenOk = Helpers::csrfVerify($_POST['_csrf'] ?? null);
        if (! $tokenOk) {
            $_SESSION['flash_error'] = 'Envio não pôde ser validado por segurança (sessão ou token CSRF expirados). '
                . 'Recarregue a página de login e preencha o formulário novamente.';
            if ($debugOn) {
                $_SESSION['flash_error_admin'] = 'csrf_verify falhou ou ausente campo _csrf no POST.';
            }
            Helpers::redirect('/login');
            return;
        }

        if (! $email) {
            $_SESSION['flash_error'] = 'O campo e-mail deve estar em um formato válido (exemplo@empresa.com).';
            if ($debugOn) {
                $_SESSION['flash_error_admin'] = 'filter_input FILTER_VALIDATE_EMAIL retornou falso.';
            }
            Helpers::redirect('/login');
            return;
        }

        try {
            $u = Usuario::findByEmail($email);
        } catch (DatabaseConnectionException $e) {
            $_SESSION['flash_error'] = 'O servidor não conseguiu acessar a base de dados no momento '
                . 'da validação da conta. Confira sua conexão, aguarde e tente de novo.';
            if ($debugOn) {
                $_SESSION['flash_error_admin'] = self::mensagemDiagDb($e);
            }
            Helpers::redirect('/login');
            return;
        }

        /** Mesma mensagem neutra para: usuário inexistente, senha incorreta ou conta desativada. */
        if ($u === null || ! (bool) $u['ativo'] || ! password_verify($senha, $u['senha_hash'])) {
            $_SESSION['flash_error'] = self::MSG_CREDENCIAL_NEGADA;
            if ($debugOn) {
                error_log('[LogBrasil login diagnóstico credencial] ' . self::diagCredencial($u, $senha));
            }
            Helpers::redirect('/login');
            return;
        }

        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id' => (int) $u['id'],
            'nome' => $u['nome_completo'],
            'email' => $u['email'],
            'papel' => $u['papel'],
            'acompanhar_cpf' => (string) ($u['acompanhar_cpf'] ?? ''),
        ];
        Helpers::redirect(AuthController::landingPath((string) $u['papel']));
    }

    private static function debugAtivo(): bool
    {
        return in_array((string) ($_ENV['APP_DEBUG'] ?? ''), ['1', 'true', 'yes'], true);
    }

    /** Texto só para modo depuração: não repetir mesmo texto que o usuário final vê. */
    private static function diagCredencial(?array $u, string $senha): string
    {
        if ($u === null) {
            return '[APP_DEBUG] Nenhuma linha em usuarios com este e-mail.';
        }
        if (! (bool) $u['ativo']) {
            return '[APP_DEBUG] Usuário encontrado mas coluna ativo = false.';
        }
        $ok = password_verify($senha, (string) $u['senha_hash']);

        return $ok ? '[APP_DEBUG] password_verify inexplicavelmente verdadeira (fluxo já teria aceitado).' : '[APP_DEBUG] password_verify falhou (senha diferente ou hash inconsistente).';
    }

    private static function mensagemDiagDb(DatabaseConnectionException $e): string
    {
        $pdo = $e->getPrevious();
        $pdoMsg = $pdo instanceof \PDOException ? $pdo->getMessage() : '';

        $lowPdo = strtolower($pdoMsg);
        $dnsHint = '';
        if (($e->getMessage() === 'CONNECT' || str_contains(strtolower((string) $e->getMessage()), 'placeholder'))
            && (str_contains($lowPdo, 'could not translate host name')
                || str_contains($lowPdo, 'unknown host'))) {
            $dnsHint = "\n\nCausa comum no LogBrasil: DB_HOST no .env ainda está com texto de exemplo (placeholder). ";
            $dnsHint .= 'Substitua por um hostname real tipo db.abcdefghij.supabase.co (painel Supabase → Configurações do projeto '
                . '→ Database → Host / Connection parameters).';
        }

        $blocoTipo = match ($e->getMessage()) {
            'DRIVER' => "Tipo detectado: PDO sem driver PostgreSQL.\n"
                . 'Solução: em php.ini ative extension=pdo_pgsql (e opcionalmente extension=pgsql); reinicie o Apache.',
            'PLACEHOLDER_HOST' => "DB_HOST não é um servidor real: ainda contém texto de exemplo (substitua SEU_PROJECT_REF ou trechos tipo COLE_AQUI).\n"
                . "Onde pegar o valor correto: Supabase → Configurações do projeto (engrenagem) → Database → Host "
                . "(ex.: db.abcdefghij.supabase.co, com as letras do seu projeto).\n",
            'CONNECT' => "Tipo detectado: falha ao negociar conexão (host/porta/credencial/rede/ssl).\n"
                . 'Confira DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD no .env e uso de pool vs conexão direta.',
            default => 'Tipo: ' . $e->getMessage(),
        };

        return trim($blocoTipo . $dnsHint . ($pdoMsg !== '' ? "\n\nMensagem PDO:\n" . $pdoMsg : ''));
    }

    public function logout(): void
    {
        if (! Helpers::csrfVerify($_POST['_csrf'] ?? null)) {
            Helpers::redirect('/login');
            return;
        }
        $_SESSION = [];
        session_destroy();
        Helpers::redirect('/login');
    }
}
