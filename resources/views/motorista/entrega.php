<?php

use App\Core\Helpers;

$vid = (int) ($v['id'] ?? 0);
$pid = (int) ($pedido['pedido_id'] ?? $pedido['id'] ?? 0);

?>
<div style="margin-bottom:10px">
    <a href="<?= htmlspecialchars(CONF_BASE_URL . '/motorista/viagem/' . $vid . '/pedido/' . $pid, ENT_QUOTES) ?>" class="lb-m-muted" style="font-size:.88rem;text-decoration:none"><i class="fa-solid fa-angle-left"></i> Voltar à parada</a>
</div>

<section class="lb-m-card" style="margin-bottom:10px;border-left:3px solid var(--lb-m-green,#1b7f4b)">
    <div class="lb-m-chip ok" style="margin-bottom:8px"><i class="fa-solid fa-file-signature"></i> Comprovante de entrega</div>
    <p class="lb-m-muted" style="margin:0;font-size:.84rem;line-height:1.45">
        Registre recebedor, foto, assinatura e localização GPS. A coordenada é capturada <strong>automaticamente</strong> ao confirmar.
    </p>
</section>

<section class="lb-m-card lb-m-muted" style="font-size:.86rem;line-height:1.45;margin-bottom:10px">
    <strong class="lb-mot-stop-title" style="display:block;font-size:1.05rem"><?= Helpers::e((string) ($pedido['nome_destinatario'] ?? '')) ?></strong>
    <?= Helpers::e((string) ($pedido['logradouro'] ?? '')) ?>, <?= Helpers::e((string) ($pedido['numero'] ?? '')) ?>
</section>

<section class="lb-m-card">
    <div class="lb-m-muted" style="margin-bottom:6px"><i class="fa-solid fa-pen-line"></i> Nome completo do recebedor</div>
    <input class="lb-m-input" id="mot-rc-nome" autocomplete="name" placeholder="Nome que assina o recebimento">

    <div class="lb-m-muted" style="margin:14px 0 6px"><i class="fa-solid fa-image"></i> Foto da mercadoria entregue</div>
    <input type="file" id="mot-rc-foto" accept="image/*" capture="environment" class="lb-m-input">

    <div class="lb-m-muted" style="margin:14px 0 6px"><i class="fa-solid fa-file-signature"></i> Assinatura do recebedor</div>
    <div style="border:1px solid var(--lb-mot-border,rgba(255,255,255,.22));border-radius:12px;overflow:hidden;background:#f8fafc;">
        <canvas id="mot-sig-canvas" style="display:block;width:100%;touch-action:none;height:min(240px,40vh)" width="880" height="480"></canvas>
    </div>
    <button type="button" class="lb-m-btn lb-m-btn-ghost" style="margin-top:8px" id="mot-sig-clear"><i class="fa-solid fa-eraser"></i> Limpar assinatura</button>

    <div id="mot-geo-status" class="lb-mot-geo-status lb-mot-geo-status--wait" style="margin-top:14px" role="status" aria-live="polite">
        <i class="fa-solid fa-location-crosshairs"></i>
        <span id="mot-geo-text">RF08 — Preparando GPS do aparelho…</span>
    </div>

    <button type="button" class="lb-m-btn lb-m-btn-primary" style="margin-top:16px" id="mot-rc-send">
        <i class="fa-solid fa-circle-check"></i> Confirmar entrega e registrar horário
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

<div id="mot-toast" class="lb-mot-toast" role="alert" hidden></div>

