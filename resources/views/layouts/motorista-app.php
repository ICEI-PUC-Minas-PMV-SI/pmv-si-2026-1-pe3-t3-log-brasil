<?php

use App\Core\Helpers;

$base = CONF_BASE_URL;
$navMot = $navMot ?? '';
$isMot = fn (string $k): string => ($navMot === $k ? 'lb-mot-nav-btn is-active' : 'lb-mot-nav-btn');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,viewport-fit=cover,user-scalable=no">
    <meta name="csrf" content="<?= Helpers::csrfToken() ?>">
    <title><?= Helpers::e($title ?? 'Motorista') ?></title>
    <link rel="icon" type="image/png" href="<?= htmlspecialchars($base . '/assets/favicon.ico', ENT_QUOTES) ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($base . '/assets/css/lb-portais-mobile.css', ENT_QUOTES) ?>?v=5">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous">
    <?php if (! empty($leafletHead ?? '')): ?>
        <?= $leafletHead ?>
    <?php endif; ?>
    <script>
        window.LOGBR_M = <?= json_encode([
            'baseUrl' => $base,
            'csrf' => Helpers::csrfToken(),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS) ?>;
    </script>
    <script src="<?= htmlspecialchars($base . '/assets/js/lb-motorista-busy.js', ENT_QUOTES) ?>?v=2"></script>
</head>
<body class="lb-m-root lb-mot-dark">
<div id="lb-mot-busy" class="lb-mot-busy lb-mot-busy--hide" aria-hidden="true" role="alert">
    <div class="lb-mot-busy__card">
        <span class="lb-mot-busy__spin" aria-hidden="true"></span>
        <span class="lb-mot-busy__text">Carregando…</span>
    </div>
</div>
<div class="lb-m-strip" style="justify-content:space-between">
    <a href="<?= htmlspecialchars($base . '/motorista', ENT_QUOTES) ?>">
        <img src="<?= htmlspecialchars($base . '/assets/logo/logo.png', ENT_QUOTES) ?>" alt="" class="lb-m-logo-img" width="140" height="40">
    </a>
    <form method="post" action="<?= htmlspecialchars($base . '/motorista/logout', ENT_QUOTES) ?>" style="margin:0">
        <input type="hidden" name="_csrf" value="<?= Helpers::csrfToken() ?>">
        <button type="submit" class="lb-m-btn lb-m-btn-ghost" style="width:auto;padding:8px 10px;font-size:.8rem"><i class="fa-solid fa-power-off"></i></button>
    </form>
</div>
<div class="lb-m-page">
    <?php if (! empty($_SESSION['flash_ok'])): ?>
        <div class="lb-m-card lb-mot-flash lb-mot-flash-ok"><?= Helpers::e((string) $_SESSION['flash_ok']) ?></div>
        <?php unset($_SESSION['flash_ok']); endif; ?>
    <?php if (! empty($_SESSION['flash_error'])): ?>
        <div class="lb-m-card lb-mot-flash lb-mot-flash-err"><?= Helpers::e((string) $_SESSION['flash_error']) ?></div>
        <?php unset($_SESSION['flash_error']); endif; ?>
    <?= $content ?>
</div>
<nav class="lb-mot-bottom" aria-label="Motorista">
    <a href="<?= htmlspecialchars($base . '/motorista', ENT_QUOTES) ?>" class="<?= $isMot('home') ?>"><i class="fa-regular fa-user"></i>Início</a>
    <a href="<?= htmlspecialchars($base . '/motorista/viagens', ENT_QUOTES) ?>" class="<?= $isMot('viagens') ?>"><i class="fa-solid fa-road"></i>Viagens</a>
</nav>
<?php if (! empty($motExtraScripts ?? '')): ?>
    <?= $motExtraScripts ?>
<?php endif; ?>
</body>
</html>
