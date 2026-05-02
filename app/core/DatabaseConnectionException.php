<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Falha ao abrir PDO (credenciais, rede ou driver PHP ausente).
 */
final class DatabaseConnectionException extends \RuntimeException
{
}
