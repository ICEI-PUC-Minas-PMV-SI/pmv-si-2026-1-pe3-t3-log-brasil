<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Helpers;
use App\Core\View;
use App\Models\Pedido;
use App\Models\Rota;
use App\Models\UnidadePadrao;
use App\Models\Viagem;
use App\Models\Veiculo;
use App\Models\Motorista;
use App\Services\OpenRouteService;
use App\Services\SequenciadorGeo;

/**
 * Agrupamentos por rota, distâncias via ORS e geração de viagens físicas planejadas.
 */
final class RoteirizadorController extends Controller
{
    /** Página com cards agrupados. */
    public function index(): void
    {
        $this->requireLogin();
        UnidadePadrao::ensureBootstrap();
        View::render('roteirizador/index', [
            'nav' => 'roteirizador',
            'title' => 'Roteirizador',
            'veiculos' => Veiculo::listarTodos(),
            'motoristas' => Motorista::listarTodos(),
            'rotasOpcoes' => Rota::listarTodos(),
        ]);
    }

    /** Resumo consolidado das rotas pendentes. */
    public function apiResumo(): void
    {
        $this->requireLogin();

        UnidadePadrao::ensureBootstrap();
        $u = UnidadePadrao::get();
        if ($u === null) {
            Helpers::jsonResponse(['ok' => false, 'message' => 'Unidade padrão ausente'], 500);
        }

        $pdo = Database::pdo();
        $rotas = Rota::listarTodos();
        $ors = new OpenRouteService();

        $cards = [];

        foreach ($rotas as $r) {
            $rid = (int) $r['id'];
            $pend = Pedido::pendentesPorRota($rid);
            if (count($pend) === 0) {
                continue;
            }

            $peso = 0.0;
            $qPed = count($pend);
            $qtEntReg = 0;
            foreach ($pend as $p) {
                $peso += (float) $p['peso_total_kg'];
                $qtEntReg += (int) $p['quantidade_entregas'];
            }

            $seqIds = SequenciadorGeo::sequenciaNearestNeighbor(
                (float) $u['latitude'],
                (float) $u['longitude'],
                $pend
            );

            /** @var list<array{0:float,1:float}> $coordsOrd */
            $coordsOrd = [];
            $coordsOrd[] = [(float) $u['latitude'], (float) $u['longitude']];
            $byId = [];
            foreach ($pend as $p) {
                $byId[(int) $p['id']] = $p;
            }
            foreach ($seqIds as $sid) {
                if (! isset($byId[$sid])) {
                    continue;
                }
                $px = $byId[$sid];
                $coordsOrd[] = [(float) $px['latitude'], (float) $px['longitude']];
            }

            $distancia = null;
            if (count($coordsOrd) > 2) {
                $distancia = $ors->directionsDistanceMeters($coordsOrd, true);
            } elseif (count($coordsOrd) === 2) {
                // Apenas uma parada na rota efetiva: ida ao ponto + retorno depot
                $distancia = $ors->directionsDistanceMeters($coordsOrd, true);
            }

            $cards[] = [
                'rota_id' => $rid,
                'rota_nome' => $r['nome'],
                'peso_total' => round($peso, 3),
                'quantidade_pedidos' => $qPed,
                'quantidade_entregas' => $qtEntReg,
                'distancia_metros_prev' => $distancia !== null ? (int) round($distancia) : null,
                'sequencia_sugerida' => array_map(static function (int $id) use ($byId): array {
                    $p = $byId[$id];

                    return [
                        'id' => $id,
                        'numero_pedido' => $p['numero_pedido'],
                        'destinatario' => $p['nome_destinatario'],
                        'bairro' => $p['bairro'] ?? '',
                        'cidade' => $p['cidade'] ?? '',
                        'uf' => $p['uf'] ?? '',
                    ];
                }, $seqIds),
            ];
        }

        Helpers::jsonResponse([
            'ok' => true,
            'cards' => $cards,
            'unidade' => [
                'lat' => (float) $u['latitude'],
                'lng' => (float) $u['longitude'],
                'nome' => $u['nome'],
                'label' => trim($u['logradouro'] . ', ' . $u['numero'] . ', ' . $u['cidade'] . '/' . $u['uf']),
            ],
        ]);
    }

    /** Detalhamento textual para modal. */
    public function apiDetalhe(int $rotaId): void
    {
        $this->requireLogin();
        $pend = Pedido::pendentesPorRota($rotaId);

        Helpers::jsonResponse([
            'ok' => true,
            'pedidos' => $pend,
        ]);
    }

