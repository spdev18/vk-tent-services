/* VK Tent Services — Admin JS
   Samastam Technologies Private Limited */

(function () {
  'use strict';

  // ── Flash message auto-dismiss ──────────────────────────────────────────────
  document.querySelectorAll('.flash-msg').forEach(function (el) {
    setTimeout(function () {
      el.style.transition = 'opacity 0.5s ease';
      el.style.opacity = '0';
      setTimeout(function () { el.remove(); }, 500);
    }, 4000);
  });

  // ── Confirm delete buttons ──────────────────────────────────────────────────
  document.querySelectorAll('[data-confirm]').forEach(function (btn) {
    btn.addEventListener('click', function (e) {
      if (!confirm(btn.dataset.confirm)) e.preventDefault();
    });
  });

  // ── Number formatting for currency inputs ─────────────────────────────────
  document.querySelectorAll('input[type="number"][name*="amount"]').forEach(function (inp) {
    inp.addEventListener('blur', function () {
      if (this.value) this.value = parseFloat(this.value).toFixed(2);
    });
  });

  // ── Tab persistence via URL hash ──────────────────────────────────────────
  if (window.location.hash) {
    const target = document.querySelector(window.location.hash);
    if (target) {
      setTimeout(function () {
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }, 200);
    }
  }

})();
