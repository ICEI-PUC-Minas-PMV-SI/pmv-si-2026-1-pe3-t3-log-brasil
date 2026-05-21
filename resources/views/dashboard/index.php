<?php

use App\Core\Helpers;
?>
<section class="lb-page-heading">
    <h1>Visão da operação</h1>
    <span class="lb-muted">Métricas resumidas e contexto TMS.</span>
</section>

<section class="lb-hero-dash">
    <article class="lb-card lb-card-accent">
        <div class="lb-chip lb-chip-ok" style="display:inline-flex;align-items:center;gap:6px;margin-bottom:10px">
            <i class="fa-solid fa-signal"></i> Monitoramento vivo
        </div>
        <h2 style="margin:10px 0 8px;font-size:clamp(1.3rem,2.4vw,1.75rem);line-height:1.26">
            Planejamento, rotas inteligentes e acompanhamento de entrega em um só fluxo.
        </h2>
        <p class="lb-muted" style="line-height:1.55;margin:0">
            Esta base integra PostgreSQL (<strong>Supabase</strong>),
            coordenadas via <strong><a href="https://openrouteservice.org/" target="_blank" rel="noopener">OpenRouteService</a></strong>,
            agrupamentos por cidade/bairro e geração de viagens físicas até o encerramento.
        </p>
        <div style="display:flex;flex-wrap:wrap;gap:10px;margin-top:16px">
            <a href="<?= htmlspecialchars(CONF_BASE_URL . '/pedidos') ?>" class="lb-btn lb-btn-primary"><i class="fa-solid fa-plus"></i> Novo pedido</a>
            <a href="<?= htmlspecialchars(CONF_BASE_URL . '/roteirizador') ?>" class="lb-btn lb-btn-secondary" title="Gerar viagens a partir dos pedidos — não confundir com Execução (abertas)"><i class="fa-solid fa-draw-polygon"></i> Planejar rotas</a>
        </div>
    </article>
    <figure>
        <img src="https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?q=85&w=1200&auto=format&fit=crop"
             alt="Centro logístico ilustrativo com armazenagem e pallets"
             loading="lazy" width="1200" height="800">
        <figcaption class="lb-hero-badge">
            <i class="fa-solid fa-warehouse"></i> Depósito e expedição
        </figcaption>
    </figure>
</section>

<div class="lb-grid-metrics">
    <div class="lb-card lb-card-accent">
        <div class="lb-metric-label">Pedidos pendentes na rotera</div>
        <div class="lb-metric-value" style="color:var(--lb-secondary-yellow)"><?= Helpers::e((string) $pendentesRot) ?></div>
    </div>
    <div class="lb-card">
        <div class="lb-metric-label">Viagens abertas</div>
        <div class="lb-metric-value"><?= Helpers::e((string) $abertas) ?></div>
    </div>
    <div class="lb-card">
        <div class="lb-metric-label">Histórico de viagens</div>
        <div class="lb-metric-value"><?= Helpers::e((string) $finalizadas) ?></div>
    </div>
    <div class="lb-card">
        <div class="lb-metric-label">Veículos ativos</div>
        <div class="lb-metric-value"><?= Helpers::e((string) $veiculosOk) ?></div>
    </div>
    <div class="lb-card">
        <div class="lb-metric-label">Motoristas ativos</div>
        <div class="lb-metric-value"><?= Helpers::e((string) $motoristasOk) ?></div>
    </div>
</div>
