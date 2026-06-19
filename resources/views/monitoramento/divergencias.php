<?php

use App\Core\Helpers;

?>
<section class="lb-page-heading">
    <h1><?= Helpers::e($title) ?></h1>
    <span class="lb-muted">Aprovação encerra pendências nos mapas/listas das viagens ou devolve ao motorista quando rejeitar.</span>
</section>

<div class="lb-grid-metrics" style="grid-template-columns:repeat(auto-fill,minmax(340px,1fr))">
    <?php foreach ($lista ?? [] as $row): ?>
        <div class="lb-card lb-route-card">
            <div style="margin-bottom:6px" class="lb-chip">DIV #<?= (int) $row['id'] ?></div>
            <strong><?= Helpers::e((string) ($row['rota_nome'] ?? '')) ?></strong>
            <div class="lb-route-metrics" style="margin-top:8px;font-size:.88rem">
                <div><strong>Viagem</strong><br>#<?= (int) ($row['viagem_id'] ?? 0) ?></div>
                <div><strong>Pedido</strong><br><?= Helpers::e((string) ($row['numero_pedido'] ?? '—')) ?></div>
                <div><strong>Motorista</strong><br><?= Helpers::e((string) ($row['motorista_nome_reporte'] ?? '—')) ?></div>
                <div style="grid-column:1/-1"><strong>Descrição</strong><br><?= nl2br(Helpers::e((string) ($row['descricao'] ?? ''))) ?></div>
                <div style="grid-column:1/-1" class="lb-muted"><?= Helpers::e((string) ($row['reportado_em'] ?? '')) ?></div>
            </div>
            <div class="lb-route-actions" style="margin-top:10px;display:flex;gap:8px;flex-wrap:wrap">
                <button type="button" class="lb-btn lb-btn-secondary lb-div-ok" data-id="<?= (int) $row['id'] ?>">
                    <i class="fa-solid fa-check"></i> Aprovar
                </button>
                <button type="button" class="lb-btn lb-btn-quiet lb-div-no" data-id="<?= (int) $row['id'] ?>">
                    <i class="fa-solid fa-xmark"></i> Rejeitar
                </button>
            </div>
        </div>
    <?php endforeach; ?>
    <?php if (empty($lista)): ?>
        <p class="lb-muted">Nenhuma divergência aguardando aprovação.</p>
    <?php endif; ?>
</div>

<script>
(function(){
  var CFG = window.LOGBR;
  async function enviar(id, aprovar){
    var body = {_csrf: CFG.csrf, divergencia_id: id, aprovar: aprovar};
    var r = await fetch(CFG.baseUrl + "/api/monitoramento/divergencia-revisao", {
      method:"POST", headers:{Accept:"application/json","Content-Type":"application/json"},
      body: JSON.stringify(body)});
    var j = await r.json().catch(function(){return {};});
    if (!j.ok) { alert(j.message||"Erro"); return;}
    location.reload();
  }
  document.querySelectorAll(".lb-div-ok").forEach(function(b){ b.addEventListener("click", ()=> enviar(+b.dataset.id, true)); });
  document.querySelectorAll(".lb-div-no").forEach(function(b){ b.addEventListener("click", ()=> enviar(+b.dataset.id, false)); });
})();
</script>
