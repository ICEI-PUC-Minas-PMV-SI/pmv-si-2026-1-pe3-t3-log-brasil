(function () {
  const CFG = window.LOGBR_M || {};

  async function fetchRastrear(cpfDigits) {
    const r = await fetch(CFG.baseUrl + "/api/cliente/rastrear", {
      method: "POST",
      headers: { Accept: "application/json", "Content-Type": "application/json" },
      body: JSON.stringify({ _csrf: CFG.csrf, cpf: cpfDigits }),
    });
    return r.json();
  }

  function chipClass(tone) {
    switch (tone) {
      case "ok":
        return "lb-m-chip ok";
      case "risk":
        return "lb-m-chip risk";
      default:
        return "lb-m-chip";
    }
  }

  function cardPedido(p) {
    const num = document.createElement("div");
    num.style.fontWeight = "800";
    num.style.marginBottom = "6px";
    num.style.fontSize = "1.05rem";
    num.textContent = "Pedido " + String(p.numero_pedido ?? "");

    const chips = document.createElement("span");
    chips.className = chipClass(p.acompanhar_tone);
    chips.textContent = String(p.acompanhar_label ?? "");

    const dest = document.createElement("div");
    dest.className = "lb-m-muted";
    dest.style.marginTop = "10px";
    dest.innerHTML =
      '<i class="fa-solid fa-location-dot"></i> ' +
      [
        String(p.logradouro ?? ""),
        String(p.numero ?? ""),
        String(p.bairro ?? ""),
        String(p.cidade ?? ""),
      ]
        .filter(Boolean)
        .join(", ")
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;");

    const outer = document.createElement("article");
    outer.className = "lb-m-card";
    outer.appendChild(num);
    outer.appendChild(chips);
    outer.appendChild(dest);
    return outer;
  }

  document.querySelectorAll(".lb-m-tab").forEach((btn) =>
    btn.addEventListener("click", () => {
      document.querySelectorAll(".lb-m-tab").forEach((b) => b.setAttribute("aria-selected", "false"));
      btn.setAttribute("aria-selected", "true");
      const pane = btn.getAttribute("data-pane");
      document.getElementById("pane-pend").style.display = pane === "pend" ? "" : "none";
      document.getElementById("pane-ok").style.display = pane === "ok" ? "" : "none";
    })
  );

  const load = document.getElementById("acre-load");
  const emptyPend = document.getElementById("acre-empty-pend");
  const emptyOk = document.getElementById("acre-empty-ok");
  const page =
    document.querySelector(".lb-public-track-card") ||
    document.querySelector(".lb-m-page") ||
    document.body;
  const cpfInput = document.getElementById("ac-cpf");
  const consultarBtn = document.getElementById("ac-btn");

  function onlyDigits(v) {
    return String(v || "").replace(/\D/g, "");
  }

  async function boot() {
    if (!load || !cpfInput) return;
    const cpfDigits = onlyDigits(cpfInput.value);
    if (cpfDigits.length !== 11) {
      alert("Informe um CPF valido com 11 digitos.");
      cpfInput.focus();
      return;
    }
    load.style.display = "";
    let res;
    try {
      res = await fetchRastrear(cpfDigits);
    } catch {
      load.style.display = "none";
      return;
    }
    load.style.display = "none";
    if (!res.ok) {
      const err = document.createElement("div");
      err.className = "lb-m-card";
      err.style.borderColor = "#f5c2c7";
      err.style.background = "#fff5f5";
      err.style.fontSize = "0.92rem";
      err.textContent = res.message || "Não foi possível carregar.";
      page.prepend(err);
      return;
    }

    const d = res.dados || {};
    const pend = d.pendentes || [];
    const ok = d.realizadas || [];
    document.getElementById("pane-pend").replaceChildren();
    document.getElementById("pane-ok").replaceChildren();

    pend.forEach((p) => document.getElementById("pane-pend").appendChild(cardPedido(p)));
    ok.forEach((p) => document.getElementById("pane-ok").appendChild(cardPedido(p)));

    emptyPend.style.display = pend.length === 0 ? "" : "none";
    emptyOk.style.display = ok.length === 0 ? "" : "none";
  }

  cpfInput?.addEventListener("input", () => {
    cpfInput.value = cpfInput.value
      .replace(/\D/g, "")
      .replace(/(\d{3})(\d)/, "$1.$2")
      .replace(/(\d{3})(\d)/, "$1.$2")
      .replace(/(\d{3})(\d{1,2})$/, "$1-$2");
  });
  consultarBtn?.addEventListener("click", boot);
  cpfInput?.addEventListener("keydown", (e) => {
    if (e.key === "Enter") {
      e.preventDefault();
      boot();
    }
  });
})();
