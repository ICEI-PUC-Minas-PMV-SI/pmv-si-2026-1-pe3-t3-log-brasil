<?php

namespace App\Models;

use App\Core\Database;
use PDO;

/**
 * Unidade padrão singleton (matriz/deposit).
 */
final class UnidadePadrao
{
    public static function get(): ?array
    {
        $st = Database::pdo()->query('SELECT * FROM unidade_padrao WHERE id = 1 LIMIT 1');
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /** Garante registro inicial; útil antes do primeiro uso. */
    public static function ensureBootstrap(): void
    {
        $pdo = Database::pdo();
        $st = $pdo->query('SELECT id FROM unidade_padrao WHERE id=1');
        if ($st !== false && $st->fetch()) {
            return;
        }
        $sql = <<<SQL
            INSERT INTO unidade_padrao (id, nome, logradouro, numero, cidade, uf, cep, latitude, longitude)
            VALUES (1,'Matriz LogBrasil','Configure o endereco','SN','Curitiba','PR','80000000', -25.428954, -49.273177)
SQL;
        $pdo->exec($sql);
    }

    /** Atualização completa pelo painel futuro ou API. */
    public static function updateCoord(int $usuarioId, array $payload): bool
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'UPDATE unidade_padrao SET nome=?, logradouro=?, numero=?, complemento=?, bairro=?, cidade=?, uf=?, cep=?,
             latitude=?, longitude=?, observacao=?, atualizado_por_id=? WHERE id=1'
        );
        return $stmt->execute([
            $payload['nome'],
            $payload['logradouro'],
            $payload['numero'],
            $payload['complemento'],
            $payload['bairro'],
            $payload['cidade'],
            $payload['uf'],
            $payload['cep'],
            $payload['latitude'],
            $payload['longitude'],
            $payload['observacao'],
            $usuarioId,
        ]);
    }
}
