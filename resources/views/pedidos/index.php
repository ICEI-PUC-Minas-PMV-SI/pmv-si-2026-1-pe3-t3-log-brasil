<?php

use App\Core\Helpers;

$ordCampo = $ordCampo ?? 'numero_pedido';
$mkSort = fn (string $col) => CONF_BASE_URL . '/pedidos?' . http_build_query(array_merge($filtro, [
    'sort' => $col,
    'dir' => (($ordCampo === $col) && ($dir ?? 'ASC') === 'ASC') ? 'DESC' : 'ASC',
]));
?>
<section class="lb-page-heading">
    <h1><?= Helpers::e($title) ?></h1>
    <span class="lb-muted">Cadastro detalhado com itens, destinatário e geocodificação automática.</span>
</section>

<div class="lb-toolbar-cadastro">
    <div class="lb-grow">
        <form method="get" class="lb-toolbar-cadastro" style="margin:0">
            <input type="search" class="lb-input" name="q" placeholder="Buscar número, destinatário, CPF ou cidade" value="<?= Helpers::e((string) $filtro['q']) ?>" style="max-width:320px">
            <select class="lb-input" name="estado" style="max-width:200px">
                <option value="">Todos estados do pedido</option>
                <?php foreach (['pendente_roterizador'=>'Pendente roteirização','alocado_rota'=>'Na rota (base)','em_viagem'=>'Em viagem','entregue'=>'Entregue','cancelado'=>'Cancelado'] as $ev=>$lbl): ?>
                    <option value="<?= Helpers::e($ev) ?>" <?= $filtro['estado']===$ev?'selected':'' ?>><?= Helpers::e($lbl) ?></option>
                <?php endforeach; ?>
            </select>
            <select class="lb-input" name="rota_id" style="max-width:240px">
                <option value="">Todas rotas</option>
                <?php foreach (($rotas ?? []) as $r): ?>
                    <option value="<?= Helpers::e((string) $r['id']) ?>" <?= (string)$filtro['rota_id'] === (string)$r['id'] ? 'selected' : '' ?>><?= Helpers::e($r['nome']) ?></option>
                <?php endforeach; ?>
            </select>
            <button class="lb-btn lb-btn-quiet" type="submit"><i class="fa-solid fa-filter"></i> Filtrar</button>
        </form>
    </div>
    <div class="lb-card" style="padding:10px 12px;display:flex;align-items:center;gap:8px;min-width:320px;max-width:460px">
        <i class="fa-solid fa-link" style="color:var(--lb-secondary-yellow)"></i>
        <input id="link-acompanhar-publico" class="lb-input" readonly value="<?= Helpers::e(CONF_BASE_URL . '/acompanhar') ?>" style="padding:8px 10px">
        <button type="button" id="btn-copy-acompanhar" class="lb-btn lb-btn-quiet" title="Copiar link"><i class="fa-regular fa-copy"></i></button>
    </div>
    <button type="button" class="lb-btn lb-btn-accent lb-open-modal-pedido" data-mode="create"><i class="fa-solid fa-plus"></i> Novo pedido</button>
</div>

<div class="lb-grid-metrics">
    <div class="lb-card lb-card-accent">
        <div class="lb-metric-label"><i class="fa-solid fa-weight-hanging"></i> Peso em tela filtrado</div>
        <div class="lb-metric-value" style="color:var(--lb-secondary-yellow)">
            <?= number_format(array_sum(array_map(static fn ($r)=>(float)$r['peso_total_kg'], $lista)), 3, ',', '.') ?> kg
        </div>
    </div>
    <div class="lb-card">
        <div class="lb-metric-label"><i class="fa-solid fa-boxes-stacked"></i> Quantidade registros listados</div>
        <div class="lb-metric-value"><?= count($lista) ?></div>
    </div>
</div>

