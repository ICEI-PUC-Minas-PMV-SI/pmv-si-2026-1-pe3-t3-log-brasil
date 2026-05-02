/**
 * Scripts front-end compartilhados: AJAX com CSRF, modais cadastro, roteirizador e viagens.
 */

(function () {
  const CFG = window.LOGBR || { baseUrl: "", csrf: "" };

  function lbEsc(s) {
    const d = document.createElement("div");
    d.textContent = s == null ? "" : String(s);
    return d.innerHTML;
  }

  /** Data/hora vinda da API PostgreSQL ISO → texto local. */
  function lbFmtTs(s) {
    if (!s) return "—";
    const t = Date.parse(String(s));
    if (Number.isNaN(t)) return lbEsc(String(s));
    return new Date(t).toLocaleString("pt-BR", { dateStyle: "short", timeStyle: "short" });
  }

  function lbParadaEstadoLabel(e) {
    const m = {
      pendente: "Aguardando início",
      indo: "Em deslocamento",
      entrega_feita: "Entregue (comprov.)",
      divergencia_aguardando: "Divergência (análise)",
      resolvido_divergencia: "Divergência OK",
    };
    return m[e] || lbEsc(String(e || "—"));
  }

  /** Caminho relativo em uploads ou data URL já salva legacy. */
  function lbUploadOrDataMediaUrl(rel) {
    if (!rel) return "";
    const s = String(rel).trim();
    if (!s) return "";
    if (s.startsWith("data:image/")) return s;
    const base = (CFG.baseUrl || "").replace(/\/$/, "");
    const path = s.replace(/^\/+/, "").replace(/^uploads\//, "");
    return base + "/uploads/" + path;
  }

  /** Debounce antes de cobrir a tela: evita piscadas em respostas muito rápidas. POST com navegação usa `immediate`. */
  const LB_BUSY_DELAY_MS = 150;

  let lbBusyDepth = 0;
  let lbBusyRevealTimer = null;

  function lbBusyShowLayer() {
    const el = document.getElementById("lb-busy-mask");
    if (!el) return;
    el.classList.add("is-active");
    el.setAttribute("aria-hidden", "false");
    el.setAttribute("aria-busy", "true");
  }

  function lbBusyHideLayer() {
    const el = document.getElementById("lb-busy-mask");
    if (!el) return;
    el.classList.remove("is-active");
    el.setAttribute("aria-hidden", "true");
    el.setAttribute("aria-busy", "false");
  }

  function lbBusyEnter(immediate) {
    lbBusyDepth++;
    if (immediate) {
      if (lbBusyRevealTimer !== null) {
        clearTimeout(lbBusyRevealTimer);
        lbBusyRevealTimer = null;
      }
      lbBusyShowLayer();
      return;
    }
    if (lbBusyDepth === 1 && lbBusyRevealTimer === null) {
      lbBusyRevealTimer = setTimeout(() => {
        lbBusyRevealTimer = null;
        if (lbBusyDepth > 0) lbBusyShowLayer();
      }, LB_BUSY_DELAY_MS);
    }
  }

  function lbBusyLeave() {
    lbBusyDepth = Math.max(0, lbBusyDepth - 1);
    if (lbBusyDepth > 0) return;
    if (lbBusyRevealTimer !== null) {
      clearTimeout(lbBusyRevealTimer);
      lbBusyRevealTimer = null;
    }
    lbBusyHideLayer();
  }

  document.addEventListener(
    "submit",
    function (e) {
      const form = e.target;
      if (!(form instanceof HTMLFormElement)) return;
      if (form.dataset.lbNoBusy !== undefined) return;
      const method = (form.getAttribute("method") || "get").toLowerCase();
      if (method !== "post") return;
      if (e.defaultPrevented) return;
      lbBusyEnter(true);
    },
    false
  );

  /** Monta headers JSON garantindo CSRF em corpo sempre que body existir como objeto literal. Opcional `{ noBusy: true }`. */
  async function lbJson(path, opts) {
    opts = opts || {};
    const noBusy = opts.noBusy === true;
    const fetchOpts = Object.assign({}, opts);
    delete fetchOpts.noBusy;

    const url = CFG.baseUrl + path;
    const headers = Object.assign({ Accept: "application/json" }, fetchOpts.headers || {});
    const method = fetchOpts.method || "GET";
    let body = fetchOpts.body;
    if (body && typeof body === "object" && !(body instanceof FormData)) {
      headers["Content-Type"] = headers["Content-Type"] || "application/json";
      body = JSON.stringify(Object.assign({ _csrf: CFG.csrf }, body));
    }

    if (!noBusy) lbBusyEnter(false);
    try {
      const res = await fetch(url, Object.assign({}, fetchOpts, { method, headers, body }));
      const txt = await res.text();
      let data = {};
      try {
        data = txt ? JSON.parse(txt) : {};
      } catch {
        alert("Resposta não JSON.");
        return { ok: false };
      }
      if (!data.ok && data.message) {
        alert(data.message);
      }
      return data;
    } catch (err) {
      alert(err && err.message ? err.message : "Falha de rede ou servidor.");
      return { ok: false };
    } finally {
      if (!noBusy) lbBusyLeave();
    }
  }

  /** Abre/fecha modais marcados pela classe máscara `.lb-modal-mask`. */
  function bindModalClose(root) {
    root.querySelectorAll(".lb-modal-close").forEach((btn) =>
      btn.addEventListener("click", () => {
        const mask = btn.closest(".lb-modal-mask");
        if (mask) mask.classList.remove("is-open");
      })
    );
  }

  bindModalClose(document);

  /** Ordena tabela simples por texto em colunas com `data-sort`. */
  function sortTable(tbl, idx, asc) {
    const tb = tbl.tBodies[0];
    const rows = Array.from(tb.querySelectorAll("tr"));
    rows.sort((ra, rb) => {
      const a = ra.cells[idx].innerText.trim();
      const b = rb.cells[idx].innerText.trim();
      const na = parseFloat(a.replace(",", "."));
      const nb = parseFloat(b.replace(",", "."));
      if (!isNaN(na) && !isNaN(nb)) return asc ? na - nb : nb - na;
      return asc ? a.localeCompare(b) : b.localeCompare(a);
    });
    rows.forEach((r) => tb.appendChild(r));
  }

  document.querySelectorAll("table.lb-table thead th[data-sort]").forEach((th) => {
    th.addEventListener("click", () => {
      const tbl = th.closest("table");
      const idx = th.cellIndex;
      const asc = th.dataset.asc !== "1";
      th.closest("thead").querySelectorAll("th").forEach((h) => (h.dataset.asc = "0"));
      th.dataset.asc = asc ? "1" : "0";
      sortTable(tbl, idx, asc);
    });
  });

  /* ===================== Pedidos ===================== */
  const modalPedido = document.getElementById("modal-pedido");
  const formPedido = document.getElementById("form-pedido");
  if (modalPedido && formPedido) {
    function pedidoGet(name) {
      const el = formPedido.querySelector(`[name="${name}"]`);
      return el ? String(el.value || "").trim() : "";
    }

    let rotaDeb;
    let cpfClienteDeb;

    function aplicarDadosCliente(cli) {
      if (!cli || typeof cli !== "object") return;
      const nf = (x) => (x != null && x !== "" ? String(x) : "");
      const nome = formPedido.querySelector("[name=nome_destinatario]");
      const tel = formPedido.querySelector("[name=telefone_destinatario]");
      if (nome) nome.value = nf(cli.nome_completo);
      if (tel) tel.value = nf(cli.telefone);
      const lg = formPedido.querySelector("[name=logradouro]");
      const nm = formPedido.querySelector("[name=numero]");
      const cp = formPedido.querySelector("[name=complemento]");
      if (lg) lg.value = nf(cli.logradouro);
      if (nm) nm.value = nf(cli.numero);
      if (cp) cp.value = nf(cli.complemento);
      formPedido.querySelector("[name=bairro]").value = nf(cli.bairro);
      formPedido.querySelector("[name=cidade]").value = nf(cli.cidade);
      formPedido.querySelector("[name=uf]").value = nf(cli.uf);
      formPedido.querySelector("[name=cep]").value = nf(cli.cep);
      const ref = formPedido.querySelector("[name=referencia_entrega]");
      if (ref) ref.value = nf(cli.referencia_entrega);
      const lat = parseFloat(cli.latitude);
      const lng = parseFloat(cli.longitude);
      const la = document.getElementById("pedido-lat");
      const lo = document.getElementById("pedido-lng");
      if (la && lo && !Number.isNaN(lat) && !Number.isNaN(lng) && lat !== 0 && lng !== 0) {
        la.value = String(lat);
        lo.value = String(lng);
      }
    }

    async function tentarCarregarClientePorCpf() {
      const raw = pedidoGet("cpf_destinatario");
      const dig = raw.replace(/\D/g, "");
      if (dig.length !== 11) return;
      const d = await lbJson("/api/cliente/por-cpf", { method: "POST", body: { cpf: dig } });
      if (!d.ok) return;
      aplicarDadosCliente(d.cliente);
      await sugerirRotaPedido();
    }

    async function sugerirRotaPedido() {
      const hint = document.getElementById("lb-rota-sug-label");
      const sel = formPedido.querySelector('[name="rota_id"]');
      if (!hint || !sel) return;
      const b = pedidoGet("bairro");
      const c = pedidoGet("cidade");
      const uf = pedidoGet("uf");
      if (!uf || uf.length < 2) {
        hint.innerHTML = "&nbsp;";
        return;
      }
      const d = await lbJson("/api/pedidos/sugerir-rota", {
        method: "POST",
        body: { bairro: b, cidade: c, uf: uf.toUpperCase() },
      });
      const isEdit = !!document.getElementById("pedido-id").value;

      if (d.ok && d.rota_id != null) {
        if (!isEdit) sel.value = String(d.rota_id);
        hint.textContent =
          "Rota aplicada conforme bairro/cidade e cadastro: " + (d.rota_nome || "ID " + d.rota_id);
      } else if (d.ok) {
        if (!isEdit) sel.value = "";
        hint.textContent =
          "Nenhuma rota automática para este bairro/cidade — selecione uma rota manualmente ou atualize o cadastro de território.";
      }
    }

    function agendarSugerirRotaPedido() {
      clearTimeout(rotaDeb);
      rotaDeb = setTimeout(sugerirRotaPedido, 380);
    }

    ["bairro", "cidade", "uf"].forEach((n) => {
      const el = formPedido.querySelector(`[name="${n}"]`);
      if (!el) return;
      el.addEventListener("input", agendarSugerirRotaPedido);
      el.addEventListener("change", agendarSugerirRotaPedido);
    });

    /** Delegação garante funcionamento mesmo com modais/forçar repaint (evita alvo “morto” no clique). */
    formPedido.addEventListener("click", async (ev) => {
      const cepBtn = ev.target.closest(".lb-buscar-cep");
      if (cepBtn && formPedido.contains(cepBtn)) {
        const raw = pedidoGet("cep");
        const dig = raw.replace(/\D/g, "");
        if (dig.length !== 8) {
          alert("Informe o CEP com 8 digitos.");
          return;
        }
        const d = await lbJson("/api/cep", { method: "POST", body: { cep: raw } });
        if (!d.ok || !d.endereco) return;
        const ende = d.endereco;
        const lr = formPedido.querySelector("[name=logradouro]");
        if (lr) lr.value = ende.logradouro || "";
        formPedido.querySelector("[name=bairro]").value = ende.bairro || "";
        formPedido.querySelector("[name=cidade]").value = ende.cidade || "";
        formPedido.querySelector("[name=uf]").value = ende.uf || "";
        formPedido.querySelector("[name=cep]").value = ende.cep || dig;
        void sugerirRotaPedido();
        return;
      }
      const geoBtn = ev.target.closest(".lb-buscar-geo");
      if (!geoBtn || !formPedido.contains(geoBtn)) return;
      const cpfDig = pedidoGet("cpf_destinatario").replace(/\D/g, "");
      const bodyGeo = {
        logradouro: pedidoGet("logradouro"),
        numero: pedidoGet("numero"),
        complemento: pedidoGet("complemento"),
        bairro: pedidoGet("bairro"),
        cidade: pedidoGet("cidade"),
        uf: pedidoGet("uf"),
      };
      if (cpfDig.length === 11) bodyGeo.cpf = cpfDig;
      const gd = await lbJson("/api/endereco-geocode", { method: "POST", body: bodyGeo });
      if (!gd.ok) return;
      const las = document.getElementById("pedido-lat");
      const los = document.getElementById("pedido-lng");
      if (las) las.value = String(gd.latitude);
      if (los) los.value = String(gd.longitude);
    });

    document.getElementById("pedido-cep")?.addEventListener("keydown", function (ev) {
      if (ev.key !== "Enter") return;
      ev.preventDefault();
      const btn = formPedido.querySelector(".lb-buscar-cep");
      if (btn) btn.click();
    });

    const pedidoCpfEl = document.getElementById("pedido-cpf-dest");
    if (pedidoCpfEl) {
      pedidoCpfEl.addEventListener("blur", () => {
        void tentarCarregarClientePorCpf();
      });
      pedidoCpfEl.addEventListener("input", () => {
        clearTimeout(cpfClienteDeb);
        const dig = pedidoGet("cpf_destinatario").replace(/\D/g, "");
        if (dig.length !== 11) return;
        cpfClienteDeb = setTimeout(() => {
          void tentarCarregarClientePorCpf();
        }, 450);
      });
    }

    function lbAddLinhaItem(pref) {
      const box = document.getElementById("itens-dynamic");
      const row = document.createElement("div");
      row.style.cssText = "display:grid;grid-template-columns:2fr 1fr 1fr 1fr;gap:8px;margin-bottom:8px";
      const inp = (ph, type, val) => {
        const el = document.createElement("input");
        el.className = "lb-input";
        if (type) el.type = type;
        if (ph) el.placeholder = ph;
        if (val != null && val !== "") el.value = val;
        return el;
      };
      const d = inp("Descrição", null, pref ? pref.descricao || "" : "");
      d.name = "it_desc";
      const q = inp(null, "number", pref ? pref.quantidade : 1);
      q.step = "0.01";
      q.name = "it_qtd";
      const p = inp("Peso un.", "number", pref && pref.peso_unit_kg != null ? pref.peso_unit_kg : "");
      p.step = "0.001";
      p.name = "it_peso";
      const s = inp("SKU", null, pref && pref.sku ? pref.sku : "");
      s.name = "it_sku";
      row.appendChild(d);
      row.appendChild(q);
      row.appendChild(p);
      row.appendChild(s);
      box.appendChild(row);
    }

    function openPedido(btn) {
      const mode = btn.dataset.mode;
      formPedido.reset();
      document.getElementById("pedido-id").value = "";
      document.getElementById("itens-dynamic").innerHTML = "";
      document.getElementById("modal-pedido-title").textContent =
        mode === "create" ? "Novo pedido" : "Editar pedido";
      const sug = document.getElementById("lb-rota-sug-label");
      if (sug) sug.textContent = " ";
      if (mode === "create") {
        lbAddLinhaItem();
        modalPedido.classList.add("is-open");
        return;
      }
      const tr = btn.closest("tr[data-pedido]");
      if (!tr) return;
      const row = JSON.parse(tr.dataset.pedido || "{}");
      document.getElementById("pedido-id").value = row.id || "";
      formPedido.querySelector("[name=numero_pedido]").value = row.numero_pedido || "";
      formPedido.querySelector("[name=estado]").value = row.estado || "";
      const cf = formPedido.querySelector("[name=cpf_destinatario]");
      if (cf) cf.value = row.cliente_cpf || "";
      formPedido.querySelector("[name=nome_destinatario]").value = row.nome_destinatario || "";
      formPedido.querySelector("[name=telefone_destinatario]").value = row.telefone_destinatario || "";
      formPedido.querySelector("[name=logradouro]").value = row.logradouro || "";
      formPedido.querySelector("[name=numero]").value = row.numero || "";
      const comp = formPedido.querySelector("[name=complemento]");
      if (comp) comp.value = row.complemento || "";
      formPedido.querySelector("[name=bairro]").value = row.bairro || "";
      formPedido.querySelector("[name=cidade]").value = row.cidade || "";
      formPedido.querySelector("[name=uf]").value = row.uf || "";
      formPedido.querySelector("[name=cep]").value = row.cep || "";
      formPedido.querySelector("[name=referencia_entrega]").value = row.referencia_entrega || "";
      formPedido.querySelector("[name=latitude]").value = row.latitude || "";
      formPedido.querySelector("[name=longitude]").value = row.longitude || "";
      formPedido.querySelector("[name=quantidade_entregas]").value = row.quantidade_entregas || 1;
      formPedido.querySelector("[name=peso_total_kg]").value = row.peso_total_kg || "";
      formPedido.querySelector("[name=observacao_interna]").value = row.observacao_interna || "";
      const sr = formPedido.querySelector('[name="rota_id"]');
      if (sr)
        sr.value =
          row.rota_id !== undefined && row.rota_id !== null && row.rota_id !== ""
            ? String(row.rota_id)
            : "";

      lbJson("/api/pedido/" + row.id + "/itens", { method: "GET" }).then(async (d) => {
        const box = document.getElementById("itens-dynamic");
        box.innerHTML = "";
        (d.itens || []).forEach((it) => lbAddLinhaItem(it));
        if (!d.itens || !d.itens.length) lbAddLinhaItem();
        await sugerirRotaPedido();
        modalPedido.classList.add("is-open");
      });
    }

    document.querySelectorAll(".lb-open-modal-pedido").forEach((b) =>
      b.addEventListener("click", () => openPedido(b))
    );
    document.querySelector(".lb-add-item")?.addEventListener("click", () => lbAddLinhaItem());

    formPedido.addEventListener("submit", async (e) => {
      e.preventDefault();
      const id = document.getElementById("pedido-id").value;
      const fd = new FormData(formPedido);
      const itens = [];
      document.querySelectorAll("#itens-dynamic > div").forEach((div) => {
        const desc = div.querySelector("[name=it_desc]").value.trim();
        if (!desc) return;
        itens.push({
          descricao: desc,
          quantidade: parseFloat(div.querySelector("[name=it_qtd]").value || "1"),
          peso_unit_kg: div.querySelector("[name=it_peso]").value
            ? parseFloat(div.querySelector("[name=it_peso]").value)
            : null,
          sku: div.querySelector("[name=it_sku]").value || null,
        });
      });
      const rotaRaw = fd.get("rota_id");
      let rotaIdPayload = null;
      if (rotaRaw !== "" && rotaRaw != null) {
        const p = parseInt(String(rotaRaw), 10);
        if (!Number.isNaN(p)) rotaIdPayload = p;
      }
      const cpfDigits = String(fd.get("cpf_destinatario") ?? "").replace(/\D/g, "");
      const payload = {
        numero_pedido: fd.get("numero_pedido"),
        estado: fd.get("estado"),
        cpf: cpfDigits.length === 11 ? cpfDigits : "",
        nome_destinatario: fd.get("nome_destinatario"),
        telefone_destinatario: fd.get("telefone_destinatario"),
        logradouro: fd.get("logradouro"),
        numero: fd.get("numero"),
        complemento: fd.get("complemento"),
        bairro: fd.get("bairro"),
        cidade: fd.get("cidade"),
        uf: fd.get("uf"),
        cep: fd.get("cep"),
        referencia_entrega: fd.get("referencia_entrega"),
        latitude: parseFloat(fd.get("latitude") || "0"),
        longitude: parseFloat(fd.get("longitude") || "0"),
        quantidade_entregas: parseInt(fd.get("quantidade_entregas") || "1", 10),
        peso_total_kg: parseFloat(fd.get("peso_total_kg") || "0"),
        observacao_interna: fd.get("observacao_interna"),
        rota_id: rotaIdPayload,
        itens,
      };
      if (!itens.length) {
        alert("Inclua ao menos uma linha de item com descrição.");
        return;
      }

      if (id) {
        const r = await lbJson("/api/pedido/" + id, { method: "PUT", body: payload });
        if (r.ok) location.reload();
      } else {
        const r = await lbJson("/api/pedidos", { method: "POST", body: payload });
        if (r.ok) location.reload();
      }
    });

    document.querySelectorAll(".lb-del-pedido").forEach((b) =>
      b.addEventListener("click", async () => {
        if (!confirm("Excluir pedido?")) return;
        const r = await lbJson("/api/pedido/" + b.dataset.id, { method: "DELETE", body: {} });
        if (r.ok) location.reload();
      })
    );
  }

  /* ===================== Veículos ===================== */
  const mv = document.getElementById("modal-veiculo");
  if (mv) {
    const form = document.getElementById("form-veiculo");
    document.querySelector(".lb-open-veiculo")?.addEventListener("click", () => {
      form.reset();
      document.getElementById("veiculo-id").value = "";
      form.querySelector("[name=ativo]").checked = true;
      mv.classList.add("is-open");
    });
    document.querySelectorAll(".lb-edit-veiculo").forEach((btn) => {
      btn.addEventListener("click", () => {
        const row = JSON.parse(btn.closest("tr").dataset.row || "{}");
        document.getElementById("veiculo-id").value = row.id;
        form.placa.value = row.placa || "";
        form.descricao.value = row.descricao || "";
        form.marca_modelo.value = row.marca_modelo || "";
        form.ano.value = row.ano || "";
        form.capacidade_kg.value = row.capacidade_kg || "";
        form.tipo.value = row.tipo || "";
        form.frota_interna.value = row.frota_interna || "";
        form.ativo.checked = !!row.ativo;
        mv.classList.add("is-open");
      });
    });
    form.addEventListener("submit", async (e) => {
      e.preventDefault();
      const id = document.getElementById("veiculo-id").value;
      const body = {
        placa: form.placa.value,
        descricao: form.descricao.value,
        marca_modelo: form.marca_modelo.value,
        ano: form.ano.value ? parseInt(form.ano.value, 10) : null,
        capacidade_kg: form.capacidade_kg.value ? parseFloat(form.capacidade_kg.value) : null,
        tipo: form.tipo.value,
        frota_interna: form.frota_interna.value,
        ativo: !!form.ativo.checked,
      };
      const path = id ? "/api/veiculos/" + id : "/api/veiculos";
      const r = await lbJson(path, { method: id ? "PUT" : "POST", body });
      if (r.ok) location.reload();
    });
    document.querySelectorAll(".lb-del-veiculo").forEach((b) =>
      b.addEventListener("click", async () => {
        if (!confirm("Remover veículo?")) return;
        const r = await lbJson("/api/veiculos/" + b.dataset.id, { method: "DELETE", body: {} });
        if (r.ok) location.reload();
      })
    );
  }

  /* ===================== Motoristas ===================== */
  const mm = document.getElementById("modal-motorista");
  if (mm) {
    const form = document.getElementById("form-motorista");
    document.querySelector(".lb-open-motorista")?.addEventListener("click", () => {
      form.reset();
        document.getElementById("motorista-id").value = "";
        if (form.app_senha) form.app_senha.value = "";
      form.ativo.checked = true;
      form.empresa_terceira.checked = false;
      mm.classList.add("is-open");
    });
    document.querySelectorAll(".lb-edit-motorista").forEach((btn) => {
      btn.addEventListener("click", () => {
        const row = JSON.parse(btn.closest("tr").dataset.row || "{}");
        document.getElementById("motorista-id").value = row.id;
        form.nome_completo.value = row.nome_completo || "";
        form.cpf.value = row.cpf || "";
        form.cnh_numero.value = row.cnh_numero || "";
        form.cnh_categoria.value = row.cnh_categoria || "";
        form.telefone.value = row.telefone || "";
        form.email.value = row.email || "";
        if (form.app_senha) {
          form.app_senha.value = "";
        }
        form.empresa_terceira.checked = !!row.empresa_terceira;
        form.nome_empresa_terceira.value = row.nome_empresa_terceira || "";
        form.ativo.checked = !!row.ativo;
        mm.classList.add("is-open");
      });
    });
    form.addEventListener("submit", async (e) => {
      e.preventDefault();
      const id = document.getElementById("motorista-id").value;
      const body = {
        nome_completo: form.nome_completo.value,
        cpf: form.cpf.value,
        cnh_numero: form.cnh_numero.value,
        cnh_categoria: form.cnh_categoria.value,
        telefone: form.telefone.value,
        email: form.email.value,
        empresa_terceira: !!form.empresa_terceira.checked,
        nome_empresa_terceira: form.nome_empresa_terceira.value,
        ativo: !!form.ativo.checked,
      };
      if (form.app_senha && form.app_senha.value.trim() !== "") {
        body.app_senha = form.app_senha.value;
      }
      const path = id ? "/api/motoristas/" + id : "/api/motoristas";
      const r = await lbJson(path, { method: id ? "PUT" : "POST", body });
      if (r.ok) location.reload();
    });
    document.querySelectorAll(".lb-del-motorista").forEach((b) =>
      b.addEventListener("click", async () => {
        if (!confirm("Remover motorista?")) return;
        const r = await lbJson("/api/motoristas/" + b.dataset.id, { method: "DELETE", body: {} });
        if (r.ok) location.reload();
      })
    );
  }

  /* ===================== Rotas ===================== */
  const modalRota = document.getElementById("modal-rota");
  const modalTerr = document.getElementById("modal-territorio");
  if (modalRota) {
    let rotasBundle = [];
    const el = document.getElementById("lb-rotas-json");
    if (el) {
      try {
        rotasBundle = JSON.parse(el.textContent || "[]");
      } catch {
        rotasBundle = [];
      }
    }

    document.querySelector(".lb-open-rota")?.addEventListener("click", () => {
      document.getElementById("rota-id").value = "";
      document.getElementById("form-rota").reset();
      document.getElementById("form-rota").ativo.checked = true;
      modalRota.classList.add("is-open");
    });
    document.querySelectorAll(".lb-edit-rota").forEach((btn) => {
      btn.addEventListener("click", () => {
        const row = JSON.parse(btn.closest("tr").dataset.row || "{}");
        document.getElementById("rota-id").value = row.id;
        document.getElementById("form-rota").nome.value = row.nome || "";
        document.getElementById("form-rota").observacao.value = row.observacao || "";
        document.getElementById("form-rota").ativo.checked = !!row.ativo;
        modalRota.classList.add("is-open");
      });
    });
    document.getElementById("form-rota")?.addEventListener("submit", async (e) => {
      e.preventDefault();
      const id = document.getElementById("rota-id").value;
      const f = document.getElementById("form-rota");
      const body = { nome: f.nome.value, observacao: f.observacao.value, ativo: !!f.ativo.checked };
      const path = id ? "/api/rota/" + id : "/api/rota";
      const r = await lbJson(path, { method: id ? "PUT" : "POST", body });
      if (r.ok) location.reload();
    });
    document.querySelectorAll(".lb-del-rota").forEach((b) =>
      b.addEventListener("click", async () => {
        if (!confirm("Excluir rota?")) return;
        const r = await lbJson("/api/rota/" + b.dataset.id, { method: "DELETE", body: {} });
        if (r.ok) location.reload();
      })
    );

    function renderTerr(id) {
      const rota = rotasBundle.find((x) => String(x.id) === String(id));
      if (!rota) return;
      document.getElementById("territorio-rota-id").value = id;
      document.getElementById("territorio-title").textContent = "Território — " + rota.nome;
      const tc = document.getElementById("tbl-cidades");
      const tb = document.getElementById("tbl-bairros");
      tc.innerHTML = "";
      (rota._cidades || []).forEach((c) => {
        const tr = document.createElement("tr");
        tr.innerHTML = `<td>${c.cidade}</td><td>${c.uf}</td><td><button type="button" class="lb-btn lb-btn-quiet lb-del-cid" data-id="${c.id}"><i class="fa-solid fa-trash"></i></button></td>`;
        tc.appendChild(tr);
      });
      tb.innerHTML = "";
      (rota._bairros || []).forEach((b) => {
        const tr = document.createElement("tr");
        tr.innerHTML = `<td>${b.bairro}</td><td>${b.cidade}/${b.uf}</td><td><button type="button" class="lb-btn lb-btn-quiet lb-del-bai" data-id="${b.id}"><i class="fa-solid fa-trash"></i></button></td>`;
        tb.appendChild(tr);
      });
      tc.querySelectorAll(".lb-del-cid").forEach((btn) =>
        btn.addEventListener("click", async () => {
          const r = await lbJson("/api/rota/cidade/" + btn.dataset.id, { method: "DELETE", body: {} });
          if (r.ok) location.reload();
        })
      );
      tb.querySelectorAll(".lb-del-bai").forEach((btn) =>
        btn.addEventListener("click", async () => {
          const r = await lbJson("/api/rota/bairro/" + btn.dataset.id, { method: "DELETE", body: {} });
          if (r.ok) location.reload();
        })
      );
    }

    document.querySelectorAll(".lb-territorio-rota").forEach((btn) => {
      btn.addEventListener("click", () => {
        renderTerr(btn.dataset.id);
        modalTerr.classList.add("is-open");
      });
    });

    document.getElementById("form-add-cidade")?.addEventListener("submit", async (e) => {
      e.preventDefault();
      const f = e.target;
      const rid = f.rota_id.value;
      await lbJson("/api/rota/" + rid + "/cidade", {
        method: "POST",
        body: { cidade: f.cidade.value, uf: f.uf.value },
      }).then(() => location.reload());
    });
    document.getElementById("form-add-bairro")?.addEventListener("submit", async (e) => {
      e.preventDefault();
      const f = e.target;
      const rid = document.getElementById("territorio-rota-id").value;
      await lbJson("/api/rota/" + rid + "/bairro", {
        method: "POST",
        body: { bairro: f.bairro.value, cidade: f.cidade.value, uf: f.uf.value },
      }).then(() => location.reload());
    });
  }

  /* ===================== Roteirizador ===================== */
  const rotCardsRoot = document.getElementById("roteirizador-cards");
  if (rotCardsRoot) {
    const fleetEl = document.getElementById("lb-fleet");
    const fleet = fleetEl ? JSON.parse(fleetEl.textContent || "{}") : {};
    let state = {
      atualRota: null,
      pedidoIds: [],
      coordMap: null,
      lastSeq: [],
    };

    lbJson("/api/roteirizador", { method: "GET" }).then((d) => {
      rotCardsRoot.innerHTML = "";
      if (!d.cards || !d.cards.length) {
        rotCardsRoot.innerHTML = '<p class="lb-muted">Nenhuma rota possui pendências disponíveis.</p>';
        return;
      }
      d.cards.forEach((c) => {
        const el = document.createElement("article");
        el.className = "lb-card lb-route-card";
        const distKm =
          c.distancia_metros_prev != null ? (c.distancia_metros_prev / 1000).toFixed(1) + " km" : "—";
        el.innerHTML = `
          <div class="lb-route-card-top"><div><div class="lb-chip">${c.rota_nome}</div>
          <div class="lb-route-metrics" style="margin-top:8px">
            <div><strong>Peso</strong><br>${c.peso_total} kg</div>
            <div><strong>Pedidos</strong><br>${c.quantidade_pedidos}</div>
            <div><strong>Entregas</strong><br>${c.quantidade_entregas}</div>
            <div><strong>Distância</strong><br>${distKm}</div>
          </div></div></div>
          <div class="lb-route-actions">
            <button type="button" class="lb-btn lb-btn-quiet lb-rot-det" data-id="${c.rota_id}">Detalhes</button>
            <button type="button" class="lb-btn lb-btn-quiet lb-rot-map" data-id="${c.rota_id}">Mapa</button>
            <button type="button" class="lb-btn lb-btn-secondary lb-rot-viag" data-id="${c.rota_id}">Gerar viagem</button>
          </div>`;
        rotCardsRoot.appendChild(el);
      });

      document.querySelectorAll(".lb-rot-det").forEach((b) =>
        b.addEventListener("click", () => abrirDetalhe(parseInt(b.dataset.id, 10)))
      );
      document.querySelectorAll(".lb-rot-map").forEach((b) =>
        b.addEventListener("click", () => abrirMapa(parseInt(b.dataset.id, 10)))
      );
      document.querySelectorAll(".lb-rot-viag").forEach((b) =>
        b.addEventListener("click", () => prepararViagem(parseInt(b.dataset.id, 10)))
      );
    });

    function preencherSelectRotas() {
      const sel = document.getElementById("rot-mudar-rota");
      sel.innerHTML = "";
      (fleet.rotas || []).forEach((r) => {
        const o = document.createElement("option");
        o.value = r.id;
        o.textContent = r.nome;
        sel.appendChild(o);
      });
    }
    preencherSelectRotas();

    function abrirDetalhe(rotaId) {
      lbJson("/api/roteirizador/rota/" + rotaId, { method: "GET" }).then((d) => {
        const tb = document.getElementById("rot-tbody-pedidos");
        tb.innerHTML = "";
        state.pedidoIds = (d.pedidos || []).map((p) => p.id);
        (d.pedidos || []).forEach((p) => {
          const tr = document.createElement("tr");
          tr.dataset.pid = String(p.id);
          tr.innerHTML = `<td><input type="checkbox" class="rot-sel" value="${p.id}"></td>
            <td>${p.numero_pedido}</td><td>${p.nome_destinatario}</td><td>${p.peso_total_kg}</td>
            <td>${p.quantidade_entregas}</td><td>${p.bairro}</td>`;
          tb.appendChild(tr);
        });
        document.getElementById("modal-rot-detalhe").dataset.rotaAtual = String(rotaId);
        document.getElementById("modal-rot-detalhe").classList.add("is-open");
      });
    }

    document.getElementById("rot-sel-all")?.addEventListener("change", (e) => {
      document.querySelectorAll(".rot-sel").forEach((c) => (c.checked = e.target.checked));
    });

    document.getElementById("rot-aplicar-rota")?.addEventListener("click", async () => {
      const novo = parseInt(document.getElementById("rot-mudar-rota").value, 10);
      const pid = [];
      document.querySelectorAll(".rot-sel:checked").forEach((c) => pid.push(parseInt(c.value, 10)));
      if (!pid.length) return alert("Selecione ao menos um pedido.");
      const r = await lbJson("/api/pedidos/alterar-rota", {
        method: "POST",
        body: { pedido_ids: pid, nova_rota_id: novo },
      });
      if (r.ok) location.reload();
    });

    async function abrirMapa(rotaId) {
      const r = await lbJson("/api/roteirizador/rota/" + rotaId, { method: "GET" });
      const resumo = await lbJson("/api/roteirizador", { method: "GET" });
      const card = (resumo.cards || []).find((c) => c.rota_id === rotaId) || {};

      document.getElementById("modal-rot-mapa").classList.add("is-open");

      const list = document.getElementById("rot-seq-list");
      const meta = document.getElementById("rot-seq-meta");
      list.innerHTML = "";
      const seq = card.sequencia_sugerida || [];
      const kmPrev =
        card.distancia_metros_prev != null ? (card.distancia_metros_prev / 1000).toFixed(1) : null;
      const rotaNome = card.rota_nome ? String(card.rota_nome) : "";

      if (meta) {
        if (seq.length === 0) {
          meta.textContent = rotaNome
            ? `${rotaNome}: nenhum pedido na fila com coordenadas suficientes para ordenar.`
            : "Sem paradas na sequência.";
        } else {
          const partes = [
            seq.length + " parada" + (seq.length !== 1 ? "s" : ""),
            kmPrev != null ? "~" + kmPrev + " km previstos (percurso fechado)" : null,
          ].filter(Boolean);
          meta.textContent = (rotaNome ? rotaNome + " · " : "") + partes.join(" · ");
        }
      }

      if (seq.length === 0) {
        const empty = document.createElement("p");
        empty.className = "lb-seq-empty";
        empty.textContent =
          "Não há pedidos pendentes nesta rota ou ainda não há coordenadas para montar a sequência.";
        list.appendChild(empty);
      } else {
        seq.forEach((s, idx) => {
          const step = document.createElement("div");
          step.className = "lb-seq-step";
          step.setAttribute("role", "listitem");

          const badge = document.createElement("div");
          badge.className = "lb-seq-num";
          badge.textContent = String(idx + 1);

          const body = document.createElement("div");
          body.className = "lb-seq-body";

          const num = document.createElement("span");
          num.className = "lb-seq-numero";
          num.textContent = "Pedido " + (s.numero_pedido != null ? "#" + s.numero_pedido : "—");

          const dest = document.createElement("p");
          dest.className = "lb-seq-dest";
          dest.textContent = s.destinatario || "—";

          body.appendChild(num);
          body.appendChild(dest);

          const locParts = [s.bairro, s.cidade, s.uf].filter(Boolean);
          if (locParts.length) {
            const loc = document.createElement("p");
            loc.className = "lb-seq-local";
            loc.innerHTML =
              '<i class="fa-solid fa-location-dot" aria-hidden="true"></i><span>' +
              locParts.join(", ") +
              "</span>";
            body.appendChild(loc);
          }

          step.appendChild(badge);
          step.appendChild(body);
          list.appendChild(step);
        });
      }

      await new Promise((res) => setTimeout(res, 80)); // garante DIM visível leaflet
      const mapEl = document.getElementById("map-rot");
      mapEl.innerHTML = "";
      if (state.coordMap) state.coordMap.remove();
      state.coordMap = null;
      if (!window.L || !r.ok) return;

      const m = L.map(mapEl).setView([-14.235, -51.9253], 5);
      L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", { maxZoom: 19 }).addTo(m);
      const grp = [];
      (r.pedidos || []).forEach((p) => {
        if (!p.latitude || !p.longitude) return;
        const mk = L.marker([p.latitude, p.longitude]).bindPopup(p.nome_destinatario);
        mk.addTo(m);
        grp.push(mk);
      });
      if (grp.length) {
        const g = L.featureGroup(grp);
        m.fitBounds(g.getBounds().pad(0.2));
      }
      state.coordMap = m;
    }

    /** Padrão: dia seguinte à data local do usuário (08h), formato input datetime-local. */
    function wizDataPadraoAmanha() {
      const d = new Date();
      d.setDate(d.getDate() + 1);
      d.setHours(8, 0, 0, 0);
      const pad = (n) => String(n).padStart(2, "0");

      return (
        d.getFullYear() +
        "-" +
        pad(d.getMonth() + 1) +
        "-" +
        pad(d.getDate()) +
        "T" +
        pad(d.getHours()) +
        ":" +
        pad(d.getMinutes())
      );
    }

    function prepararViagem(rotaId) {
      lbJson("/api/roteirizador/rota/" + rotaId, { method: "GET" }).then((d) => {
        state.atualRota = rotaId;
        state.pedidoIds = (d.pedidos || []).map((p) => p.id);
        if (!state.pedidoIds.length) {
          alert("Sem pedidos pendentes.");
          return;
        }
        const inpData = document.getElementById("wiz-data");
        if (inpData) inpData.value = wizDataPadraoAmanha();

        const selV = document.getElementById("wiz-veiculo");
        const selM = document.getElementById("wiz-motorista");
        selV.innerHTML = "";
        (fleet.veiculos || [])
          .filter((v) => v.ativo)
          .forEach((v) => {
            const o = document.createElement("option");
            o.value = v.id;
            o.textContent = v.placa + " — " + (v.marca_modelo || "");
            selV.appendChild(o);
          });
        selM.innerHTML = "";
        (fleet.motoristas || [])
          .filter((x) => x.ativo)
          .forEach((x) => {
            const o = document.createElement("option");
            o.value = x.id;
            o.textContent = x.nome_completo;
            selM.appendChild(o);
          });
        wizardStep(0);
        document.getElementById("modal-viagem-wizard").classList.add("is-open");
      });
    }

    let wStep = 0;
    function wizardStep(n) {
      wStep = n;
      document.getElementById("wiz-pane-veiculo").style.display = n === 0 ? "block" : "none";
      document.getElementById("wiz-pane-motorista").style.display = n === 1 ? "block" : "none";
      document.getElementById("wiz-pane-confirm").style.display = n === 2 ? "block" : "none";
      document.getElementById("wiz-back").style.display = n === 0 ? "none" : "inline-flex";
      document.getElementById("wiz-next").textContent = n === 2 ? "Confirmar" : "Avançar";
      const steps = document.getElementById("wiz-steps");
      steps.innerHTML = ["Veículo", "Motorista", "Confirmação"]
        .map((t, i) => `<span class="lb-step-chip ${i <= n ? "done" : ""}">${i + 1}. ${t}</span>`)
        .join("");
    }

    document.getElementById("wiz-back")?.addEventListener("click", () => wizardStep(Math.max(0, wStep - 1)));
    document.getElementById("wiz-next")?.addEventListener("click", async () => {
      if (wStep < 2) {
        wizardStep(wStep + 1);
        return;
      }
      const body = {
        rota_id: state.atualRota,
        pedido_ids: state.pedidoIds,
        veiculo_id: parseInt(document.getElementById("wiz-veiculo").value, 10),
        motorista_id: parseInt(document.getElementById("wiz-motorista").value, 10),
        data_largada_prevista: document.getElementById("wiz-data").value || null,
        lead_planejado_texto: document.getElementById("wiz-lead").value,
        observacao_planejamento: document.getElementById("wiz-obs").value,
      };
      const r = await lbJson("/api/viagem/gerar", { method: "POST", body });
      if (r.ok) {
        alert("Viagem " + r.viagem_id + " criada.");
        location.href = CFG.baseUrl + "/viagens/abertas";
      }
    });
  }

  /* ===================== Viagens abertas ===================== */
  if (document.querySelector(".lb-v-detalhes")) {
    let mapViagem = null;

    async function pedidosTrip(id) {
      return lbJson("/api/viagem/" + id + "/pedidos", { method: "GET" });
    }

    document.querySelectorAll(".lb-v-detalhes").forEach((b) =>
      b.addEventListener("click", async () => {
        const id = parseInt(b.dataset.id, 10);
        const d = await pedidosTrip(id);
        const tb = document.querySelector("#v-det-body");
        tb.innerHTML = "";
        document.getElementById("v-itens-box").style.display = "block";
        document.getElementById("v-itens-list").style.display = "none";
        const itbClear = document.querySelector("#v-itens-list tbody");
        if (itbClear) itbClear.innerHTML = "";
        (d.pedidos || []).forEach((p) => {
          const tr = document.createElement("tr");
          tr.dataset.pid = String(p.pedido_id || p.id);
          tr.style.cursor = "pointer";
          const cidadeUf = [p.bairro, p.cidade, p.uf].filter(Boolean).join(" · ");
          const stClass =
            (p.parada_estado === "entrega_feita" && "rgba(34,197,94,.2)") ||
            (p.parada_estado === "indo" && "rgba(250,204,21,.22)") ||
            (p.parada_estado === "divergencia_aguardando" && "rgba(251,146,60,.22)") ||
            "transparent";
          tr.innerHTML =
            `<td>${lbEsc(p.ordem_entrega)}</td>` +
            `<td><strong>${lbEsc(p.numero_pedido)}</strong></td>` +
            `<td>${lbEsc(p.nome_destinatario)}</td>` +
            `<td><span style="display:inline-block;padding:4px 8px;border-radius:999px;font-size:.74rem;font-weight:650;background:${stClass};border:1px solid rgba(0,0,0,.06)">${lbParadaEstadoLabel(p.parada_estado)}</span></td>` +
            `<td>${lbFmtTs(p.parada_indo_em)}</td>` +
            `<td>${lbFmtTs(p.parada_entregue_em)}</td>` +
            `<td>${lbEsc(cidadeUf || "—")}</td>`;
          tr.addEventListener("click", async () => {
            const pid = parseInt(tr.dataset.pid, 10);
            const ti = await lbJson("/api/pedido/" + pid + "/itens");
            const itb = document.querySelector("#v-itens-list tbody");
            itb.innerHTML = "";
            (ti.itens || []).forEach((it) => {
              const r = document.createElement("tr");
              r.innerHTML =
                `<td>${lbEsc(it.descricao)}</td><td>${lbEsc(it.quantidade)}</td><td>${lbEsc(it.peso_unit_kg ?? "")}</td>`;
              itb.appendChild(r);
            });
            document.getElementById("v-itens-list").style.display = "block";
          });
          tb.appendChild(tr);
        });
        document.getElementById("modal-v-det").classList.add("is-open");
      })
    );

    document.querySelectorAll(".lb-v-mapa").forEach((b) =>
      b.addEventListener("click", async () => {
        const id = parseInt(b.dataset.id, 10);
        const d = await pedidosTrip(id);
        document.getElementById("modal-v-mapa").classList.add("is-open");
        const el = document.getElementById("map-viagem");
        el.innerHTML = "";
        await new Promise((r) => setTimeout(r, 60));
        if (mapViagem) mapViagem.remove();
        mapViagem = null;
        if (!window.L) return;
        const m = L.map(el).setView([-14.235, -51.9253], 5);
        L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", { maxZoom: 19 }).addTo(m);
        const grp = [];
        (d.pedidos || []).forEach((p) => {
          if (!p.latitude || !p.longitude) return;
          const mk = L.marker([p.latitude, p.longitude]).bindPopup("#" + p.numero_pedido);
          mk.addTo(m);
          grp.push(mk);
        });
        if (grp.length) m.fitBounds(L.featureGroup(grp).getBounds().pad(0.2));
        mapViagem = m;
      })
    );

    document.querySelectorAll(".lb-v-div").forEach((b) =>
      b.addEventListener("click", async () => {
        const id = parseInt(b.dataset.id, 10);
        document.getElementById("v-div-viagem-id").value = String(id);
        const d = await lbJson("/api/viagem/" + id + "/divergencias", { method: "GET" });
        const ul = document.getElementById("v-div-ul");
        ul.innerHTML = "";
        (d.divergencias || []).forEach((x) => {
          const li = document.createElement("li");
          li.textContent = "[" + x.reportado_em + "] " + x.descricao;
          ul.appendChild(li);
        });
        document.getElementById("modal-v-div").classList.add("is-open");
      })
    );

    document.getElementById("v-div-save")?.addEventListener("click", async () => {
      const id = parseInt(document.getElementById("v-div-viagem-id").value, 10);
      const txt = document.getElementById("v-div-txt").value.trim();
      if (!txt) return;
      await lbJson("/api/viagem/" + id + "/divergencia", {
        method: "POST",
        body: { descricao: txt, pedido_id: null },
      });
      document.getElementById("modal-v-div").classList.remove("is-open");
    });

    document.querySelectorAll(".lb-v-fin").forEach((b) =>
      b.addEventListener("click", async () => {
        if (!confirm("Finalizar viagem e marcar pedidos como entregues?")) return;
        const r = await lbJson("/api/viagem/" + b.dataset.id + "/finalizar", { method: "POST", body: {} });
        if (r.ok) location.reload();
      })
    );
  }

  /* ===================== Finalizadas (mapa/leitura) ===================== */
  function lbAbrirModalApontamentoFinalizada(p) {
    const nf = document.getElementById("v2-ap-nf");
    const emp = document.getElementById("v2-ap-empty");
    const box = document.getElementById("v2-ap-content");
    const rec = document.getElementById("v2-ap-recebedor");
    const dtEl = document.getElementById("v2-ap-dt");
    const imgF = document.getElementById("v2-ap-foto");
    const imgS = document.getElementById("v2-ap-sig");
    if (!nf || !emp || !box || !rec || !dtEl || !imgF || !imgS) return;

    nf.textContent = "NF / Pedido " + String(p.numero_pedido ?? "—");

    const tem =
      !!(p.parada_recebedor_nome || p.parada_entregue_em || p.parada_foto_mercadoria || p.parada_assinatura_png);
    if (!tem) {
      emp.style.display = "";
      box.style.display = "none";
      document.getElementById("modal-v2-entrega").classList.add("is-open");
      return;
    }
    emp.style.display = "none";
    box.style.display = "";
    rec.textContent = p.parada_recebedor_nome ? String(p.parada_recebedor_nome) : "—";
    dtEl.textContent = lbFmtTs(p.parada_entregue_em);

    const uFoto = lbUploadOrDataMediaUrl(p.parada_foto_mercadoria);
    const wrapF = imgF.parentElement;
    if (uFoto) {
      imgF.src = uFoto;
      imgF.style.display = "block";
      imgF.onerror = function () {
        this.style.display = "none";
        if (wrapF) wrapF.style.display = "none";
      };
      if (wrapF) wrapF.style.display = "block";
    } else if (wrapF) wrapF.style.display = "none";

    const uSig = lbUploadOrDataMediaUrl(p.parada_assinatura_png);
    const wrapS = imgS.parentElement;
    if (uSig) {
      imgS.src = uSig;
      imgS.style.display = "block";
      imgS.onerror = function () {
        this.style.display = "none";
        if (wrapS) wrapS.style.display = "none";
      };
      if (wrapS) wrapS.style.display = "block";
    } else if (wrapS) wrapS.style.display = "none";

    document.getElementById("modal-v2-entrega").classList.add("is-open");
  }

  if (document.querySelector(".lb-v2-detalhes")) {
    let mapV2 = null;
    document.querySelectorAll(".lb-v2-detalhes").forEach((b) =>
      b.addEventListener("click", async () => {
        const id = parseInt(b.dataset.id, 10);
        const d = await lbJson("/api/viagem/" + id + "/pedidos");
        const tb = document.querySelector("#v2-det-body");
        tb.innerHTML = "";
        (d.pedidos || []).forEach((p) => {
          const tr = document.createElement("tr");
          const botoes = document.createElement("td");
          const btn = document.createElement("button");
          btn.type = "button";
          btn.className = "lb-btn lb-btn-quiet";
          btn.innerHTML = '<i class="fa-solid fa-file-signature"></i> Ver apontamento';
          btn.addEventListener("click", (ev) => {
            ev.stopPropagation();
            lbAbrirModalApontamentoFinalizada(p);
          });
          botoes.appendChild(btn);
          tr.innerHTML =
            `<td>${lbEsc(p.ordem_entrega)}</td>` +
            `<td>${lbEsc(p.numero_pedido)}</td>` +
            `<td>${lbEsc(p.nome_destinatario)}</td>` +
            `<td><span style="font-size:.8rem;font-weight:650">${lbParadaEstadoLabel(p.parada_estado)}</span></td>` +
            `<td>${lbFmtTs(p.parada_entregue_em)}</td>`;
          tr.appendChild(botoes);
          tb.appendChild(tr);
        });
        document.getElementById("modal-v2-det").classList.add("is-open");
      })
    );

    document.querySelectorAll(".lb-v2-mapa").forEach((b) =>
      b.addEventListener("click", async () => {
        const id = parseInt(b.dataset.id, 10);
        const d = await lbJson("/api/viagem/" + id + "/pedidos");
        document.getElementById("modal-v2-mapa").classList.add("is-open");
        const el = document.getElementById("map-viagem2");
        el.innerHTML = "";
        await new Promise((r) => setTimeout(r, 50));
        if (mapV2) mapV2.remove();
        mapV2 = null;
        if (!window.L) return;
        const m = L.map(el).setView([-14.235, -51.9253], 5);
        L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", { maxZoom: 19 }).addTo(m);
        const grp = [];
        (d.pedidos || []).forEach((p) => {
          if (!p.latitude || !p.longitude) return;
          const mk = L.marker([p.latitude, p.longitude]).bindPopup("#" + p.numero_pedido);
          mk.addTo(m);
          grp.push(mk);
        });
        if (grp.length) m.fitBounds(L.featureGroup(grp).getBounds().pad(0.2));
        mapV2 = m;
      })
    );
  }

})();
