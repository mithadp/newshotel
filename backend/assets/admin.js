/* News Hotel: panel admin. Hanya penyempurnaan kecil; semua tetap berfungsi tanpa JavaScript. */
(() => {
  'use strict';

  // Menu samping di layar kecil.
  const sb = document.getElementById('sidebar');
  const toggle = document.querySelector('[data-sb-toggle]');
  if (sb && toggle) {
    toggle.addEventListener('click', () => {
      const open = sb.classList.toggle('is-open');
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
    document.addEventListener('click', (e) => {
      if (sb.classList.contains('is-open') && !sb.contains(e.target) && !toggle.contains(e.target)) {
        sb.classList.remove('is-open');
        toggle.setAttribute('aria-expanded', 'false');
      }
    });
  }

  // Cegah klik ganda saat unggahan lambat.
  document.querySelectorAll('form').forEach((f) => f.addEventListener('submit', (e) => {
    const btn = e.submitter;
    if (btn && btn.tagName === 'BUTTON' && !btn.name) setTimeout(() => { btn.disabled = true; }, 0);
  }));

  // Pratinjau gambar yang baru dipilih, sebelum disimpan.
  document.querySelectorAll('.img-field input[type="file"]').forEach((input) => {
    input.addEventListener('change', () => {
      const file = input.files && input.files[0];
      const box = input.closest('.img-field').querySelector('.img-preview');
      if (!file || !box || !file.type.startsWith('image/')) return;
      const img = document.createElement('img');
      img.alt = '';
      img.src = URL.createObjectURL(file);
      box.replaceChildren(img);
      box.classList.remove('img-preview--empty');
    });
  });

  // Peringatkan file yang terlalu besar sebelum terkirim.
  document.querySelectorAll('input[type="file"]').forEach((input) => {
    input.addEventListener('change', () => {
      const big = Array.from(input.files || []).filter((f) => f.size > 5 * 1024 * 1024).map((f) => f.name);
      input.setCustomValidity(big.length ? `Terlalu besar (maks. 5 MB): ${big.join(', ')}` : '');
      if (big.length) input.reportValidity();
    });
  });
})();
