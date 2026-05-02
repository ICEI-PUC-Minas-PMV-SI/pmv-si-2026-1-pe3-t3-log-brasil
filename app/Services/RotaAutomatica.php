<?php

namespace App\Services;

use PDO;

/**
 * Atribui rota aos pedidos a partir das tabelas rota_bairros e rota_cidades (prioridade ao bairro).
 */
final class RotaAutomatica
{
    public function __construct(private PDO $pdo)
    {
    }

    /** Retorna ID da rota ou null se não houver correspondência declarada na base de rotas. */
    public function resolverPorEndereco(string $bairro, string $cidade, string $uf): ?int
    {
        $uf = mb_strtoupper($uf, 'UTF-8');

        $st = $this->pdo->prepare(
            'SELECT rota_id FROM rota_bairros 
             WHERE cidade ILIKE ? AND uf = ? AND bairro ILIKE ? LIMIT 1'
        );
        $st->execute([$cidade, $uf, $bairro]);
        $rid = $st->fetchColumn();
        if ($rid !== false) {
            return (int) $rid;
        }

        $st = $this->pdo->prepare(
            'SELECT rota_id FROM rota_cidades WHERE cidade ILIKE ? AND uf = ? LIMIT 1'
        );
        $st->execute([$cidade, $uf]);
        $rid2 = $st->fetchColumn();

        return $rid2 !== false ? (int) $rid2 : null;
    }
}
