<?php

use App\Core\Helpers;

$help = static fn (string $t): string => '<span class="lb-help" tabindex="0" role="button" aria-label="Ajuda" data-lb-tip="' . Helpers::e($t) . '">?</span>';
?>
<section class="lb-page-heading">
    <h1><?= Helpers::e($title) ?></h1>
    <span class="lb-muted">Frota utilizada no planejamento e execução das viagens.</span>
</section>

<div class="lb-toolbar-cadastro">
    <div class="lb-grow" style="display:flex;flex-wrap:wrap;gap:12px;align-items:center">
        <div class="lb-quick-search">
            <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
            <input type="search" class="lb-input" placeholder="Buscar placa, modelo ou tipo…" data-lb-table-search="#tbl-veiculos" aria-label="Buscar veículos">
        </div>
        <div class="lb-card" style="padding:10px 14px;margin:0">
            <span class="lb-muted" style="font-size:.72rem">Ativos</span>
            <div style="font-size:1.25rem;font-weight:700"><?= count(array_filter($lista ?? [], fn ($v) => (bool) $v['ativo'])) ?></div>
        </div>
    </div>
    <button type="button" class="lb-btn lb-btn-accent lb-open-veiculo"><i class="fa-solid fa-plus"></i> Novo veículo</button>
</div>

<div class="lb-table-shell">
    <table class="lb-table" id="tbl-veiculos">
        <thead>
        <tr>
            <th data-sort="placa"><i class="fa-solid fa-sort"></i> Placa</th>
            <th data-sort="marca_modelo">Modelo</th>
            <th data-sort="capacidade_kg">Capacidade (kg)</th>
            <th data-sort="tipo">Tipo do veículo</th>
            <th>Ativo</th>
            <th style="width:100px">Ações</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($lista ?? [] as $v): ?>
            <tr data-row="<?= htmlspecialchars(json_encode($v, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES) ?>">
                <td><?= Helpers::e($v['placa']) ?></td>
                <td><?= Helpers::e($v['marca_modelo']) ?></td>
                <td><?= Helpers::e((string) ($v['capacidade_kg'] ?? '')) ?></td>
                <td><?= Helpers::e($v['tipo']) ?></td>
                <td><?= ((bool) $v['ativo']) ? 'Sim' : 'Não' ?></td>
                <td>
                    <button type="button" class="lb-btn lb-btn-quiet lb-edit-veiculo" aria-label="Editar"><i class="fa-solid fa-pen"></i></button>
                    <button type="button" class="lb-btn lb-btn-quiet lb-del-veiculo" data-id="<?= (int) $v['id'] ?>" aria-label="Excluir"><i class="fa-solid fa-trash"></i></button>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div id="modal-veiculo" class="lb-modal-mask">
    <div class="lb-modal">
        <div class="lb-modal-head">
            <strong id="modal-veiculo-title">Veículo</strong>
            <button type="button" class="lb-btn lb-btn-quiet lb-modal-close" aria-label="Fechar"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="lb-modal-body">
            <form id="form-veiculo">
                <input type="hidden" name="id" id="veiculo-id">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
                    <div>
                        <label class="lb-field-label">Placa</label>
                        <input class="lb-input" name="placa" required placeholder="ABC1D23">
                    </div>
                    <div>
                        <label class="lb-field-label">Ano</label>
                        <input class="lb-input" name="ano" type="number" placeholder="2022">
                    </div>
                </div>
                <label class="lb-field-label" style="margin-top:10px">Descrição</label>
                <input class="lb-input" name="descricao" placeholder="Ex.: Baú refrigerado">
                <label class="lb-field-label" style="margin-top:10px">Marca / modelo</label>
                <input class="lb-input" name="marca_modelo" placeholder="Ex.: Mercedes Accelo">
                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;margin-top:10px">
                    <div>
                        <label class="lb-field-label">Capacidade (kg)</label>
                        <input class="lb-input" name="capacidade_kg" type="number" step="0.01" placeholder="3500">
                    </div>
                    <div>
                        <label class="lb-field-label">Tipo do veículo <?= $help('Ex.: VUC, 3/4, Toco, Carreta.') ?></label>
                        <input class="lb-input" name="tipo" placeholder="Ex.: VUC">
                    </div>
                    <div>
                        <label class="lb-field-label">Referência da frota interna <?= $help('Código interno da transportadora, se houver.') ?></label>
                        <input class="lb-input" name="frota_interna" placeholder="Ex.: F-042">
                    </div>
                </div>
                <label class="lb-field-label" style="margin-top:10px"><input type="checkbox" name="ativo" checked> Ativo para planejamento de viagens</label>
                <div style="margin-top:14px;display:flex;gap:8px;justify-content:flex-end">
                    <button type="button" class="lb-btn lb-btn-quiet lb-modal-close">Cancelar</button>
                    <button type="submit" class="lb-btn lb-btn-primary"><i class="fa-solid fa-check"></i> Salvar veículo</button>
                </div>
            </form>
        </div>
    </div>
</div>
