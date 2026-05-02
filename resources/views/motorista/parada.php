<?php

use App\Core\Helpers;

$vid = (int) ($v['id'] ?? 0);
$pid = (int) ($pedido['pedido_id'] ?? $pedido['id'] ?? 0);
$est = (string) ($pedido['parada_estado'] ?? 'pendente');

?>
<div style="margin-bottom:10px">
    <a href="<?= htmlspecialchars(CONF_BASE_URL . '/motorista/viagem/' . $vid, ENT_QUOTES) ?>" class="lb-m-muted" style="font-size:.88rem;text-decoration:none"><i class="fa-solid fa-angle-left"></i> Viagem</a>
</div>

<section class="lb-m-card">
    <div class="lb-m-muted" style="margin-bottom:4px"><i class="fa-solid fa-user"></i> Destino</div>
    <div class="lb-mot-stop-title" style="font-size:1.12rem"><?= Helpers::e((string) ($pedido['nome_destinatario'] ?? '')) ?></div>
    <div class="lb-m-muted" style="margin-top:10px;line-height:1.45">
        <?= Helpers::e((string) ($pedido['logradouro'] ?? '')) ?> <?= Helpers::e((string) ($pedido['numero'] ?? '')) ?><br>
        <?= Helpers::e((string) ($pedido['bairro'] ?? '')) ?> · <?= Helpers::e((string) ($pedido['cidade'] ?? '')) ?>/<?= Helpers::e((string) ($pedido['uf'] ?? '')) ?>
    </div>
    <div style="margin-top:8px;"><span class="lb-m-chip"><?= Helpers::e('Pedido ' . (string) ($pedido['numero_pedido'] ?? '')) ?></span></div>
</section>

<section class="lb-m-card" style="margin-top:12px">
    <div class="lb-m-muted"><i class="fa-solid fa-boxes-stacked"></i> Itens da carga</div>
    <ul style="margin:8px 0 0;padding-left:18px;font-size:.9rem;line-height:1.5">
        <?php foreach ($itens ?? [] as $it): ?>
            <li><?= Helpers::e((string) ($it['descricao'] ?? '')) ?> × <?= Helpers::e((string) ($it['quantidade'] ?? '')) ?></li>
        <?php endforeach; ?>
        <?php if (empty($itens)): ?>
            <li class="lb-m-muted">Sem lista detalhada cadastrada.</li>
        <?php endif; ?>
    </ul>
</section>

<div id="mot-actions" style="margin-top:14px">
<?php if ($est === 'pendente'): ?>
    <button type="button" class="lb-m-btn lb-m-btn-primary" id="mot-indo">
        <i class="fa-solid fa-person-running"></i> Indo até o cliente
    </button>
<?php elseif ($est === 'indo'): ?>
    <div class="lb-mot-icon-row">
        <a class="lb-mot-ic navy" href="<?= htmlspecialchars(CONF_BASE_URL . '/motorista/viagem/' . $vid . '/pedido/' . $pid . '/entrega', ENT_QUOTES) ?>" style="text-decoration:none">
            <i class="fa-solid fa-box-archive"></i> Entrega
        </a>
        <button type="button" class="lb-mot-ic amber" id="mot-div-btn"><i class="fa-solid fa-triangle-exclamation"></i> Divergência</button>
    </div>
<?php elseif ($est === 'divergencia_aguardando'): ?>
    <div class="lb-m-card lb-mot-panel-warn" style="display:flex;gap:10px;border-width:1px">
        <i class="fa-solid fa-hourglass-start fa-xl lb-mot-soft-ico"></i>
        <div style="font-size:.9rem;color:var(--lb-mot-muted)">Divergência registrada.<br><span style="color:var(--lb-mot-fg);font-weight:650">A equipe está analisando.</span></div>
    </div>
<?php elseif ($est === 'entrega_feita'): ?>
    <div class="lb-m-card lb-mot-panel-ok" style="display:flex;align-items:center;gap:10px;border-width:1px">
        <i class="fa-solid fa-circle-check fa-2x" style="color:#86efac"></i><div style="font-weight:750;color:#fff">Parada encerrada com entrega realizada.</div>
    </div>
<?php else: ?>
    <div class="lb-m-card lb-m-muted"><i class="fa-solid fa-briefcase-medical"></i> Incidente registrado pela operação.</div>
<?php endif; ?>
</div>

<div id="mot-div-mask" style="display:none;position:fixed;inset:0;background:rgba(17,23,43,.52);z-index:200;align-items:center;justify-content:center;padding:16px">
    <div class="lb-m-card" style="max-width:440px;margin:auto">
        <strong><i class="fa-solid fa-triangle-exclamation"></i> Divergência</strong>
        <p class="lb-m-muted" style="margin:6px 0 8px;font-size:.86rem">Data/hora são registradas no sistema ao confirmar.</p>
        <textarea id="mot-div-txt" class="lb-m-input" rows="4" placeholder="Descreva o problema (ex.: ausência, mercadoria avariada)"></textarea>
        <div style="margin-top:10px;display:flex;gap:8px">
            <button type="button" class="lb-m-btn lb-m-btn-ghost" id="mot-div-cancel">Cancelar</button>
            <button type="button" class="lb-m-btn lb-m-btn-primary" id="mot-div-confirm">Registrar</button>
        </div>
    </div>
</div>

<script>
(function(){
  var CFG = window.LOGBR_M || {};
  var VID = <?= json_encode($vid) ?>;
  var PID = <?= json_encode($pid) ?>;

  async function lbMotJson(url, obj){
    window.LB_MOT_BUSY.show("Processando…");
    try {
      var r = await fetch(CFG.baseUrl + url, {
        method:"POST",
        headers:{"Accept":"application/json","Content-Type":"application/json"},
        body: JSON.stringify(Object.assign({_csrf:CFG.csrf}, obj||{})),
      });
      return await r.json().catch(function(){return {};});
    } finally {
      window.LB_MOT_BUSY.hide();
    }
  }

  document.getElementById("mot-indo")?.addEventListener("click", async function(){
    var j = await lbMotJson("/api/motorista/indo", {viagem_id:VID, pedido_id:PID});
    if (!j.ok) { alert(j.message || "Falhou"); return; }
    location.reload();
  });

  document.getElementById("mot-div-btn")?.addEventListener("click", function(){ document.getElementById("mot-div-mask").style.display="flex"; });
  document.getElementById("mot-div-cancel")?.addEventListener("click", function(){ document.getElementById("mot-div-mask").style.display="none"; });
  document.getElementById("mot-div-confirm")?.addEventListener("click", async function(){
    var txt = (document.getElementById("mot-div-txt")||{}).value || "";
    if (!txt.trim()) { alert("Descreva a divergência"); return; }
    var j = await lbMotJson("/api/motorista/divergencia", {viagem_id:VID, pedido_id:PID, descricao: txt.trim()});
    if (!j.ok) { alert(j.message||"Não registrou"); return; }
    location.reload();
  });
})();
</script>