<script>
(function(){
  var CFG = window.LOGBR_M || {};
  var VID = <?= json_encode($vid) ?>;
  var PID = <?= json_encode($pid) ?>;
  var geoCache = null;
  var geoWatchId = null;

  function motToast(msg, isErr){
    var t = document.getElementById("mot-toast");
    if (!t) { alert(msg); return; }
    t.textContent = msg;
    t.className = "lb-mot-toast" + (isErr ? " lb-mot-toast--err" : "");
    t.hidden = false;
    clearTimeout(t._hid);
    t._hid = setTimeout(function(){ t.hidden = true; }, 5200);
  }

  function setGeoStatus(mode, text){
    var box = document.getElementById("mot-geo-status");
    var tx = document.getElementById("mot-geo-text");
    if (!box || !tx) return;
    box.className = "lb-mot-geo-status lb-mot-geo-status--" + mode;
    tx.textContent = text;
  }

  function applyGeo(pos){
    geoCache = {
      lat: pos.coords.latitude,
      lng: pos.coords.longitude,
      acc: pos.coords.accuracy
    };
    var acc = geoCache.acc != null ? " (~" + Math.round(geoCache.acc) + " m)" : "";
    setGeoStatus("ok", "GPS pronto: " + geoCache.lat.toFixed(5) + ", " + geoCache.lng.toFixed(5) + acc);
  }

  function captureGeoFresh(){
    return new Promise(function(resolve, reject){
      if (!navigator.geolocation) {
        reject(new Error("Este aparelho não oferece geolocalização."));
        return;
      }
      setGeoStatus("wait", "Capturando localização agora…");
      navigator.geolocation.getCurrentPosition(
        function(pos){ applyGeo(pos); resolve(geoCache); },
        function(err){
          if (geoCache) { resolve(geoCache); return; }
          var msg = "Não foi possível obter GPS.";
          if (err && err.code === 1) msg = "Permissão de localização negada. Ative nas configurações do navegador.";
          else if (err && err.code === 3) msg = "Tempo esgotado ao buscar GPS. Tente em área aberta.";
          reject(new Error(msg));
        },
        { enableHighAccuracy: true, timeout: 18000, maximumAge: 0 }
      );
    });
  }

  if (navigator.geolocation) {
    geoWatchId = navigator.geolocation.watchPosition(
      function(pos){ applyGeo(pos); },
      function(){
        if (!geoCache) setGeoStatus("warn", "Aguardando permissão de localização…");
      },
      { enableHighAccuracy: true, maximumAge: 8000, timeout: 20000 }
    );
  } else {
    setGeoStatus("err", "GPS indisponível neste dispositivo.");
  }

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
    if (!nome.trim()) { motToast("Informe o nome do recebedor.", true); return; }
    var f = document.getElementById("mot-rc-foto").files?.[0];
    if(!f){ motToast("Envie a foto da mercadoria entregue.", true); return; }
    var png = cv.toDataURL("image/png");
    if(!png || png.length<100){ motToast("Assine no quadro antes de confirmar.", true); return; }

    var btn = document.getElementById("mot-rc-send");
    btn.disabled = true;
    window.LB_MOT_BUSY.show("Obtendo localização e registrando…");
    try {
      var g = await captureGeoFresh();
      var fd = new FormData();
      fd.append("_csrf", CFG.csrf);
      fd.append("viagem_id", String(VID));
      fd.append("pedido_id", String(PID));
      fd.append("recebedor_nome", nome.trim());
      fd.append("assinatura_data_url", png);
      fd.append("mercadoria", f);
      fd.append("entrega_latitude", String(g.lat));
      fd.append("entrega_longitude", String(g.lng));
      if (g.acc != null && !isNaN(g.acc)) fd.append("entrega_geo_precisao_m", String(g.acc));
      var r = await fetch(CFG.baseUrl + "/api/motorista/concluir", {method:"POST", body: fd, headers:{Accept:"application/json"}});
      var j = await r.json().catch(function(){return {};});
      if (!j.ok) { motToast(j.message || ("Erro " + r.status), true); return; }
      location.href = CFG.baseUrl + "/motorista/viagem/"+VID+"#ok";
    } catch (e) {
      motToast(e && e.message ? e.message : "Falha ao registrar entrega.", true);
    } finally {
      btn.disabled = false;
      window.LB_MOT_BUSY.hide();
    }
  });

  window.addEventListener("pagehide", function(){
    if (geoWatchId != null && navigator.geolocation) navigator.geolocation.clearWatch(geoWatchId);
  });
})();
</script>
