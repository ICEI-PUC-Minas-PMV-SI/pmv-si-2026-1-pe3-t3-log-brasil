<?php

use App\Core\Helpers;

$ordCampo = $ordCampo ?? 'numero_pedido';
$mkSort = fn (string $col) => CONF_BASE_URL . '/pedidos?' . http_build_query(array_merge($filtro, [
    'sort' => $col,
    'dir' => (($ordCampo === $col) && ($dir ?? 'ASC') === 'ASC') ? 'DESC' : 'ASC',
]));
$estadosAmig = [
    'pendente_roterizador' => ['Pendente planejamento', 'lb-status--pendente'],
    'alocado_rota' => ['Alocado na rota', 'lb-status--rota'],
    'em_viagem' => ['Em viagem', 'lb-status--viagem'],
    'entregue' => ['Entregue', 'lb-status--ok'],
    'cancelado' => ['Cancelado', 'lb-status--cancel'],
];
$help = static fn (string $t): string => '<span class="lb-help" tabindex="0" role="button" aria-label="Ajuda" data-lb-tip="' . Helpers::e($t) . '">?</span>';
?>
<section class="lb-page-heading">
    <h1><?= Helpers::e($title) ?></h1>
    <span class="lb-muted">Pedidos de entrega — o cadastro do cliente é feito pelo CPF do destinatário.</span>
</section>

<div class="lb-page-tip">
    <i class="fa-solid fa-user-group"></i>
    <div><strong>Clientes:</strong> informe o CPF no formulário de pedido. Endereço e coordenadas ficam salvos para reutilizar nos próximos pedidos do mesmo cliente. <?= $help('Não há tela separada de clientes: o vínculo é automático pelo CPF.') ?></div>
</div>

<div class="lb-toolbar-cadastro">
    <div class="lb-grow">
        <form method="get" class="lb-toolbar-cadastro" style="margin:0">
            <input type="search" class="lb-input" name="q" placeholder="Buscar número, destinatário, CPF ou cidade" value="<?= Helpers::e((string) $filtro['q']) ?>" style="max-width:320px">
            <select class="lb-input" name="estado" style="max-width:200px">
                <option value="">Todos estados do pedido</option>
                <?php foreach ($estadosAmig as $ev => $lbl): ?>
                    <option value="<?= Helpers::e($ev) ?>" <?= $filtro['estado']===$ev?'selected':'' ?>><?= Helpers::e($lbl[0]) ?></option>
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
                <td><?php $e = (string)$row['estado']; $ea = $estadosAmig[$e] ?? [$e, '']; ?>
                    <span class="lb-status <?= Helpers::e($ea[1]) ?>"><?= Helpers::e($ea[0]) ?></span></td>
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

                <details class="lb-advanced-panel" style="margin-bottom:14px;border-style:solid">
                    <summary><i class="fa-solid fa-circle-question"></i> Ajuda — o que significa cada campo</summary>
                    <div class="lb-advanced-panel__body" style="font-size:.84rem;line-height:1.55;color:var(--lb-muted)">
                        <ul style="margin:0;padding-left:18px">
                            <li><strong style="color:var(--lb-high)">Número</strong> — número identificador interno do cliente (ex.: NF, ordem ou código que a empresa usa para localizar o pedido).</li>
                            <li><strong style="color:var(--lb-high)">Situação</strong> — etapa do pedido no fluxo (planejamento, viagem, entregue etc.).</li>
                            <li><strong style="color:var(--lb-high)">CPF destinatário</strong> — cadastra ou reutiliza o cliente; endereço fica salvo para próximos pedidos.</li>
                            <li><strong style="color:var(--lb-high)">Número (endereço)</strong> — número da casa ou prédio no logradouro (não confundir com o número do pedido).</li>
                            <li><strong style="color:var(--lb-high)">Rota</strong> — área de entrega; pode ser sugerida automaticamente pelo endereço.</li>
                            <li><strong style="color:var(--lb-high)">Coordenadas (avançado)</strong> — posição no mapa; use o botão automático após preencher o endereço.</li>
                        </ul>
                    </div>
                </details>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
                    <div>
                        <label class="lb-field-label">Número <?= $help('Número identificador interno do cliente — ex.: nota fiscal, ordem de compra ou código usado pela sua operação para localizar este pedido.') ?></label>
                        <input class="lb-input" name="numero_pedido" required placeholder="Ex.: NF-2026-00482">
                    </div>
                    <div><label class="lb-field-label">Situação do pedido</label>
                        <select class="lb-input" name="estado">
                            <?php foreach ($estadosAmig as $ev => $lbl): ?>
                                <option value="<?= $ev ?>"><?= Helpers::e($lbl[0]) ?></option>
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
                    <div><label class="lb-field-label">Número do endereço <?= $help('Número da residência ou estabelecimento no logradouro — diferente do número identificador do pedido acima.') ?></label><input class="lb-input" name="numero" id="pedido-numero" placeholder="Ex.: 120 ou S/N"></div>
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
                    <div><label class="lb-field-label">Qtd. entregas (NF)</label><input class="lb-input" name="quantidade_entregas" type="number" min="1" value="1" placeholder="1"></div>
                    <div><label class="lb-field-label">Peso total (kg)</label><input class="lb-input" name="peso_total_kg" type="number" step="0.001" placeholder="Ex.: 125.500"></div>
                </div>

                <details class="lb-advanced-panel">
                    <summary><i class="fa-solid fa-location-crosshairs"></i> Coordenadas geográficas (avançado) <?= $help('Usadas no mapa e na roteirização. Normalmente preenchidas automaticamente pelo endereço.') ?></summary>
                    <div class="lb-advanced-panel__body">
                        <p class="lb-field-hint">Latitude e longitude permitem posicionar o pedido no mapa e calcular distâncias. Use o botão abaixo após preencher o endereço — não é necessário digitar manualmente na maioria dos casos.</p>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:10px">
                            <div>
                                <label class="lb-field-label">Latitude <?= $help('Norte/sul em graus decimais. Ex.: -23.55052') ?></label>
                                <input class="lb-input" name="latitude" id="pedido-lat" placeholder="Preenchido automaticamente" readonly>
                            </div>
                            <div>
                                <label class="lb-field-label">Longitude <?= $help('Leste/oeste. Ex.: -46.63331') ?></label>
                                <input class="lb-input" name="longitude" id="pedido-lng" placeholder="Preenchido automaticamente" readonly>
                            </div>
                        </div>
                        <button type="button" class="lb-btn lb-btn-secondary lb-buscar-geo" style="margin-top:10px"><i class="fa-solid fa-wand-magic-sparkles"></i> Obter coordenadas pelo endereço</button>
                    </div>
                </details>

                <label class="lb-field-label" style="margin-top:10px">Observação interna</label><textarea class="lb-input" name="observacao_interna" rows="2"></textarea>

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
