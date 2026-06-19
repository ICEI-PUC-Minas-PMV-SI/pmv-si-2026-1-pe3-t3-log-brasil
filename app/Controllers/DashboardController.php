<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Helpers;
use App\Core\View;
use App\Models\Veiculo;
use App\Models\Motorista;

/**
 * Tela inicial com métricas resumidas.
 */
final class DashboardController extends Controller
{
    public function index(): void
    {
        $u = $this->requireLogin();
        $p = (string) ($u['papel'] ?? '');
        if ($p === 'cliente') {
            Helpers::redirect(AuthController::landingPath('cliente'));
            return;
        }

        $pdo = Database::pdo();

        $pendentesRot = (int) $pdo->query(
            "SELECT COUNT(*) FROM pedidos WHERE estado IN ('pendente_roterizador','alocado_rota')"
        )->fetchColumn();

        $abertas = (int) $pdo->query("SELECT COUNT(*) FROM viagens WHERE status='aberta'")->fetchColumn();
        $finalizadas = (int) $pdo->query("SELECT COUNT(*) FROM viagens WHERE status='finalizada'")->fetchColumn();
        $veiculosOk = count(array_filter(Veiculo::listarTodos(), fn ($v) => (bool) $v['ativo']));
        $motoristasOk = count(array_filter(Motorista::listarTodos(), fn ($m) => (bool) $m['ativo']));

        $divPend = (int) $pdo->query(
            "SELECT COUNT(*) FROM divergencias_entrega WHERE revisao_estado='pendente_aprovacao'"
        )->fetchColumn();

        $emViagem = (int) $pdo->query("SELECT COUNT(*) FROM pedidos WHERE estado='em_viagem'")->fetchColumn();

        View::render('dashboard/index', [
            'nav' => 'inicio',
            'title' => 'Painel LogBrasil',
            'pendentesRot' => $pendentesRot,
            'abertas' => $abertas,
            'finalizadas' => $finalizadas,
            'veiculosOk' => $veiculosOk,
            'motoristasOk' => $motoristasOk,
            'divPend' => $divPend,
            'emViagem' => $emViagem,
            'linksCadastro' => in_array($p, ['admin', 'gestor', 'roteirizador'], true),
            'linksRevDiv' => in_array($p, ['admin', 'gestor', 'monitoramento'], true),
        ]);
    }
}
