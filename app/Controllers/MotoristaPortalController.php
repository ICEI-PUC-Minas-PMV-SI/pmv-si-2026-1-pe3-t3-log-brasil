<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Helpers;
use App\Core\View;
use App\Models\Motorista;
use App\Models\Pedido;
use App\Models\Viagem;

/**
 * App do motorista: sessão separada.
 */
final class MotoristaPortalController extends Controller
{
    private static function sanitizeMotorista(array $m): array
    {
        unset($m['senha_hash']);

        return $m;
    }

    private static function isPgAtivo(mixed $v): bool
    {
        if ($v === true || $v === 1) {
            return true;
        }
        if ($v === 't' || $v === '1' || strtolower((string) $v) === 'true') {
            return true;
        }

        return false;
    }

    private function requireMotoristaSessao(): array
    {
        if (empty($_SESSION['motorista_app']) || empty($_SESSION['motorista_app']['id'])) {
            Helpers::redirect('/motorista/login');
            exit;
        }
        $m = Motorista::encontrar((int) $_SESSION['motorista_app']['id']);
        if ($m === null || ! self::isPgAtivo($m['ativo'] ?? null)) {
            unset($_SESSION['motorista_app']);
            $_SESSION['flash_error'] = 'Cadastro indisponível ou inativo.';
            Helpers::redirect('/motorista/login');
            exit;
        }

        return $m;
    }

    private static function atualizarSessaoMotorista(array $m): void
    {
        $_SESSION['motorista_app'] = [
            'id' => (int) $m['id'],
            'nome' => $m['nome_completo'],
            'cpf' => $m['cpf'],
            'foto_perfil' => $m['foto_perfil'] ?? null,
        ];
    }

    public function loginForm(): void
    {
        if (! empty($_SESSION['motorista_app']['id'])) {
            Helpers::redirect('/motorista');
            exit;
        }
        View::render('motorista/login', [
            'title' => 'Motorista • Entrar',
        ], 'layouts/motorista-guest');
    }

    public function loginAttempt(): void
    {
        if (! Helpers::csrfVerify($_POST['_csrf'] ?? null)) {
            $_SESSION['flash_error'] = 'Sessão expirada. Recarregue e tente de novo.';
            Helpers::redirect('/motorista/login');
            return;
        }
        $cpf = preg_replace('/\D/', '', (string) ($_POST['cpf'] ?? ''));
        $senha = (string) ($_POST['senha'] ?? '');

        if (strlen($cpf) !== 11 || strlen($senha) < 4) {
            $_SESSION['flash_error'] = 'CPF ou senha inválidos.';
            Helpers::redirect('/motorista/login');
            return;
        }

        $m = Motorista::encontrarPorCpfDigits($cpf);
        if ($m === null || empty($m['senha_hash'])) {
            $_SESSION['flash_error'] = 'Não foi possível validar o acesso.';
            Helpers::redirect('/motorista/login');
            return;
        }
        if (! self::isPgAtivo($m['ativo'] ?? null)) {
            $_SESSION['flash_error'] = 'Cadastro inativo.';
            Helpers::redirect('/motorista/login');
            return;
        }
        if (! password_verify($senha, (string) $m['senha_hash'])) {
            $_SESSION['flash_error'] = 'Não foi possível validar o acesso.';
            Helpers::redirect('/motorista/login');
            return;
        }

        session_regenerate_id(true);
        self::atualizarSessaoMotorista($m);

        Helpers::redirect('/motorista');
    }

    public function logout(): void
    {
        if (! Helpers::csrfVerify($_POST['_csrf'] ?? null)) {
            Helpers::redirect('/motorista/login');
            return;
        }
        unset($_SESSION['motorista_app']);
        Helpers::redirect('/motorista/login');
    }

    public function home(): void
    {
        $m = $this->requireMotoristaSessao();

        $abertas = Viagem::listarAbertasPorMotorista((int) $m['id']);

        View::render('motorista/home', [
            'title' => 'Minha conta',
            'navMot' => 'home',
            'motorista' => self::sanitizeMotorista($m),
            'motorista_viagens_abertas' => $abertas,
        ], 'layouts/motorista-app');
    }

