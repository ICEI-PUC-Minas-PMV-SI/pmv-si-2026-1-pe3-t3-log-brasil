<?php

use App\Core\Helpers;

$u ??= [];
?>
<section class="lb-page-heading">
    <h1><?= Helpers::e($title) ?></h1>
    <span class="lb-muted">Origem física utilizada pelo roteirizador e no cálculo de distância via OpenRouteService.</span>
</section>

<div class="lb-card lb-card-accent" style="max-width:640px">
    <form method="post">
        <input type="hidden" name="_csrf" value="<?= Helpers::csrfToken() ?>">
        <label class="lb-muted">Nome</label>
        <input class="lb-input" name="nome" value="<?= Helpers::e((string) ($u['nome'] ?? '')) ?>" required>

        <div style="display:grid;grid-template-columns:1fr 140px;gap:10px;margin-top:12px">
            <div><label class="lb-muted">Logradouro</label><input class="lb-input" name="logradouro"
                  value="<?= Helpers::e((string) ($u['logradouro'] ?? '')) ?>" required></div>
            <div><label class="lb-muted">Número</label><input class="lb-input" name="numero"
                  value="<?= Helpers::e((string) ($u['numero'] ?? '')) ?>"></div>
        </div>
        <label class="lb-muted" style="margin-top:12px;display:block">Complemento</label>
        <input class="lb-input" name="complemento" value="<?= Helpers::e((string) ($u['complemento'] ?? '')) ?>">

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:12px">
            <div><label class="lb-muted">Bairro</label><input class="lb-input" name="bairro"
                  value="<?= Helpers::e((string) ($u['bairro'] ?? '')) ?>"></div>
            <div><label class="lb-muted">CEP</label><input class="lb-input" name="cep"
                  value="<?= Helpers::e((string) ($u['cep'] ?? '')) ?>"></div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 80px;gap:10px;margin-top:12px">
            <div><label class="lb-muted">Cidade</label><input class="lb-input" name="cidade"
                  value="<?= Helpers::e((string) ($u['cidade'] ?? '')) ?>" required></div>
            <div><label class="lb-muted">UF</label><input class="lb-input" name="uf" maxlength="2"
                  value="<?= Helpers::e((string) ($u['uf'] ?? '')) ?>" required></div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:12px">
            <div><label class="lb-muted">Latitude (opcional; deixe 0 para geocodificar)</label>
                <input class="lb-input" name="latitude" value="<?= Helpers::e((string) ($u['latitude'] ?? '0')) ?>"></div>
            <div><label class="lb-muted">Longitude</label>
                <input class="lb-input" name="longitude" value="<?= Helpers::e((string) ($u['longitude'] ?? '0')) ?>"></div>
        </div>

        <label class="lb-muted" style="margin-top:12px;display:block">Observação</label>
        <textarea class="lb-input" name="observacao" rows="3"><?= Helpers::e((string) ($u['observacao'] ?? '')) ?></textarea>

        <button class="lb-btn lb-btn-primary" style="margin-top:14px" type="submit"><i class="fa-solid fa-floppy-disk"></i> Salvar</button>
    </form>
</div>
