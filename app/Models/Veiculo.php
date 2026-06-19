<?php

namespace App\Models;

use App\Core\Database;
use PDO;

/**
 * Frota.
 */
final class Veiculo
{
    public static function listarTodos(): array
    {
        return Database::pdo()
            ->query('SELECT * FROM veiculos ORDER BY placa ASC')
            ->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function encontrar(int $id): ?array
    {
        $st = Database::pdo()->prepare('SELECT * FROM veiculos WHERE id=?');
        $st->execute([$id]);
        $r = $st->fetch(PDO::FETCH_ASSOC);
        return $r ?: null;
    }

    public static function criar(array $d): int
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'INSERT INTO veiculos (placa, descricao, marca_modelo, ano, capacidade_kg, tipo, frota_interna, ativo)
             VALUES (?,?,?,?,?,?,?,?) RETURNING id'
        );
        $stmt->execute([
            $d['placa'],
            $d['descricao'],
            $d['marca_modelo'],
            $d['ano'],
            $d['capacidade_kg'],
            $d['tipo'],
            $d['frota_interna'],
            $d['ativo'],
        ]);
        return (int) $stmt->fetchColumn();
    }

    public static function atualizar(int $id, array $d): bool
    {
        $stmt = Database::pdo()->prepare(
            'UPDATE veiculos SET placa=?, descricao=?, marca_modelo=?, ano=?, capacidade_kg=?, tipo=?, frota_interna=?, ativo=? WHERE id=?'
        );
        return $stmt->execute([
            $d['placa'],
            $d['descricao'],
            $d['marca_modelo'],
            $d['ano'],
            $d['capacidade_kg'],
            $d['tipo'],
            $d['frota_interna'],
            $d['ativo'],
            $id,
        ]);
    }

    public static function remover(int $id): bool
    {
        return Database::pdo()->prepare('DELETE FROM veiculos WHERE id=?')->execute([$id]);
    }
}
