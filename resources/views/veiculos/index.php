<?php

use App\Core\Helpers;
?>
<section class="lb-page-heading">
    <h1><?= Helpers::e($title) ?></h1>
    <span class="lb-muted">Frota: placa única como chave visual de operação.</span>
</section>

<div class="lb-toolbar-cadastro">
    <div class="lb-grow">
        <div class="lb-card" style="display:inline-flex;align-items:center;gap:10px;margin:0;padding:12px">
            <i class="fa-solid fa-chart-column" aria-hidden="true"></i>
            <div><span class="lb-muted">Ativos cadastrados</span><div style="font-size:1.4rem;font-weight:700"><?= count(array_filter($lista ?? [], fn($v)=>(bool)$v['ativo'])) ?></div></div>
        </div>
    </div>
    <button type="button" class="lb-btn lb-btn-accent lb-open-veiculo"><i class="fa-solid fa-plus"></i> Inserir veículo</button>
</div>

<div class="lb-table-shell">
    <table class="lb-table" id="tbl-veiculos">
        <thead>
        <tr>
            <th data-sort="placa"><i class="fa-solid fa-sort"></i> Placa</th>
            <th data-sort="marca_modelo">Modelo</th>
            <th data-sort="capacidade_kg">Cap. kg</th>
            <th data-sort="tipo">Tipo</th>
            <th>Ativo</th>
            <th style="width:100px">Ações</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($lista ?? [] as $v): ?>
            <tr data-row="<?= htmlspecialchars(json_encode($v, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES), ENT_QUOTES) ?>">
                <td><?= Helpers::e($v['placa']) ?></td>
                <td><?= Helpers::e($v['marca_modelo']) ?></td>
                <td><?= Helpers::e((string)($v['capacidade_kg'] ?? '')) ?></td>
                <td><?= Helpers::e($v['tipo']) ?></td>
                <td><?= ((bool)$v['ativo']) ? 'Sim' : 'Não' ?></td>
                <td>
                    <button type="button" class="lb-btn lb-btn-quiet lb-edit-veiculo"><i class="fa-solid fa-pen"></i></button>
                    <button type="button" class="lb-btn lb-btn-quiet lb-del-veiculo" data-id="<?= (int)$v['id'] ?>"><i class="fa-solid fa-trash"></i></button>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div id="modal-veiculo" class="lb-modal-mask">
    <div class="lb-modal">
        <div class="lb-modal-head">
            <strong>Veículo</strong>
            <button type="button" class="lb-btn lb-btn-quiet lb-modal-close"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="lb-modal-body">
            <form id="form-veiculo">
                <input type="hidden" name="id" id="veiculo-id">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
                    <div><label class="lb-muted">Placa</label><input class="lb-input" name="placa" required></div>
                    <div><label class="lb-muted">Ano</label><input class="lb-input" name="ano" type="number"></div>
                </div>
                <label class="lb-muted" style="margin-top:10px;display:block">Descrição</label>
                <input class="lb-input" name="descricao">
                <label class="lb-muted" style="margin-top:10px;display:block">Marca / modelo</label>
                <input class="lb-input" name="marca_modelo">
                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;margin-top:10px">
                    <div><label class="lb-muted">Cap. kg</label><input class="lb-input" name="capacidade_kg" type="number" step="0.01"></div>
                    <div><label class="lb-muted">Tipo</label><input class="lb-input" name="tipo"></div>
                    <div><label class="lb-muted">Frota interna ref.</label><input class="lb-input" name="frota_interna"></div>
                </div>
                <label class="lb-muted" style="margin-top:10px;display:block"><input type="checkbox" name="ativo" checked> Ativo para planejamento</label>
                <div style="margin-top:14px;display:flex;gap:8px;justify-content:flex-end">
                    <button type="button" class="lb-btn lb-btn-quiet lb-modal-close">Cancelar</button>
                    <button type="submit" class="lb-btn lb-btn-primary"><i class="fa-solid fa-floppy-disk"></i> Gravar</button>
                </div>
            </form>
        </div>
    </div>
</div>
