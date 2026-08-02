/* =============================================
   EMERALD LIBRARY - small UI helpers
   ============================================= */
document.addEventListener('DOMContentLoaded', function () {

  // Mobile menu toggle
  var toggle = document.getElementById('mobileMenuToggle');
  var menu = document.getElementById('mobileMenu');
  if (toggle && menu) {
    toggle.addEventListener('click', function () {
      menu.classList.toggle('hidden');
    });
  }

  // Auto-hide flash toast
  var toast = document.getElementById('flashToast');
  if (toast) {
    setTimeout(function () {
      toast.classList.add('transition', 'duration-500', 'opacity-0', 'translate-y-[-8px]');
      setTimeout(function () { toast.remove(); }, 550);
    }, 4000);
  }

  // Confirmation dialogs for destructive forms
  document.querySelectorAll('form[data-confirm]').forEach(function (f) {
    f.addEventListener('submit', function (ev) {
      var msg = f.getAttribute('data-confirm');
      if (msg && !window.confirm(msg)) {
        ev.preventDefault();
      }
    });
  });

});
