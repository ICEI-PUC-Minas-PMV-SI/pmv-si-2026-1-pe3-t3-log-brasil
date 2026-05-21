<?php

use App\Core\Helpers;

$headExtra = '<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="">' . "\n"
    . '<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>';
?>
<section class="lb-page-heading">
    <h1><?= Helpers::e($title) ?></h1>
    <span class="lb-muted">Histórico apenas leitura: mapas e cargas já encerradas.</span>
</section>

<div class="lb-grid-metrics" style="grid-template-columns:repeat(auto-fill,minmax(300px,1fr))">
    <?php foreach ($lista ?? [] as $v): ?>
        <div class="lb-card" style="border-left:3px solid var(--lb-primary);opacity:.95">
            <div class="lb-chip">#<?= Helpers::e((string)$v['id']) ?></div>
            <h3 style="margin:6px 0 0;font-size:1rem"><?= Helpers::e($v['rota_nome']) ?></h3>
            <p class="lb-muted" style="margin:8px 0 0;font-size:.82rem;line-height:1.45">
                Histórico de paradas no detalhes; comprovantes (foto do recebedor, assinatura e horário) em <strong>Apontamento</strong> por NF.
            </p>
            <div class="lb-route-metrics" style="margin-top:10px">
                <div><strong>Fim em</strong><br><?= Helpers::e($v['finalizado_em'] ?? '—') ?></div>
                <div><strong>Motorista</strong><br><?= Helpers::e($v['motorista_nome'] ?? '—') ?></div>
                <div><strong>Peso total</strong><br><?= Helpers::e((string)$v['peso_total_kg']) ?> kg</div>
                <div><strong>Entregas</strong><br><?= Helpers::e((string)$v['qt_entregas']) ?></div>
            </div>
            <div class="lb-route-actions" style="margin-top:10px">
                <button type="button" class="lb-btn lb-btn-quiet lb-v2-detalhes" data-id="<?= (int)$v['id'] ?>"><i class="fa-solid fa-list"></i> Detalhes</button>
                <button type="button" class="lb-btn lb-btn-quiet lb-v2-mapa" data-id="<?= (int)$v['id'] ?>"><i class="fa-solid fa-location-dot"></i> Mapa</button>
            </div>
        </div>
    <?php endforeach; ?>
    <?php if (empty($lista)): ?>
        <p class="lb-muted">Nenhuma viagem finalizada.</p>
    <?php endif; ?>
</div>

<div id="modal-v2-det" class="lb-modal-mask">
    <div class="lb-modal" style="max-width:1040px">
        <div class="lb-modal-head">
            <strong>Pedidos da viagem (finalizada)</strong>
            <button type="button" class="lb-btn lb-btn-quiet lb-modal-close"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="lb-modal-body">
            <p class="lb-muted" style="margin:0 0 10px;font-size:.82rem">Status final da parada e acesso ao comprovante registrado pelo motorista.</p>
            <div class="lb-table-shell" style="overflow-x:auto">
                <table class="lb-table" style="min-width:920px">
                    <thead><tr>
                        <th>#</th>
                        <th>NF</th>
                        <th>Destinatário</th>
                        <th>Status</th>
                        <th>Entregue em</th>
                        <th>Comprovante</th>
                    </tr></thead>
                    <tbody id="v2-det-body"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div id="modal-v2-entrega" class="lb-modal-mask">
    <div class="lb-modal" style="max-width:640px">
        <div class="lb-modal-head">
            <strong>Apontamento de entrega</strong>
            <button type="button" class="lb-btn lb-btn-quiet lb-modal-close"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="lb-modal-body">
            <div class="lb-chip" id="v2-ap-nf" style="margin-bottom:10px"></div>
            <div id="v2-ap-empty" class="lb-muted" style="display:none;padding:14px;border-radius:10px;background:rgba(0,0,50,.05);border:1px dashed rgba(0,0,139,.2)">
                Não há registro de entrega com foto/assinatura para esta parada (ex.: cancelada ou só divergência sem comprovante).
            </div>
            <div id="v2-ap-content" style="display:none">
                <div style="margin-bottom:12px">
                    <span class="lb-muted" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.04em">Recebedor (nome informado)</span>
                    <div id="v2-ap-recebedor" style="font-weight:700;font-size:1.05rem;margin-top:4px"></div>
                </div>
                <div style="margin-bottom:12px">
                    <span class="lb-muted" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.04em">Data e hora do registro</span>
                    <div id="v2-ap-dt" style="font-weight:650;margin-top:4px"></div>
                </div>
                <div style="margin-bottom:12px" id="v2-ap-geo-wrap">
                    <span class="lb-muted" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.04em">Local GPS na entrega</span>
                    <div id="v2-ap-geo" style="font-weight:650;margin-top:4px;font-size:.92rem"></div>
                </div>
                <div style="margin-bottom:14px">
                    <span class="lb-muted" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.04em">Foto da mercadoria</span>
                    <div style="margin-top:8px;border-radius:10px;overflow:hidden;border:1px solid rgba(0,0,0,.1);background:#f4f4f4">
                        <img id="v2-ap-foto" alt="Foto da entrega" style="display:block;width:100%;max-height:360px;object-fit:contain">
                    </div>
                </div>
                <div>
                    <span class="lb-muted" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.04em">Assinatura</span>
                    <div style="margin-top:8px;border-radius:10px;overflow:hidden;border:1px solid rgba(0,0,0,.1);background:#fff">
                        <img id="v2-ap-sig" alt="Assinatura" style="display:block;width:100%;max-height:220px;object-fit:contain">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="modal-v2-mapa" class="lb-modal-mask">
    <div class="lb-modal" style="max-width:960px">
        <div class="lb-modal-head">
            <strong>Mapa — cliente × apontamento GPS (raio 100 m)</strong>
            <button type="button" class="lb-btn lb-btn-quiet lb-modal-close"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="lb-modal-body">
            <p class="lb-muted" style="margin:0 0 10px;font-size:.82rem;line-height:1.45">
                <span style="display:inline-flex;align-items:center;gap:6px;margin-right:12px"><i class="fa-solid fa-circle" style="color:#2563eb;font-size:.65rem"></i> Cliente (cadastro)</span>
                <span style="display:inline-flex;align-items:center;gap:6px;margin-right:12px"><i class="fa-solid fa-circle" style="color:#16a34a;font-size:.65rem"></i> Motorista (na entrega)</span>
                <span style="display:inline-flex;align-items:center;gap:6px"><i class="fa-regular fa-circle" style="color:#64748b;font-size:.65rem"></i> Raio 100 m</span>
            </p>
            <div id="map-viagem2" style="height:400px;border-radius:10px;border:1px solid rgba(0,0,50,.08)"></div>
            <div id="v2-mapa-legenda" class="lb-muted" style="margin-top:12px;font-size:.8rem;line-height:1.5;max-height:140px;overflow-y:auto"></div>
        </div>
    </div>
</div>