    public function enviarFotoPerfil(): void
    {
        $m = $this->requireMotoristaSessao();
        $this->denyUnlessCsrf($_POST['_csrf'] ?? null);

        if (! isset($_FILES['foto']) || ! is_array($_FILES['foto'])) {
            $_SESSION['flash_error'] = 'Nenhuma imagem foi enviada.';
            Helpers::redirect('/motorista');

            return;
        }
        $up = $_FILES['foto'];
        $err = (int) ($up['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($err !== UPLOAD_ERR_OK) {
            $_SESSION['flash_error'] = match ($err) {
                UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'A foto excede o tamanho máximo permitido pelo servidor.',
                UPLOAD_ERR_PARTIAL => 'Upload interrompido. Tente novamente.',
                UPLOAD_ERR_NO_FILE => 'Escolha uma imagem antes de enviar.',
                default => 'Não foi possível receber o arquivo.',
            };
            Helpers::redirect('/motorista');

            return;
        }
        try {
            $rel = Helpers::saveUploadedSecure(
                $up,
                'motoristas',
                ['image/jpeg', 'image/png', 'image/webp'],
                4_000_000
            );
        } catch (\Throwable) {
            $_SESSION['flash_error'] = 'Não foi possível salvar a foto (tipo ou tamanho inválido).';
            Helpers::redirect('/motorista');

            return;
        }

        $old = (string) ($m['foto_perfil'] ?? '');
        if (! Motorista::atualizarFotoPerfil((int) $m['id'], $rel)) {
            $_SESSION['flash_error'] = 'Não foi possível atualizar a foto no cadastro. Tente novamente.';
            Helpers::redirect('/motorista');

            return;
        }
        if ($old !== '' && strpos($old, '..') === false) {
            $path = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $old);
            if (is_file($path)) {
                @unlink($path);
            }
        }

        $at = Motorista::encontrar((int) $m['id']);
        if ($at !== null) {
            self::atualizarSessaoMotorista($at);
        }
        $_SESSION['flash_ok'] = 'Foto atualizada.';
        Helpers::redirect('/motorista');
    }

    public function viagens(): void
    {
        $m = $this->requireMotoristaSessao();
        $lista = Viagem::listarAbertasPorMotorista((int) $m['id']);
        foreach ($lista as &$row) {
            $row['_div_pend'] = Viagem::contarDivergenciasPendentesViagem((int) $row['id']);
            $row['_pode_fin'] = Viagem::podeFinalizarOperacional((int) $row['id']);
        }
        unset($row);

        View::render('motorista/viagens', [
            'title' => 'Viagens em aberto',
            'navMot' => 'viagens',
            'motorista' => self::sanitizeMotorista($m),
            'lista' => $lista,
        ], 'layouts/motorista-app');
    }

    public function viagemDetalhe(string $viagemId): void
    {
        $m = $this->requireMotoristaSessao();
        $vid = (int) $viagemId;
        if (! Viagem::garantirMotoristaDaViagem($vid, (int) $m['id'])) {
            http_response_code(403);
            echo 'Sem acesso a esta viagem.';
            return;
        }
        $v = Viagem::encontrar($vid);
        $pedidos = Viagem::pedidosDaViagem($vid);
        $divPend = Viagem::contarDivergenciasPendentesViagem($vid);

        $divLista = array_values(array_filter(Viagem::divergencias($vid), static function (array $d): bool {
            return ($d['revisao_estado'] ?? '') === 'pendente_aprovacao';
        }));

        View::render('motorista/viagem', [
            'title' => 'Viagem #' . $vid,
            'navMot' => 'viagens',
            'leafletHead' => '<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="">' . "\n"
                . '<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>',
            'motorista' => self::sanitizeMotorista($m),
            'v' => $v,
            'pedidos' => $pedidos,
            'div_pend' => $divPend,
            'div_lista_local' => $divLista,
            'pode_finalizar' => $divPend === 0 && Viagem::podeFinalizarOperacional($vid),
        ], 'layouts/motorista-app');
    }

    public function parada(string $viagemId, string $pedidoId): void
    {
        $m = $this->requireMotoristaSessao();
        $vid = (int) $viagemId;
        $pid = (int) $pedidoId;

        if (! Viagem::garantirMotoristaDaViagem($vid, (int) $m['id'])) {
            http_response_code(403);
            echo 'Sem acesso a esta viagem.';
            return;
        }
        $todos = Viagem::pedidosDaViagem($vid);
        $atual = null;
        foreach ($todos as $p) {
            if ((int) $p['id'] === $pid) {
                $atual = $p;
                break;
            }
        }
        if ($atual === null) {
            http_response_code(404);
            echo 'Pedido não pertence à viagem.';
            return;
        }

        View::render('motorista/parada', [
            'title' => 'Parada — entrega ou ocorrência',
            'navMot' => 'viagens',
            'motorista' => self::sanitizeMotorista($m),
            'v' => Viagem::encontrar($vid),
            'pedido' => $atual,
            'itens' => Pedido::itens($pid),
        ], 'layouts/motorista-app');
    }

