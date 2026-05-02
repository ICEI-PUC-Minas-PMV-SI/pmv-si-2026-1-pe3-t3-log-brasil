<?php

namespace App\Models;

use App\Core\Database;
use PDO;

/**
 * Rotas de entrega declaradas pela operação.
 */
final class Rota
{
    public static function listarTodos(): array
    {
        $st = Database::pdo()->query('SELECT * FROM rotas ORDER BY nome ASC');
        return $st !== false ? $st->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    public static function encontrar(int $id): ?array
    {
        $st = Database::pdo()->prepare('SELECT * FROM rotas WHERE id=?');
        $st->execute([$id]);
        $r = $st->fetch(PDO::FETCH_ASSOC);
        return $r ?: null;
    }

    public static function criar(string $nome, ?string $obs, bool $ativo = true): int
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare('INSERT INTO rotas (nome, observacao, ativo) VALUES (?,?,?) RETURNING id');
        $stmt->execute([trim($nome), $obs !== null ? trim($obs) : null, $ativo]);
        return (int) $stmt->fetchColumn();
    }

    public static function atualizar(int $id, string $nome, ?string $obs, bool $ativo): bool
    {
        $stmt = Database::pdo()->prepare('UPDATE rotas SET nome=?, observacao=?, ativo=? WHERE id=?');
        return $stmt->execute([trim($nome), $obs !== null ? trim($obs) : null, $ativo, $id]);
    }

    public static function remover(int $id): bool
    {
        $stmt = Database::pdo()->prepare('DELETE FROM rotas WHERE id=?');
        return $stmt->execute([$id]);
    }

    public static function cidadesPorRota(int $rotaId): array
    {
        $stmt = Database::pdo()->prepare('SELECT * FROM rota_cidades WHERE rota_id=? ORDER BY cidade ASC');
        $stmt->execute([$rotaId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function bairrosPorRota(int $rotaId): array
    {
        $stmt = Database::pdo()->prepare('SELECT * FROM rota_bairros WHERE rota_id=? ORDER BY cidade, bairro');
        $stmt->execute([$rotaId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function adicionarCidade(int $rotaId, string $cidade, string $uf): void
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'INSERT INTO rota_cidades (rota_id, cidade, uf) VALUES (?,?,?)
             ON CONFLICT (rota_id, cidade, uf) DO NOTHING'
        );
        $stmt->execute([$rotaId, trim($cidade), mb_strtoupper($uf, 'UTF-8')]);
    }

    public static function removerCidade(int $id): bool
    {
        return Database::pdo()->prepare('DELETE FROM rota_cidades WHERE id=?')->execute([$id]);
    }

    public static function adicionarBairro(int $rotaId, string $bairro, string $cidade, string $uf): void
    {
        $stmt = Database::pdo()->prepare(
            'INSERT INTO rota_bairros (rota_id, bairro, cidade, uf) VALUES (?,?,?,?)
             ON CONFLICT (rota_id, bairro, cidade, uf) DO NOTHING'
        );
        $stmt->execute([$rotaId, trim($bairro), trim($cidade), mb_strtoupper($uf, 'UTF-8')]);
    }

    public static function removerBairro(int $id): bool
    {
        return Database::pdo()->prepare('DELETE FROM rota_bairros WHERE id=?')->execute([$id]);
    }
}
