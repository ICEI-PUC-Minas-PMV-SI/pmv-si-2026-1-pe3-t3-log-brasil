#!/usr/bin/env php
<?php

/**
 * Gera INSERT SQL para public.usuarios com senha em bcrypt compatível ao password_verify PHP.
 *
 * Uso: php scripts/gerar_usuario_cli.php nome@email.com "SenhaForte123" "Nome Sobrenome" [admin|gestor|monitoramento|roteirizador|cliente|motorista]
 */
declare(strict_types=1);

if ($argc < 3) {
    fwrite(STDERR, "Uso: php scripts/gerar_usuario_cli.php email senha nome_completo [papel]\n");
    exit(1);
}

$email = $argv[1];
$senha = $argv[2];
$nome = $argv[3] ?? 'Operador TMS';
$papelRaw = $argv[4] ?? 'gestor';

if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fwrite(STDERR, "E-mail inválido.\n");
    exit(1);
}

$papelRaw = trim(mb_strtolower((string) $papelRaw, 'UTF-8'));
$papeisValidos = ['admin', 'gestor', 'monitoramento', 'roteirizador', 'cliente', 'motorista'];
$papelSan = in_array($papelRaw, $papeisValidos, true) ? $papelRaw : 'gestor';
$hash = password_hash($senha, PASSWORD_DEFAULT);

$esc = fn (string $s): string => str_replace("'", "''", $s);

$sql = sprintf(
    "INSERT INTO public.usuarios (email, senha_hash, nome_completo, papel) VALUES ('%s', '%s', '%s', '%s');",
    $esc(mb_strtolower($email, 'UTF-8')),
    $esc($hash),
    $esc($nome),
    $esc($papelSan)
);

echo "-- Cole no SQL Editor do Supabase (projeto PostgreSQL)\n";
echo $sql . "\n";