    public function formEntrega(string $viagemId, string $pedidoId): void
    {
        $m = $this->requireMotoristaSessao();
        $vid = (int) $viagemId;
        $pid = (int) $pedidoId;
        if (! Viagem::garantirMotoristaDaViagem($vid, (int) $m['id'])) {
            http_response_code(403);
            return;
        }
        $ped = null;
        foreach (Viagem::pedidosDaViagem($vid) as $p) {
            if ((int) $p['id'] === $pid) {
                $ped = $p;
                break;
            }
        }
        if ($ped === null || (string) ($ped['parada_estado'] ?? '') !== 'indo') {
            $_SESSION['flash_error'] = 'Retorne aos detalhes e confirme o deslocamento.';
            Helpers::redirect('/motorista/viagem/' . $vid . '/pedido/' . $pid);
            return;
        }

        View::render('motorista/entrega', [
            'title' => 'Comprovante de entrega (RF07)',
            'navMot' => 'viagens',
            'motorista' => self::sanitizeMotorista($m),
            'v' => Viagem::encontrar($vid),
            'pedido' => $ped,
            'itens' => Pedido::itens($pid),
        ], 'layouts/motorista-app');
    }

    public function apiIndo(): void
    {
        $m = $this->requireMotoristaSessao();
        $payload = json_decode((string) file_get_contents('php://input'), true) ?: [];
        $this->denyUnlessCsrf($payload['_csrf'] ?? null);

        $vid = (int) ($payload['viagem_id'] ?? 0);
        $pid = (int) ($payload['pedido_id'] ?? 0);

        if (! Viagem::garantirMotoristaDaViagem($vid, (int) $m['id'])) {
            Helpers::jsonResponse(['ok' => false, 'message' => 'Não autorizado'], 403);
        }
        $ok = Viagem::marcarParadaIndo($vid, $pid);

        Helpers::jsonResponse([
            'ok' => $ok,
            'message' => $ok ? null : 'Não foi possível alterar o status.',
        ]);
    }

    public function apiDivergencia(): void
    {
        $m = $this->requireMotoristaSessao();

        if (! empty($_POST['_csrf'])) {
            $this->denyUnlessCsrf($_POST['_csrf'] ?? null);
            $vid = (int) ($_POST['viagem_id'] ?? 0);
            $pid = (int) ($_POST['pedido_id'] ?? 0);
            $txt = trim((string) ($_POST['descricao'] ?? ''));
        } else {
            $payload = json_decode((string) file_get_contents('php://input'), true) ?: [];
            $this->denyUnlessCsrf($payload['_csrf'] ?? null);
            $vid = (int) ($payload['viagem_id'] ?? 0);
            $pid = (int) ($payload['pedido_id'] ?? 0);
            $txt = trim((string) ($payload['descricao'] ?? ''));
        }

        if ($txt === '') {
            Helpers::jsonResponse(['ok' => false, 'message' => 'Descreva a ocorrência'], 422);
        }
        if (! Viagem::garantirMotoristaDaViagem($vid, (int) $m['id'])) {
            Helpers::jsonResponse(['ok' => false, 'message' => 'Não autorizado'], 403);
        }

        $fotoRel = null;
        if (isset($_FILES['foto_ocorrencia']) && is_array($_FILES['foto_ocorrencia'])) {
            try {
                $fotoRel = Helpers::saveUploadedSecure(
                    $_FILES['foto_ocorrencia'],
                    'entregas',
                    ['image/jpeg', 'image/png', 'image/webp'],
                    8_000_000
                );
            } catch (\Throwable) {
                Helpers::jsonResponse(['ok' => false, 'message' => 'Foto da ocorrência inválida ou muito grande'], 422);
            }
        }

        $idDiv = Viagem::abrirDivergenciaParada($vid, $pid, (int) $m['id'], $txt, $fotoRel);
        Helpers::jsonResponse([
            'ok' => $idDiv !== false,
            'divergencia_id' => $idDiv ?: null,
            'message' => $idDiv === false ? 'Não foi possível registrar a ocorrência' : null,
        ]);
    }

