<?php

use App\Core\Helpers;

$headExtra = '<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="">' . "\n"
    . '<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>';
$extraScripts = '<script type="application/json" id="lb-fleet">' . htmlspecialchars(json_encode([
    'veiculos' => $veiculos ?? [],
    'motoristas' => $motoristas ?? [],
    'rotas' => $rotasOpcoes ?? [],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_NOQUOTES, 'UTF-8') . '</script>';

$help = static fn (string $t): string => '<span class="lb-help" tabindex="0" role="button" aria-label="Ajuda" data-lb-tip="' . Helpers::e($t) . '">?</span>';

?>
<section class="lb-page-heading">
    <h1><?= Helpers::e($title) ?></h1>
    <span class="lb-muted">Monte viagens a partir dos pedidos pendentes por rota territorial.</span>
</section>

<div class="lb-page-tip">
    <i class="fa-solid fa-lightbulb"></i>
    <div>
        <strong>Fluxo:</strong> escolha uma rota abaixo → <em>Detalhes</em> (opcional) → <em>Gerar viagem</em> abre o assistente em 6 etapas.
        Depois acompanhe em <a href="<?= Helpers::e(CONF_BASE_URL . '/viagens/abertas') ?>" style="color:var(--lb-secondary-yellow)">Execução → Em andamento</a>.
        <?= $help('Pedidos já vinculados a viagem aberta não aparecem aqui.') ?>
    </div>
</div>

<div id="roteirizador-cards" class="lb-grid-metrics" style="grid-template-columns:repeat(auto-fill,minmax(300px,1fr))">
    <p class="lb-muted" id="roteirizador-placeholder"><i class="fa-solid fa-spinner fa-spin"></i> Carregando rotas pendentes…</p>
</div>

<div id="modal-rot-detalhe" class="lb-modal-mask">
    <div class="lb-modal" style="max-width:1100px">
        <div class="lb-modal-head">
            <strong>Pedidos da rota</strong>
            <button type="button" class="lb-btn lb-btn-quiet lb-modal-close" aria-label="Fechar"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="lb-modal-body">
            <div class="lb-toolbar-cadastro">
                <select class="lb-input" id="rot-mudar-rota" style="max-width:280px" aria-label="Mover para outra rota"></select>
                <button type="button" class="lb-btn lb-btn-secondary" id="rot-aplicar-rota"><i class="fa-solid fa-shuffle"></i> Mover selecionados</button>
            </div>
            <div class="lb-table-shell">
                <table class="lb-table">
                    <thead><tr><th><input type="checkbox" id="rot-sel-all" aria-label="Selecionar todos"></th><th>Número</th><th>Destinatário</th><th>Peso</th><th>Entregas</th><th>Bairro</th></tr></thead>
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
            <button type="button" class="lb-btn lb-btn-quiet lb-modal-close" aria-label="Fechar"><i class="fa-solid fa-xmark"></i></button>
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
    <div class="lb-modal" style="max-width:580px">
        <div class="lb-modal-head">
            <strong id="wiz-title">Assistente — gerar viagem</strong>
            <button type="button" class="lb-btn lb-btn-quiet lb-modal-close" aria-label="Fechar"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="lb-modal-body">
            <div class="lb-wizard-progress" id="wiz-progress" aria-hidden="true"></div>
            <div class="lb-wizard-steps-label" id="wiz-step-label">Etapa 1 de 6</div>
            <div class="lb-steps" id="wiz-steps"></div>

            <div id="wiz-pane-rota">
                <p class="lb-muted" style="margin:0 0 10px;font-size:.88rem">Confirme a rota selecionada. Todos os pedidos pendentes desta rota entrarão na viagem (você pode desmarcar na próxima etapa).</p>
                <div class="lb-wizard-review-row"><strong>Rota</strong><span id="wiz-rota-nome">—</span></div>
                <div class="lb-wizard-review-row"><strong>Pedidos na fila</strong><span id="wiz-rota-qtd">—</span></div>
            </div>
            <div id="wiz-pane-pedidos" style="display:none">
                <p class="lb-muted" style="margin:0 0 8px;font-size:.86rem">Marque os pedidos que farão parte desta viagem:</p>
                <div class="lb-table-shell" style="max-height:220px">
                    <table class="lb-table"><tbody id="wiz-pedidos-tbody"></tbody></table>
                </div>
            </div>
            <div id="wiz-pane-veiculo" style="display:none">
                <label class="lb-field-label">Veículo <?= $help('Placa da frota que executará a viagem.') ?></label>
                <select class="lb-input" id="wiz-veiculo" aria-label="Veículo"></select>
            </div>
            <div id="wiz-pane-motorista" style="display:none">
                <label class="lb-field-label">Motorista <?= $help('Condutor que verá a viagem no app mobile.') ?></label>
                <select class="lb-input" id="wiz-motorista" aria-label="Motorista"></select>
            </div>
            <div id="wiz-pane-revisao" style="display:none">
                <p class="lb-muted" style="margin:0 0 10px">Revise o resumo antes de confirmar:</p>
                <div class="lb-wizard-review" id="wiz-resumo"></div>
            </div>
            <div id="wiz-pane-confirm" style="display:none">
                <label class="lb-field-label">Data e hora de saída prevista</label>
                <input class="lb-input" type="datetime-local" id="wiz-data" aria-label="Saída prevista">
                <label class="lb-field-label" style="margin-top:12px">Tempo planejado da viagem <?= $help('Texto livre: ex. “4h”, “retorno até 18h”.') ?></label>
                <input class="lb-input" id="wiz-lead" placeholder="Ex.: 6 horas · retorno previsto 17h">
                <label class="lb-field-label" style="margin-top:12px">Observação interna</label>
                <textarea class="lb-input" id="wiz-obs" rows="2" placeholder="Instruções para monitoramento ou motorista"></textarea>
            </div>

            <div style="margin-top:16px;display:flex;justify-content:space-between;gap:8px">
                <button type="button" class="lb-btn lb-btn-quiet" id="wiz-back" style="display:none"><i class="fa-solid fa-arrow-left"></i> Voltar</button>
                <span style="flex:1"></span>
                <button type="button" class="lb-btn lb-btn-primary" id="wiz-next">Avançar <i class="fa-solid fa-arrow-right"></i></button>
            </div>
        </div>
    </div>
</div>
