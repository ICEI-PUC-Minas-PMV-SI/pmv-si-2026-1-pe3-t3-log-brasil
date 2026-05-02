<?php

use App\Core\Helpers;

$base = CONF_BASE_URL;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
    <meta name="csrf" content="<?= Helpers::csrfToken() ?>">
    <title><?= Helpers::e($title ?? 'LogBrasil') ?></title>
    <link rel="icon" type="image/png" href="<?= htmlspecialchars($base . '/assets/favicon.ico', ENT_QUOTES) ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($base . '/assets/css/login.css', ENT_QUOTES) ?>?v=3">
    <link rel="stylesheet" href="<?= htmlspecialchars($base . '/assets/css/lb-portais-mobile.css', ENT_QUOTES) ?>?v=2">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous">
    <script>
        window.LOGBR_M = <?= json_encode([
            'baseUrl' => $base,
            'csrf' => Helpers::csrfToken(),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS) ?>;
    </script>
</head>
<body class="lb-login-root">
<div class="lb-login-page lb-public-track-page">
    <div class="lb-login-bg-grid" aria-hidden="true"></div>
    <div class="lb-login-glow lb-login-glow-1" aria-hidden="true"></div>
    <div class="lb-login-glow lb-login-glow-2" aria-hidden="true"></div>

    <header class="lb-public-track-bar">
        <a href="<?= htmlspecialchars($base . '/', ENT_QUOTES) ?>" class="lb-public-track-brand">
            <img src="<?= htmlspecialchars($base . '/assets/logo/logo.png', ENT_QUOTES) ?>" alt="LogBrasil" width="160" height="42">
        </a>
        <a href="<?= htmlspecialchars($base . '/login', ENT_QUOTES) ?>" class="lb-public-track-link">
            <i class="fa-solid fa-user-lock"></i> Área interna
        </a>
    </header>

    <div class="lb-public-track-main">
        <?= $content ?>
    </div>
</div>
<script defer src="<?= htmlspecialchars($base . '/assets/js/lb-portal-cliente.js', ENT_QUOTES) ?>?v=1"></script>
</body>
</html>
