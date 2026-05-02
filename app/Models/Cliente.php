<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

/**
 * Cliente físico por CPF; endereço e geocódigo mantidos uma vez só.
 */
final class Cliente
{
    /** Aceita apenas 11 dígitos (somente número). */

    public static function normalizarCpf(?string $s): ?string
    {
        $d = preg_replace('/\D/', '', trim((string) $s));

        return strlen($d) === 11 ? $d : null;
    }

    public static function encontrarPorCpf(string $cpf11): ?array
    {
        $st = Database::pdo()->prepare('SELECT * FROM clientes WHERE cpf = ? LIMIT 1');
        $st->execute([$cpf11]);
        $r = $st->fetch(PDO::FETCH_ASSOC);

        return $r ?: null;
    }

    /** @return positive-int|null */
    public static function encontrarIdPorCpf(string $cpf11): ?int
    {
        $st = Database::pdo()->prepare('SELECT id FROM clientes WHERE cpf=? LIMIT 1');
        $st->execute([$cpf11]);
        $id = $st->fetchColumn();

        return $id !== false ? (int) $id : null;
    }

    /**
     * Cria ou atualiza dados cadastrais/endereço/geo do cliente.
     *
     * @return positive-int ID do cliente no banco
     */
    public static function upsertPorCpf(array $d): int
    {
        $cpf = $d['cpf'];
        if (strlen((string) $cpf) !== 11) {
            throw new \InvalidArgumentException('CPF inválido.');
        }

        $pdo = Database::pdo();
        $sql = <<<'SQL'
            INSERT INTO clientes (
                cpf, nome_completo, telefone, logradouro, numero, complemento,
                bairro, cidade, uf, cep, referencia_entrega, latitude, longitude
            ) VALUES (
                ?,?,?,?,?,?,?,?,?,?,?,?,?
            )
            ON CONFLICT (cpf) DO UPDATE SET
                nome_completo       = EXCLUDED.nome_completo,
                telefone            = EXCLUDED.telefone,
                logradouro          = EXCLUDED.logradouro,
                numero              = EXCLUDED.numero,
                complemento         = EXCLUDED.complemento,
                bairro              = EXCLUDED.bairro,
                cidade              = EXCLUDED.cidade,
                uf                  = EXCLUDED.uf,
                cep                 = EXCLUDED.cep,
                referencia_entrega   = EXCLUDED.referencia_entrega,
                latitude            = EXCLUDED.latitude,
                longitude           = EXCLUDED.longitude,
                atualizado_em       = NOW()
            RETURNING id
SQL;
        $st = $pdo->prepare($sql);
        $st->execute([
            $cpf,
            $d['nome_completo'],
            $d['telefone'],
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
        ]);

        return (int) $st->fetchColumn();
    }

    public static function atualizarCoordenadas(int $clienteId, float $lat, float $lng): void
    {
        $st = Database::pdo()->prepare(
            'UPDATE clientes SET latitude=?, longitude=?, atualizado_em=NOW() WHERE id=?'
        );
        $st->execute([$lat, $lng, $clienteId]);
    }
}
