<?php

use App\Core\Helpers;

$base = CONF_BASE_URL;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
    <title><?= Helpers::e($title ?? 'Motorista LogBrasil') ?></title>
    <link rel="icon" type="image/png" href="<?= htmlspecialchars($base . '/assets/favicon.ico', ENT_QUOTES) ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars($base . '/assets/css/login.css', ENT_QUOTES) ?>?v=3">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous">
</head>
<body class="lb-login-root">
<div class="lb-login-page" id="lb-mot-login-page">
    <div class="lb-login-bg-grid" aria-hidden="true"></div>
    <div class="lb-login-glow lb-login-glow-1" aria-hidden="true"></div>
    <div class="lb-login-glow lb-login-glow-2" aria-hidden="true"></div>

    <div class="lb-login-inner">
        <aside class="lb-login-hero">
            <p class="lb-login-kicker"><i class="fa-solid fa-truck-fast"></i> App do motorista</p>
            <h1 class="lb-login-hero-title">Sua rota,<br>no bolso.</h1>
            <p class="lb-login-hero-desc">
                Lista de paradas, mapa, status em tempo real e registro de entrega com foto e assinatura — alinhado à operação LogBrasil.
            </p>
            <ul class="lb-login-feature-list">
                <li>
                    <span class="lb-login-feature-ic" aria-hidden="true"><i class="fa-solid fa-list-check"></i></span>
                    <span>Viagens abertas e sequência das entregas sempre visíveis.</span>
                </li>
                <li>
                    <span class="lb-login-feature-ic" aria-hidden="true"><i class="fa-solid fa-location-crosshairs"></i></span>
                    <span>Mapa e navegação entre destinos sem sair do app.</span>
                </li>
                <li>
                    <span class="lb-login-feature-ic" aria-hidden="true"><i class="fa-solid fa-pen-nib"></i></span>
                    <span>Divergências e conferência de entrega com registro objetivo.</span>
                </li>
                <li>
                    <span class="lb-login-feature-ic" aria-hidden="true"><i class="fa-solid fa-shield-halved"></i></span>
                    <span>Acesso somente com CPF e senha definidos pela gestão da transportadora.</span>
                </li>
            </ul>
        </aside>

        <div class="lb-login-card-wrap">
            <?= $content ?>
        </div>
    </div>
</div>
<script>
(function () {
    var t = document.getElementById('lb-mot-pass-toggle');
    var i = document.getElementById('lb-mot-senha');
    if (!t || !i) return;
    t.addEventListener('click', function () {
        var show = i.type === 'password';
        i.type = show ? 'text' : 'password';
        t.setAttribute('aria-label', show ? 'Ocultar senha' : 'Mostrar senha');
        t.innerHTML = show ? '<i class="fa-regular fa-eye-slash"></i>' : '<i class="fa-regular fa-eye"></i>';
    });
})();
</script>
</body>
</html>
