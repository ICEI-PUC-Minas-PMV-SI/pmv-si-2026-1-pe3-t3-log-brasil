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
    <p class="lb-m-muted" style="font-size:.82rem;margin:0 0 10px">Escolha uma ação para esta parada:</p>
    <div class="lb-mot-icon-row">
        <a class="lb-mot-ic navy" href="<?= htmlspecialchars(CONF_BASE_URL . '/motorista/viagem/' . $vid . '/pedido/' . $pid . '/entrega', ENT_QUOTES) ?>" style="text-decoration:none" title="RF07 — Comprovante de entrega">
            <i class="fa-solid fa-file-signature"></i> Comprovante
        </a>
        <button type="button" class="lb-mot-ic amber" id="mot-div-btn" title="RF06 — Registrar ocorrência"><i class="fa-solid fa-triangle-exclamation"></i> Ocorrência</button>
    </div>
<?php elseif ($est === 'divergencia_aguardando'): ?>
    <div class="lb-m-card lb-mot-panel-warn" style="display:flex;gap:10px;border-width:1px">
        <i class="fa-solid fa-hourglass-start fa-xl lb-mot-soft-ico"></i>
        <div style="font-size:.9rem;color:var(--lb-mot-muted)">Ocorrência registrada.<br><span style="color:var(--lb-mot-fg);font-weight:650">A equipe está analisando.</span></div>
    </div>
<?php elseif ($est === 'entrega_feita'): ?>
    <div class="lb-m-card lb-mot-panel-ok" style="display:flex;align-items:center;gap:10px;border-width:1px">
        <i class="fa-solid fa-circle-check fa-2x" style="color:#86efac"></i><div style="font-weight:750;color:#fff">Parada encerrada com entrega e comprovante.</div>
    </div>
<?php else: ?>
    <div class="lb-m-card lb-m-muted"><i class="fa-solid fa-briefcase-medical"></i> Incidente registrado pela operação.</div>
<?php endif; ?>
</div>

<div id="mot-div-mask" style="display:none;position:fixed;inset:0;background:rgba(17,23,43,.52);z-index:200;align-items:center;justify-content:center;padding:16px">
    <div class="lb-m-card" style="max-width:440px;margin:auto">
        <div class="lb-m-chip risk" style="margin-bottom:8px"><i class="fa-solid fa-triangle-exclamation"></i> RF06 — Registrar ocorrência</div>
        <p class="lb-m-muted" style="margin:0 0 8px;font-size:.86rem">Descreva o problema. Data/hora são registradas automaticamente.</p>
        <textarea id="mot-div-txt" class="lb-m-input" rows="4" placeholder="Ex.: cliente ausente, mercadoria avariada, endereço incorreto"></textarea>
        <div class="lb-m-muted" style="margin:12px 0 6px"><i class="fa-solid fa-camera"></i> Foto da ocorrência (opcional)</div>
        <input type="file" id="mot-div-foto" accept="image/*" capture="environment" class="lb-m-input">
        <div style="margin-top:10px;display:flex;gap:8px">
            <button type="button" class="lb-m-btn lb-m-btn-ghost" id="mot-div-cancel">Cancelar</button>
            <button type="button" class="lb-m-btn lb-m-btn-primary" id="mot-div-confirm"><i class="fa-solid fa-paper-plane"></i> Registrar ocorrência</button>
        </div>
    </div>
</div>

<div id="mot-toast" class="lb-mot-toast" role="alert" hidden></div>

<script>
(function(){
  var CFG = window.LOGBR_M || {};
  var VID = <?= json_encode($vid) ?>;
  var PID = <?= json_encode($pid) ?>;

  function motToast(msg, isErr){
    var t = document.getElementById("mot-toast");
    if (!t) { alert(msg); return; }
    t.textContent = msg;
    t.className = "lb-mot-toast" + (isErr ? " lb-mot-toast--err" : "");
    t.hidden = false;
    clearTimeout(t._hid);
    t._hid = setTimeout(function(){ t.hidden = true; }, 5200);
  }

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
    if (!j.ok) { motToast(j.message || "Não foi possível atualizar o status.", true); return; }
    location.reload();
  });

  document.getElementById("mot-div-btn")?.addEventListener("click", function(){ document.getElementById("mot-div-mask").style.display="flex"; });
  document.getElementById("mot-div-cancel")?.addEventListener("click", function(){ document.getElementById("mot-div-mask").style.display="none"; });
  document.getElementById("mot-div-confirm")?.addEventListener("click", async function(){
    var txt = (document.getElementById("mot-div-txt")||{}).value || "";
    if (!txt.trim()) { motToast("Descreva a ocorrência antes de enviar.", true); return; }
    window.LB_MOT_BUSY.show("Registrando ocorrência…");
    try {
      var fd = new FormData();
      fd.append("_csrf", CFG.csrf);
      fd.append("viagem_id", String(VID));
      fd.append("pedido_id", String(PID));
      fd.append("descricao", txt.trim());
      var foto = document.getElementById("mot-div-foto").files?.[0];
      if (foto) fd.append("foto_ocorrencia", foto);
      var r = await fetch(CFG.baseUrl + "/api/motorista/divergencia", {method:"POST", body: fd, headers:{Accept:"application/json"}});
      var j = await r.json().catch(function(){return {};});
      if (!j.ok) { motToast(j.message||"Não registrou a ocorrência", true); return; }
      location.reload();
    } finally {
      window.LB_MOT_BUSY.hide();
    }
  });
})();
</script>
