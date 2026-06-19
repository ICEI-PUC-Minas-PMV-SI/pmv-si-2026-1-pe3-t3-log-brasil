<?php

use App\Core\Helpers;

$base = htmlspecialchars(CONF_BASE_URL, ENT_QUOTES);
$logoUrl = CONF_BASE_URL . '/assets/images/logbrasil-logo.svg';
?>
<article class="lb-login-card">
    <header class="lb-login-card-head">
        <div class="lb-login-brand-row">
            <img src="<?= htmlspecialchars($logoUrl, ENT_QUOTES) ?>" width="48" height="48" alt="">
            <span class="lb-login-brand-text">Motorista</span>
        </div>
        <p class="lb-login-card-sub">Entre com seu CPF e senha cadastrados no TMS</p>
    </header>

    <?php if (! empty($_SESSION['flash_error'])): ?>
        <div class="lb-alert-login" role="alert"><?= Helpers::e((string) $_SESSION['flash_error']) ?></div>
        <?php unset($_SESSION['flash_error']); endif; ?>

    <form method="post" action="<?= $base ?>/motorista/login" autocomplete="on">
        <input type="hidden" name="_csrf" value="<?= Helpers::csrfToken() ?>">

        <div class="lb-form-group">
            <label class="lb-form-label" for="mot-cpf">CPF</label>
            <div class="lb-input-affix">
                <i class="fa-regular fa-id-card"></i>
                <input id="mot-cpf" name="cpf" inputmode="numeric" maxlength="14" placeholder="000.000.000-00" required
                       autocomplete="username">
            </div>
        </div>

        <div class="lb-form-group">
            <label class="lb-form-label" for="lb-mot-senha">Senha</label>
            <div class="lb-input-affix lb-pass-affix">
                <i class="fa-solid fa-lock"></i>
                <input id="lb-mot-senha" name="senha" type="password" placeholder="••••••••" required
                       autocomplete="current-password">
                <button type="button" class="lb-pass-toggle" id="lb-mot-pass-toggle" aria-label="Mostrar ou ocultar senha">
                    <i class="fa-regular fa-eye"></i>
                </button>
            </div>
        </div>

        <button class="lb-btn-enter" type="submit">
            <i class="fa-solid fa-truck-moving"></i> Entrar
        </button>
    </form>

    <p class="lb-login-foot" style="border-top:0;margin-top:18px;padding-top:0">
        <span style="display:block;font-size:0.82rem;color:#6b7289;line-height:1.5">
            A senha é definida no painel interno em <strong>Gestão › Motoristas</strong> antes do primeiro acesso.
        </span>
    </p>
</article>
