<?php

use App\Core\Helpers;
?>
<section class="lb-page-heading">
    <h1><?= Helpers::e($title) ?></h1>
    <span class="lb-muted">Condutores operacionais próprios e terceiros.</span>
</section>

<div class="lb-toolbar-cadastro">
    <div class="lb-grow">
        <div class="lb-grid-metrics" style="margin:0;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));width:100%">
            <div class="lb-card" style="padding:14px;margin:0">
                <div class="lb-muted" style="font-size:.73rem;text-transform:uppercase">Licença ativas</div>
                <div style="font-size:1.45rem;font-weight:700;margin-top:4px"><?= count(array_filter($lista ?? [], fn($v)=>(bool)$v['ativo'])) ?></div>
            </div>
            <div class="lb-card" style="padding:14px;margin:0">
                <div class="lb-muted" style="font-size:.73rem;text-transform:uppercase">Terceirizados</div>
                <div style="font-size:1.45rem;font-weight:700;margin-top:4px"><?= count(array_filter($lista ?? [], fn($v)=>(bool)$v['empresa_terceira'])) ?></div>
            </div>
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
            <tr data-row="<?= htmlspecialchars(json_encode($m, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES), ENT_QUOTES) ?>">
                <td><?= Helpers::e($m['nome_completo']) ?></td>
                <td><?= Helpers::e($m['telefone']) ?></td>
                <td><?= Helpers::e(trim(($m['cnh_categoria']??'').' '.($m['cnh_numero']??''))) ?></td>
                <td><?= ((bool)$m['empresa_terceira']) ? Helpers::e($m['nome_empresa_terceira']??'') : 'Próprio' ?></td>
                <td><?= ((bool)$m['ativo']) ? 'Sim' : 'Não' ?></td>
                <td>
                    <button type="button" class="lb-btn lb-btn-quiet lb-edit-motorista"><i class="fa-solid fa-pen"></i></button>
                    <button type="button" class="lb-btn lb-btn-quiet lb-del-motorista" data-id="<?= (int)$m['id'] ?>"><i class="fa-solid fa-trash"></i></button>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div id="modal-motorista" class="lb-modal-mask">
    <div class="lb-modal">
        <div class="lb-modal-head">
            <strong>Motorista</strong>
            <button type="button" class="lb-btn lb-btn-quiet lb-modal-close"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="lb-modal-body">
            <form id="form-motorista">
                <input type="hidden" name="id" id="motorista-id">
                <label class="lb-muted">Nome completo</label>
                <input class="lb-input" name="nome_completo" required>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:10px">
                    <div><label class="lb-muted">CPF só dígitos</label><input class="lb-input" name="cpf"></div>
                    <div><label class="lb-muted">Telefone</label><input class="lb-input" name="telefone"></div>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:10px">
                    <div><label class="lb-muted">CNH número</label><input class="lb-input" name="cnh_numero"></div>
                    <div><label class="lb-muted">CNH cat.</label><input class="lb-input" name="cnh_categoria"></div>
                </div>
                <label class="lb-muted" style="margin-top:10px;display:block">E-mail</label>
                <input class="lb-input" name="email" type="email">
                <label class="lb-muted" style="margin-top:10px;display:block"><input type="checkbox" name="empresa_terceira"> Terceirizado</label>
                <label class="lb-muted">Empresa contratada</label>
                <input class="lb-input" name="nome_empresa_terceira">
                <label class="lb-muted" style="margin-top:12px;display:block">Senha do app motorista (min. 8)</label>
                <input class="lb-input" name="app_senha" type="password" autocomplete="new-password" placeholder="Nova senha ao cadastrar; em edição deixar em branco para manter">
                <label class="lb-muted" style="margin-top:14px;display:block"><input type="checkbox" name="ativo" checked> Ativo para viagens</label>
                <div style="margin-top:14px;display:flex;gap:8px;justify-content:flex-end">
                    <button type="button" class="lb-btn lb-btn-quiet lb-modal-close">Cancelar</button>
                    <button type="submit" class="lb-btn lb-btn-primary"><i class="fa-solid fa-floppy-disk"></i> Gravar</button>
                </div>
            </form>
        </div>
    </div>
</div>
