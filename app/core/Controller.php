<?php

namespace App\Core;

abstract class Controller
{
    protected function requireLogin(): array
    {
        if (empty($_SESSION['user'])) {
            Helpers::redirect('/login');
            exit;
        }
        /** @var array $u */
        $u = $_SESSION['user'];
        return $u;
    }

    /**
     * @param list<string> $papeis
     * @return array{id:int,nome:string,email:string,papel:string}
     */
    protected function requirePapel(array $papeis): array
    {
        $u = $this->requireLogin();
        if (! in_array($u['papel'] ?? '', $papeis, true)) {
            Helpers::redirect('/inicio');
            exit;
        }
        return $u;
    }

    protected function denyUnlessCsrf(?string $token): void
    {
        if (! Helpers::csrfVerify($token ?? null)) {
            http_response_code(419);
            Helpers::jsonResponse(['ok' => false, 'message' => 'Sessão/CSRF inválido']);
            exit;
        }
    }
}
