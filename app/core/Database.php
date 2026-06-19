<?php

namespace App\Core;

use PDO;

/**
 * Conexão única PDO com PostgreSQL (Supabase pool ou host direto).
 */
final class Database
{
    private static ?PDO $pdo = null;

    public static function pdo(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }
        $host = $_ENV['DB_HOST'] ?? 'localhost';
        $port = $_ENV['DB_PORT'] ?? '5432';
        $db = $_ENV['DB_DATABASE'] ?? 'postgres';
        $user = $_ENV['DB_USERNAME'] ?? 'postgres';
        $pass = $_ENV['DB_PASSWORD'] ?? '';
        $sslmode = $_ENV['DB_SSLMODE'] ?? '';

        $hn = strtolower((string) $host);
        if (
            str_contains($hn, 'seu_project_ref')
            || str_contains($hn, 'cole_aqui')
            || str_contains($hn, 'replace_me')
            || preg_match('/<.*>/', (string) $host)
        ) {
            throw new DatabaseConnectionException('PLACEHOLDER_HOST', 0);
        }

        $dsn = sprintf('pgsql:host=%s;port=%s;dbname=%s', $host, $port, $db);
        $hn2 = strtolower((string) $host);
        $looksSupabase = str_contains($hn2, '.supabase.co') || str_contains($hn2, 'pooler.supabase.com');
        $sslmode = trim((string) $sslmode);
        if ($sslmode === '' && $looksSupabase) {
            $sslmode = 'require';
        }
        if ($sslmode !== '') {
            $dsn .= ';sslmode=' . $sslmode;
        }
        try {
            self::$pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (\PDOException $e) {
            error_log('[LogBrasil DB] ' . $e->getMessage());
            $low = strtolower($e->getMessage());
            $missingDriver = str_contains($low, 'could not find driver')
                || str_contains($low, 'não foi possível encontrar o driver')
                || str_contains($low, 'driver não encontrado');

            throw new DatabaseConnectionException(
                $missingDriver
                    ? 'DRIVER'
                    : 'CONNECT',
                (int) $e->getCode(),
                $e
            );
        }

        return self::$pdo;
    }
}
