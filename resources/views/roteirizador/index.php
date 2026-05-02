<?php

use App\Core\Helpers;

$headExtra = '<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="">' . "\n"
    . '<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>';
$extraScripts = '<script type="application/json" id="lb-fleet">' . htmlspecialchars(json_encode([
    'veiculos' => $veiculos ?? [],
    'motoristas' => $motoristas ?? [],
    'rotas' => $rotasOpcoes ?? [],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_NOQUOTES, 'UTF-8') . '</script>';
?>
<section class="lb-page-heading">
    <h1><?= Helpers::e($title) ?></h1>
    <span class="lb-muted">Somente pedidos sem viagem aberta. Distância estimada via OpenRouteService (perfil carro).</span>
</section>

<div id="roteirizador-cards" class="lb-grid-metrics" style="grid-template-columns:repeat(auto-fill,minmax(300px,1fr))">
    <p class="lb-muted" id="roteirizador-placeholder">Carregando rotas pendentes…</p>
</div>

<div id="modal-rot-detalhe" class="lb-modal-mask">
    <div class="lb-modal" style="max-width:1100px">
        <div class="lb-modal-head">
            <strong>Pedidos da rota</strong>
            <button type="button" class="lb-btn lb-btn-quiet lb-modal-close"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="lb-modal-body">
            <div class="lb-toolbar-cadastro">
                <select class="lb-input" id="rot-mudar-rota" style="max-width:280px"></select>
                <button type="button" class="lb-btn lb-btn-secondary" id="rot-aplicar-rota"><i class="fa-solid fa-shuffle"></i> Mover selecionados</button>
            </div>
            <div class="lb-table-shell">
                <table class="lb-table">
                    <thead><tr><th><input type="checkbox" id="rot-sel-all"></th><th>Número</th><th>Destinatário</th><th>Peso</th><th>Entregas</th><th>Bairro</th></tr></thead>
                    <tbody id="rot-tbody-pedidos"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div id="modal-rot-mapa" class="lb-modal-mask">
    <div class="lb-modal" style="max-width:1100px">
        <div class="lb-modal-head">
            <strong>Mapa de paradas</strong>
            <button type="button" class="lb-btn lb-btn-quiet lb-modal-close"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="lb-modal-body">
            <div class="lb-map-rows">
                <div id="map-rot" class="lb-map-slot" style="min-height:420px"></div>
                <aside class="lb-seq-panel" aria-label="Sequência sugerida de paradas">
                    <div class="lb-seq-panel-head">
                        <span class="lb-seq-panel-kicker"><i class="fa-solid fa-route"></i> Ordem de visita</span>
                        <h4 class="lb-seq-panel-title">Sequência sugerida</h4>
                        <p class="lb-seq-panel-meta" id="rot-seq-meta">—</p>
                    </div>
                    <div class="lb-seq-steps" id="rot-seq-list" role="list"></div>
                </aside>
            </div>
        </div>
    </div>
</div>

<div id="modal-viagem-wizard" class="lb-modal-mask">
    <div class="lb-modal" style="max-width:520px">
        <div class="lb-modal-head">
            <strong id="wiz-title">Gerar viagem</strong>
            <button type="button" class="lb-btn lb-btn-quiet lb-modal-close"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="lb-modal-body">
            <div class="lb-steps" id="wiz-steps"></div>
            <div id="wiz-pane-veiculo">
                <label class="lb-muted">Veículo</label>
                <select class="lb-input" id="wiz-veiculo"></select>
            </div>
            <div id="wiz-pane-motorista" style="display:none">
                <label class="lb-muted">Motorista</label>
                <select class="lb-input" id="wiz-motorista"></select>
            </div>
            <div id="wiz-pane-confirm" style="display:none">
                <label class="lb-muted">Data/hora largada prevista</label>
                <input class="lb-input" type="datetime-local" id="wiz-data">
                <label class="lb-muted" style="margin-top:10px;display:block">Lead planejado (texto livre)</label>
                <input class="lb-input" id="wiz-lead">
                <label class="lb-muted" style="margin-top:10px;display:block">Observação</label>
                <textarea class="lb-input" id="wiz-obs" rows="2"></textarea>
            </div>
            <div style="margin-top:16px;display:flex;justify-content:space-between;gap:8px">
                <button type="button" class="lb-btn lb-btn-quiet" id="wiz-back" style="display:none">Voltar</button>
                <span style="flex:1"></span>
                <button type="button" class="lb-btn lb-btn-primary" id="wiz-next">Avançar</button>
            </div>
        </div>
    </div>
</div>
