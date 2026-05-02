<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

/**
 * Motoristas.
 */
final class Motorista
{
    /** Literais aceitos pelo PostgreSQL (evita '' com PDO + boolean). */
    private static function pgBool(mixed $v): string
    {
        return filter_var($v, FILTER_VALIDATE_BOOLEAN) ? 't' : 'f';
    }

    public static function listarTodos(): array
    {
        return Database::pdo()
            ->query('SELECT * FROM motoristas ORDER BY nome_completo ASC')
            ->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function encontrar(int $id): ?array
    {
        $st = Database::pdo()->prepare('SELECT * FROM motoristas WHERE id=?');
        $st->execute([$id]);
        $r = $st->fetch(PDO::FETCH_ASSOC);
        return $r ?: null;
    }

    /** CPF apenas dígitos (11 caracteres esperado). */

    public static function encontrarPorCpfDigits(string $cpfDigits): ?array
    {
        $digits = preg_replace('/\D/', '', $cpfDigits);
        if ($digits === '') {
            return null;
        }
        $st = Database::pdo()->prepare(
            "SELECT * FROM motoristas WHERE regexp_replace(COALESCE(cpf, ''), '[^0-9]', '', 'g') = ? LIMIT 1"
        );

        $st->execute([$digits]);
        $r = $st->fetch(PDO::FETCH_ASSOC);

        return $r ?: null;
    }

    public static function atualizarFotoPerfil(int $id, ?string $arquivoNome): bool
    {
        $stmt = Database::pdo()->prepare('UPDATE motoristas SET foto_perfil=?, atualizado_em=NOW() WHERE id=?');

        return $stmt->execute([$arquivoNome, $id]);
    }

    public static function criar(array $d): int
    {
        $stmt = Database::pdo()->prepare(
            'INSERT INTO motoristas (
              nome_completo, cpf, cnh_numero, cnh_categoria, telefone, email, empresa_terceira, nome_empresa_terceira, ativo, senha_hash, foto_perfil
            ) VALUES (?,?,?,?,?,?,?,?,?,?,?) RETURNING id'
        );
        $stmt->execute([
            $d['nome_completo'],
            $d['cpf'],
            $d['cnh_numero'],
            $d['cnh_categoria'],
            $d['telefone'],
            $d['email'],
            self::pgBool($d['empresa_terceira'] ?? false),
            $d['nome_empresa_terceira'],
            self::pgBool($d['ativo'] ?? true),
            $d['senha_hash'] ?? null,
            $d['foto_perfil'] ?? null,
        ]);
        return (int) $stmt->fetchColumn();
    }

    public static function atualizar(int $id, array $d): bool
    {
        $hash = isset($d['senha_hash']) ? trim((string) $d['senha_hash']) : '';

        if ($hash !== '') {
            $stmt = Database::pdo()->prepare(
                'UPDATE motoristas SET nome_completo=?, cpf=?, cnh_numero=?, cnh_categoria=?, telefone=?, email=?,
                 empresa_terceira=?, nome_empresa_terceira=?, ativo=?, senha_hash=? WHERE id=?'
            );

            return $stmt->execute([
                $d['nome_completo'],
                $d['cpf'],
                $d['cnh_numero'],
                $d['cnh_categoria'],
                $d['telefone'],
                $d['email'],
                self::pgBool($d['empresa_terceira'] ?? false),
                $d['nome_empresa_terceira'],
                self::pgBool($d['ativo'] ?? true),
                $hash,
                $id,
            ]);
        }

        $stmt = Database::pdo()->prepare(
            'UPDATE motoristas SET nome_completo=?, cpf=?, cnh_numero=?, cnh_categoria=?, telefone=?, email=?,
             empresa_terceira=?, nome_empresa_terceira=?, ativo=? WHERE id=?'
        );

        return $stmt->execute([
            $d['nome_completo'],
            $d['cpf'],
            $d['cnh_numero'],
            $d['cnh_categoria'],
            $d['telefone'],
            $d['email'],
            self::pgBool($d['empresa_terceira'] ?? false),
            $d['nome_empresa_terceira'],
            self::pgBool($d['ativo'] ?? true),
            $id,
        ]);
    }

    public static function remover(int $id): bool
    {
        return Database::pdo()->prepare('DELETE FROM motoristas WHERE id=?')->execute([$id]);
    }
}
