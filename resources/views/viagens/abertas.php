<?php

use App\Core\Helpers;

$headExtra = '<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="">' . "\n"
    . '<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>';
?>
<section class="lb-page-heading">
    <h1><?= Helpers::e($title) ?></h1>
    <span class="lb-muted">Viagens abertas: ações de mapa, itens dos pedidos, divergências e encerramento.</span>
</section>

<div class="lb-toolbar-cadastro" style="margin-bottom:18px">
    <div class="lb-card" style="padding:10px 12px;display:flex;align-items:center;gap:10px;flex:1;min-width:280px;max-width:560px">
        <i class="fa-solid fa-mobile-screen-button" style="color:var(--lb-secondary-yellow)" title="App motorista"></i>
        <div style="min-width:0;flex:1">
            <div class="lb-muted" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.04em">Link do motorista</div>
            <input id="link-motorista-app" class="lb-input" readonly value="<?= Helpers::e(CONF_BASE_URL . '/motorista/login') ?>" style="padding:8px 10px;margin-top:4px;width:100%">
        </div>
        <button type="button" id="btn-copy-motorista-app" class="lb-btn lb-btn-quiet" title="Copiar link de acesso"><i class="fa-regular fa-copy"></i></button>
    </div>
</div>

<div class="lb-grid-metrics" style="grid-template-columns:repeat(auto-fill,minmax(300px,1fr))">
    <?php foreach ($lista ?? [] as $v): ?>
        <div class="lb-card lb-route-card" data-viagem="<?= (int)$v['id'] ?>">
            <div class="lb-route-card-top">
                <div>
                    <div class="lb-chip">Viagem #<?= Helpers::e((string)$v['id']) ?></div>
                    <?php if (! empty($v['_div_pend'])): ?>
                        <div style="margin-top:8px;display:inline-flex;align-items:center;gap:6px;padding:6px 10px;border-radius:999px;background:rgba(245,173,74,.28);border:1px solid rgba(230,152,53,.65);font-size:.78rem;font-weight:650">
                            <span class="lb-pulse-dot"></span>
                            <?= (int) $v['_div_pend'] ?> divergência(s) aguardando aprovação
                        </div>
                    <?php endif; ?>
                    <h3 style="margin:6px 0 0;font-size:1rem"><?= Helpers::e($v['rota_nome']) ?></h3>
                </div>
            </div>
            <?php
            $vtotal = (int) ($v['_vp_total'] ?? 0);
            $vpend = (int) ($v['_vp_pend'] ?? 0);
            $vindo = (int) ($v['_vp_indo'] ?? 0);
            $vfeito = (int) ($v['_vp_feito'] ?? 0);
            $vdivp = (int) ($v['_vp_div_parada'] ?? 0);
            $vres = (int) ($v['_vp_resolv'] ?? 0);
            ?>
            <div class="lb-route-status-strip" style="margin-top:12px;padding:10px 12px;border-radius:10px;background:rgba(0,0,50,.06);border:1px solid rgba(0,0,139,.1);display:grid;grid-template-columns:repeat(auto-fill,minmax(108px,1fr));gap:8px;font-size:.78rem;line-height:1.35">
                <span title="Pontos na rota"><strong style="display:block;color:var(--lb-primary,#00008b)"><?= $vtotal ?></strong>Paradas</span>
                <span title="Sem saída"><strong style="display:block;color:#b45309"><?= $vpend ?></strong>Pendentes</span>
                <span title="A caminho / na entrega"><strong style="display:block;color:#15803d"><?= $vindo ?></strong>Em rota</span>
                <span title="Confirmadas com foto/assinatura"><strong style="display:block;color:#166534"><?= $vfeito ?></strong>Entregues</span>
                <?php if ($vdivp > 0): ?><span><strong style="display:block;color:#c2410c"><?= $vdivp ?></strong>Div. parada</span><?php endif; ?>
                <?php if ($vres > 0): ?><span><strong style="display:block;color:#4338ca"><?= $vres ?></strong>Div. ok</span><?php endif; ?>
            </div>
            <?php if (! empty($v['_pode_fin'])): ?>
                <div style="margin-top:8px;font-size:.78rem;font-weight:700;color:#166534;display:flex;align-items:center;gap:6px">
                    <i class="fa-solid fa-circle-check"></i> Todas as paradas quitadas — pronta para finalizar
                </div>
            <?php endif; ?>
            <div class="lb-route-metrics">
                <div><strong>Veículo</strong><br><?= Helpers::e($v['placa'] ?? '—') ?></div>
                <div><strong>Motorista</strong><br><?= Helpers::e($v['motorista_nome'] ?? '—') ?></div>
                <div><strong>Peso</strong><br><?= Helpers::e((string)$v['peso_total_kg']) ?> kg</div>
                <div><strong>Entregas</strong><br><?= Helpers::e((string)$v['qt_entregas']) ?></div>
                <div style="grid-column:1/-1"><strong>Largada prevista</strong><br><?= Helpers::e($v['data_largada_prevista'] ?? '—') ?></div>
                <div style="grid-column:1/-1"><strong>Lead</strong><br><?= Helpers::e($v['lead_planejado_texto'] ?? '—') ?></div>
            </div>
            <div class="lb-route-actions">
                <button type="button" class="lb-btn lb-btn-quiet lb-v-detalhes" data-id="<?= (int)$v['id'] ?>"><i class="fa-solid fa-list"></i> Detalhes</button>
                <button type="button" class="lb-btn lb-btn-quiet lb-v-mapa" data-id="<?= (int)$v['id'] ?>"><i class="fa-solid fa-location-dot"></i> Mapa</button>
                <button type="button" class="lb-btn lb-btn-quiet lb-v-div" data-id="<?= (int)$v['id'] ?>"><i class="fa-solid fa-triangle-exclamation"></i> Divergências</button>
                <button type="button" class="lb-btn lb-btn-secondary lb-v-fin" data-id="<?= (int)$v['id'] ?>"><i class="fa-solid fa-flag-checkered"></i> Finalizar</button>
            </div>
        </div>
    <?php endforeach; ?>
    <?php if (empty($lista)): ?>
        <p class="lb-muted">Nenhuma viagem em aberto.</p>
    <?php endif; ?>
