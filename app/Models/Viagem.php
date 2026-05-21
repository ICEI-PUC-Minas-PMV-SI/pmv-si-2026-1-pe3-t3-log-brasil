<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

/**
 * Viagens e vínculos com pedidos.
 */
final class Viagem
{
    public static function criarCabecalho(array $d): int
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'INSERT INTO viagens (
               rota_id, veiculo_id, motorista_id, status, data_largada_prevista,
               lead_planejado_texto, observacao_planejamento, peso_total_kg, qt_entregas,
               distancia_metros_prev, distancia_via_ors_metros, ordem_geo_json
             ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?) RETURNING id'
        );
        $stmt->execute([
            $d['rota_id'],
            $d['veiculo_id'],
            $d['motorista_id'],
            $d['status'],
            $d['data_largada_prevista'],
            $d['lead_planejado_texto'],
            $d['observacao_planejamento'],
            $d['peso_total_kg'],
            $d['qt_entregas'],
            $d['distancia_metros_prev'],
            $d['distancia_via_ors_metros'],
            $d['ordem_geo_json'],
        ]);
        return (int) $stmt->fetchColumn();
    }

    public static function anexarPedidos(int $viagemId, array $pedidoIdsOrdenados): void
    {
        $pdo = Database::pdo();
        $ord = 1;
        $st = $pdo->prepare('INSERT INTO viagem_pedidos (viagem_id, pedido_id, ordem_entrega) VALUES (?,?,?)');
        foreach ($pedidoIdsOrdenados as $pid) {
            $st->execute([$viagemId, (int) $pid, $ord++]);
        }
    }

    public static function marcarPedidosEmViagem(array $pedidoIds): void
    {
        if ($pedidoIds === []) {
            return;
        }
        $pdo = Database::pdo();
        $place = implode(',', array_fill(0, count($pedidoIds), '?'));
        $sql = "UPDATE pedidos SET estado='em_viagem', atualizado_em=NOW() WHERE id IN ($place)";
        $st = $pdo->prepare($sql);
        $st->execute($pedidoIds);
    }

    public static function listarPorStatus(string $status): array
    {
        $sql = <<<SQL
            SELECT v.*, r.nome AS rota_nome, ve.placa, m.nome_completo AS motorista_nome
            FROM viagens v
            JOIN rotas r ON r.id = v.rota_id
            LEFT JOIN veiculos ve ON ve.id = v.veiculo_id
            LEFT JOIN motoristas m ON m.id = v.motorista_id
            WHERE v.status = ?
            ORDER BY v.id DESC
SQL;
        $st = Database::pdo()->prepare($sql);
        $st->execute([$status]);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Viagens abertas com contagens por estado de parada (painel operacional / monitoramento).
     *
     * @return list<array<string,mixed>>
     */
    public static function listarAbertasParaPainel(): array
    {
        $sql = <<<SQL
            SELECT v.*, r.nome AS rota_nome, ve.placa, m.nome_completo AS motorista_nome,
              (SELECT COUNT(*)::int FROM viagem_pedidos vp WHERE vp.viagem_id = v.id) AS _vp_total,
              (SELECT COUNT(*)::int FROM viagem_pedidos vp
                WHERE vp.viagem_id = v.id AND COALESCE(vp.estado_parada, 'pendente') = 'pendente') AS _vp_pend,
              (SELECT COUNT(*)::int FROM viagem_pedidos vp
                WHERE vp.viagem_id = v.id AND COALESCE(vp.estado_parada, 'pendente') = 'indo') AS _vp_indo,
              (SELECT COUNT(*)::int FROM viagem_pedidos vp
                WHERE vp.viagem_id = v.id AND COALESCE(vp.estado_parada, 'pendente') = 'entrega_feita') AS _vp_feito,
              (SELECT COUNT(*)::int FROM viagem_pedidos vp
                WHERE vp.viagem_id = v.id AND COALESCE(vp.estado_parada, 'pendente') = 'divergencia_aguardando') AS _vp_div_parada,
              (SELECT COUNT(*)::int FROM viagem_pedidos vp
                WHERE vp.viagem_id = v.id AND COALESCE(vp.estado_parada, 'pendente') = 'resolvido_divergencia') AS _vp_resolv
            FROM viagens v
            JOIN rotas r ON r.id = v.rota_id
            LEFT JOIN veiculos ve ON ve.id = v.veiculo_id
            LEFT JOIN motoristas m ON m.id = v.motorista_id
            WHERE v.status = 'aberta'
            ORDER BY v.id DESC
SQL;
        return Database::pdo()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function encontrar(int $id): ?array
    {
        $sql = <<<SQL
            SELECT v.*, r.nome AS rota_nome, ve.placa, m.nome_completo AS motorista_nome
            FROM viagens v
            JOIN rotas r ON r.id=v.rota_id
            LEFT JOIN veiculos ve ON ve.id=v.veiculo_id
            LEFT JOIN motoristas m ON m.id=v.motorista_id
            WHERE v.id=? LIMIT 1
SQL;
        $st = Database::pdo()->prepare($sql);
        $st->execute([$id]);
        $r = $st->fetch(PDO::FETCH_ASSOC);
        return $r ?: null;
    }

    public static function pedidosDaViagem(int $viagemId): array
    {
        $sql = <<<SQL
            SELECT p.*, p.id AS pedido_id, vp.ordem_entrega,
              COALESCE(vp.estado_parada, 'pendente') AS parada_estado,
              vp.indo_em AS parada_indo_em,
              vp.recebedor_nome AS parada_recebedor_nome,
              vp.foto_mercadoria AS parada_foto_mercadoria,
              vp.entregue_em AS parada_entregue_em,
              vp.divergencia_id AS parada_divergencia_id,
              vp.assinatura_png AS parada_assinatura_png,
              vp.entrega_latitude AS parada_entrega_latitude,
              vp.entrega_longitude AS parada_entrega_longitude,
              vp.entrega_geo_precisao_m AS parada_entrega_geo_precisao_m,
              vp.entrega_geo_capturada_em AS parada_entrega_geo_capturada_em
            FROM viagem_pedidos vp
            JOIN pedidos p ON p.id = vp.pedido_id
            WHERE vp.viagem_id = ?
            ORDER BY vp.ordem_entrega ASC
SQL;
        $st = Database::pdo()->prepare($sql);
        $st->execute([$viagemId]);

        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function podeFinalizarOperacional(int $viagemId): bool
    {
        $st = Database::pdo()->prepare(
            'SELECT estado_parada FROM viagem_pedidos WHERE viagem_id=?'
        );
        $st->execute([$viagemId]);
        $rows = $st->fetchAll(PDO::FETCH_ASSOC);
        if ($rows === []) {
            return false;
        }
        foreach ($rows as $r) {
            $e = (string) ($r['estado_parada'] ?? 'pendente');
            if (! in_array($e, ['entrega_feita', 'resolvido_divergencia'], true)) {
                return false;
            }
        }

        return true;
    }

    public static function contarDivergenciasPendentesViagem(int $viagemId): int
    {
        $sql = <<<'SQL'
            SELECT COUNT(*)::int FROM divergencias_entrega
            WHERE viagem_id=? AND revisao_estado='pendente_aprovacao'
SQL;
        $st = Database::pdo()->prepare($sql);
        $st->execute([$viagemId]);

        return (int) $st->fetchColumn();
    }

    public static function finalizar(int $id): void
    {
        $pdo = Database::pdo();
        if (! self::podeFinalizarOperacional($id)) {
            throw new \RuntimeException(
                'Finalize todas as entregas ou resolva divergências (aprovadas) antes de encerrar a viagem.'
            );
        }
        $st = $pdo->prepare(
            "UPDATE viagens SET status='finalizada', finalizado_em=NOW(), atualizado_em=NOW() WHERE id=?"
        );
        $st->execute([$id]);

        $sql = <<<'SQL'
            UPDATE pedidos p SET estado='entregue', atualizado_em=NOW()
            FROM viagem_pedidos vp
            WHERE vp.pedido_id = p.id AND vp.viagem_id = ?
              AND vp.estado_parada = 'entrega_feita'
SQL;
        $st2 = $pdo->prepare($sql);
        $st2->execute([$id]);
    }

    /** Viagens em aberto onde o motorista é o responsável informado. */

    public static function listarAbertasPorMotorista(int $motoristaId): array
    {
        $sql = <<<SQL
            SELECT v.*, r.nome AS rota_nome, ve.placa,
              (SELECT COUNT(*)::int FROM viagem_pedidos vp WHERE vp.viagem_id = v.id) AS _vp_total,
              (SELECT COUNT(*)::int FROM viagem_pedidos vp
                WHERE vp.viagem_id = v.id AND COALESCE(vp.estado_parada, 'pendente') = 'pendente') AS _vp_pend,
              (SELECT COUNT(*)::int FROM viagem_pedidos vp
                WHERE vp.viagem_id = v.id AND COALESCE(vp.estado_parada, 'pendente') = 'indo') AS _vp_indo,
              (SELECT COUNT(*)::int FROM viagem_pedidos vp
                WHERE vp.viagem_id = v.id AND COALESCE(vp.estado_parada, 'pendente') = 'entrega_feita') AS _vp_feito,
              (SELECT COUNT(*)::int FROM viagem_pedidos vp
                WHERE vp.viagem_id = v.id
                  AND COALESCE(vp.estado_parada, 'pendente') = 'divergencia_aguardando') AS _vp_div_parada
            FROM viagens v
            JOIN rotas r ON r.id = v.rota_id
            LEFT JOIN veiculos ve ON ve.id = v.veiculo_id
            WHERE v.status = 'aberta' AND v.motorista_id = ?
            ORDER BY v.id DESC
SQL;
        $st = Database::pdo()->prepare($sql);
        $st->execute([$motoristaId]);

        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function divergencias(int $viagemId): array
    {
        $st = Database::pdo()->prepare(
            'SELECT d.*, p.numero_pedido FROM divergencias_entrega d
             LEFT JOIN pedidos p ON p.id = d.pedido_id
             WHERE d.viagem_id=? ORDER BY d.reportado_em DESC'
        );
        $st->execute([$viagemId]);

        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function divergenciasPendentesPainel(?int $lim = 300): array
    {
        $lim = max(1, min(500, $lim ?? 300));
        $sql = <<<SQL
            SELECT d.*, v.id AS viagem_id, v.rota_id, r.nome AS rota_nome,
              p.numero_pedido, m.nome_completo AS motorista_nome_reporte
            FROM divergencias_entrega d
            JOIN viagens v ON v.id = d.viagem_id
            JOIN rotas r ON r.id = v.rota_id
            LEFT JOIN pedidos p ON p.id = d.pedido_id
            LEFT JOIN motoristas m ON m.id = d.motorista_id
            WHERE d.revisao_estado = 'pendente_aprovacao'
            ORDER BY d.reportado_em ASC
            LIMIT {$lim}
SQL;

        return Database::pdo()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function aprovarDivergencia(int $divId, int $usuarioId, bool $aprovar): bool
    {
        $pdo = Database::pdo();
        $pdo->beginTransaction();
        try {
            $div = Database::pdo()->prepare(
                'SELECT id, viagem_id, pedido_id FROM divergencias_entrega WHERE id=? FOR UPDATE'
            );
            $div->execute([$divId]);
            $row = $div->fetch(PDO::FETCH_ASSOC);
            if (! $row) {
                $pdo->rollBack();

                return false;
            }
            $estado = $aprovar ? 'aprovada' : 'rejeitada';
            $st = Database::pdo()->prepare(
                'UPDATE divergencias_entrega SET revisao_estado=?, revisado_em=NOW(), revisado_por_usuario_id=?
                WHERE id=?'
            );
            $st->execute([$estado, $usuarioId, $divId]);

            $vpRow = Database::pdo()->prepare(
                'SELECT viagem_id, pedido_id FROM viagem_pedidos WHERE divergencia_id=? LIMIT 1 FOR UPDATE'
            );
            $vpRow->execute([$divId]);
            $lnk = $vpRow->fetch(PDO::FETCH_ASSOC);
            if ($lnk !== false) {
                if ($aprovar) {
                    Database::pdo()->prepare(
                        "UPDATE viagem_pedidos SET estado_parada='resolvido_divergencia'
                         WHERE divergencia_id=?"
                    )->execute([$divId]);
                } else {
                    Database::pdo()->prepare(
                        "UPDATE viagem_pedidos SET estado_parada='pendente', divergencia_id=NULL
                         WHERE divergencia_id=?"
                    )->execute([$divId]);
                }
            }
            $pdo->commit();

            return true;
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    /**
     * @return positive-int|false ID novo registro divergência
     */
    public static function registrarDivergenciaFlex(
        int $viagemId,
        ?int $pedidoId,
        string $texto,
        ?int $usuarioId,
        ?int $motoristaId,
        string $revisaoEstadoInicial,
        ?string $fotoUrl = null,
    ): int|false {
        $st = Database::pdo()->prepare(
            'INSERT INTO divergencias_entrega (viagem_id, pedido_id, descricao, foto_url, origem_usuario_id, motorista_id, revisao_estado)
             VALUES (?,?,?,?,?,?,?)
             RETURNING id'
        );
        $ok = $st->execute([$viagemId, $pedidoId, $texto, $fotoUrl, $usuarioId, $motoristaId, $revisaoEstadoInicial]);
        if (! $ok) {
            return false;
        }

        return (int) $st->fetchColumn();
    }

    public static function registrarDivergencia(int $viagemId, ?int $pedidoId, string $texto, ?int $usuarioId): void
    {
        static::registrarDivergenciaFlex(
            $viagemId,
            $pedidoId,
            $texto,
            $usuarioId,
            null,
            'aprovada'
        );
    }

    public static function garantirMotoristaDaViagem(int $viagemId, int $motoristaId): bool
    {
        $st = Database::pdo()->prepare(
            'SELECT 1 FROM viagens WHERE id=? AND motorista_id=? AND status=? LIMIT 1'
        );
        $st->execute([$viagemId, $motoristaId, 'aberta']);

        return (bool) $st->fetchColumn();
    }

    public static function marcarParadaIndo(int $viagemId, int $pedidoId): bool
    {
        $pdo = Database::pdo();
        $st = $pdo->prepare(
            "UPDATE viagem_pedidos SET estado_parada='indo', indo_em=NOW()
             WHERE viagem_id=? AND pedido_id=? AND estado_parada='pendente'"
        );
        $st->execute([$viagemId, $pedidoId]);

        return $st->rowCount() > 0;
    }

    public static function concluirParada(
        int $viagemId,
        int $pedidoId,
        string $nomeRecebedor,
        string $fotoPath,
        string $assinaturaDataUrl,
        float $entregaLatitude,
        float $entregaLongitude,
        ?float $entregaGeoPrecisaoM = null,
    ): bool {
        $pdo = Database::pdo();
        $st = $pdo->prepare(
            "UPDATE viagem_pedidos SET estado_parada='entrega_feita', recebedor_nome=?, foto_mercadoria=?,
               assinatura_png=?, entregue_em=NOW(),
               entrega_latitude=?, entrega_longitude=?, entrega_geo_precisao_m=?, entrega_geo_capturada_em=NOW()
             WHERE viagem_id=? AND pedido_id=? AND estado_parada='indo'"
        );
        $st->execute([
            $nomeRecebedor,
            $fotoPath,
            $assinaturaDataUrl,
            $entregaLatitude,
            $entregaLongitude,
            $entregaGeoPrecisaoM,
            $viagemId,
            $pedidoId,
        ]);

        return $st->rowCount() > 0;
    }

    /** @return positive-int|false */
    public static function abrirDivergenciaParada(
        int $viagemId,
        int $pedidoId,
        int $motoristaId,
        string $descricao,
        ?string $fotoUrl = null,
    ): int|false {
        $pdo = Database::pdo();
        $pdo->beginTransaction();
        try {
            $idFlex = static::registrarDivergenciaFlex(
                $viagemId,
                $pedidoId,
                $descricao,
                null,
                $motoristaId,
                'pendente_aprovacao',
                $fotoUrl,
            );
            if ($idFlex === false) {
                $pdo->rollBack();

                return false;
            }
            $idDiv = $idFlex;
            $st = $pdo->prepare(
                "UPDATE viagem_pedidos SET estado_parada='divergencia_aguardando', divergencia_id=?
                 WHERE viagem_id=? AND pedido_id=? AND estado_parada='indo'"
            );
            $st->execute([$idDiv, $viagemId, $pedidoId]);
            if ($st->rowCount() === 0) {
                $pdo->rollBack();

                return false;
            }
            $pdo->commit();

            return $idDiv;
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }
}
