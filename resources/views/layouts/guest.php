<?php

use App\Core\Helpers;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= Helpers::e($title ?? 'LogBrasil — Acesso') ?></title>
    <link rel="icon" href="<?= htmlspecialchars(CONF_BASE_URL, ENT_QUOTES) ?>/assets/favicon.ico" type="image/png">
    <link rel="stylesheet" href="<?= htmlspecialchars(CONF_BASE_URL, ENT_QUOTES) ?>/assets/css/lb-busy.css?v=1">
    <link rel="stylesheet" href="<?= htmlspecialchars(CONF_BASE_URL, ENT_QUOTES) ?>/assets/css/login.css?v=2">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous">
</head>
<body class="lb-login-root">
<?= $content ?>
<div id="lb-busy-mask" class="lb-busy-mask" aria-hidden="true" aria-busy="false">
    <div class="lb-busy-inner">
        <div class="lb-busy-spinner" aria-hidden="true"></div>
        <p class="lb-busy-text">Processando…</p>
    </div>
</div>
<script defer src="<?= htmlspecialchars(CONF_BASE_URL, ENT_QUOTES) ?>/assets/js/lb-busy-nav.js?v=1"></script>
</body>
</html>
