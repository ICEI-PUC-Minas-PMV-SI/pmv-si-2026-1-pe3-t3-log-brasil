<?php

use App\Core\Helpers;

$help = static fn (string $t): string => '<span class="lb-help" tabindex="0" role="button" aria-label="Ajuda" data-lb-tip="' . Helpers::e($t) . '">?</span>';
?>
<section class="lb-page-heading">
    <h1><?= Helpers::e($title) ?></h1>
    <span class="lb-muted">Condutores que executam viagens e acessam o app mobile.</span>
</section>

<div class="lb-toolbar-cadastro">
    <div class="lb-grow" style="display:flex;flex-wrap:wrap;gap:12px;align-items:center">
        <div class="lb-quick-search">
            <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
            <input type="search" class="lb-input" placeholder="Buscar nome, telefone ou CNH…" data-lb-table-search="#tbl-motoristas" aria-label="Buscar motoristas">
        </div>
        <div class="lb-card" style="padding:10px 14px;margin:0;display:inline-flex;gap:16px">
            <div><span class="lb-muted" style="font-size:.72rem">Ativos</span><div style="font-weight:700"><?= count(array_filter($lista ?? [], fn ($v) => (bool) $v['ativo'])) ?></div></div>
            <div><span class="lb-muted" style="font-size:.72rem">Terceirizados</span><div style="font-weight:700"><?= count(array_filter($lista ?? [], fn ($v) => (bool) $v['empresa_terceira'])) ?></div></div>
        </div>
    </div>
    <button type="button" class="lb-btn lb-btn-accent lb-open-motorista"><i class="fa-solid fa-plus"></i> Novo motorista</button>
</div>

<div class="lb-table-shell">
    <table class="lb-table" id="tbl-motoristas">
        <thead>
        <tr>
            <th data-sort="nome_completo">Nome</th>
            <th data-sort="telefone">Telefone</th>
            <th>CNH</th>
            <th>Terceiro</th>
            <th>Ativo</th>
            <th style="width:100px">Ações</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($lista ?? [] as $m): ?>
            <tr data-row="<?= htmlspecialchars(json_encode($m, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES) ?>">
                <td><?= Helpers::e($m['nome_completo']) ?></td>
                <td><?= Helpers::e($m['telefone']) ?></td>
                <td><?= Helpers::e(trim(($m['cnh_categoria'] ?? '') . ' ' . ($m['cnh_numero'] ?? ''))) ?></td>
                <td><?= ((bool) $m['empresa_terceira']) ? Helpers::e($m['nome_empresa_terceira'] ?? '') : 'Próprio' ?></td>
                <td><?= ((bool) $m['ativo']) ? 'Sim' : 'Não' ?></td>
                <td>
                    <button type="button" class="lb-btn lb-btn-quiet lb-edit-motorista" aria-label="Editar"><i class="fa-solid fa-pen"></i></button>
                    <button type="button" class="lb-btn lb-btn-quiet lb-del-motorista" data-id="<?= (int) $m['id'] ?>" aria-label="Excluir"><i class="fa-solid fa-trash"></i></button>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div id="modal-motorista" class="lb-modal-mask">
    <div class="lb-modal">
        <div class="lb-modal-head">
            <strong id="modal-motorista-title">Motorista</strong>
            <button type="button" class="lb-btn lb-btn-quiet lb-modal-close" aria-label="Fechar"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="lb-modal-body">
            <form id="form-motorista">
                <input type="hidden" name="id" id="motorista-id">
                <label class="lb-field-label">Nome completo</label>
                <input class="lb-input" name="nome_completo" required placeholder="Ex.: João da Silva">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:10px">
                    <div>
                        <label class="lb-field-label">CPF (somente números) <?= $help('Usado no login do app motorista.') ?></label>
                        <input class="lb-input" name="cpf" inputmode="numeric" placeholder="00000000000">
                    </div>
                    <div>
                        <label class="lb-field-label">Telefone</label>
                        <input class="lb-input" name="telefone" placeholder="(11) 99999-9999">
                    </div>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:10px">
                    <div>
                        <label class="lb-field-label">Número da CNH</label>
                        <input class="lb-input" name="cnh_numero" placeholder="Registro nacional">
                    </div>
                    <div>
                        <label class="lb-field-label">Categoria da CNH <?= $help('Ex.: B, C, D conforme habilitação.') ?></label>
                        <input class="lb-input" name="cnh_categoria" placeholder="Ex.: D" maxlength="10">
                    </div>
                </div>
                <label class="lb-field-label" style="margin-top:10px">E-mail</label>
                <input class="lb-input" name="email" type="email" placeholder="opcional@empresa.com">
                <label class="lb-field-label" style="margin-top:10px"><input type="checkbox" name="empresa_terceira"> Motorista terceirizado</label>
                <label class="lb-field-label">Nome da empresa contratada</label>
                <input class="lb-input" name="nome_empresa_terceira" placeholder="Preencher se terceirizado">
                <label class="lb-field-label" style="margin-top:12px">Senha do app motorista (mín. 8 caracteres)</label>
                <input class="lb-input" name="app_senha" type="password" autocomplete="new-password" placeholder="Defina na criação; em edição deixe vazio para manter">
                <label class="lb-field-label" style="margin-top:14px"><input type="checkbox" name="ativo" checked> Ativo — pode ser alocado em viagens</label>
                <div style="margin-top:14px;display:flex;gap:8px;justify-content:flex-end">
                    <button type="button" class="lb-btn lb-btn-quiet lb-modal-close">Cancelar</button>
                    <button type="submit" class="lb-btn lb-btn-primary"><i class="fa-solid fa-check"></i> Salvar motorista</button>
                </div>
            </form>
        </div>
    </div>
</div>
