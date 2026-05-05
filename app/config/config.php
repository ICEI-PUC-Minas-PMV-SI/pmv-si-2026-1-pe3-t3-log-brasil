<?php

/**
 * Carrega .env (formato KEY=VAL) na raiz e define arrays de configuração.
 */
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$dotenvPath = $root . DIRECTORY_SEPARATOR . '.env';

if (is_readable($dotenvPath)) {
    foreach (file($dotenvPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        if (! str_contains($line, '=')) {
            continue;
        }
        [$k, $v] = explode('=', $line, 2);
        $k = trim($k);
        $v = trim($v);
        if ($v !== '') {
            // O .env local deve prevalecer sobre variáveis herdadas do ambiente (Apache/Windows).
            $_ENV[$k] = $v;
        }
    }
}

define('CONF_BASE_URL', rtrim($_ENV['BASE_URL'] ?? 'http://localhost/LogBrasil/public', '/'));
define('CONF_ORS_API_KEY', $_ENV['OPENROUTESERVICE_API_KEY'] ?? '');
define('CONF_ORS_PROFILE', $_ENV['ORS_PROFILE'] ?? 'driving-car');
