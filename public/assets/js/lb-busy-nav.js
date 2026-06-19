/**
 * Exibe overlay ao enviar formulários POST (login, logout, páginas sem logbrasil.js).
 */
(function () {
  function showBusy() {
    var el = document.getElementById("lb-busy-mask");
    if (!el) return;
    el.classList.add("is-active");
    el.setAttribute("aria-hidden", "false");
    el.setAttribute("aria-busy", "true");
  }

  document.addEventListener(
    "submit",
    function (e) {
      var form = e.target;
      if (!form || !form.matches || !form.matches("form")) return;
      if (form.dataset.lbNoBusy !== undefined) return;
      var method = (form.getAttribute("method") || "get").toLowerCase();
      if (method !== "post") return;
      if (e.defaultPrevented) return;
      showBusy();
    },
    false
  );
})();
