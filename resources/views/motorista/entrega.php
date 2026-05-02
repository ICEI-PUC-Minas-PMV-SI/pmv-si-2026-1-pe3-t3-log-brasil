<?php

use App\Core\Helpers;

$vid = (int) ($v['id'] ?? 0);
$pid = (int) ($pedido['pedido_id'] ?? $pedido['id'] ?? 0);

?>
<div style="margin-bottom:10px">
    <a href="<?= htmlspecialchars(CONF_BASE_URL . '/motorista/viagem/' . $vid . '/pedido/' . $pid, ENT_QUOTES) ?>" class="lb-m-muted" style="font-size:.88rem;text-decoration:none"><i class="fa-solid fa-angle-left"></i> Voltar</a>
</div>

<section class="lb-m-card lb-m-muted" style="font-size:.86rem;line-height:1.45;margin-bottom:10px">
    <strong class="lb-mot-stop-title" style="display:block;font-size:1.05rem"><?= Helpers::e((string) ($pedido['nome_destinatario'] ?? '')) ?></strong>
    <?= Helpers::e((string) ($pedido['logradouro'] ?? '')) ?>, <?= Helpers::e((string) ($pedido['numero'] ?? '')) ?>
</section>

<section class="lb-m-card">
    <div class="lb-m-muted" style="margin-bottom:6px"><i class="fa-solid fa-pen-line"></i> Nome completo do recebedor</div>
    <input class="lb-m-input" id="mot-rc-nome" autocomplete="name" placeholder="Nome que assina o recebimento">

    <div class="lb-m-muted" style="margin:14px 0 6px"><i class="fa-solid fa-image"></i> Foto da mercadoria entregue</div>
    <input type="file" id="mot-rc-foto" accept="image/*" capture="environment" class="lb-m-input">

    <div class="lb-m-muted" style="margin:14px 0 6px"><i class="fa-solid fa-file-signature"></i> Assinatura</div>
    <div style="border:1px solid var(--lb-mot-border,rgba(255,255,255,.22));border-radius:12px;overflow:hidden;background:#f8fafc;">
        <canvas id="mot-sig-canvas" style="display:block;width:100%;touch-action:none;height:min(240px,40vh)" width="880" height="480"></canvas>
    </div>
    <button type="button" class="lb-m-btn lb-m-btn-ghost" style="margin-top:8px" id="mot-sig-clear"><i class="fa-solid fa-eraser"></i> Limpar</button>

    <button type="button" class="lb-m-btn lb-m-btn-primary" style="margin-top:16px" id="mot-rc-send">
        <i class="fa-solid fa-cloud-arrow-up"></i> Confirmar entrega e registrar horário
    </button>
</section>

<section class="lb-m-card" style="margin-top:12px">
    <div class="lb-m-muted"><i class="fa-solid fa-cubes"></i> Itens declarados</div>
    <ul style="margin:8px 0 0;padding-left:18px;font-size:.86rem;line-height:1.45;color:var(--lb-mot-soft,var(--lb-m-muted))">
        <?php foreach ($itens ?? [] as $it): ?>
            <li><?= Helpers::e((string) ($it['descricao'] ?? '')) ?></li>
        <?php endforeach; ?>
    </ul>
</section>

<script>
(function(){
  var CFG = window.LOGBR_M || {};
  var VID = <?= json_encode($vid) ?>;
  var PID = <?= json_encode($pid) ?>;
  var cv = document.getElementById("mot-sig-canvas");
  if (!cv) return;
  var ctx = cv.getContext("2d");
  cv.width = cv.clientWidth * Math.min(2, window.devicePixelRatio || 2);
  cv.height = (cv.clientHeight||240) * Math.min(2, window.devicePixelRatio || 2);
  ctx.fillStyle = "#ffffff";
  ctx.fillRect(0, 0, cv.width, cv.height);
  ctx.strokeStyle="#0a2463"; ctx.lineWidth=2.8; ctx.lineCap="round";
  var ink=false, lx=0, ly=0;
  function pos(e){ var r=cv.getBoundingClientRect(); var t=e.touches?e.touches[0]:e; return{
    x:(t.clientX-r.left)/(r.width)*cv.width, y:(t.clientY-r.top)/(r.height)*cv.height }; }
  cv.addEventListener("pointerdown", function(e){ ink=true; e.preventDefault(); var p=pos(e); lx=p.x; ly=p.y; });
  cv.addEventListener("pointermove", function(e){ if(!ink)return; e.preventDefault(); var p=pos(e);
    ctx.beginPath(); ctx.moveTo(lx,ly); ctx.lineTo(p.x,p.y); ctx.stroke(); lx=p.x; ly=p.y;
  });
  ["pointerup","pointerleave"].forEach(function(ev){ cv.addEventListener(ev,function(){ ink=false; }); });

  document.getElementById("mot-sig-clear").addEventListener("click", function(){
    ctx.fillStyle="#fff"; ctx.fillRect(0,0,cv.width,cv.height); ctx.strokeStyle="#0a2463";
  });

  document.getElementById("mot-rc-send").addEventListener("click", async function(){
    var nome = (document.getElementById("mot-rc-nome")||{}).value || "";
    if (!nome.trim()) { alert("Informe o nome do recebedor"); return; }
    var f = document.getElementById("mot-rc-foto").files?.[0];
    if(!f){ alert("Foto obrigatória"); return; }
    var png = cv.toDataURL("image/png");
    if(!png || png.length<100){ alert("Assine no quadro."); return; }
    window.LB_MOT_BUSY.show("Registrando entrega…");
    try {
      var fd = new FormData();
      fd.append("_csrf", CFG.csrf);
      fd.append("viagem_id", String(VID));
      fd.append("pedido_id", String(PID));
      fd.append("recebedor_nome", nome.trim());
      fd.append("assinatura_data_url", png);
      fd.append("mercadoria", f);
      var r = await fetch(CFG.baseUrl + "/api/motorista/concluir", {method:"POST", body: fd, headers:{Accept:"application/json"}});
      var j = await r.json().catch(function(){return {};});
      if (!j.ok) { alert(j.message||("Erro"+r.status)); return; }
      location.href = CFG.baseUrl + "/motorista/viagem/"+VID+"#ok";
    } finally {
      window.LB_MOT_BUSY.hide();
    }
  });
})();
</script>
