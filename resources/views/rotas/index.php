<?php

use App\Core\Helpers;
?>
<section class="lb-page-heading">
    <h1><?= Helpers::e($title) ?></h1>
    <span class="lb-muted">Associação de cidades e bairros servidos por cada rota nomeada.</span>
</section>

<div class="lb-toolbar-cadastro">
    <div class="lb-grow">
        <div class="lb-card" style="padding:12px 16px;margin:0;display:inline-flex;gap:16px;align-items:center">
            <i class="fa-solid fa-route" style="font-size:1.5rem;color:var(--lb-secondary-yellow)"></i>
            <div class="lb-muted">Rotas ativas: <strong style="color:var(--lb-high)"><?= count(array_filter($rotas ?? [], fn($r)=>(bool)$r['ativo'])) ?></strong></div>
        </div>
    </div>
    <button type="button" class="lb-btn lb-btn-accent lb-open-rota"><i class="fa-solid fa-plus"></i> Nova rota</button>
</div>

<div class="lb-table-shell">
    <table class="lb-table" id="tbl-rotas">
        <thead>
        <tr>
            <th data-sort="nome">Nome</th>
            <th>Ativo</th>
            <th>Cidades vinculadas</th>
            <th>Bairros vinculados</th>
            <th style="width:180px">Ações</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($rotas ?? [] as $r): ?>
            <tr data-row="<?= htmlspecialchars(json_encode($r, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES), ENT_QUOTES) ?>">
                <td><?= Helpers::e($r['nome']) ?></td>
                <td><?= ((bool)$r['ativo']) ? 'Sim' : 'Não' ?></td>
                <td><?= count($r['_cidades'] ?? []) ?></td>
                <td><?= count($r['_bairros'] ?? []) ?></td>
                <td>
                    <button type="button" class="lb-btn lb-btn-quiet lb-territorio-rota" data-id="<?= (int)$r['id'] ?>"><i class="fa-solid fa-map"></i></button>
                    <button type="button" class="lb-btn lb-btn-quiet lb-edit-rota"><i class="fa-solid fa-pen"></i></button>
                    <button type="button" class="lb-btn lb-btn-quiet lb-del-rota" data-id="<?= (int)$r['id'] ?>"><i class="fa-solid fa-trash"></i></button>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script type="application/json" id="lb-rotas-json"><?= htmlspecialchars(json_encode($rotas ?? [], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES), ENT_NOQUOTES, 'UTF-8') ?></script>

<div id="modal-rota" class="lb-modal-mask">
    <div class="lb-modal">
        <div class="lb-modal-head">
            <strong>Rota</strong>
            <button type="button" class="lb-btn lb-btn-quiet lb-modal-close"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="lb-modal-body">
            <form id="form-rota">
                <input type="hidden" name="id" id="rota-id">
                <label class="lb-muted">Nome único</label>
                <input class="lb-input" name="nome" required>
                <label class="lb-muted" style="margin-top:10px;display:block">Observação</label>
                <textarea class="lb-input" name="observacao" rows="2"></textarea>
                <label class="lb-muted" style="margin-top:10px;display:block"><input type="checkbox" name="ativo" checked> Ativa</label>
                <div style="margin-top:14px;display:flex;gap:8px;justify-content:flex-end">
                    <button type="button" class="lb-btn lb-btn-quiet lb-modal-close">Cancelar</button>
                    <button type="submit" class="lb-btn lb-btn-primary"><i class="fa-solid fa-floppy-disk"></i> Gravar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="modal-territorio" class="lb-modal-mask">
    <div class="lb-modal" style="max-width:1020px">
        <div class="lb-modal-head">
            <strong id="territorio-title">Território da rota</strong>
            <button type="button" class="lb-btn lb-btn-quiet lb-modal-close"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="lb-modal-body">
            <div class="lb-map-rows">
                <div>
                    <h4 style="margin:0 0 8px;color:var(--lb-secondary-yellow)">Cidades atendidas</h4>
                    <form id="form-add-cidade" class="lb-toolbar-cadastro" style="margin-bottom:8px">
                        <input type="hidden" name="rota_id" id="territorio-rota-id">
                        <input class="lb-input" name="cidade" placeholder="Cidade" required style="flex:1">
                        <input class="lb-input" name="uf" placeholder="UF" maxlength="2" required style="max-width:80px">
                        <button class="lb-btn lb-btn-secondary" type="submit"><i class="fa-solid fa-plus"></i></button>
                    </form>
                    <div class="lb-table-shell" style="max-height:220px">
                        <table class="lb-table"><tbody id="tbl-cidades"></tbody></table>
                    </div>
                </div>
                <div>
                    <h4 style="margin:0 0 8px;color:var(--lb-secondary-yellow)">Bairros específicos</h4>
                    <form id="form-add-bairro" class="lb-toolbar-cadastro" style="margin-bottom:8px;flex-direction:column;align-items:stretch">
                        <div style="display:flex;gap:8px;flex-wrap:wrap">
                            <input class="lb-input" name="bairro" placeholder="Bairro" required style="flex:1;min-width:140px">
                            <input class="lb-input" name="cidade" placeholder="Cidade" required style="flex:1;min-width:120px">
                            <input class="lb-input" name="uf" placeholder="UF" maxlength="2" required style="max-width:70px">
                            <button class="lb-btn lb-btn-secondary" type="submit"><i class="fa-solid fa-plus"></i></button>
                        </div>
                    </form>
                    <div class="lb-table-shell" style="max-height:220px">
                        <table class="lb-table"><tbody id="tbl-bairros"></tbody></table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
