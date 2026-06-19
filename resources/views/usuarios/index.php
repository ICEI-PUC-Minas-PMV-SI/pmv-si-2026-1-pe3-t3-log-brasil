<?php

use App\Core\Helpers;

// @var array<int,array<string,mixed>> $lista
// @var list<string> $papeis
?>
<section class="lb-page-heading">
    <h1><?= Helpers::e($title) ?></h1>
    <span class="lb-muted">Somente administradores podem criar contas para o sistema e vincular o CPF aos clientes.</span>
</section>

<button type="button" class="lb-btn lb-btn-primary lb-open-usuario"><i class="fa-solid fa-user-plus"></i> Novo usuário</button>

<div class="lb-table-shell" style="margin-top:18px">
    <table class="lb-table">
        <thead>
        <tr>
            <th>Nome</th>
            <th>E-mail</th>
            <th>Perfil de acesso</th>
            <th>CPF acompanh.</th>
            <th>Ativo</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($lista ?? [] as $row): ?>
            <?php
            $a = $row['ativo'] ?? false;
            $aStr = strtolower(trim((string) $a));
            $ativoSim = ($a === true || $a === 1 || $aStr === 't' || $aStr === '1' || $aStr === 'true');
            ?>
            <tr>
                <td><?= Helpers::e((string) ($row['nome_completo'] ?? '')) ?></td>
                <td><?= Helpers::e((string) ($row['email'] ?? '')) ?></td>
                <td><?= Helpers::e(Helpers::papelRotulo((string) ($row['papel'] ?? ''))) ?></td>
                <td><?= Helpers::e((string) ($row['acompanhar_cpf'] ?? '') ?: '—') ?></td>
                <td><?= $ativoSim ? 'Sim' : 'Não' ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div id="modal-usuario" class="lb-modal-mask">
    <div class="lb-modal" style="max-width:480px">
        <div class="lb-modal-head">
            <strong>Novo usuário</strong>
            <button type="button" class="lb-btn lb-btn-quiet lb-modal-close"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="lb-modal-body">
            <label class="lb-muted">Nome completo</label>
            <input class="lb-input" id="u-nome" required>

            <label class="lb-muted" style="display:block;margin-top:12px">E-mail (login)</label>
            <input class="lb-input" id="u-email" type="email" required>

            <label class="lb-muted" style="display:block;margin-top:12px">Senha (mínimo 8)</label>
            <input class="lb-input" id="u-senha" type="password" autocomplete="new-password" required>

            <label class="lb-muted" style="display:block;margin-top:12px">Perfil de acesso</label>
            <select class="lb-input" id="u-papel" title="Define o que o usuário pode fazer no painel">
                <?php foreach (Helpers::papeisFormulario() as $pf): ?>
                    <option value="<?= Helpers::e($pf['value']) ?>"><?= Helpers::e($pf['label']) ?></option>
                <?php endforeach; ?>
            </select>

            <div id="u-cpf-row" style="display:none;margin-top:12px">
                <label class="lb-muted">CPF cliente (somente perfil Cliente portal)</label>
                <input class="lb-input" id="u-cliente-cpf" inputmode="numeric" maxlength="14" placeholder="11 dígitos">
            </div>

            <label class="lb-muted" style="display:block;margin-top:14px"><input type="checkbox" id="u-ativo" checked> Usuário ativo</label>

            <div style="margin-top:16px;display:flex;justify-content:flex-end;gap:8px">
                <button type="button" class="lb-btn lb-btn-quiet lb-modal-close">Fechar</button>
                <button type="button" class="lb-btn lb-btn-primary" id="u-save"><i class="fa-solid fa-floppy-disk"></i> Criar</button>
            </div>
        </div>
    </div>
</div>

<script>
(function(){
  var modal=document.getElementById("modal-usuario");
  document.querySelector(".lb-open-usuario")?.addEventListener("click", function(){ modal.classList.add("is-open");});
  document.querySelectorAll("#modal-usuario .lb-modal-close").forEach(function(b){ b.addEventListener("click", function(){ modal.classList.remove("is-open"); }); });

  var sel=document.getElementById("u-papel"), row=document.getElementById("u-cpf-row");
  sel.addEventListener("change", function(){ row.style.display = sel.value === "cliente" ? "" : "none"; });
  sel.dispatchEvent(new Event("change"));

  document.getElementById("u-save").addEventListener("click", async function(){
    var body = {
      nome_completo: document.getElementById("u-nome").value.trim(),
      email: document.getElementById("u-email").value.trim(),
      senha: document.getElementById("u-senha").value,
      papel: document.getElementById("u-papel").value,
      ativo: document.getElementById("u-ativo").checked,
      cliente_cpf: document.getElementById("u-cliente-cpf").value.trim(),
      _csrf: window.LOGBR.csrf
    };
    if (!body.email || body.nome_completo.length<3) return alert("Preencha nome e e-mail");
    var cfg = window.LOGBR;
    var r = await fetch(cfg.baseUrl+"/api/usuarios", { method:"POST", headers: {"Content-Type":"application/json","Accept":"application/json"}, body: JSON.stringify(body)});
    var j = await r.json().catch(function(){return {};});
    if (!j.ok) { alert(j.message||"Erro"); return;}
    location.reload();
  });
})();
</script>
