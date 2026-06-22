<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Helpers;
use App\Core\View;
use App\Models\Cliente;
use App\Models\Pedido;
use App\Models\Rota;
use App\Services\ConsultaCepService;
use App\Services\OpenRouteService;
use App\Services\RotaAutomatica;
use App\Core\Database;

/**
 * Cadastro/listagem detalhada de pedidos (itens, geocódigo, vínculo de rota pela base territorial).
 */
final class PedidoController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();
        $filtro = [
            'q' => $_GET['q'] ?? '',
            'estado' => $_GET['estado'] ?? '',
            'rota_id' => $_GET['rota_id'] ?? '',
        ];
        $ordCampo = (string) ($_GET['sort'] ?? 'numero_pedido');
        $dir = (string) ($_GET['dir'] ?? 'ASC');
        $lista = Pedido::listar($filtro, $ordCampo, $dir);
        View::render('pedidos/index', [
            'nav' => 'pedidos',
            'title' => 'Pedidos',
            'lista' => $lista,
            'rotas' => Rota::listarTodos(),
            'filtro' => $filtro,
            'ordCampo' => $ordCampo,
            'dir' => $dir,
        ]);
    }

    /** Cria pedido via JSON (+ itens) e atualiza estado/rota/coordenadas quando possível. */
    public function apiCriar(): void
    {
        $this->requireLogin();
        $payload = json_decode((string) file_get_contents('php://input'), true) ?: [];
        $this->denyUnlessCsrf($payload['_csrf'] ?? null);

        $numero = trim((string) ($payload['numero_pedido'] ?? ''));
        if ($numero === '') {
            Helpers::jsonResponse(['ok' => false, 'message' => 'Número do pedido obrigatório'], 422);
        }

        $pdo = Database::pdo();
        $class = new RotaAutomatica($pdo);
        $autoRotaId = $class->resolverPorEndereco(
            (string) ($payload['bairro'] ?? ''),
            (string) ($payload['cidade'] ?? ''),
            (string) ($payload['uf'] ?? '')
        );

        /** Rota enviada pelo formulário só é aceita se existir ativa no cadastro; senão volta à sugestão automática. */
        $rotaId = $autoRotaId;
        $rotaCliente = $payload['rota_id'] ?? null;
        if ($rotaCliente !== null && $rotaCliente !== '') {
            $rid = (int) $rotaCliente;
            $rRow = $rid > 0 ? Rota::encontrar($rid) : null;
            if ($rRow !== null && (bool) $rRow['ativo']) {
                $rotaId = $rid;
            }
        }

        $ors = new OpenRouteService();
        $partesGeo = self::payloadPartesParaGeocode($payload);
        $lat = (float) ($payload['latitude'] ?? 0);
        $lng = (float) ($payload['longitude'] ?? 0);
        if ($lat == 0.0 && $lng == 0.0) {
            $g = $ors->geocodeEnderecoBrasilCascade($partesGeo);
            if ($g !== null) {
                [$lat, $lng] = $g;
            }
        }

        $cpfCliente = Cliente::normalizarCpf((string) ($payload['cpf'] ?? ''));
        $clienteId = null;

        $estadoIni = $rotaId !== null ? 'pendente_roterizador' : 'pendente_roterizador';

        try {
            $pdo->beginTransaction();

            if ($cpfCliente !== null) {
                $clienteId = Cliente::upsertPorCpf([
                    'cpf' => $cpfCliente,
                    'nome_completo' => (string) ($payload['nome_destinatario'] ?? ''),
                    'telefone' => (string) ($payload['telefone_destinatario'] ?? ''),
                    'logradouro' => (string) ($payload['logradouro'] ?? ''),
                    'numero' => trim((string) ($payload['numero'] ?? 'S/N')) === '' ? 'S/N' : (string) ($payload['numero'] ?? 'S/N'),
                    'complemento' => (string) ($payload['complemento'] ?? ''),
                    'bairro' => (string) ($payload['bairro'] ?? ''),
                    'cidade' => (string) ($payload['cidade'] ?? ''),
                    'uf' => mb_strtoupper((string) ($payload['uf'] ?? ''), 'UTF-8'),
                    'cep' => preg_replace('/\D/', '', (string) ($payload['cep'] ?? '')),
                    'referencia_entrega' => (string) ($payload['referencia_entrega'] ?? ''),
                    'latitude' => $lat,
                    'longitude' => $lng,
                ]);
            }

            $id = Pedido::criarCabecalho([
                'numero_pedido' => $numero,
                'estado' => $estadoIni,
                'rota_id' => $rotaId,
                'cliente_id' => $clienteId,
                'nome_destinatario' => (string) ($payload['nome_destinatario'] ?? ''),
                'telefone_destinatario' => (string) ($payload['telefone_destinatario'] ?? ''),
                'logradouro' => (string) ($payload['logradouro'] ?? ''),
                'numero' => (string) ($payload['numero'] ?? 'S/N'),
                'complemento' => (string) ($payload['complemento'] ?? ''),
                'bairro' => (string) ($payload['bairro'] ?? ''),
                'cidade' => (string) ($payload['cidade'] ?? ''),
                'uf' => mb_strtoupper((string) ($payload['uf'] ?? ''), 'UTF-8'),
                'cep' => preg_replace('/\D/', '', (string) ($payload['cep'] ?? '')),
                'referencia_entrega' => (string) ($payload['referencia_entrega'] ?? ''),
                'latitude' => $lat,
                'longitude' => $lng,
                'peso_total_kg' => (float) ($payload['peso_total_kg'] ?? 0),
                'quantidade_entregas' => max(1, (int) ($payload['quantidade_entregas'] ?? 1)),
                'observacao_interna' => (string) ($payload['observacao_interna'] ?? ''),
            ]);

            foreach ((array) ($payload['itens'] ?? []) as $it) {
                if (! is_array($it)) {
                    continue;
                }
                Pedido::inserirItem(
                    $id,
                    (string) ($it['descricao'] ?? 'Item'),
                    (float) ($it['quantidade'] ?? 1),
                    isset($it['peso_unit_kg']) ? (float) $it['peso_unit_kg'] : null,
                    isset($it['sku']) ? (string) $it['sku'] : null,
                    isset($it['observacao']) ? (string) $it['observacao'] : null
                );
            }
            $pdo->commit();
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            Helpers::jsonResponse(['ok' => false, 'message' => 'Falha ao gravar: ' . $e->getMessage()], 500);
        }

        Helpers::jsonResponse(['ok' => true, 'id' => $id, 'rota_id' => $rotaId]);
    }

    public function apiItens(int $id): void
    {
        $this->requireLogin();
        Helpers::jsonResponse(['ok' => true, 'itens' => Pedido::itens($id)]);
    }

    /** CEP público BrasilAPI/ViaCEP (sem cadastro próprio nos Correios). */
    public function apiConsultaCep(): void
    {
        $this->requireLogin();
        $payload = json_decode((string) file_get_contents('php://input'), true) ?: [];
        $this->denyUnlessCsrf($payload['_csrf'] ?? null);

        $cepSrv = new ConsultaCepService();
        $dados = $cepSrv->buscar((string) ($payload['cep'] ?? ''));
        if ($dados === null) {
            Helpers::jsonResponse(['ok' => false, 'message' => 'CEP não encontrado ou inválido.'], 404);
        }

        Helpers::jsonResponse(['ok' => true, 'endereco' => $dados]);
    }

    /** Geocódigo do endereço atual com chave servidor (OpenRouteService). */
    public function apiGeocode(): void
    {
        $this->requireLogin();
        $payload = json_decode((string) file_get_contents('php://input'), true) ?: [];
        $this->denyUnlessCsrf($payload['_csrf'] ?? null);

        $partesGeo = self::payloadPartesParaGeocode($payload);
        if (! OpenRouteService::enderecoPossuiGeocodeCascadeMinimo($partesGeo)) {
            Helpers::jsonResponse(['ok' => false, 'message' => 'Informe ao menos cidade e UF para geocodificar (Brasil).'], 422);
        }

        $ors = new OpenRouteService();
        $g = $ors->geocodeEnderecoBrasilCascade($partesGeo);
        if ($g === null) {
            Helpers::jsonResponse([
                'ok' => false,
                'message' => 'Coordenadas não obtidas (verifique o endereço ou a configuração da chave de mapa na operação).',
            ], 422);
        }

        [$glat, $glng] = $g;
        $cpfNorm = Cliente::normalizarCpf((string) ($payload['cpf'] ?? ''));
        if ($cpfNorm !== null) {
            $cidGeo = Cliente::encontrarIdPorCpf($cpfNorm);
            if ($cidGeo !== null) {
                Cliente::atualizarCoordenadas($cidGeo, $glat, $glng);
            }
        }

        Helpers::jsonResponse([
            'ok' => true,
            'latitude' => $glat,
            'longitude' => $glng,
        ]);
    }

    /** Busca cadastro prévio pelo CPF (11 dígitos). */
    public function apiClientePorCpf(): void
    {
        $this->requireLogin();
        $payload = json_decode((string) file_get_contents('php://input'), true) ?: [];
        $this->denyUnlessCsrf($payload['_csrf'] ?? null);

        $cpfNorm = Cliente::normalizarCpf((string) ($payload['cpf'] ?? ''));
        if ($cpfNorm === null) {
            Helpers::jsonResponse(['ok' => true, 'cliente' => null, 'message' => null]);

            return;
        }

        Helpers::jsonResponse(['ok' => true, 'cliente' => Cliente::encontrarPorCpf($cpfNorm)]);
    }

    /** Sugere ID da rota logística segundo bairro + cidade já cadastrados na base de rotas. */
    public function apiSugerirRota(): void
    {
        $this->requireLogin();
        $payload = json_decode((string) file_get_contents('php://input'), true) ?: [];
        $this->denyUnlessCsrf($payload['_csrf'] ?? null);

        $pdo = Database::pdo();
        $rid = (new RotaAutomatica($pdo))->resolverPorEndereco(
            trim((string) ($payload['bairro'] ?? '')),
            trim((string) ($payload['cidade'] ?? '')),
            (string) ($payload['uf'] ?? '')
        );

        $nome = null;
        if ($rid !== null) {
            $r = Rota::encontrar($rid);
            $nome = $r['nome'] ?? null;
        }

        Helpers::jsonResponse([
            'ok' => true,
            'rota_id' => $rid,
            'rota_nome' => $nome,
        ]);
    }

    public function apiAtualizar(int $id): void
    {
        $this->requireLogin();
        $payload = json_decode((string) file_get_contents('php://input'), true) ?: [];
        $this->denyUnlessCsrf($payload['_csrf'] ?? null);
        $atual = Pedido::encontrar($id);
        if ($atual === null) {
            Helpers::jsonResponse(['ok' => false, 'message' => 'Pedido inexistente'], 404);
        }

        $pdo = Database::pdo();
        $class = new RotaAutomatica($pdo);
        $rotaId = $class->resolverPorEndereco(
            (string) ($payload['bairro'] ?? $atual['bairro']),
            (string) ($payload['cidade'] ?? $atual['cidade']),
            (string) ($payload['uf'] ?? $atual['uf'])
        );

        $ors = new OpenRouteService();
        $lat = isset($payload['latitude']) ? (float) $payload['latitude'] : (float) $atual['latitude'];
        $lng = isset($payload['longitude']) ? (float) $payload['longitude'] : (float) $atual['longitude'];
        if ($lat == 0.0 && $lng == 0.0) {
            $mergedGeo = [
                'logradouro' => (string) ($payload['logradouro'] ?? $atual['logradouro'] ?? ''),
                'numero' => (string) ($payload['numero'] ?? $atual['numero'] ?? ''),
                'complemento' => (string) ($payload['complemento'] ?? ($atual['complemento'] ?? '')),
                'bairro' => (string) ($payload['bairro'] ?? $atual['bairro'] ?? ''),
                'cidade' => (string) ($payload['cidade'] ?? $atual['cidade'] ?? ''),
                'uf' => (string) ($payload['uf'] ?? $atual['uf'] ?? ''),
            ];
            $g = $ors->geocodeEnderecoBrasilCascade(self::payloadPartesParaGeocode($mergedGeo));
            if ($g !== null) {
                [$lat, $lng] = $g;
            }
        }

        $cpfClienteAt = Cliente::normalizarCpf((string) ($payload['cpf'] ?? ''));
        $clienteIdAt = null;

        try {
            $pdo->beginTransaction();

            if ($cpfClienteAt !== null) {
                $clienteIdAt = Cliente::upsertPorCpf([
                    'cpf' => $cpfClienteAt,
                    'nome_completo' => (string) ($payload['nome_destinatario'] ?? $atual['nome_destinatario']),
                    'telefone' => (string) ($payload['telefone_destinatario'] ?? $atual['telefone_destinatario']),
                    'logradouro' => (string) ($payload['logradouro'] ?? $atual['logradouro']),
                    'numero' => trim((string) ($payload['numero'] ?? $atual['numero'])) === '' ? 'S/N' : (string) ($payload['numero'] ?? $atual['numero']),
                    'complemento' => (string) ($payload['complemento'] ?? ($atual['complemento'] ?? '')),
                    'bairro' => (string) ($payload['bairro'] ?? $atual['bairro']),
                    'cidade' => (string) ($payload['cidade'] ?? $atual['cidade']),
                    'uf' => mb_strtoupper((string) ($payload['uf'] ?? $atual['uf']), 'UTF-8'),
                    'cep' => preg_replace('/\D/', '', (string) ($payload['cep'] ?? $atual['cep'])),
                    'referencia_entrega' => (string) ($payload['referencia_entrega'] ?? ($atual['referencia_entrega'] ?? '')),
                    'latitude' => $lat,
                    'longitude' => $lng,
                ]);
            }

            $rotaValor = array_key_exists('rota_id', $payload)
                ? (($payload['rota_id'] === null || $payload['rota_id'] === '') ? null : (int) $payload['rota_id'])
                : $rotaId;

            $ok = Pedido::atualizarCabecalho($id, [
                'numero_pedido' => (string) ($payload['numero_pedido'] ?? $atual['numero_pedido']),
                'nome_destinatario' => (string) ($payload['nome_destinatario'] ?? $atual['nome_destinatario']),
                'telefone_destinatario' => (string) ($payload['telefone_destinatario'] ?? $atual['telefone_destinatario']),
                'logradouro' => (string) ($payload['logradouro'] ?? $atual['logradouro']),
                'numero' => (string) ($payload['numero'] ?? $atual['numero']),
                'complemento' => (string) ($payload['complemento'] ?? $atual['complemento']),
                'bairro' => (string) ($payload['bairro'] ?? $atual['bairro']),
                'cidade' => (string) ($payload['cidade'] ?? $atual['cidade']),
                'uf' => mb_strtoupper((string) ($payload['uf'] ?? $atual['uf']), 'UTF-8'),
                'cep' => preg_replace('/\D/', '', (string) ($payload['cep'] ?? $atual['cep'])),
                'referencia_entrega' => (string) ($payload['referencia_entrega'] ?? $atual['referencia_entrega']),
                'latitude' => $lat,
                'longitude' => $lng,
                'peso_total_kg' => (float) ($payload['peso_total_kg'] ?? $atual['peso_total_kg']),
                'quantidade_entregas' => max(1, (int) ($payload['quantidade_entregas'] ?? $atual['quantidade_entregas'])),
                'estado' => (string) ($payload['estado'] ?? $atual['estado']),
                'rota_id' => $rotaValor,
                'cliente_id' => $clienteIdAt,
                'observacao_interna' => (string) ($payload['observacao_interna'] ?? $atual['observacao_interna']),
            ]);

            if ($ok && isset($payload['itens']) && is_array($payload['itens'])) {
                Pedido::limparItens($id);
                foreach ($payload['itens'] as $it) {
                    if (! is_array($it)) {
                        continue;
                    }
                    Pedido::inserirItem(
                        $id,
                        (string) ($it['descricao'] ?? 'Item'),
                        (float) ($it['quantidade'] ?? 1),
                        isset($it['peso_unit_kg']) ? (float) $it['peso_unit_kg'] : null,
                        isset($it['sku']) ? (string) $it['sku'] : null,
                        isset($it['observacao']) ? (string) $it['observacao'] : null
                    );
                }
            }

            if (! $ok) {
                $pdo->rollBack();
                Helpers::jsonResponse(['ok' => false, 'message' => 'Falha ao atualizar cabeçalho'], 500);
            }

            $pdo->commit();
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            Helpers::jsonResponse(['ok' => false, 'message' => $e->getMessage()], 500);
        }

        Helpers::jsonResponse(['ok' => true]);
    }

    public function apiExcluir(int $id): void
    {
        $this->requireLogin();
        $payload = json_decode((string) file_get_contents('php://input'), true) ?: [];
        $this->denyUnlessCsrf($payload['_csrf'] ?? null);
        $pdo = Database::pdo();
        $pdo->prepare('DELETE FROM pedidos WHERE id=?')->execute([$id]);
        Helpers::jsonResponse(['ok' => true]);
    }

    
    private static function payloadPartesParaGeocode(array $src): array
    {
        return [
            'logradouro' => trim((string) ($src['logradouro'] ?? '')),
            'numero' => trim((string) ($src['numero'] ?? '')),
            'complemento' => trim((string) ($src['complemento'] ?? '')),
            'bairro' => trim((string) ($src['bairro'] ?? '')),
            'cidade' => trim((string) ($src['cidade'] ?? '')),
            'uf' => mb_strtoupper(trim((string) ($src['uf'] ?? '')), 'UTF-8'),
        ];
    }
}
