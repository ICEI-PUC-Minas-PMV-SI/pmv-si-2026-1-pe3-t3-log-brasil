<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

/**
 * Usuários do painel.
 */
final class Usuario
{
    /** Tipos válidos nos formulários cadastro. */

    public static function papeisLista(): array
    {
        return ['admin', 'gestor', 'monitoramento', 'roteirizador', 'cliente', 'motorista'];
    }

    public static function findByEmail(string $email): ?array
    {
        $pdo = Database::pdo();
        $st = $pdo->prepare('SELECT id,email,senha_hash,nome_completo,papel,ativo,acompanhar_cpf FROM usuarios WHERE email = ? LIMIT 1');
        $st->execute([mb_strtolower($email, 'UTF-8')]);
        $row = $st->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public static function encontrar(int $id): ?array
    {
        $st = Database::pdo()->prepare(
            'SELECT id,email,nome_completo,papel,ativo,criado_em FROM usuarios WHERE id=? LIMIT 1'
        );
        $st->execute([$id]);
        $r = $st->fetch(PDO::FETCH_ASSOC);

        return $r ?: null;
    }

    public static function listarTodos(): array
    {
        return Database::pdo()
            ->query('SELECT id,email,nome_completo,papel,ativo,acompanhar_cpf,criado_em FROM usuarios ORDER BY nome_completo ASC')
            ->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function criar(array $d): int
    {
        $ativo = filter_var($d['ativo'] ?? true, FILTER_VALIDATE_BOOLEAN);

        $cpfA = null;
        if (($d['papel'] ?? '') === 'cliente') {
            $cpfA = preg_replace('/\D/', '', (string) ($d['acompanhar_cpf'] ?? ''));
            $cpfA = $cpfA !== '' ? $cpfA : null;
        }

        $st = Database::pdo()->prepare(
            'INSERT INTO usuarios (email, senha_hash, nome_completo, papel, ativo, acompanhar_cpf)
             VALUES (?,?,?,?,?,?)
             RETURNING id'
        );
        $st->execute([
            $d['email'],
            $d['senha_hash'],
            $d['nome_completo'],
            $d['papel'],
            $ativo ? 't' : 'f',
            $cpfA,
        ]);

        return (int) $st->fetchColumn();
    }

    public static function atualizar(int $id, array $d): bool
    {
        $pdo = Database::pdo();
        $ativo = filter_var($d['ativo'] ?? true, FILTER_VALIDATE_BOOLEAN) ? 't' : 'f';
        $hash = isset($d['senha_hash']) ? trim((string) $d['senha_hash']) : '';

        if ($hash !== '') {
            $st = $pdo->prepare(
                'UPDATE usuarios SET email=?, nome_completo=?, papel=?, ativo=?, senha_hash=? WHERE id=?'
            );

            return $st->execute([
                $d['email'],
                $d['nome_completo'],
                $d['papel'],
                $ativo,
                $hash,
                $id,
            ]);
        }

        $st = $pdo->prepare(
            'UPDATE usuarios SET email=?, nome_completo=?, papel=?, ativo=? WHERE id=?'
        );

        return $st->execute([
            $d['email'],
            $d['nome_completo'],
            $d['papel'],
            $ativo,
            $id,
        ]);
    }

    public static function emailExisteOuOutroId(string $email, ?int $ignorarId = null): bool
    {
        $pdo = Database::pdo();
        $em = mb_strtolower(trim($email), 'UTF-8');

        if ($ignorarId === null) {
            $st = $pdo->prepare('SELECT 1 FROM usuarios WHERE lower(email)=? LIMIT 1');
            $st->execute([$em]);
        } else {
            $st = $pdo->prepare(
                'SELECT 1 FROM usuarios WHERE lower(email)=? AND id<>? LIMIT 1'
            );
            $st->execute([$em, $ignorarId]);
        }

        return (bool) $st->fetchColumn();
    }
}