    public function apiFinalizar(): void
    {
        $m = $this->requireMotoristaSessao();
        $payload = json_decode((string) file_get_contents('php://input'), true) ?: [];
        $this->denyUnlessCsrf($payload['_csrf'] ?? null);

        $vid = (int) ($payload['viagem_id'] ?? 0);

        if (! Viagem::garantirMotoristaDaViagem($vid, (int) $m['id'])) {
            Helpers::jsonResponse(['ok' => false, 'message' => 'Não autorizado'], 403);
        }
        if (Viagem::contarDivergenciasPendentesViagem($vid) > 0) {
            Helpers::jsonResponse(['ok' => false, 'message' => 'Há divergências aguardando análise.'], 409);
        }
        try {
            Viagem::finalizar($vid);
        } catch (\RuntimeException $e) {
            Helpers::jsonResponse(['ok' => false, 'message' => $e->getMessage()], 422);
        }
        Helpers::jsonResponse(['ok' => true]);
    }

    public function apiConcluir(): void
    {
        $m = $this->requireMotoristaSessao();

        $this->denyUnlessCsrf($_POST['_csrf'] ?? null);

        $vid = (int) ($_POST['viagem_id'] ?? 0);
        $pid = (int) ($_POST['pedido_id'] ?? 0);
        $nomeR = trim((string) ($_POST['recebedor_nome'] ?? ''));
        $assinaturaRaw = trim((string) ($_POST['assinatura_data_url'] ?? ''));

        if ($nomeR === '' || strlen($assinaturaRaw) < 40) {
            Helpers::jsonResponse(['ok' => false, 'message' => 'Preencha recebedor e assinatura.'], 422);
        }

        if (! isset($_FILES['mercadoria'])) {
            Helpers::jsonResponse(['ok' => false, 'message' => 'Foto obrigatória'], 422);
        }

        if (! Viagem::garantirMotoristaDaViagem($vid, (int) $m['id'])) {
            Helpers::jsonResponse(['ok' => false, 'message' => 'Não autorizado'], 403);
        }

        try {
            $relFoto = Helpers::saveUploadedSecure(
                $_FILES['mercadoria'],
                'entregas',
                ['image/jpeg', 'image/png', 'image/webp'],
                8_000_000
            );
        } catch (\Throwable) {
            Helpers::jsonResponse(['ok' => false, 'message' => 'Foto inválida ou muito grande'], 422);
        }

        try {
            $sigRel = $this->salvarAssinaturaDataUrl($assinaturaRaw);
        } catch (\Throwable) {
            Helpers::jsonResponse(['ok' => false, 'message' => 'Assinatura inválida'], 422);
        }

        $lat = filter_var($_POST['entrega_latitude'] ?? null, FILTER_VALIDATE_FLOAT);
        $lng = filter_var($_POST['entrega_longitude'] ?? null, FILTER_VALIDATE_FLOAT);
        if ($lat === false || $lng === false || abs($lat) > 90 || abs($lng) > 180) {
            Helpers::jsonResponse([
                'ok' => false,
                'message' => 'Localização GPS obrigatória. Ative o GPS do aparelho e tente novamente.',
            ], 422);
        }
        $prec = filter_var($_POST['entrega_geo_precisao_m'] ?? null, FILTER_VALIDATE_FLOAT);
        $precOk = ($prec !== false && $prec >= 0) ? (float) $prec : null;

        $ok = Viagem::concluirParada($vid, $pid, $nomeR, $relFoto, $sigRel, (float) $lat, (float) $lng, $precOk);

        Helpers::jsonResponse([
            'ok' => $ok,
            'message' => $ok ? null : 'Não foi possível concluir (confira status da parada).',
        ]);
    }

    /** @throws \RuntimeException */

    private function salvarAssinaturaDataUrl(string $raw): string
    {
        if (! str_starts_with($raw, 'data:image/')) {
            throw new \RuntimeException('tipo');
        }
        $semi = strpos($raw, ';base64,');
        if ($semi === false) {
            throw new \RuntimeException('b64');
        }
        $b64 = substr($raw, $semi + 8);
        $binary = base64_decode($b64, true);
        if ($binary === false || strlen($binary) < 80) {
            throw new \RuntimeException('decode');
        }
        $subdir = 'entregas';
        $pub = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $subdir;
        if (! is_dir($pub)) {
            mkdir($pub, 0775, true);
        }
        $nome = 'sig_' . bin2hex(random_bytes(10)) . '.png';
        $dest = $pub . DIRECTORY_SEPARATOR . $nome;
        if (file_put_contents($dest, $binary, LOCK_EX) === false) {
            throw new \RuntimeException('disk');
        }

        return $subdir . '/' . $nome;
    }
}