    /** Permite mover pedidos livres entre rotas registradas antes da viagem. */
    public function apiAlterarRota(): void
    {
        $this->requireLogin();

        $payload = json_decode((string) file_get_contents('php://input'), true) ?: [];
        $this->denyUnlessCsrf($payload['_csrf'] ?? null);

        /** @var int[] $ids */
        $ids = array_map('intval', (array) ($payload['pedido_ids'] ?? []));
        $novaRota = (int) ($payload['nova_rota_id'] ?? 0);

        if ($ids === [] || $novaRota <= 0) {
            Helpers::jsonResponse(['ok' => false, 'message' => 'Seleção incompleta'], 422);
        }

        foreach ($ids as $pid) {
            $p = Pedido::encontrar($pid);
            if ($p === null || ! in_array($p['estado'], ['pendente_roterizador', 'alocado_rota'], true)) {
                continue;
            }

            Pedido::atualizarRota($pid, $novaRota);
            Pedido::atualizarEstado($pid, $p['estado']); // apenas refresh timestamp
        }

        Helpers::jsonResponse(['ok' => true]);
    }

    /** Gera uma viagem e remove demanda do roteirizador. */
    public function apiGerarViagem(): void
    {
        $this->requireLogin();

        UnidadePadrao::ensureBootstrap();
        $u = UnidadePadrao::get();

        $payload = json_decode((string) file_get_contents('php://input'), true) ?: [];
        $this->denyUnlessCsrf($payload['_csrf'] ?? null);

        $rotaId = (int) ($payload['rota_id'] ?? 0);
        /** @var int[] $pedidoIds */
        $pedidoIds = array_map('intval', (array) ($payload['pedido_ids'] ?? []));
        $veId = isset($payload['veiculo_id']) ? (int) $payload['veiculo_id'] : null;
        $motId = isset($payload['motorista_id']) ? (int) $payload['motorista_id'] : null;

        $dataPrev = isset($payload['data_largada_prevista']) ? (string) $payload['data_largada_prevista'] : null;
        $lead = (string) ($payload['lead_planejado_texto'] ?? '');
        $obs = (string) ($payload['observacao_planejamento'] ?? '');

        if ($rotaId <= 0 || $pedidoIds === []) {
            Helpers::jsonResponse(['ok' => false, 'message' => 'Rota ou pedidos inválidos'], 422);
        }

        $pdo = Database::pdo();
        $lista = [];

        foreach ($pedidoIds as $pid) {
            $pd = Pedido::encontrar($pid);
            if (
                $pd === null || (int) $pd['rota_id'] !== $rotaId
                || ! in_array($pd['estado'], ['pendente_roterizador', 'alocado_rota'], true)
            ) {
                Helpers::jsonResponse(['ok' => false, 'message' => 'Pedido inconsistente ou fora da rota'], 409);
            }
            // bloqueado se já em viagem aberta — query model já filtra pendente_rot
            $lista[] = $pd;
        }

        $ors = new OpenRouteService();

        $seqIds = SequenciadorGeo::sequenciaNearestNeighbor(
            $u !== null ? (float) $u['latitude'] : 0.0,
            $u !== null ? (float) $u['longitude'] : 0.0,
            $lista
        );

        /** @var list<array{0:float,1:float}> $coordsOrd */
        $coordsOrd = [];
        if ($u !== null) {
            $coordsOrd[] = [(float) $u['latitude'], (float) $u['longitude']];
        }
        $byId = [];
        foreach ($lista as $p) {
            $byId[(int) $p['id']] = $p;
        }
        foreach ($seqIds as $sid) {
            if (! isset($byId[$sid])) {
                continue;
            }
            $px = $byId[$sid];
            $coordsOrd[] = [(float) $px['latitude'], (float) $px['longitude']];
        }

        $distancia = count($coordsOrd) > 1 ? $ors->directionsDistanceMeters($coordsOrd, true) : null;

        $pesoTot = array_sum(array_map(static fn ($p) => (float) $p['peso_total_kg'], $lista));
        $qtEntrg = array_sum(array_map(static fn ($p) => (int) $p['quantidade_entregas'], $lista));

        try {
            $pdo->beginTransaction();

            $jsonOrd = json_encode($seqIds, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

            $tripId = Viagem::criarCabecalho([
                'rota_id' => $rotaId,
                'veiculo_id' => $veId,
                'motorista_id' => $motId,
                'status' => 'aberta',
                'data_largada_prevista' => $dataPrev !== '' && $dataPrev !== null ? $dataPrev : null,
                'lead_planejado_texto' => $lead,
                'observacao_planejamento' => $obs,
                'peso_total_kg' => $pesoTot,
                'qt_entregas' => $qtEntrg,
                'distancia_metros_prev' => $distancia,
                'distancia_via_ors_metros' => $distancia,
                'ordem_geo_json' => $jsonOrd,
            ]);

            Viagem::anexarPedidos($tripId, $seqIds);
            Viagem::marcarPedidosEmViagem($seqIds);

            $pdo->commit();

            Helpers::jsonResponse([
                'ok' => true,
                'viagem_id' => $tripId,
            ]);
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            Helpers::jsonResponse(['ok' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
