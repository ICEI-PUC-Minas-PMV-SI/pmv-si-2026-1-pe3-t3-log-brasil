<?php

use App\Core\Helpers;

$base = CONF_BASE_URL;
$linksCadastro = $linksCadastro ?? false;
$linksRevDiv = $linksRevDiv ?? false;
?>
<section class="lb-page-heading">
    <h1>Painel operacional</h1>
    <span class="lb-muted">Atalhos, indicadores e pendências do dia.</span>
</section>

<?php if ($linksCadastro): ?>
<section class="lb-dash-shortcuts" aria-label="Ações rápidas">
    <a href="<?= htmlspecialchars($base . '/pedidos', ENT_QUOTES) ?>" class="lb-dash-shortcut">
        <i class="fa-solid fa-box"></i>
        <span>Novo pedido</span>
        <small>Cadastra cliente (CPF) e endereço</small>
    </a>
    <a href="<?= htmlspecialchars($base . '/motoristas', ENT_QUOTES) ?>" class="lb-dash-shortcut">
        <i class="fa-solid fa-id-card"></i>
        <span>Novo motorista</span>
        <small>Acesso ao app em campo</small>
    </a>
    <a href="<?= htmlspecialchars($base . '/veiculos', ENT_QUOTES) ?>" class="lb-dash-shortcut">
        <i class="fa-solid fa-truck"></i>
        <span>Novo veículo</span>
        <small>Frota para planejamento</small>
    </a>
    <a href="<?= htmlspecialchars($base . '/roteirizador', ENT_QUOTES) ?>" class="lb-dash-shortcut">
        <i class="fa-solid fa-wand-magic-sparkles"></i>
        <span>Gerar viagem</span>
        <small>Assistente passo a passo</small>
    </a>
    <a href="<?= htmlspecialchars($base . '/viagens/abertas', ENT_QUOTES) ?>" class="lb-dash-shortcut">
        <i class="fa-solid fa-road"></i>
        <span>Em andamento</span>
        <small>Status das entregas</small>
    </a>
    <?php if ($linksRevDiv): ?>
    <a href="<?= htmlspecialchars($base . '/monitoramento/divergencias', ENT_QUOTES) ?>" class="lb-dash-shortcut">
        <i class="fa-solid fa-triangle-exclamation"></i>
        <span>Ocorrências</span>
        <small><?= (int) ($divPend ?? 0) ?> aguardando análise</small>
    </a>
    <?php endif; ?>
</section>
<?php else: ?>
<div class="lb-page-tip">
    <i class="fa-solid fa-circle-info"></i>
    <div>Use <strong>Em andamento</strong> para acompanhar viagens e status das paradas atualizados pelo app motorista.</div>
</div>
<?php endif; ?>

<div class="lb-grid-metrics">
    <div class="lb-card lb-card-accent">
        <div class="lb-metric-label"><i class="fa-solid fa-hourglass-half"></i> Pedidos aguardando viagem</div>
        <div class="lb-metric-value" style="color:var(--lb-secondary-yellow)"><?= Helpers::e((string) $pendentesRot) ?></div>
        <p class="lb-field-hint">Prontos para planejamento em Gerar viagem</p>
    </div>
    <div class="lb-card">
        <div class="lb-metric-label"><i class="fa-solid fa-road"></i> Viagens em andamento</div>
        <div class="lb-metric-value"><?= Helpers::e((string) $abertas) ?></div>
        <p class="lb-field-hint"><a href="<?= htmlspecialchars($base . '/viagens/abertas', ENT_QUOTES) ?>" style="color:inherit">Ver execução →</a></p>
    </div>
    <div class="lb-card">
        <div class="lb-metric-label"><i class="fa-solid fa-box-open"></i> Pedidos em viagem</div>
        <div class="lb-metric-value"><?= Helpers::e((string) ($emViagem ?? 0)) ?></div>
    </div>
    <?php if ($linksRevDiv): ?>
    <div class="lb-card" style="border-left:3px solid #ea580c">
        <div class="lb-metric-label"><i class="fa-solid fa-triangle-exclamation"></i> Ocorrências pendentes</div>
        <div class="lb-metric-value" style="color:#fb923c"><?= Helpers::e((string) ($divPend ?? 0)) ?></div>
    </div>
    <?php endif; ?>
    <div class="lb-card">
        <div class="lb-metric-label"><i class="fa-solid fa-clock-rotate-left"></i> Viagens finalizadas</div>
        <div class="lb-metric-value"><?= Helpers::e((string) $finalizadas) ?></div>
    </div>
    <div class="lb-card">
        <div class="lb-metric-label"><i class="fa-solid fa-truck"></i> Veículos ativos</div>
        <div class="lb-metric-value"><?= Helpers::e((string) $veiculosOk) ?></div>
    </div>
    <div class="lb-card">
        <div class="lb-metric-label"><i class="fa-solid fa-id-card"></i> Motoristas ativos</div>
        <div class="lb-metric-value"><?= Helpers::e((string) $motoristasOk) ?></div>
    </div>
</div>

<section class="lb-card" style="margin-top:8px">
    <h2 style="margin:0 0 10px;font-size:1.05rem"><i class="fa-solid fa-map-signs"></i> Como funciona o fluxo</h2>
    <ol style="margin:0;padding-left:20px;line-height:1.65;color:var(--lb-muted);font-size:.9rem">
        <li><strong style="color:var(--lb-high)">Cadastros</strong> — Pedidos (com CPF do cliente), veículos e motoristas.</li>
        <li><strong style="color:var(--lb-high)">Gerar viagem</strong> — Selecione rota, pedidos, veículo e motorista no assistente.</li>
        <li><strong style="color:var(--lb-high)">Em andamento</strong> — Motorista atualiza status no app; monitore paradas e ocorrências.</li>
        <li><strong style="color:var(--lb-high)">Histórico</strong> — Comprovantes, GPS e mapas das entregas concluídas.</li>
    </ol>
</section>
