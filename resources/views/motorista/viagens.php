<?php

use App\Core\Helpers;

function lbMotFmtData(?string $s): string
{
    if ($s === null || trim($s) === '') {
        return '—';
    }
    $t = strtotime($s);

    return $t ? date('d/m/Y H:i', $t) : htmlspecialchars(mb_substr($s, 0, 48), ENT_QUOTES, 'UTF-8');
}

?>
<?php foreach ($lista ?? [] as $v): ?>
    <?php
    $vid = (int) $v['id'];
    $href = CONF_BASE_URL . '/motorista/viagem/' . $vid;
    $total = (int) ($v['_vp_total'] ?? 0);
    $pend = (int) ($v['_vp_pend'] ?? 0);
    $indo = (int) ($v['_vp_indo'] ?? 0);
    $feito = (int) ($v['_vp_feito'] ?? 0);
    $divPar = (int) ($v['_vp_div_parada'] ?? 0);
    $peso = (string) ($v['peso_total_kg'] ?? '0');
    $qt = (int) ($v['qt_entregas'] ?? 0);
    $distM = (int) ($v['distancia_metros_prev'] ?? 0);
    $distKm = $distM > 0 ? number_format($distM / 1000, 1, ',', '.') : '';
    ?>
    <article class="lb-m-card lb-mot-go"
        style="cursor:pointer"
        onclick="location.href='<?= htmlspecialchars($href, ENT_QUOTES) ?>'"
        role="link"
        tabindex="0"
        onkeydown="if(event.key==='Enter')location.href=this.getAttribute('data-href')"
        data-href="<?= htmlspecialchars($href, ENT_QUOTES) ?>">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px">
            <div style="flex:1;min-width:0">
                <div class="lb-m-chip"><i class="fa-solid fa-hashtag"></i> <?= $vid ?></div>
                <div class="lb-mot-trip-hdr-title" style="margin-top:10px;font-size:1.08rem;font-weight:850;letter-spacing:-.02em;line-height:1.25">
                    <?= Helpers::e((string) ($v['rota_nome'] ?? '')) ?>
                </div>
                <div class="lb-m-muted" style="margin-top:6px;display:flex;flex-wrap:wrap;gap:8px;align-items:center">
                    <span><i class="fa-solid fa-truck-pickup"></i> <?= Helpers::e((string) ($v['placa'] ?? '—')) ?></span>
                    <span><i class="fa-solid fa-weight-hanging"></i> <?= Helpers::e($peso) ?> kg</span>
                    <?php if ($qt > 0): ?>
                        <span><i class="fa-solid fa-box"></i> <?= $qt ?> vol.</span>
                    <?php endif; ?>
                    <?php if ($distKm !== ''): ?>
                        <span><i class="fa-solid fa-route"></i> ~<?= $distKm ?> km</span>
                    <?php endif; ?>
                </div>
            </div>
            <div style="text-align:right;flex-shrink:0">
                <?php if (! empty($v['_div_pend'])): ?>
                    <span class="lb-pulse-dot" title="Divergência em análise"></span>
                    <div style="margin-top:6px;font-size:.72rem;color:#fbbf24;font-weight:700">Painel: <?= (int) $v['_div_pend'] ?></div>
                <?php elseif (! empty($v['_pode_fin'])): ?>
                    <div style="padding:8px 10px;border-radius:12px;background:rgba(34,197,94,.15);color:#86efac;font-size:.75rem;font-weight:700">
                        <i class="fa-solid fa-flag-checkered"></i> Pode finalizar
                    </div>
                <?php else: ?>
                    <i class="fa-solid fa-chevron-right" style="opacity:.35;margin-top:8px"></i>
                <?php endif; ?>
            </div>
        </div>

        <div class="lb-mot-trip-card-metrics">
            <span class="lb-mot-pill-muted" title="Paradas na viagem"><i class="fa-solid fa-map-pin"></i> <?= $total ?> paradas</span>
            <span class="lb-mot-pill-muted" title="Ainda não iniciadas"><i class="fa-regular fa-clock"></i> <?= $pend ?> pendentes</span>
            <span class="lb-mot-pill-muted" title="A caminho ou na entrega"><i class="fa-solid fa-person-running"></i> <?= $indo ?> em rota</span>
            <span class="lb-mot-pill-muted" title="Concluídas com sucesso"><i class="fa-solid fa-circle-check"></i> <?= $feito ?> feitas</span>
            <?php if ($divPar > 0): ?>
                <span class="lb-mot-pill-muted" style="color:#fbbf24"><i class="fa-solid fa-triangle-exclamation"></i> <?= $divPar ?> aguard. div.</span>
            <?php endif; ?>
        </div>

        <div class="lb-m-muted" style="margin-top:10px;font-size:.78rem">
            <i class="fa-regular fa-calendar"></i> Previsto: <?= lbMotFmtData(isset($v['data_largada_prevista']) ? (string) $v['data_largada_prevista'] : null) ?>
        </div>
    </article>
<?php endforeach; ?>
<?php if (empty($lista)): ?>
    <div class="lb-m-card lb-m-muted" style="text-align:center;padding:28px 16px">
        <i class="fa-solid fa-mug-hot fa-2x" style="opacity:.35;display:block;margin-bottom:10px"></i>
        Sem viagens abertas no momento. Quando a operação atribuir uma rota a você, ela aparece aqui.
    </div>
<?php endif; ?>
