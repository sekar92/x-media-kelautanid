(function () {
  'use strict';

  // Inisialisasi ikon Lucide
  function refreshIcons() {
    if (window.lucide && typeof window.lucide.createIcons === 'function') {
      window.lucide.createIcons();
    }
  }

  document.addEventListener('DOMContentLoaded', refreshIcons);

  // Mobile Menu Toggle
  var mobileMenuBtn = document.getElementById('mobile-menu-btn');
  var mobileMenuDrawer = document.getElementById('mobile-menu-drawer');
  if (mobileMenuBtn && mobileMenuDrawer) {
    mobileMenuBtn.addEventListener('click', function () {
      var isHidden = mobileMenuDrawer.classList.contains('hidden');
      if (isHidden) {
        mobileMenuDrawer.classList.remove('hidden');
      } else {
        mobileMenuDrawer.classList.add('hidden');
      }
      refreshIcons();
    });
  }

  // Salin tautan ke clipboard
  document.addEventListener('click', function (ev) {
    var btn = ev.target.closest('[data-copy]');
    if (!btn) return;
    var input = document.querySelector(btn.getAttribute('data-copy'));
    if (!input) return;
    var text = input.value;
    var done = function () {
      var oldHtml = btn.getAttribute('data-old-html') || btn.innerHTML;
      btn.setAttribute('data-old-html', oldHtml);
      btn.innerHTML = '<i data-lucide="check" class="w-4 h-4 inline mr-1"></i> Tersalin!';
      btn.classList.add('is-done');
      refreshIcons();
      setTimeout(function () {
        btn.innerHTML = oldHtml;
        btn.classList.remove('is-done');
        refreshIcons();
      }, 2000);
    };

    if (navigator.clipboard && window.isSecureContext) {
      navigator.clipboard.writeText(text).then(done, fallback);
    } else {
      fallback();
    }

    function fallback() {
      input.select();
      input.setSelectionRange(0, 99999);
      try {
        document.execCommand('copy');
        done();
      } catch (e) {
        /* abaikan */
      }
    }
  });

  // Konfirmasi aksi berbahaya
  document.addEventListener('submit', function (ev) {
    var form = ev.target;
    var msg = form.getAttribute('data-confirm');
    if (msg && !window.confirm(msg)) {
      ev.preventDefault();
      return;
    }
    // Status loading untuk form submit
    var label = form.getAttribute('data-loading');
    if (label) {
      var btn = form.querySelector('button[type="submit"]');
      if (btn) {
        setTimeout(function () {
          btn.disabled = true;
          btn.classList.add('is-loading');
          btn.innerHTML = '<i data-lucide="loader-2" class="w-4 h-4 inline animate-spin mr-1.5"></i> ' + label;
          refreshIcons();
        }, 0);
      }
    }
  });

  // Pilih varian: perbarui tautan buka/unduh & input direct
  document.querySelectorAll('select[data-variant-select]').forEach(function (sel) {
    sel.addEventListener('change', function () {
      var opt = sel.options[sel.selectedIndex];
      var scope = sel.closest('.media-card');
      if (!scope) return;
      var openBtn = scope.querySelector('.js-open');
      var dlBtn = scope.querySelector('.js-download');
      var directInput = scope.querySelector('.js-direct');
      if (openBtn) openBtn.href = opt.getAttribute('data-url');
      if (dlBtn) dlBtn.href = opt.getAttribute('data-download');
      if (directInput) directInput.value = opt.getAttribute('data-url');
    });
  });
})();
