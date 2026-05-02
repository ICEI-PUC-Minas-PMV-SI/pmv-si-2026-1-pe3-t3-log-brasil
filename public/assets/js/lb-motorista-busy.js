/**
 * Overlay de carregamento + navegação interna do app motorista.
 * Depende de window.LOGBR_M definido antes deste script.
 */
(function () {
  var CFG = typeof window.LOGBR_M !== "undefined" ? window.LOGBR_M : {};
  function el() {
    return document.getElementById("lb-mot-busy");
  }
  function setText(label) {
    var n = el();
    if (!n) return;
    var t = n.querySelector(".lb-mot-busy__text");
    if (!t) return;
    t.textContent = label || "Carregando…";
  }
  window.LB_MOT_BUSY = {
    show: function (label) {
      setText(label === undefined ? "Carregando…" : String(label || "Carregando…"));
      var n = el();
      if (!n) return;
      n.classList.remove("lb-mot-busy--hide");
      n.setAttribute("aria-busy", "true");
    },
    hide: function () {
      var n = el();
      if (!n) return;
      n.classList.add("lb-mot-busy--hide");
      n.removeAttribute("aria-busy");
    },
  };

  window.addEventListener("pageshow", function () {
    window.LB_MOT_BUSY.hide();
  });

  function sameAppOrigin(linkUrl) {
    try {
      if (!CFG.baseUrl) return false;
      var u = new URL(linkUrl, window.location.href);
      var b = new URL(CFG.baseUrl);
      return u.origin === b.origin;
    } catch (_) {
      return false;
    }
  }

  function isMotoristaPath(pathname) {
    return /(^|\/)motorista(\/|$)/.test(pathname);
  }

  document.addEventListener(
    "click",
    function (e) {
      if (e.target.closest(".lb-mot-go")) {
        window.LB_MOT_BUSY.show("Carregando…");
        return;
      }
      var a = e.target.closest && e.target.closest("a[href]");
      if (!a || !a.href) return;
      if (a.target === "_blank" || a.hasAttribute("download")) return;
      if (a.getAttribute("data-lb-mot-busy") === "0") return;
      try {
        var u = new URL(a.href);
        if (!sameAppOrigin(a.href) || !isMotoristaPath(u.pathname)) return;
      } catch (_) {
        return;
      }
      window.LB_MOT_BUSY.show("Carregando…");
    },
    true
  );

  document.addEventListener("submit", function (e) {
    var form = e.target;
    if (!form || form.tagName !== "FORM") return;
    if (!document.body.classList.contains("lb-mot-dark")) return;
    var enc = ((form.getAttribute("enctype") || "") + " " + (form.enctype || "")).toLowerCase();
    if (enc.indexOf("multipart") !== -1 && form.querySelector('input[type="file"]')) {
      window.LB_MOT_BUSY.show("Enviando arquivo…");
      return;
    }
    window.LB_MOT_BUSY.show("Processando…");
  });
})();
