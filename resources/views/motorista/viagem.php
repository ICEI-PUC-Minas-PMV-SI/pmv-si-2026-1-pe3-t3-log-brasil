<?php

use App\Core\Helpers;

$vid = (int) ($v['id'] ?? 0);
$baseUrl = htmlspecialchars(CONF_BASE_URL, ENT_QUOTES);
$geo = [];
foreach ($pedidos ?? [] as $p) {
    $ppid = (int) ($p['pedido_id'] ?? $p['id'] ?? 0);
    $logrLine = trim((string) ($p['logradouro'] ?? ''));
    $numEl = trim((string) ($p['numero'] ?? ''));
    $comp = trim((string) ($p['complemento'] ?? ''));
    $bai = trim((string) ($p['bairro'] ?? ''));
    $cid = trim((string) ($p['cidade'] ?? ''));
    $ufEl = trim((string) ($p['uf'] ?? ''));
    $cep = trim((string) ($p['cep'] ?? ''));
    $endParts = array_filter([
        trim($logrLine . ($numEl !== '' ? ', ' . $numEl : '')),
        $comp,
        ($bai !== '' ? $bai : ''),
        trim($cid . ($ufEl !== '' ? '/' . $ufEl : '')),
        $cep !== '' ? 'CEP ' . $cep : '',
    ]);
    $geo[] = [
        'id' => $ppid,
        'lat' => (float) ($p['latitude'] ?? 0),
        'lng' => (float) ($p['longitude'] ?? 0),
        'label' => (string) ($p['nome_destinatario'] ?? ''),
        'end' => implode(' · ', $endParts),
        'href' => CONF_BASE_URL . '/motorista/viagem/' . $vid . '/pedido/' . $ppid,
    ];
}
?>

<div style="margin-bottom:10px">
    <a href="<?= $baseUrl ?>/motorista/viagens" class="lb-m-muted" style="text-decoration:none;font-size:.88rem"><i class="fa-solid fa-angle-left"></i> Viagens</a>
</div>

<section class="lb-m-card" style="padding:14px;margin-bottom:10px">
    <div class="lb-m-chip"><i class="fa-solid fa-hashtag"></i> <?= $vid ?></div>
    <div class="lb-mot-trip-hdr-title"><?= Helpers::e((string) ($v['rota_nome'] ?? '')) ?></div>
    <div class="lb-m-muted" style="margin-top:4px;color:var(--lb-mot-muted,var(--lb-m-muted))"><i class="fa-solid fa-weight-hanging"></i> <?= Helpers::e((string) ($v['peso_total_kg'] ?? '0')) ?> kg
        <?php $qEnt = (int) ($v['qt_entregas'] ?? 0); ?>
        <?php if ($qEnt > 0): ?> · <i class="fa-solid fa-box"></i> <?= $qEnt ?> <?= $qEnt === 1 ? 'entrega planej.' : 'entregas planej.' ?><?php endif; ?>
    </div>

    <?php if (! empty($div_pend)): ?>
        <div id="div-alert-panel" class="lb-mot-div-alert">
            <span class="lb-pulse-dot"></span>
            <div style="flex:1">
                <strong>Pendências</strong>
                — há <?= (int) $div_pend ?> divergência(ns) em análise pelo monitoramento.
                <?php if (! empty($div_lista_local)): ?>
                    <ul>
                        <?php foreach ($div_lista_local as $dv): ?>
                            <li><?= Helpers::e((string) ($dv['descricao'] ?? '')) ?> <span class="lb-m-muted" style="color:#fef3c7;opacity:.9">(NF <?= Helpers::e((string) ($dv['numero_pedido'] ?? '—')) ?>)</span></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if (! empty($pode_finalizar)): ?>
        <button type="button" class="lb-m-btn lb-m-btn-primary" style="margin-top:12px" id="mot-fin-trip">
            <i class="fa-solid fa-flag-checkered"></i> Finalizar viagem
        </button>
    <?php endif; ?>
</section>

<div class="lb-m-tabs" role="tablist">
    <button type="button" class="lb-m-tab" role="tab" aria-selected="true" data-mot-pane="lista"><i class="fa-solid fa-list"></i><span style="margin-left:4px">Lista</span></button>
    <button type="button" class="lb-m-tab" role="tab" aria-selected="false" data-mot-pane="mapa"><i class="fa-solid fa-map-pin"></i><span style="margin-left:4px">Mapa</span></button>
</div>