</div>

<div id="modal-v-det" class="lb-modal-mask">
    <div class="lb-modal" style="max-width:1020px">
        <div class="lb-modal-head">
            <strong>Pedidos da viagem</strong>
            <button type="button" class="lb-btn lb-btn-quiet lb-modal-close"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="lb-modal-body">
            <p class="lb-muted" style="margin:0 0 10px;font-size:.82rem;line-height:1.45">
                Situação de cada pedido conforme atualização pelo app motorista. Use uma linha para ver os itens da NF no bloco inferior.
            </p>
            <div class="lb-table-shell" style="overflow-x:auto">
                <table class="lb-table" style="min-width:880px">
                    <thead><tr>
                        <th>#</th>
                        <th>NF / Pedido</th>
                        <th>Destinatário</th>
                        <th>Status parada</th>
                        <th>Saiu para entrega</th>
                        <th>Entrega registrada</th>
                        <th>Destino</th>
                    </tr></thead>
                    <tbody id="v-det-body"></tbody>
                </table>
            </div>
            <div id="v-itens-box" style="margin-top:12px;display:none">
                <button type="button" class="lb-btn lb-btn-quiet" id="v-itens-toggle"><i class="fa-solid fa-cubes"></i> Itens do pedido selecionado</button>
                <div id="v-itens-list" style="margin-top:8px" class="lb-table-shell"><table class="lb-table"><tbody></tbody></table></div>
            </div>
        </div>
    </div>
</div>

<div id="modal-v-mapa" class="lb-modal-mask">
    <div class="lb-modal" style="max-width:980px">
        <div class="lb-modal-head">
            <strong>Mapa das entregas</strong>
            <button type="button" class="lb-btn lb-btn-quiet lb-modal-close"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="lb-modal-body"><div id="map-viagem" style="height:460px;border-radius:10px;border:1px solid rgba(255,255,255,.12)"></div></div>
    </div>
</div>

<div id="modal-v-div" class="lb-modal-mask">
    <div class="lb-modal" style="max-width:720px">
        <div class="lb-modal-head">
            <strong>Divergências</strong>
            <button type="button" class="lb-btn lb-btn-quiet lb-modal-close"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="lb-modal-body">
            <ul id="v-div-ul" style="padding-left:18px;color:var(--lb-high);line-height:1.5"></ul>
            <hr style="border-color:rgba(255,255,255,.08);margin:16px 0">
            <label class="lb-muted">Registrar divergência</label>
            <textarea class="lb-input" id="v-div-txt" rows="3"></textarea>
            <input type="hidden" id="v-div-viagem-id">
            <button type="button" class="lb-btn lb-btn-primary" style="margin-top:8px" id="v-div-save"><i class="fa-solid fa-paper-plane"></i> Enviar</button>
        </div>
    </div>
</div>

<script>
(function () {
  var btn = document.getElementById('btn-copy-motorista-app');
  var inp = document.getElementById('link-motorista-app');
  if (!btn || !inp) return;
  btn.addEventListener('click', function () {
    var value = inp.value || '';
    if (!value) return;
    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(value).then(function () {
        btn.innerHTML = '<i class="fa-solid fa-check"></i>';
        setTimeout(function () { btn.innerHTML = '<i class="fa-regular fa-copy"></i>'; }, 1200);
      }).catch(function () { fallback(); });
    } else {
      fallback();
    }
    function fallback() {
      inp.select();
      try { document.execCommand('copy'); } catch (e) {}
    }
  });
})();
</script>
