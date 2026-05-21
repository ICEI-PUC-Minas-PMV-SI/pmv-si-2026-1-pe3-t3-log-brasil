<?php

use App\Core\Helpers;

$navKey = $nav ?? '';
$isNav = fn (string $k): string => $navKey === $k ? 'nav-item active' : 'nav-item';
$papelUsuario = (string) ($_SESSION['user']['papel'] ?? '');
$monitorSomente = $papelUsuario === 'monitoramento';
$linksCadastro = in_array($papelUsuario, ['admin', 'gestor', 'roteirizador'], true);
$linksRevDiv = in_array($papelUsuario, ['admin', 'gestor', 'monitoramento'], true);
$podeAdminUsuarios = $papelUsuario === 'admin';
$baseSafe = htmlspecialchars(CONF_BASE_URL, ENT_QUOTES);

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf" content="<?= Helpers::csrfToken() ?>">
    <title><?= Helpers::e($title ?? 'LogBrasil TMS') ?></title>
    <link rel="icon" href="<?= $baseSafe ?>/assets/favicon.ico" type="image/png">
    <link rel="stylesheet" href="<?= htmlspecialchars(CONF_BASE_URL, ENT_QUOTES) ?>/assets/css/logbrasil.css?v=2">
    <link rel="stylesheet" href="<?= htmlspecialchars(CONF_BASE_URL, ENT_QUOTES) ?>/assets/css/lb-busy.css?v=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous">
    <?php if (! empty($headExtra ?? '')): ?>
        <?= $headExtra ?>
    <?php endif; ?>
</head>
<body>
<header class="lb-topnav">
    <a href="<?= $baseSafe ?>/inicio" class="lb-brand lb-nav-links" style="gap:10px;text-decoration:none">
        <img src="<?= $baseSafe ?>/assets/logo/logo.png" alt="LogBrasil" height="42" width="164" style="height:42px;width:auto;display:block">
    </a>
    <nav class="lb-nav-links" aria-label="Principal">
        <a class="<?= $isNav('inicio') ?>" href="<?= $baseSafe ?>/inicio"><i class="fa-solid fa-chart-line"></i> Início</a>

        <?php if ($monitorSomente): ?>
            <a class="<?= $isNav('viagens_abertas') ?>" href="<?= $baseSafe ?>/viagens/abertas" title="Acompanhar viagens já geradas em execução"><i class="fa-solid fa-clock"></i> Execução (abertas)</a>
            <a class="<?= $isNav('viagens_final') ?>" href="<?= $baseSafe ?>/viagens/finalizadas" title="Histórico e comprovantes de entrega"><i class="fa-solid fa-circle-check"></i> Histórico</a>
            <?php if ($linksRevDiv): ?>
                <a class="<?= $isNav('monitoramento_div') ?>" href="<?= $baseSafe ?>/monitoramento/divergencias"><i class="fa-solid fa-triangle-exclamation"></i> Divergências</a>
            <?php endif; ?>
        <?php else: ?>
            <?php if ($linksCadastro): ?>
                <a class="<?= $isNav('pedidos') ?>" href="<?= $baseSafe ?>/pedidos"><i class="fa-solid fa-box"></i> Pedidos</a>
                <a class="<?= $isNav('rotas') ?>" href="<?= $baseSafe ?>/rotas"><i class="fa-solid fa-map-location-dot"></i> Rotas</a>
                <a class="<?= $isNav('veiculos') ?>" href="<?= $baseSafe ?>/veiculos"><i class="fa-solid fa-truck-moving"></i> Veículos</a>
                <a class="<?= $isNav('motoristas') ?>" href="<?= $baseSafe ?>/motoristas"><i class="fa-solid fa-id-card"></i> Motoristas</a>
                <a class="<?= $isNav('roteirizador') ?>" href="<?= $baseSafe ?>/roteirizador" title="Montar rotas e gerar viagens a partir dos pedidos"><i class="fa-solid fa-bezier-curve"></i> Planejar rotas</a>
                <a class="<?= $isNav('unidade') ?>" href="<?= $baseSafe ?>/unidade"><i class="fa-solid fa-warehouse"></i> Unidade</a>
            <?php endif; ?>
            <a class="<?= $isNav('viagens_abertas') ?>" href="<?= $baseSafe ?>/viagens/abertas" title="Acompanhar viagens já geradas em execução"><i class="fa-solid fa-clock"></i> Execução (abertas)</a>
            <a class="<?= $isNav('viagens_final') ?>" href="<?= $baseSafe ?>/viagens/finalizadas" title="Histórico e comprovantes de entrega"><i class="fa-solid fa-circle-check"></i> Histórico</a>
            <?php if ($linksRevDiv): ?>
                <a class="<?= $isNav('monitoramento_div') ?>" href="<?= $baseSafe ?>/monitoramento/divergencias"><i class="fa-solid fa-triangle-exclamation"></i> Divergências</a>
            <?php endif; ?>
            <?php if ($podeAdminUsuarios): ?>
                <a class="<?= $isNav('usuarios') ?>" href="<?= $baseSafe ?>/usuarios"><i class="fa-solid fa-users-gear"></i> Usuários</a>
            <?php endif; ?>
        <?php endif; ?>
        <form method="post" action="<?= $baseSafe ?>/logout" style="margin:0;display:inline">
            <input type="hidden" name="_csrf" value="<?= Helpers::csrfToken() ?>">
            <button type="submit" class="lb-btn lb-btn-quiet" title="Encerrar sessão"><i class="fa-solid fa-right-from-bracket"></i></button>
        </form>
    </nav>
</header>
<main class="lb-shell">
    <?php if (! empty($_SESSION['flash_ok'])): ?>
        <div class="lb-alert lb-alert-success"><?= Helpers::e((string) $_SESSION['flash_ok']) ?></div>
        <?php unset($_SESSION['flash_ok']); endif; ?>
    <?php if (! empty($_SESSION['flash_error'])): ?>
        <div class="lb-alert lb-alert-danger"><?= Helpers::e((string) $_SESSION['flash_error']) ?></div>
        <?php unset($_SESSION['flash_error']); endif; ?>

    <?= $content ?>
</main>
<div id="lb-busy-mask" class="lb-busy-mask" aria-hidden="true" aria-busy="false">
    <div class="lb-busy-inner">
        <div class="lb-busy-spinner" aria-hidden="true"></div>
        <p class="lb-busy-text">Processando…</p>
    </div>
</div>
<script>
    window.LOGBR = <?= json_encode([
        'baseUrl' => CONF_BASE_URL,
        'csrf' => Helpers::csrfToken(),
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS) ?>;
</script>
<script defer src="<?= htmlspecialchars(CONF_BASE_URL, ENT_QUOTES) ?>/assets/js/logbrasil.js?v=9"></script>
<?php if (! empty($extraScripts ?? '')): ?>
    <?= $extraScripts ?>
<?php endif; ?>
</body>
</html>