<div id="mot-pane-lista">
    <?php foreach ($pedidos ?? [] as $p): ?>
        <?php
        $__pid = (int) ($p['pedido_id'] ?? $p['id'] ?? 0);
        $hrefEsc = htmlspecialchars(CONF_BASE_URL . '/motorista/viagem/' . $vid . '/pedido/' . $__pid, ENT_QUOTES);

        $logrLine = trim((string) ($p['logradouro'] ?? ''));
        $numEl = trim((string) ($p['numero'] ?? ''));
        $comp = trim((string) ($p['complemento'] ?? ''));
        $bai = trim((string) ($p['bairro'] ?? ''));
        $cid = trim((string) ($p['cidade'] ?? ''));
        $ufEl = trim((string) ($p['uf'] ?? ''));
        $cep = trim((string) ($p['cep'] ?? ''));
        $ref = trim((string) ($p['referencia_entrega'] ?? ''));
        $addrLines = array_filter([
            trim($logrLine . ($numEl !== '' ? ', ' . $numEl : '')),
            $comp,
            $bai,
            trim($cid . ($ufEl !== '' ? '/' . $ufEl : '')),
            $cep !== '' ? 'CEP ' . $cep : '',
        ]);
        $enderecoBloco = $addrLines !== [] ? implode("\n", $addrLines) : 'Endereço não informado.';
        $qEntregas = max(1, (int) ($p['quantidade_entregas'] ?? 1));
        $pesoPed = isset($p['peso_total_kg']) ? number_format((float) $p['peso_total_kg'], 3, ',', '.') : '—';
        $tel = trim((string) ($p['telefone_destinatario'] ?? ''));
        $ordem = (int) ($p['ordem_entrega'] ?? 0);
        $nNf = (string) ($p['numero_pedido'] ?? '');
        ?>
        <article class="lb-m-card lb-mot-go" role="button" tabindex="0" style="cursor:pointer"
            data-mot-href="<?= $hrefEsc ?>"
            onclick="location.href=this.getAttribute('data-mot-href')"
            onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();location.href=this.getAttribute('data-mot-href');}">
            <div class="lb-mot-stop-num">Parada <?= $ordem > 0 ? $ordem : '—' ?> · NF <?= Helpers::e($nNf !== '' ? $nNf : '—') ?></div>
            <div class="lb-mot-stop-title"><?= Helpers::e((string) ($p['nome_destinatario'] ?? '')) ?></div>
            <div class="lb-mot-stop-addr" style="white-space:pre-line"><?= Helpers::e($enderecoBloco) ?></div>
            <?php if ($ref !== ''): ?>
                <div class="lb-m-muted" style="margin-top:8px;font-size:.82rem;color:var(--lb-mot-soft,var(--lb-m-muted))"><i class="fa-solid fa-signs-post"></i> <?= Helpers::e($ref) ?></div>
            <?php endif; ?>
            <?php if ($tel !== ''): ?>
                <div class="lb-m-muted" style="margin-top:6px;font-size:.84rem;color:var(--lb-mot-soft,var(--lb-m-muted))"><i class="fa-solid fa-phone"></i> <?= Helpers::e($tel) ?></div>
            <?php endif; ?>
            <div class="lb-mot-stop-meta">
                <span class="lb-mot-stop-pill"><i class="fa-solid fa-layer-group"></i> <?= $qEntregas ?> <?= $qEntregas === 1 ? 'volume (NF)' : 'volumes (NF)' ?></span>
                <span class="lb-mot-stop-pill"><i class="fa-solid fa-weight-hanging"></i> <?= Helpers::e($pesoPed) ?> kg</span>
            </div>
            <?php $pe = (string) ($p['parada_estado'] ?? 'pendente'); ?>
            <div style="margin-top:10px;display:flex;justify-content:flex-end">
                <?php if ($pe === 'pendente'): ?>
                    <span title="Aguardando" class="lb-mot-parada-ico lb-mot-parada-ico--pendente"><i class="fa-regular fa-clock"></i> <span class="lb-m-muted" style="font-size:.76rem;color:var(--lb-mot-soft,var(--lb-m-muted));margin-left:4px;font-weight:600">Pendente</span></span>
                <?php elseif ($pe === 'indo'): ?>
                    <span title="Em deslocamento" class="lb-mot-parada-ico lb-mot-parada-ico--indo"><i class="fa-solid fa-person-running"></i> <span style="font-size:.76rem;font-weight:650;margin-left:4px;color:var(--lb-mot-muted)">Em rota</span></span>
                <?php elseif ($pe === 'entrega_feita'): ?>
                    <span title="Concluída" class="lb-mot-parada-ico lb-mot-parada-ico--ok"><i class="fa-solid fa-circle-check"></i> <span style="font-size:.76rem;font-weight:650;margin-left:4px;color:var(--lb-mot-muted)">Feita</span></span>
                <?php elseif ($pe === 'divergencia_aguardando'): ?>
                    <span title="Divergência" class="lb-mot-parada-ico lb-mot-parada-ico--div"><i class="fa-solid fa-triangle-exclamation"></i> <span style="font-size:.76rem;font-weight:650;margin-left:4px;color:var(--lb-mot-muted)">Divergência</span></span>
                <?php else: ?>
                    <span class="lb-mot-parada-ico lb-mot-parada-ico--out"><i class="fa-solid fa-shield-heart"></i> <span style="font-size:.76rem;margin-left:4px;color:var(--lb-mot-muted)">Situação especial</span></span>
                <?php endif; ?>
            </div>
        </article>
    <?php endforeach; ?>
