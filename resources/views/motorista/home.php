<?php

use App\Core\Helpers;

$m = $motorista ?? [];
$base = CONF_BASE_URL;
$foto = trim((string) ($m['foto_perfil'] ?? ''));

$digits = preg_replace('/\D/', '', (string) ($m['cpf'] ?? ''));
$cpfMask = $digits !== '' ? preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $digits) : '—';

$abertas = $motorista_viagens_abertas ?? [];
$totalAbertas = count($abertas);
?>

<section class="lb-m-card lb-mot-home-intro" style="padding:22px;background:linear-gradient(145deg,rgba(30,78,216,.22),transparent 52%),var(--lb-mot-card,var(--lb-m-card))">
    <span class="lb-mot-hero-chip"><i class="fa-solid fa-gauge-high"></i> Painel rápido</span>
    <div style="display:flex;align-items:center;gap:16px;margin-top:16px;flex-wrap:wrap">
        <div style="position:relative;width:108px;height:108px;border-radius:999px;padding:3px;background:linear-gradient(135deg,#3b82f6,#1b7f4b);flex-shrink:0">
            <div style="width:100%;height:100%;border-radius:999px;overflow:hidden;background:#142a62;display:flex;align-items:center;justify-content:center">
                <?php if ($foto !== ''): ?>
                    <img src="<?= htmlspecialchars($base . '/uploads/' . $foto, ENT_QUOTES) ?>" alt=""
                         style="width:100%;height:100%;object-fit:cover">
                <?php else: ?>
                    <i class="fa-regular fa-circle-user fa-4x" style="color:rgba(220,230,255,.35)"></i>
                <?php endif; ?>
            </div>
        </div>
        <div style="flex:1;min-width:0">
            <div style="font-weight:850;font-size:1.28rem;letter-spacing:-.02em;line-height:1.2"><?= Helpers::e((string) ($m['nome_completo'] ?? '')) ?></div>
            <div class="lb-m-muted" style="margin-top:6px;display:flex;flex-wrap:wrap;gap:10px;align-items:center">
                <span><i class="fa-solid fa-phone"></i> <?= Helpers::e((string) ($m['telefone'] ?? '—')) ?></span>
                <span><i class="fa-regular fa-id-card"></i> <?= Helpers::e($cpfMask) ?></span>
            </div>
            <form method="post" enctype="multipart/form-data" action="<?= htmlspecialchars($base . '/motorista/foto', ENT_QUOTES) ?>" style="margin-top:14px">
                <input type="hidden" name="_csrf" value="<?= Helpers::csrfToken() ?>">
                <label class="lb-m-btn lb-m-btn-primary" style="cursor:pointer;display:inline-flex;width:auto;padding:10px 16px;font-size:.86rem;align-items:center;gap:8px">
                    <i class="fa-solid fa-camera"></i> Trocar foto do perfil
                    <input type="file" name="foto" accept="image/jpeg,image/png,image/webp" capture="environment" hidden onchange="this.form.submit()">
                </label>
            </form>
        </div>
    </div>

    <div class="lb-mot-stat-grid">
        <div class="lb-mot-stat">
            <b><?= $totalAbertas ?></b>
            <span>Viagens abertas</span>
        </div>
        <div class="lb-mot-stat">
            <b><?= $totalAbertas > 0 ? (int) ($abertas[0]['id'] ?? 0) : '—' ?></b>
            <span>Última viagem #</span>
        </div>
        <div class="lb-mot-stat">
            <b><i class="fa-solid fa-shield-heart" style="opacity:.75"></i></b>
            <span>Conta ativa</span>
        </div>
    </div>
</section>

<a href="<?= htmlspecialchars($base . '/motorista/viagens', ENT_QUOTES) ?>" class="lb-m-card lb-mot-go" style="display:flex;align-items:center;gap:16px;text-decoration:none;border:1px solid var(--lb-mot-border, rgba(255,255,255,.14));padding:18px;color:inherit">
    <div style="width:58px;height:58px;border-radius:16px;background:linear-gradient(135deg,#1e4ed8,#1b7f4b);display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.45rem;flex-shrink:0">
        <i class="fa-solid fa-road"></i>
    </div>
    <div style="flex:1;min-width:0">
        <div style="font-weight:850;font-size:1.08rem">Viagens em aberto</div>
        <div class="lb-m-muted" style="margin-top:4px;line-height:1.45">Mapa, lista de paradas, entregas e divergências.</div>
    </div>
    <span style="opacity:.45"><i class="fa-solid fa-chevron-right"></i></span>
</a>

<section class="lb-m-card lb-m-muted" style="font-size:.84rem;line-height:1.55">
    <strong style="display:block;color:inherit;opacity:1;margin-bottom:6px"><i class="fa-solid fa-circle-info"></i> Dicas</strong>
    Atualize o status em cada parada (<em>Indo até o cliente</em> → entrega) para o monitoramento acompanhar em tempo real. Fotos de perfil ficam salvas no servidor e aparecem aqui e no painel interno.
</section>
