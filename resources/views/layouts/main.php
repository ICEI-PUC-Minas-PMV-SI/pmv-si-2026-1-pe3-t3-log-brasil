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
    <link rel="stylesheet" href="<?= htmlspecialchars(CONF_BASE_URL, ENT_QUOTES) ?>/assets/css/logbrasil.css?v=3">
    <link rel="stylesheet" href="<?= htmlspecialchars(CONF_BASE_URL, ENT_QUOTES) ?>/assets/css/lb-ux.css?v=2">
    <link rel="stylesheet" href="<?= htmlspecialchars(CONF_BASE_URL, ENT_QUOTES) ?>/assets/css/lb-busy.css?v=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous">
    <?php if (! empty($headExtra ?? '')): ?>
        <?= $headExtra ?>
    <?php endif; ?>
</head>
<body>
<header class="lb-topnav">
    <a href="<?= $baseSafe ?>/inicio" class="lb-brand lb-nav-links" style="gap:10px;text-decoration:none" title="Painel inicial">
        <img src="<?= $baseSafe ?>/assets/logo/logo.png" alt="LogBrasil" height="42" width="164" style="height:42px;width:auto;display:block">
    </a>
    <nav class="lb-nav-groups" aria-label="Principal">
        <a class="<?= $isNav('inicio') ?>" href="<?= $baseSafe ?>/inicio" title="Resumo e atalhos"><i class="fa-solid fa-house"></i> Início</a>

        <?php if ($monitorSomente): ?>
            <div class="lb-nav-group" role="group" aria-label="Execução">
                <span class="lb-nav-group-label"><i class="fa-solid fa-truck-ramp-box"></i> Execução</span>
                <a class="<?= $isNav('viagens_abertas') ?>" href="<?= $baseSafe ?>/viagens/abertas" title="Viagens em andamento — status atualizado pelo app motorista"><i class="fa-solid fa-road"></i> Em andamento</a>
                <a class="<?= $isNav('viagens_final') ?>" href="<?= $baseSafe ?>/viagens/finalizadas" title="Histórico e comprovantes"><i class="fa-solid fa-clock-rotate-left"></i> Histórico</a>
                <?php if ($linksRevDiv): ?>
                    <a class="<?= $isNav('monitoramento_div') ?>" href="<?= $baseSafe ?>/monitoramento/divergencias" title="Ocorrências aguardando análise"><i class="fa-solid fa-triangle-exclamation"></i> Ocorrências</a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <?php if ($linksCadastro): ?>
                <div class="lb-nav-group" role="group" aria-label="Cadastros">
                    <span class="lb-nav-group-label"><i class="fa-solid fa-folder-open"></i> Cadastros</span>
                    <a class="<?= $isNav('pedidos') ?> lb-nav-action" href="<?= $baseSafe ?>/pedidos" title="Pedidos e clientes (via CPF)"><i class="fa-solid fa-box"></i> Pedidos</a>
                    <a class="<?= $isNav('veiculos') ?>" href="<?= $baseSafe ?>/veiculos" title="Frota disponível para viagens"><i class="fa-solid fa-truck"></i> Veículos</a>
                    <a class="<?= $isNav('motoristas') ?>" href="<?= $baseSafe ?>/motoristas" title="Condutores do app motorista"><i class="fa-solid fa-id-card"></i> Motoristas</a>
                    <a class="<?= $isNav('rotas') ?>" href="<?= $baseSafe ?>/rotas" title="Áreas atendidas (cidades/bairros)"><i class="fa-solid fa-map"></i> Rotas</a>
                    <a class="<?= $isNav('unidade') ?>" href="<?= $baseSafe ?>/unidade" title="Depósito de origem das viagens"><i class="fa-solid fa-warehouse"></i> Unidade</a>
                </div>
                <div class="lb-nav-group" role="group" aria-label="Planejamento">
                    <span class="lb-nav-group-label"><i class="fa-solid fa-route"></i> Planejamento</span>
                    <a class="<?= $isNav('roteirizador') ?> lb-nav-action" href="<?= $baseSafe ?>/roteirizador" title="Agrupar pedidos e gerar viagem — assistente passo a passo"><i class="fa-solid fa-wand-magic-sparkles"></i> Gerar viagem</a>
                </div>
            <?php endif; ?>
            <div class="lb-nav-group" role="group" aria-label="Execução">
                <span class="lb-nav-group-label"><i class="fa-solid fa-satellite-dish"></i> Execução</span>
                <a class="<?= $isNav('viagens_abertas') ?>" href="<?= $baseSafe ?>/viagens/abertas" title="Acompanhar entregas e status das paradas"><i class="fa-solid fa-road"></i> Em andamento</a>
                <a class="<?= $isNav('viagens_final') ?>" href="<?= $baseSafe ?>/viagens/finalizadas" title="Viagens encerradas e comprovantes"><i class="fa-solid fa-clock-rotate-left"></i> Histórico</a>
                <?php if ($linksRevDiv): ?>
                    <a class="<?= $isNav('monitoramento_div') ?>" href="<?= $baseSafe ?>/monitoramento/divergencias" title="Revisar ocorrências do motorista"><i class="fa-solid fa-triangle-exclamation"></i> Ocorrências</a>
                <?php endif; ?>
            </div>
            <?php if ($podeAdminUsuarios): ?>
                <div class="lb-nav-group" role="group" aria-label="Administração">
                    <span class="lb-nav-group-label"><i class="fa-solid fa-gear"></i> Admin</span>
                    <a class="<?= $isNav('usuarios') ?>" href="<?= $baseSafe ?>/usuarios" title="Perfis de acesso ao painel"><i class="fa-solid fa-users-gear"></i> Usuários</a>
                </div>
            <?php endif; ?>
        <?php endif; ?>
        <form method="post" action="<?= $baseSafe ?>/logout" style="margin:0">
            <input type="hidden" name="_csrf" value="<?= Helpers::csrfToken() ?>">
            <button type="submit" class="lb-btn lb-btn-quiet" title="Encerrar sessão" aria-label="Sair"><i class="fa-solid fa-right-from-bracket"></i></button>
        </form>
    </nav>
</header>
<main class="lb-shell">
    <?php if (! empty($_SESSION['flash_ok'])): ?>
        <div class="lb-alert lb-alert-success" role="status"><?= Helpers::e((string) $_SESSION['flash_ok']) ?></div>
        <?php unset($_SESSION['flash_ok']); endif; ?>
    <?php if (! empty($_SESSION['flash_error'])): ?>
        <div class="lb-alert lb-alert-danger" role="alert"><?= Helpers::e((string) $_SESSION['flash_error']) ?></div>
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
<script defer src="<?= htmlspecialchars(CONF_BASE_URL, ENT_QUOTES) ?>/assets/js/lb-ux.js?v=2"></script>
<script defer src="<?= htmlspecialchars(CONF_BASE_URL, ENT_QUOTES) ?>/assets/js/logbrasil.js?v=10"></script>
<?php if (! empty($extraScripts ?? '')): ?>
    <?= $extraScripts ?>
<?php endif; ?>
</body>
</html>