<div class="lb-table-shell">
    <table class="lb-table lb-table-sortable">
        <thead>
        <tr>
            <th><a href="<?= htmlspecialchars($mkSort('numero_pedido')) ?>" class="nav-item" style="color:inherit;text-decoration:none"><i class="fa-solid fa-sort"></i> Número</a></th>
            <th>Destinatário</th>
            <th><a href="<?= htmlspecialchars($mkSort('cidade')) ?>" style="color:inherit;text-decoration:none">Cidade</a></th>
            <th>Rota</th>
            <th><a href="<?= htmlspecialchars($mkSort('peso_total_kg')) ?>" style="color:inherit;text-decoration:none">Peso</a></th>
            <th>Estado</th>
            <th style="width:110px">Ações</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($lista as $row): ?>
            <tr data-pedido="<?= htmlspecialchars(json_encode($row, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8') ?>">
                <td><?= Helpers::e($row['numero_pedido']) ?></td>
                <td><?= Helpers::e($row['nome_destinatario']) ?></td>
                <td><?= Helpers::e($row['cidade']) ?>/<?= Helpers::e($row['uf']) ?></td>
                <td><?= Helpers::e($row['rota_nome'] ?? '—') ?></td>
                <td><?= Helpers::e((string) $row['peso_total_kg']) ?></td>
                <td><span class="lb-tag-pill"><?= Helpers::e($row['estado']) ?></span></td>
                <td>
                    <button type="button" class="lb-btn lb-btn-quiet lb-open-modal-pedido" data-mode="edit"><i class="fa-solid fa-pen"></i></button>
                    <button type="button" class="lb-btn lb-btn-quiet lb-del-pedido" data-id="<?= (int) $row['id'] ?>"><i class="fa-solid fa-trash"></i></button>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div id="modal-pedido" class="lb-modal-mask" role="dialog" aria-modal="true">
    <div class="lb-modal" style="max-width:760px;width:94vw">
        <div class="lb-modal-head">
            <strong id="modal-pedido-title">Pedido</strong>
            <button type="button" class="lb-btn lb-btn-quiet lb-modal-close"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="lb-modal-body">
            <form id="form-pedido">
                <input type="hidden" name="id" id="pedido-id">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
                    <div><label class="lb-muted">Número</label><input class="lb-input" name="numero_pedido" required></div>
                    <div><label class="lb-muted">Estado pedido</label>
                        <select class="lb-input" name="estado">
                            <?php foreach (['pendente_roterizador','alocado_rota','em_viagem','entregue','cancelado'] as $ev): ?>
                                <option value="<?= $ev ?>"><?= $ev ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;margin-top:10px">
                    <div><label class="lb-muted">CPF destinatário</label><input class="lb-input" name="cpf_destinatario" id="pedido-cpf-dest" inputmode="numeric" maxlength="14" placeholder="11 dígitos" autocomplete="off"></div>
                    <div><label class="lb-muted">Destinatário</label><input class="lb-input" name="nome_destinatario" required></div>
                    <div><label class="lb-muted">Telefone</label><input class="lb-input" name="telefone_destinatario"></div>
                </div>
                <p class="lb-muted" style="font-size:.78rem;margin:6px 0 0">Com CPF informado, o sistema busca cadastro existente; ao gravar o pedido, endereço e coordenadas ficam salvos no cadastro do cliente.</p>

                <h4 style="margin:14px 0 8px;color:var(--lb-secondary-yellow)"><i class="fa-solid fa-location-dot"></i> Endereço de entrega</h4>
                <p class="lb-muted" style="font-size:.78rem;margin:0 0 10px">CEP primeiro: busca nacional (BrasilAPI / ViaCEP). Ajuste bairro e cidade conforme cadastro territorial das rotas.</p>

                <div style="display:grid;grid-template-columns:1fr auto;gap:10px;align-items:end;margin-top:10px">
                    <div>
                        <label class="lb-muted">CEP</label>
                        <input class="lb-input" name="cep" id="pedido-cep" inputmode="numeric" maxlength="12" placeholder="Somente números ou com hífen" autocomplete="shipping postal-code">
                    </div>
                    <button type="button" class="lb-btn lb-btn-secondary lb-buscar-cep" title="Consultar dados do CEP nos serviços públicos BrasilAPI/ViaCEP"><i class="fa-solid fa-magnifying-glass"></i> Buscar CEP</button>
                </div>

                <div style="display:grid;grid-template-columns:2fr 1fr;gap:10px;margin-top:10px">
                    <div><label class="lb-muted">Logradouro</label><input class="lb-input" name="logradouro" required id="pedido-logradouro"></div>
                    <div><label class="lb-muted">Número</label><input class="lb-input" name="numero" id="pedido-numero"></div>
                </div>
                <label class="lb-muted" style="margin-top:10px;display:block">Complemento</label>
                <input class="lb-input" name="complemento" id="pedido-complemento">

                <div style="display:grid;grid-template-columns:repeat(3, 1fr);gap:10px;margin-top:10px">
                    <div><label class="lb-muted">Bairro</label><input class="lb-input" name="bairro" required id="pedido-bairro"></div>
                    <div><label class="lb-muted">Cidade</label><input class="lb-input" name="cidade" required id="pedido-cidade"></div>
                    <div><label class="lb-muted">UF</label><input class="lb-input" name="uf" maxlength="2" id="pedido-uf"></div>
                </div>

                <label class="lb-muted" style="margin-top:10px;display:block">Referência da entrega</label>
                <input class="lb-input" name="referencia_entrega" placeholder="Pontos próximos, portaria, torre">

                <div style="margin-top:12px">
                    <label class="lb-muted">Rota planejamento</label>
                    <select class="lb-input" name="rota_id" id="pedido-rota-select">
                        <option value="">— Automática pela base territorial —</option>
                        <?php foreach (($rotas ?? []) as $r): ?>
                            <option value="<?= Helpers::e((string) $r['id']) ?>"><?= Helpers::e($r['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <small id="lb-rota-sug-label" class="lb-muted" style="display:block;margin-top:4px;font-size:.78rem">&nbsp;</small>
                </div>

                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:10px;margin-top:12px;align-items:end">
                    <div><label class="lb-muted">Latitude</label><input class="lb-input" name="latitude" id="pedido-lat" placeholder="Opcional"></div>
                    <div><label class="lb-muted">Longitude</label><input class="lb-input" name="longitude" id="pedido-lng" placeholder="Opcional"></div>
                    <button type="button" class="lb-btn lb-btn-quiet lb-buscar-geo"><i class="fa-solid fa-map-pin"></i> Buscar coordenadas</button>
                    <div><label class="lb-muted">Qtd entregas</label><input class="lb-input" name="quantidade_entregas" type="number" min="1" value="1"></div>
                    <div><label class="lb-muted">Peso total (kg)</label><input class="lb-input" name="peso_total_kg" type="number" step="0.001"></div>
                </div>
                <label class="lb-muted" style="margin-top:10px;display:block">Obs.</label><textarea class="lb-input" name="observacao_interna" rows="2"></textarea>

                <h4 style="margin:16px 0 8px;color:var(--lb-secondary-yellow)"><i class="fa-solid fa-list"></i> Itens da carga</h4>
                <div id="itens-dynamic"></div>
                <button type="button" class="lb-btn lb-btn-quiet lb-add-item" style="margin-top:8px"><i class="fa-solid fa-circle-plus"></i> Linha de item</button>

                <div style="margin-top:14px;display:flex;gap:8px;justify-content:flex-end">
                    <button type="button" class="lb-btn lb-btn-quiet lb-modal-close">Cancelar</button>
                    <button type="submit" class="lb-btn lb-btn-primary"><i class="fa-solid fa-floppy-disk"></i> Gravar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function(){
  const rotasSelect = <?= json_encode($rotas ?? [], JSON_UNESCAPED_UNICODE) ?>;
  window.__LB_PEDIDOS_ROTAS = rotasSelect;
  const btnCopy = document.getElementById('btn-copy-acompanhar');
  const inp = document.getElementById('link-acompanhar-publico');
  btnCopy?.addEventListener('click', async () => {
    const value = inp?.value || '';
    if (!value) return;
    try {
      await navigator.clipboard.writeText(value);
      btnCopy.innerHTML = '<i class="fa-solid fa-check"></i>';
      setTimeout(() => { btnCopy.innerHTML = '<i class="fa-regular fa-copy"></i>'; }, 1200);
    } catch (_) {
      inp?.select();
      document.execCommand('copy');
    }
  });
})();
</script>
