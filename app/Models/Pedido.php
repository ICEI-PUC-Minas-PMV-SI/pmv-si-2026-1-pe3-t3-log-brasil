<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

/**
 * Pedidos e itens relacionados ao roteirização planejamento.
 */
final class Pedido
{
    /** Pedidos disponíveis no roteirizador: pendentes, sem vínculo com viagem ativa. */
    public static function pendentesPorRota(int $rotaId): array
    {
        $sql = <<<SQL
            SELECT p.* FROM pedidos p
            WHERE p.rota_id = ? AND p.estado IN ('pendente_roterizador','alocado_rota')
              AND NOT EXISTS (
                SELECT 1 FROM viagem_pedidos vp
                  JOIN viagens v ON v.id = vp.viagem_id
                 WHERE vp.pedido_id = p.id AND v.status = 'aberta'
              )
            ORDER BY p.criado_em ASC
SQL;
        $stmt = Database::pdo()->prepare($sql);
        $stmt->execute([$rotaId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function encontrar(int $id): ?array
    {
        $st = Database::pdo()->prepare('SELECT * FROM pedidos WHERE id=? LIMIT 1');
        $st->execute([$id]);
        $r = $st->fetch(PDO::FETCH_ASSOC);
        return $r ?: null;
    }

    public static function criarCabecalho(array $d): int
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'INSERT INTO pedidos (
               numero_pedido, estado, rota_id, cliente_id, nome_destinatario, telefone_destinatario,
               logradouro, numero, complemento, bairro, cidade, uf, cep, referencia_entrega,
               latitude, longitude, peso_total_kg, quantidade_entregas, observacao_interna
             ) VALUES (
              ?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?
             ) RETURNING id'
        );
        $stmt->execute([
            $d['numero_pedido'],
            $d['estado'],
            $d['rota_id'],
            $d['cliente_id'],
            $d['nome_destinatario'],
            $d['telefone_destinatario'],
            $d['logradouro'],
            $d['numero'],
            $d['complemento'],
            $d['bairro'],
            $d['cidade'],
            $d['uf'],
            $d['cep'],
            $d['referencia_entrega'],
            $d['latitude'],
            $d['longitude'],
            $d['peso_total_kg'],
            $d['quantidade_entregas'],
            $d['observacao_interna'],
        ]);
        return (int) $stmt->fetchColumn();
    }

    public static function inserirItem(int $pedidoId, string $desc, float $qtd, ?float $peso, ?string $sku, ?string $obs): void
    {
        $stmt = Database::pdo()->prepare(
            'INSERT INTO pedido_itens (pedido_id, descricao, quantidade, peso_unit_kg, sku, observacao)
             VALUES (?,?,?,?,?,?)'
        );
        $stmt->execute([
            $pedidoId,
            $desc,
            $qtd,
            $peso,
            $sku,
            $obs,
        ]);
    }

    /** Atualização completa opcional pelo painel. */
    public static function atualizarEstado(int $id, string $estado): void
    {
        $stmt = Database::pdo()->prepare('UPDATE pedidos SET estado=? WHERE id=?');
        $stmt->execute([$estado, $id]);
    }

    public static function atualizarRota(int $pedidoId, ?int $rotaId): void
    {
        $stmt = Database::pdo()->prepare('UPDATE pedidos SET rota_id=?, atualizado_em=NOW() WHERE id=?');
        $stmt->execute([$rotaId, $pedidoId]);
    }

    public static function atualizarCabecalho(int $id, array $d): bool
    {
        $stmt = Database::pdo()->prepare(
            'UPDATE pedidos SET
               numero_pedido=?, nome_destinatario=?, telefone_destinatario=?,
               logradouro=?, numero=?, complemento=?, bairro=?, cidade=?, uf=?, cep=?,
               referencia_entrega=?, latitude=?, longitude=?, peso_total_kg=?, quantidade_entregas=?,
               estado=?, rota_id=?, cliente_id=?, observacao_interna=?, atualizado_em=NOW()
             WHERE id=?'
        );
        return $stmt->execute([
            $d['numero_pedido'],
            $d['nome_destinatario'],
            $d['telefone_destinatario'],
            $d['logradouro'],
            $d['numero'],
            $d['complemento'],
            $d['bairro'],
            $d['cidade'],
            $d['uf'],
            $d['cep'],
            $d['referencia_entrega'],
            $d['latitude'],
            $d['longitude'],
            $d['peso_total_kg'],
            $d['quantidade_entregas'],
            $d['estado'],
            $d['rota_id'],
            $d['cliente_id'],
            $d['observacao_interna'],
            $id,
        ]);
    }

