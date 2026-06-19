<?php

use App\Core\Helpers;

$base = htmlspecialchars(CONF_BASE_URL, ENT_QUOTES);
$logoUrl = $base . '/assets/images/logbrasil-logo.svg';
?>
<div class="lb-login-page" id="lb-login-page">
    <div class="lb-login-bg-grid" aria-hidden="true"></div>
    <div class="lb-login-glow lb-login-glow-1" aria-hidden="true"></div>
    <div class="lb-login-glow lb-login-glow-2" aria-hidden="true"></div>

    <div class="lb-login-inner">
        <aside class="lb-login-hero">
            <p class="lb-login-kicker">Operação inteligente</p>
            <h1 class="lb-login-hero-title">Planeje rotas,<br>acompanhe entregas.</h1>
            <p class="lb-login-hero-desc">
                Visão única dos pedidos, da frota e das viagens. Decisões mais rápidas e clientes bem atendidos.
            </p>
            <ul class="lb-login-feature-list">
                <li>
                    <span class="lb-login-feature-ic" aria-hidden="true"><i class="fa-solid fa-chart-column"></i></span>
                    <span>Indicadores e status em tempo útil para a equipe.</span>
                </li>
                <li>
                    <span class="lb-login-feature-ic" aria-hidden="true"><i class="fa-solid fa-route"></i></span>
                    <span>Roteiros organizados antes do motorista sair da base.</span>
                </li>
                <li>
                    <span class="lb-login-feature-ic" aria-hidden="true"><i class="fa-solid fa-lock"></i></span>
                    <span>Acesso restrito apenas a quem faz parte da operação.</span>
                </li>
                <li>
                    <span class="lb-login-feature-ic" aria-hidden="true"><i class="fa-solid fa-map-location-dot"></i></span>
                    <span>Mapas e sequência das paradas sempre à mão.</span>
                </li>
            </ul>
        </aside>

        <div class="lb-login-card-wrap">
            <article class="lb-login-card">
                <header class="lb-login-card-head">
                    <div class="lb-login-brand-row">
                        <img src="<?= $logoUrl ?>" width="48" height="48" alt="">
                        <span class="lb-login-brand-text">LogBrasil</span>
                    </div>
                    <p class="lb-login-card-sub">Entre para continuar</p>
                </header>

                <?php if (! empty($_SESSION['flash_error'])): ?>
                    <div class="lb-alert-login" role="alert"><?= Helpers::e((string) $_SESSION['flash_error']) ?></div>
                    <?php unset($_SESSION['flash_error']); endif; ?>

                <?php if (! empty($_SESSION['flash_error_admin'])): ?>
                    <div class="lb-alert-login" style="background:rgba(0,0,139,.06);border-color:rgba(0,0,139,.22);color:#00008b">
                        <?= Helpers::e((string) $_SESSION['flash_error_admin']) ?>
                    </div>
                    <?php unset($_SESSION['flash_error_admin']); endif; ?>

                <form method="post" action="<?= $base ?>/login" autocomplete="on">
                    <input type="hidden" name="_csrf" value="<?= Helpers::csrfToken() ?>">

                    <div class="lb-form-group">
                        <label class="lb-form-label" for="email">E-mail</label>
                        <div class="lb-input-affix">
                            <i class="fa-regular fa-envelope"></i>
                            <input id="email" type="email" name="email" placeholder="nome@empresa.com.br" required
                                   autocomplete="username" inputmode="email">
                        </div>
                    </div>

                    <div class="lb-form-group">
                        <label class="lb-form-label" for="senha">Senha</label>
                        <div class="lb-input-affix lb-pass-affix">
                            <i class="fa-solid fa-lock"></i>
                            <input id="senha" type="password" name="senha" placeholder="••••••••" required
                                   autocomplete="current-password">
                            <button type="button" class="lb-pass-toggle" id="lb-pass-toggle"
                                    aria-label="Mostrar ou ocultar senha"><i class="fa-regular fa-eye"></i></button>
                        </div>
                    </div>

                    <div class="lb-login-meta-row">
                        <label>
                            <input type="checkbox" name="remember" value="1"> Lembrar-me
                        </label>
                        <a href="#" title="Para redefinir o acesso, fale com o gestor da operação."
                           onclick="event.preventDefault()">Esqueceu a senha?</a>
                    </div>

                    <button type="submit" class="lb-btn-enter">
                        <i class="fa-solid fa-right-to-bracket"></i>
                        Entrar no sistema
                    </button>
                </form>

                <footer class="lb-login-foot">
                    <a href="<?= $base ?>/">Voltar à página inicial</a>
                    <a href="#" onclick="event.preventDefault()">Política de privacidade</a>
                </footer>
            </article>
        </div>
    </div>
</div>

<script>
(function () {
  var page = document.getElementById("lb-login-page");
  if (!page) return;
  requestAnimationFrame(function () {
    page.classList.add("is-ready");
  });
  var pwd = document.getElementById("senha");
  var btn = document.getElementById("lb-pass-toggle");
  if (btn && pwd) {
    btn.addEventListener("click", function () {
      var hide = pwd.type === "password";
      pwd.type = hide ? "text" : "password";
      btn.innerHTML = hide
        ? '<i class="fa-regular fa-eye-slash"></i>'
        : '<i class="fa-regular fa-eye"></i>';
      btn.setAttribute(
        "aria-label",
        hide ? "Ocultar senha" : "Mostrar senha"
      );
    });
  }
})();
</script>
