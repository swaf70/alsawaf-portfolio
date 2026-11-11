(function () {
  'use strict';

  /* ---------- Sidebar mobile ---------- */
  const sidebar = document.getElementById('sidebar');
  const menuBtn = document.getElementById('menuBtn');
  const sidebarClose = document.getElementById('sidebarClose');

  if (menuBtn && sidebar) {
    menuBtn.addEventListener('click', () => sidebar.classList.add('open'));
  }
  if (sidebarClose && sidebar) {
    sidebarClose.addEventListener('click', () => sidebar.classList.remove('open'));
  }

  /* ---------- Confirm modal ---------- */
  const confirmModal = document.getElementById('confirmModal');
  const confirmTitle = document.getElementById('confirmTitle');
  const confirmText = document.getElementById('confirmText');
  const confirmOk = document.getElementById('confirmOk');
  let pendingAction = null;

  function showConfirm(title, text, callback) {
    if (!confirmModal) { callback(); return; }
    confirmTitle.textContent = title;
    confirmText.textContent = text;
    pendingAction = callback;
    confirmModal.classList.add('open');
  }

  function closeConfirm() {
    confirmModal.classList.remove('open');
    pendingAction = null;
  }

  document.addEventListener('click', function (e) {
    if (e.target.closest('[data-close]')) { closeConfirm(); return; }

    const delBtn = e.target.closest('.del[data-confirm-action]');
    if (delBtn) {
      e.preventDefault();
      showConfirm(
        delBtn.dataset.confirmTitle || 'تأكيد الحذف',
        delBtn.dataset.confirmText || 'هل أنت متأكد؟',
        function () { window.location.href = delBtn.dataset.confirmAction; }
      );
      return;
    }

    const subBtn = e.target.closest('.confirm-submit');
    if (subBtn) {
      e.preventDefault();
      const form = subBtn.closest('form');
      showConfirm(subBtn.dataset.confirmTitle || 'تأكيد الحذف', subBtn.dataset.confirmText || 'هل أنت متأكد من إتمام العملية؟', function () {
        if (form) form.submit();
      });
      return;
    }
  });

  if (confirmOk) {
    confirmOk.addEventListener('click', function () {
      if (pendingAction) pendingAction();
      closeConfirm();
    });
  }
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeConfirm(); });

  /* ---------- Password toggles ---------- */
  document.querySelectorAll('.password-toggle').forEach(function (btn) {
    btn.addEventListener('click', function () {
      const targetId = btn.dataset.toggle;
      const input = targetId ? document.getElementById(targetId) : btn.parentElement.querySelector('input');
      if (!input) return;
      const icon = btn.querySelector('i');
      const show = input.type === 'password';
      input.type = show ? 'text' : 'password';
      if (icon) icon.className = show ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye';
    });
  });

  /* ---------- Image preview ---------- */
  document.querySelectorAll('input[type="file"][name="image"]').forEach(function (input) {
    input.addEventListener('change', function () {
      const file = input.files[0];
      if (!file) return;
      if (file.size > 2097152) {
        toast('حجم الصورة يتجاوز 2MB.', true);
        input.value = '';
        return;
      }
      const wrap = input.closest('.form-group');
      let preview = wrap.querySelector('.img-preview');
      if (!preview) {
        preview = document.createElement('img');
        preview.className = 'img-preview';
        preview.style.cssText = 'width:110px;height:72px;object-fit:cover;border-radius:10px;border:1px solid rgba(148,163,184,0.2);display:block;margin-bottom:10px;';
        input.parentElement.insertBefore(preview, input);
      }
      preview.src = URL.createObjectURL(file);
    });
  });

  /* ---------- Toast ---------- */
  const toastWrap = document.getElementById('toastWrap');
  function toast(msg, isError) {
    if (!toastWrap) return;
    const el = document.createElement('div');
    el.className = 'toast-mini';
    el.innerHTML = '<i class="fa-solid ' + (isError ? 'fa-circle-exclamation' : 'fa-circle-check') + '"></i><span>' + msg + '</span>';
    if (isError) el.style.color = '#fca5a5';
    toastWrap.appendChild(el);
    setTimeout(function () {
      el.style.opacity = '0';
      el.style.transition = 'opacity .3s';
      setTimeout(function () { el.remove(); }, 320);
    }, 3500);
  }
  window.adminToast = toast;

  /* ---------- Unread messages badge ---------- */
  const badge = document.getElementById('msgBadge');
  function fetchUnread() {
    if (!badge) return;
    fetch('api/unread.php', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        const n = parseInt(data.count || 0, 10);
        badge.textContent = n;
        badge.classList.toggle('show', n > 0);
      })
      .catch(function () {});
  }
  fetchUnread();
  setInterval(fetchUnread, 30000);

})();