    /** Lista genérica paginável com filtros simples para telas cadastro. */
    public static function listar(array $filtro, string $ordemCampo = 'numero_pedido', string $dire = 'ASC', int $lim = 200): array
    {
        $ordemCampoMap = [
            'numero_pedido' => 'p.numero_pedido',
            'criado_em' => 'p.criado_em',
            'nome_destinatario' => 'p.nome_destinatario',
            'peso_total_kg' => 'p.peso_total_kg',
            'cidade' => 'p.cidade',
        ];
        $colOrd = $ordemCampoMap[$ordemCampo] ?? 'p.numero_pedido';
        $dir = strtoupper($dire) === 'DESC' ? 'DESC' : 'ASC';

        $where = ['1=1'];
        $pars = [];

        if (! empty($filtro['q'])) {
            $where[] = '(p.numero_pedido ILIKE ? OR p.nome_destinatario ILIKE ? OR p.cidade ILIKE ? OR cl.cpf ILIKE ?)';
            $like = '%' . $filtro['q'] . '%';
            $pars[] = $like;
            $pars[] = $like;
            $pars[] = $like;
            $digits = preg_replace('/\D/', '', (string) $filtro['q']);
            $pars[] = '%' . ($digits !== '' ? $digits : $filtro['q']) . '%';
        }
        if (! empty($filtro['estado'])) {
            $where[] = 'p.estado=?';
            $pars[] = $filtro['estado'];
        }
        if (! empty($filtro['rota_id'])) {
            $where[] = 'p.rota_id=?';
            $pars[] = $filtro['rota_id'];
        }

        $sql = 'SELECT p.*, r.nome AS rota_nome, cl.cpf AS cliente_cpf '
            . 'FROM pedidos p '
            . 'LEFT JOIN rotas r ON r.id=p.rota_id '
            . 'LEFT JOIN clientes cl ON cl.id=p.cliente_id '
            . 'WHERE '
            . implode(' AND ', $where)
            . " ORDER BY {$colOrd} {$dir} LIMIT {$lim}";
        $st = Database::pdo()->prepare($sql);
        $st->execute($pars);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Itens de um pedido. */
    public static function itens(int $pedidoId): array
    {
        $st = Database::pdo()->prepare('SELECT * FROM pedido_itens WHERE pedido_id=? ORDER BY id');
        $st->execute([$pedidoId]);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function atualizarCoordenadas(int $pedidoId, float $lat, float $lng): void
    {
        $st = Database::pdo()->prepare('UPDATE pedidos SET latitude=?, longitude=?, atualizado_em=NOW() WHERE id=?');
        $st->execute([$lat, $lng, $pedidoId]);
    }

    public static function excluirItem(int $itemId): void
    {
        Database::pdo()->prepare('DELETE FROM pedido_itens WHERE id=?')->execute([$itemId]);
    }

    /** Remove todos os itens do pedido (antes de registrar nova estrutura de carga no UPDATE). */
    public static function limparItens(int $pedidoId): void
    {
        Database::pdo()->prepare('DELETE FROM pedido_itens WHERE pedido_id=?')->execute([$pedidoId]);
    }

    /**
     * Acompanhamento por CPF do cliente cadastrado (apenas dígitos na consulta).
     *
     * @return array{
     *   encontrado_cliente: bool,
     *   cliente: array<string, scalar|null>|null,
     *   pendentes: list<array<string, mixed>>,
     *   realizadas: list<array<string, mixed>>,
     * }
     */
    public static function rastrearPorCpfCliente(string $cpf): array
    {
        $dig = preg_replace('/\D/', '', $cpf);
        $out = [
            'encontrado_cliente' => false,
            'cliente' => null,
            'pendentes' => [],
            'realizadas' => [],
        ];

        if ($dig === '') {
            return $out;
        }

        $sql = <<<'SQL'
            SELECT p.*,
                   c.id AS cliente_ref_id,
                   c.cpf AS cliente_cpf_tbl,
                   c.nome_completo AS cliente_nome_tbl,
                   aberta.estado_parada AS viagem_parada_estado,
                   aberta.viagem_id AS viagem_aberta_id
            FROM pedidos p
            JOIN clientes c ON c.id = p.cliente_id
            LEFT JOIN LATERAL (
                SELECT vp.estado_parada, vp.viagem_id
                FROM viagem_pedidos vp
                INNER JOIN viagens v ON v.id = vp.viagem_id AND v.status = 'aberta'
                WHERE vp.pedido_id = p.id
                ORDER BY v.id DESC
                LIMIT 1
            ) aberta ON TRUE
            WHERE regexp_replace(COALESCE(c.cpf, ''), '[^0-9]', '', 'g') = ?
            ORDER BY p.criado_em DESC
SQL;
        $st = Database::pdo()->prepare($sql);
        $st->execute([$dig]);
        $rows = $st->fetchAll(PDO::FETCH_ASSOC);

        if ($rows === []) {
            return $out;
        }

        $out['encontrado_cliente'] = true;
        $first = $rows[0];
        $out['cliente'] = [
            'id' => (int) $first['cliente_ref_id'],
            'cpf' => (string) $first['cliente_cpf_tbl'],
            'nome_completo' => (string) $first['cliente_nome_tbl'],
        ];

        foreach ($rows as $row) {
            $chip = match ((string) $row['estado']) {
                'entregue' => ['Entrega realizada', 'ok'],
                'cancelado' => ['Cancelado', 'muted'],
                'em_viagem' => self::chipParaParadaCliente((string) ($row['viagem_parada_estado'] ?? 'pendente')),
                'pendente_roterizador', 'alocado_rota' => ['Aguardando rota / planejamento', 'wait'],
                default => ['Em tratamento na base', 'wait'],
            };

            $enriched = $row;
            unset($enriched['cliente_ref_id'], $enriched['cliente_cpf_tbl'], $enriched['cliente_nome_tbl']);
            $enriched['acompanhar_label'] = $chip[0];
            $enriched['acompanhar_tone'] = $chip[1];

            if ((string) $row['estado'] === 'entregue') {
                $out['realizadas'][] = $enriched;
            } else {
                $out['pendentes'][] = $enriched;
            }
        }

        return $out;
    }

    /**
     * @return array{0:string,1:string}
     */

    private static function chipParaParadaCliente(string $paradaEstado): array
    {
        return match ($paradaEstado) {
            'pendente' => ['Na sua rota — aguardando chegada', 'progress'],
            'indo' => ['Motorista a caminho do seu endereço', 'moving'],
            'entrega_feita' => ['Entrega registrada (viagem sendo encerrada)', 'ok'],
            'divergencia_aguardando' => ['Pendência em análise', 'risk'],
            'resolvido_divergencia' => ['Incidente tratado pela operação', 'wait'],
            default => ['Em transporte', 'progress'],
        };
    }
}
