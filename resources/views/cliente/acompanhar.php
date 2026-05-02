<section class="lb-public-track-intro">
    <h1 class="lb-public-track-title">Acompanhar entrega</h1>
    <p class="lb-public-track-desc"><i class="fa-solid fa-fingerprint"></i> Consulta pública pelo CPF do destinatário — sem login.</p>
</section>

<article class="lb-login-card lb-public-track-card">
    <div class="lb-form-group" style="margin-bottom:12px">
        <label class="lb-form-label" for="ac-cpf">CPF do destinatário</label>
        <div class="lb-public-cpf-row">
            <div class="lb-input-affix">
                <i class="fa-regular fa-id-card"></i>
                <input id="ac-cpf" inputmode="numeric" maxlength="14" placeholder="000.000.000-00">
            </div>
            <button id="ac-btn" type="button" class="lb-btn-enter" style="width:auto;padding:12px 18px;white-space:nowrap">
                <i class="fa-solid fa-magnifying-glass"></i> Buscar
            </button>
        </div>
    </div>

    <div class="lb-m-tabs lb-public-tabs" role="tablist" style="margin:4px 0 14px">
        <button type="button" class="lb-m-tab" role="tab" aria-selected="true" data-pane="pend">Em andamento</button>
        <button type="button" class="lb-m-tab" role="tab" aria-selected="false" data-pane="ok">Realizadas</button>
    </div>

    <div id="pane-pend"></div>
    <div id="pane-ok" style="display:none"></div>

    <div id="acre-load" class="lb-m-muted" style="display:none;text-align:center;padding:20px"><i class="fa-solid fa-circle-notch fa-spin"></i> Carregando…</div>
    <div id="acre-empty-pend" class="lb-public-empty" style="display:none">Nenhum pedido em andamento para este CPF.</div>
    <div id="acre-empty-ok" class="lb-public-empty" style="display:none">Nenhuma entrega concluída encontrada.</div>
</article>
