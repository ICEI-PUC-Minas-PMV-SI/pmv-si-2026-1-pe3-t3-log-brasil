/**
 * LogBrasil UX: ajuda contextual, busca em tabelas, onboarding, toasts.
 */
(function () {
  "use strict";

  var TIP_EL = null;

  function showTip(el, text) {
    hideTip();
    if (!text) return;
    TIP_EL = document.createElement("div");
    TIP_EL.className = "lb-tip";
    TIP_EL.setAttribute("role", "tooltip");
    TIP_EL.textContent = text;
    document.body.appendChild(TIP_EL);
    var r = el.getBoundingClientRect();
    var top = r.bottom + 8;
    var left = Math.min(r.left, window.innerWidth - TIP_EL.offsetWidth - 12);
    if (top + TIP_EL.offsetHeight > window.innerHeight - 8) top = r.top - TIP_EL.offsetHeight - 8;
    TIP_EL.style.top = Math.max(8, top) + "px";
    TIP_EL.style.left = Math.max(8, left) + "px";
  }

  function hideTip() {
    if (TIP_EL) {
      TIP_EL.remove();
      TIP_EL = null;
    }
  }

  document.querySelectorAll(".lb-help[data-lb-tip]").forEach(function (btn) {
    var tip = btn.getAttribute("data-lb-tip") || "";
    btn.addEventListener("mouseenter", function () {
      showTip(btn, tip);
    });
    btn.addEventListener("mouseleave", hideTip);
    btn.addEventListener("focus", function () {
      showTip(btn, tip);
    });
    btn.addEventListener("blur", hideTip);
    btn.addEventListener("click", function (e) {
      e.preventDefault();
      if (TIP_EL) hideTip();
      else showTip(btn, tip);
    });
  });

  document.addEventListener("scroll", hideTip, true);
  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape") hideTip();
  });

  /* Busca rápida em tabelas */
  document.querySelectorAll("[data-lb-table-search]").forEach(function (inp) {
    var sel = inp.getAttribute("data-lb-table-search");
    var table = sel ? document.querySelector(sel) : inp.closest(".lb-table-shell")?.querySelector("table");
    if (!table) return;
    var tbody = table.querySelector("tbody");
    if (!tbody) return;
    inp.addEventListener("input", function () {
      var q = (inp.value || "").trim().toLowerCase();
      tbody.querySelectorAll("tr").forEach(function (tr) {
        tr.hidden = q !== "" && !tr.textContent.toLowerCase().includes(q);
      });
    });
  });

  /* Busca em grids de cards (viagens, etc.) */
  document.querySelectorAll("[data-lb-grid-search]").forEach(function (inp) {
    var sel = inp.getAttribute("data-lb-grid-search");
    var grid = sel ? document.querySelector(sel) : null;
    if (!grid) return;
    inp.addEventListener("input", function () {
      var q = (inp.value || "").trim().toLowerCase();
      grid.querySelectorAll("[data-viagem], .lb-route-card").forEach(function (card) {
        var el = card.closest("[data-viagem]") || card;
        el.hidden = q !== "" && !el.textContent.toLowerCase().includes(q);
      });
    });
  });

  /* Toast padronizado (flash success enhancement) */
  window.LB_UX_TOAST = function (msg, ms) {
    var old = document.querySelector(".lb-toast-ux");
    if (old) old.remove();
    var t = document.createElement("div");
    t.className = "lb-toast-ux";
    t.setAttribute("role", "status");
    t.textContent = msg;
    document.body.appendChild(t);
    setTimeout(function () {
      t.remove();
    }, ms || 3200);
  };

  var flashOk = document.querySelector(".lb-alert-success");
  if (flashOk && flashOk.textContent.trim()) {
    window.LB_UX_TOAST(flashOk.textContent.trim());
  }

  /* Onboarding (painel TMS) */
  if (document.querySelector(".lb-nav-groups") && !localStorage.getItem("lb_onboard_v2")) {
    var steps = [
      {
        title: "Bem-vindo ao LogBrasil",
        body: "O menu está organizado em três blocos: Cadastros (dados base), Planejamento (montar viagens) e Execução (acompanhar entregas em andamento).",
      },
      {
        title: "Cadastro de clientes",
        body: "Clientes são registrados ao informar o CPF no formulário de Pedidos. O endereço fica salvo para os próximos pedidos do mesmo cliente.",
      },
      {
        title: "Gerar uma viagem",
        body: "Em Planejar viagens, escolha uma rota com pedidos pendentes e use o assistente passo a passo. Depois acompanhe em Execução (abertas).",
      },
    ];
    var stepIdx = 0;
    var mask = document.createElement("div");
    mask.className = "lb-onboard-mask";
    mask.setAttribute("role", "dialog");
    mask.setAttribute("aria-modal", "true");
    mask.innerHTML =
      '<div class="lb-onboard-card">' +
      '<h2 id="lb-ob-title" style="margin:0 0 10px;font-size:1.2rem"></h2>' +
      '<p id="lb-ob-body" class="lb-muted" style="margin:0;line-height:1.55"></p>' +
      '<div class="lb-onboard-dots" id="lb-ob-dots"></div>' +
      '<div style="display:flex;gap:8px;justify-content:flex-end;flex-wrap:wrap">' +
      '<button type="button" class="lb-btn lb-btn-quiet" id="lb-ob-skip">Não mostrar de novo</button>' +
      '<button type="button" class="lb-btn lb-btn-primary" id="lb-ob-next">Próximo</button>' +
      "</div></div>";
    document.body.appendChild(mask);

    function renderOb() {
      document.getElementById("lb-ob-title").textContent = steps[stepIdx].title;
      document.getElementById("lb-ob-body").textContent = steps[stepIdx].body;
      var dots = document.getElementById("lb-ob-dots");
      dots.innerHTML = steps
        .map(function (_, i) {
          return '<span class="' + (i === stepIdx ? "is-on" : "") + '"></span>';
        })
        .join("");
      document.getElementById("lb-ob-next").textContent =
        stepIdx >= steps.length - 1 ? "Começar" : "Próximo";
    }

    function closeOb(persist) {
      if (persist) localStorage.setItem("lb_onboard_v2", "1");
      mask.remove();
    }

    document.getElementById("lb-ob-skip").addEventListener("click", function () {
      closeOb(true);
    });
    document.getElementById("lb-ob-next").addEventListener("click", function () {
      if (stepIdx >= steps.length - 1) closeOb(true);
      else {
        stepIdx++;
        renderOb();
      }
    });
    renderOb();
  }

  /* Painel itens colapsável (viagens) */
  document.querySelectorAll(".lb-items-panel__head").forEach(function (head) {
    head.addEventListener("click", function () {
      var panel = head.closest(".lb-items-panel");
      if (panel) panel.classList.toggle("is-collapsed");
    });
    head.addEventListener("keydown", function (e) {
      if (e.key === "Enter" || e.key === " ") {
        e.preventDefault();
        head.click();
      }
    });
  });
})();