</div>

<div id="mot-pane-mapa" style="display:none;">
    <div id="mot-map" class="lb-mot-map"></div>
</div>

<script>
(function(){
  var CFG = window.LOGBR_M || {};
  var pts = <?= json_encode($geo, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP) ?>;

  document.querySelectorAll("[data-mot-pane]").forEach(function(btn){
    btn.addEventListener("click", function(){
      var pane = btn.getAttribute("data-mot-pane");
      if (pane === "mapa" && window.LB_MOT_BUSY) window.LB_MOT_BUSY.show("Carregando mapa…");
      document.querySelectorAll("[data-mot-pane]").forEach(function(b){ b.setAttribute("aria-selected","false"); });
      btn.setAttribute("aria-selected","true");
      document.getElementById("mot-pane-lista").style.display = pane === "lista" ? "" : "none";
      document.getElementById("mot-pane-mapa").style.display = pane === "mapa" ? "" : "none";
      window.setTimeout(function(){
        if (pane === "mapa" && window.__lbMotFit) window.__lbMotFit();
        if (pane === "mapa" && window.LB_MOT_BUSY) window.LB_MOT_BUSY.hide();
      }, pane === "mapa" ? 420 : 0);
    });
  });

  if (typeof L !== "undefined" && pts && pts.length) {
    var map = L.map("mot-map", { zoomControl: true });
    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
      maxZoom: 19,
      attribution: "&copy; OpenStreetMap",
    }).addTo(map);
    var gj = pts.filter(function(x){ return x.lat && x.lng; });
    var bounds = null;
    if (!gj.length) {
      map.setView([-14.235, -51.925], 4);
    } else {
      bounds = gj.map(function(p){ return [p.lat, p.lng]; });
      gj.forEach(function(p){
        var m = L.marker([p.lat, p.lng]).addTo(map);
        m.bindPopup("<strong>"+(p.label||"")+"</strong><br>"+(p.end||"")+"<br><a href='"+encodeURI(p.href)+"' style='display:inline-block;margin-top:8px;font-weight:bold'>Ir para parada →</a>", {maxWidth: 260});
      });
      if (bounds.length === 1) {
        map.setView(bounds[0], 13);
      } else {
        map.fitBounds(bounds, { padding: [40,40] });
      }
    }
    window.__lbMotFit = function(){ try {
      map.invalidateSize();
      if (bounds && bounds.length > 1) map.fitBounds(bounds, {padding:[40,40]});
      else if (bounds && bounds.length === 1) map.setView(bounds[0], 13);
    } catch(e){} };
  }

  document.getElementById("mot-fin-trip")?.addEventListener("click", async function(){
    if(!confirm("Encerrar viagem? Esta ação considera todas as paradas concluídas ou divergências aprovadas.")) return;
    window.LB_MOT_BUSY.show("Finalizando viagem…");
    try {
      var r = await fetch(CFG.baseUrl + "/api/motorista/viagem-finalizar", {
        method: "POST",
        headers:{ "Accept":"application/json","Content-Type":"application/json" },
        body: JSON.stringify({ _csrf: CFG.csrf, viagem_id: <?= json_encode($vid) ?> }),
      });
      var j = await r.json().catch(function(){return {};});
      if (!j.ok) { alert(j.message || "Não foi possível finalizar"); window.LB_MOT_BUSY.hide(); return; }
      location.href = CFG.baseUrl + "/motorista/viagens";
    } catch (ex) {
      window.LB_MOT_BUSY.hide();
      alert("Falha de conexão.");
    }
  });
})();
</script>
