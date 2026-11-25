/* News Hotel: penyempurnaan bertahap; semua fitur tetap berfungsi tanpa JavaScript. */
(() => {
  'use strict';
  const NH = window.NH || { i18n: {} };
  const $ = (s, r = document) => r.querySelector(s);
  const $$ = (s, r = document) => Array.from(r.querySelectorAll(s));
  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  const iconHref = (name) => {
    const any = $('svg.icon use');
    const base = any ? any.getAttribute('href').split('#')[0] : '';
    return `${base}#${name}`;
  };

  // Header: transparan di atas hero, solid setelah menggulir, menyembunyikan diri saat menggulir ke bawah
  const header = $('[data-header]');
  if (header) {
    const overHero = header.classList.contains('is-over-hero');
    let lastY = window.scrollY;
    let ticking = false;
    const update = () => {
      const y = window.scrollY;
      if (overHero) header.classList.toggle('is-scrolled', y > 24);
      const goingDown = y > lastY + 4;
      const goingUp = y < lastY - 4;
      const hide = goingDown && y > window.innerHeight * 0.6 && !document.querySelector('[data-wa-pick][open]');
      if (hide) header.classList.add('is-hidden');
      else if (goingUp || y < 120) header.classList.remove('is-hidden');
      document.body.classList.toggle('header-is-hidden', header.classList.contains('is-hidden'));
      if (goingDown || goingUp) lastY = y;
      ticking = false;
    };
    update();
    window.addEventListener('scroll', () => { if (!ticking) { ticking = true; requestAnimationFrame(update); } }, { passive: true });
    header.addEventListener('focusin', () => { header.classList.remove('is-hidden'); document.body.classList.remove('header-is-hidden'); });
  }

  // Drawer menu
  const drawer = $('#site-drawer');
  const drawerBtn = $('[data-drawer-open]');
  if (drawer && drawerBtn && typeof drawer.showModal === 'function') {
    drawerBtn.addEventListener('click', () => { drawer.showModal(); drawerBtn.setAttribute('aria-expanded', 'true'); });
    drawer.addEventListener('close', () => drawerBtn.setAttribute('aria-expanded', 'false'));
    $$('[data-drawer-close]', drawer).forEach((b) => b.addEventListener('click', () => drawer.close()));
    drawer.addEventListener('click', (e) => { if (e.target === drawer) drawer.close(); });
  }

  // Pilihan WhatsApp: tutup saat klik di luar atau Escape; hanya satu yang terbuka
  const picks = $$('[data-wa-pick]');
  picks.forEach((pick) => {
    pick.addEventListener('toggle', () => {
      if (pick.open) picks.forEach((other) => { if (other !== pick) other.open = false; });
    });
    pick.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && pick.open) { pick.open = false; $('summary', pick).focus(); }
    });
  });
  document.addEventListener('click', (e) => {
    picks.forEach((pick) => { if (pick.open && !pick.contains(e.target)) pick.open = false; });
  });

  // Sub-menu hotel: tombol CTA muncul saat menempel; dock WA ponsel muncul setelah hero terlewati
  const subNav = $('[data-hotel-nav]');
  if (subNav) {
    const onScroll = () => {
      const top = parseFloat(getComputedStyle(subNav).top) || 0;
      subNav.classList.toggle('is-stuck', subNav.getBoundingClientRect().top <= top + 1);
    };
    let ticking = false;
    onScroll();
    window.addEventListener('scroll', () => { if (!ticking) { ticking = true; requestAnimationFrame(() => { ticking = false; onScroll(); }); } }, { passive: true });
  }
  const dock = $('[data-wa-dock]');
  if (dock) {
    const heroEl = $('.hero');
    if (heroEl && 'IntersectionObserver' in window) {
      new IntersectionObserver(([en]) => dock.classList.toggle('is-shown', !en.isIntersecting && en.boundingClientRect.top < 0), { threshold: 0 }).observe(heroEl);
    } else {
      dock.classList.add('is-shown');
    }
  }

  // Hero beranda grup: kartu hotel mengganti foto latar di desktop
  // Di ponsel slide tanpa foto (img_desktop) dan kartu adalah isinya, jadi tidak ada rotasi.
  const hero = $('[data-hero]');
  if (hero) {
    const slides = $$('[data-slide]', hero);
    const cards = $$('[data-hero-card]', hero);
    const desk = window.matchMedia('(min-width: 861px)');
    const SLIDE_MS = 7000;
    hero.style.setProperty('--slide-ms', SLIDE_MS + 'ms');
    let idx = 0;
    let timer = null;
    const wantPlay = !reduceMotion && slides.length > 1;
    let userPaused = false;   // tombol jeda dari pengunjung (WCAG 2.2.2)
    const motionBtn = $('[data-motion-pause]');
    // Slide tersembunyi memakai data-srcset agar tidak bersaing dengan gambar pertama (LCP).
    const hydrate = (slide) => {
      if (!slide || !desk.matches) return;
      $$('source[data-srcset], img[data-srcset], img[data-src]', slide).forEach((el) => {
        if (el.dataset.srcset) { el.srcset = el.dataset.srcset; el.removeAttribute('data-srcset'); }
        if (el.dataset.src) { el.src = el.dataset.src; el.removeAttribute('data-src'); }
      });
    };
    // Hanya slide berikutnya dimuat lebih awal (3 dtk setelah load); show() memuat slide lain sesaat sebelum dipakai.
    window.addEventListener('load', () => setTimeout(() => hydrate(slides[1]), 3000));

    const show = (i) => {
      idx = (i + slides.length) % slides.length;
      slides.forEach((s, n) => s.classList.toggle('is-active', n === idx));
      cards.forEach((c, n) => {
        c.classList.toggle('is-active', n === idx);
        const bar = $('.hero-hotel-bar i', c); if (bar) { bar.style.animation = 'none'; void bar.offsetWidth; bar.style.animation = ''; }
      });
      hydrate(slides[idx]); hydrate(slides[(idx + 1) % slides.length]);
    };
    const playing = () => wantPlay && !userPaused && desk.matches;
    const schedule = () => { clearTimeout(timer); if (playing()) timer = setTimeout(() => { show(idx + 1); schedule(); }, SLIDE_MS); };
    const sync = () => {
      hero.classList.toggle('is-playing', playing());
      schedule();
    };
    // Hover atau fokus pada kartu menaruh foto hotelnya di latar; sentuhan langsung mengikuti tautan.
    cards.forEach((c, n) => {
      c.addEventListener('pointerenter', (e) => { if (e.pointerType === 'mouse' && desk.matches) show(n); });
      c.addEventListener('focus', () => { if (desk.matches) show(n); });
    });
    if (motionBtn) {
      if (reduceMotion) motionBtn.hidden = true;
      motionBtn.addEventListener('click', () => {
        userPaused = !userPaused;
        motionBtn.setAttribute('aria-pressed', String(userPaused));
        document.documentElement.classList.toggle('motion-paused', userPaused);
        sync();
      });
    }
    hero.addEventListener('mouseenter', () => clearTimeout(timer));
    hero.addEventListener('mouseleave', schedule);
    hero.addEventListener('focusin', () => clearTimeout(timer));
    document.addEventListener('visibilitychange', () => (document.hidden ? clearTimeout(timer) : schedule()));
    // Saat melewati lebar desktop (ubah ukuran/putar layar): muat foto sekarang; load pertama tetap ditunda 1,5 dtk seperti di atas.
    desk.addEventListener('change', () => { if (desk.matches) { hydrate(slides[idx]); hydrate(slides[(idx + 1) % slides.length]); } sync(); });
    sync();
  }

  // Film beranda: dimuat saat mendekati layar, diputar saat terlihat, patuh pada jeda dan gerak dikurangi
  $$('[data-film]').forEach((box) => {
    const v = $('video', box);
    if (!v) return;
    const root = document.documentElement;
    const saver = navigator.connection && navigator.connection.saveData;
    let loaded = false, visible = false;
    const attach = () => {
      if (loaded) return;
      loaded = true;
      [[box.dataset.srcWebm, 'video/webm'], [box.dataset.srcMp4, 'video/mp4']].forEach(([src, type]) => {
        if (!src) return;
        const s = document.createElement('source');
        s.src = src; s.type = type;
        v.appendChild(s);
      });
      v.load();
    };
    // Gerak dikurangi atau hemat data: tidak diputar otomatis; pengguna memutar sendiri lewat kontrol bawaan.
    if (reduceMotion || saver) { v.controls = true; v.loop = false; attach(); return; }
    const sync = () => {
      const want = loaded && visible && !document.hidden && !root.classList.contains('motion-paused');
      if (want) { const p = v.play(); if (p && p.catch) p.catch(() => {}); } else v.pause();
    };
    if ('IntersectionObserver' in window) {
      new IntersectionObserver((es) => { if (es.some((e) => e.isIntersecting)) { attach(); sync(); } }, { rootMargin: '400px 0px' }).observe(box);
      new IntersectionObserver((es) => { visible = es.some((e) => e.isIntersecting); sync(); }, { threshold: 0.35 }).observe(box);
    } else { attach(); visible = true; sync(); }
    new MutationObserver(sync).observe(root, { attributes: true, attributeFilter: ['class'] });
    document.addEventListener('visibilitychange', sync);
  });

  // Rail: geser, seret, tombol, keyboard, progres
  $$('[data-rail]').forEach((rail) => {
    const track = $('[data-rail-track]', rail);
    const prev = $('[data-rail-prev]', rail);
    const next = $('[data-rail-next]', rail);
    const bar = $('[data-rail-progress]', rail);
    const foot = $('.rail-foot', rail);
    if (!track) return;
    const step = () => {
      const item = track.firstElementChild;
      const gap = parseFloat(getComputedStyle(track).columnGap) || 24;
      return item ? item.getBoundingClientRect().width + gap : track.clientWidth * 0.8;
    };
    const go = (dir) => track.scrollBy({ left: dir * step(), behavior: reduceMotion ? 'auto' : 'smooth' });
    const update = () => {
      const max = track.scrollWidth - track.clientWidth;
      if (prev) prev.disabled = track.scrollLeft <= 2;
      if (next) next.disabled = track.scrollLeft >= max - 2;
      if (bar) {
        const visible = max > 0 ? track.clientWidth / track.scrollWidth : 1;
        const pos = max > 0 ? track.scrollLeft / max : 0;
        bar.style.setProperty('--w', (visible * 100).toFixed(2) + '%');
        bar.style.setProperty('--x', (pos * (100 - visible * 100)).toFixed(2) + '%');
      }
      if (foot) foot.hidden = max <= 2;
    };
    if (prev) prev.addEventListener('click', () => go(-1));
    if (next) next.addEventListener('click', () => go(1));
    track.addEventListener('keydown', (e) => {
      if (e.key === 'ArrowRight') { e.preventDefault(); go(1); }
      if (e.key === 'ArrowLeft') { e.preventDefault(); go(-1); }
    });
    // Seret dengan mouse (sentuhan sudah menggulir secara bawaan).
    let down = false; let startX = 0; let startLeft = 0; let moved = false;
    track.addEventListener('pointerdown', (e) => {
      if (e.pointerType !== 'mouse' || e.button !== 0) return;
      down = true; moved = false; startX = e.clientX; startLeft = track.scrollLeft;
    });
    window.addEventListener('pointermove', (e) => {
      if (!down) return;
      const dx = e.clientX - startX;
      if (!moved && Math.abs(dx) > 6) { moved = true; track.classList.add('is-dragging'); }
      if (moved) track.scrollLeft = startLeft - dx;
    });
    window.addEventListener('pointerup', () => {
      if (!down) return;
      down = false;
      if (moved) {
        track.classList.remove('is-dragging');
        const s = step();
        track.scrollTo({ left: Math.round(track.scrollLeft / s) * s, behavior: reduceMotion ? 'auto' : 'smooth' });
        setTimeout(() => { moved = false; }, 0);
      }
    });
    track.addEventListener('click', (e) => { if (moved) { e.preventDefault(); e.stopPropagation(); } }, true);
    track.addEventListener('scroll', () => requestAnimationFrame(update), { passive: true });
    window.addEventListener('resize', update);
    update();
  });

  // Peta tiga hotel: tab memilih panel hotel dan bingkai Google Maps-nya
  $$('[data-map-explorer]').forEach((box) => {
    const tabs = $$('[role="tab"]', box);
    const panels = $$('[data-map-panel]', box);
    const frames = $$('[data-map-frame]', box);
    if (!tabs.length) return;
    // Embed Google dipasang hanya saat bagian ini dekat layar dan hotelnya dipilih.
    let near = !('IntersectionObserver' in window);
    const loadActive = () => frames.forEach((fr) => { if (near && !fr.hidden && !fr.src && fr.dataset.src) fr.src = fr.dataset.src; });
    if (!near) new IntersectionObserver(([en]) => { if (en.isIntersecting) { near = true; loadActive(); } }, { rootMargin: '300px 0px' }).observe(box);
    const select = (slug, focus) => {
      tabs.forEach((t) => {
        const on = t.dataset.mapTab === slug;
        t.setAttribute('aria-selected', on ? 'true' : 'false');
        t.tabIndex = on ? 0 : -1;
        if (on && focus) t.focus();
      });
      panels.forEach((pn) => { pn.hidden = pn.dataset.mapPanel !== slug; });
      frames.forEach((fr) => { fr.hidden = fr.dataset.mapFrame !== slug; });
      loadActive();
    };
    tabs.forEach((t, i) => {
      t.addEventListener('click', () => select(t.dataset.mapTab));
      t.addEventListener('keydown', (e) => {
        const d = { ArrowDown: 1, ArrowRight: 1, ArrowUp: -1, ArrowLeft: -1 }[e.key];
        if (e.key === 'Home' || e.key === 'End') { e.preventDefault(); select(tabs[e.key === 'Home' ? 0 : tabs.length - 1].dataset.mapTab, true); }
        if (d) { e.preventDefault(); select(tabs[(i + d + tabs.length) % tabs.length].dataset.mapTab, true); }
      });
    });
    select(tabs[0].dataset.mapTab);
  });

  // Paralaks: foto bergeser pelan di dalam bingkainya
  const plx = $$('[data-parallax]');
  if (plx.length && !reduceMotion && 'IntersectionObserver' in window) {
    const live = new Set();
    const pio = new IntersectionObserver((entries) => entries.forEach((en) => (en.isIntersecting ? live.add(en.target) : live.delete(en.target))), { rootMargin: '15% 0px' });
    plx.forEach((el) => pio.observe(el));
    let praf = 0;
    const tick = () => {
      praf = 0;
      const vh = window.innerHeight;
      live.forEach((el) => {
        const r = el.getBoundingClientRect();
        const k = parseFloat(el.dataset.parallax) || 0.1;
        const lim = r.height * 0.065;
        const off = Math.max(-lim, Math.min(lim, (r.top + r.height / 2 - vh / 2) * -k));
        el.style.setProperty('--py', off.toFixed(1) + 'px');
      });
    };
    window.addEventListener('scroll', () => { if (!praf) praf = requestAnimationFrame(tick); }, { passive: true });
    window.addEventListener('resize', tick);
    tick();
  }

  // Lightbox
  const groups = {};
  $$('[data-lightbox]').forEach((el) => { (groups[el.dataset.lightbox] ||= []).push(el); });
  if (Object.keys(groups).length) {
    const lb = document.createElement('dialog');
    lb.className = 'lightbox';
    lb.setAttribute('aria-label', NH.i18n.gallery || 'Gallery');
    lb.innerHTML = `<div class="lb-top"><span data-lb-count role="status" aria-live="polite"></span><button type="button" class="icon-btn" data-lb-close aria-label="${NH.i18n.close || 'Close'}"><svg class="icon"><use href="${iconHref('x')}"/></svg></button></div>
      <div class="lb-stage"><button type="button" class="icon-btn" data-lb-prev aria-label="${NH.i18n.prev || 'Previous'}"><svg class="icon"><use href="${iconHref('chevron-left')}"/></svg></button><img alt=""><button type="button" class="icon-btn" data-lb-next aria-label="${NH.i18n.next || 'Next'}"><svg class="icon"><use href="${iconHref('chevron-right')}"/></svg></button></div>
      <p class="lb-caption" data-lb-caption></p>`;
    document.body.append(lb);
    const img = $('img', lb); let list = []; let i = 0;
    // Ponsel memakai varian 960 px (data-full-sm), bukan 1440–1600 px.
    const srcOf = (el) => (window.innerWidth <= 820 && el.dataset.fullSm) || el.dataset.full;
    const render = () => {
      const el = list[i];
      img.classList.remove('is-in'); void img.offsetWidth; img.classList.add('is-in');
      img.src = srcOf(el); img.alt = el.dataset.caption || '';
      // Muat foto di sebelahnya agar tombol berikut/sebelumnya terasa instan.
      [1, -1].forEach((d) => { if (list.length > 1) new Image().src = srcOf(list[(i + d + list.length) % list.length]); });
      $('[data-lb-caption]', lb).textContent = el.dataset.caption || '';
      $('[data-lb-count]', lb).textContent = `${i + 1} / ${list.length}`;
      $('[data-lb-prev]', lb).hidden = $('[data-lb-next]', lb).hidden = list.length < 2;
    };
    const go = (d) => { i = (i + d + list.length) % list.length; render(); };
    Object.values(groups).forEach((items) => items.forEach((el) => el.addEventListener('click', (e) => {
      e.preventDefault();
      // Thumbnail yang disembunyikan tata letak (mis. foto kamar tambahan) tetap bagian dari set.
      list = items.filter((x) => !x.closest('.gallery-grid') || !x.hidden); i = Math.max(0, list.indexOf(el));
      render(); lb.showModal();
    })));
    $('[data-lb-close]', lb).addEventListener('click', () => lb.close());
    $('[data-lb-prev]', lb).addEventListener('click', () => go(-1));
    $('[data-lb-next]', lb).addEventListener('click', () => go(1));
    lb.addEventListener('keydown', (e) => { if (e.key === 'ArrowLeft') go(-1); if (e.key === 'ArrowRight') go(1); });
    // Geser kiri/kanan pada foto (sentuh, pena, atau seret mouse); gulir vertikal tetap bawaan (touch-action: pan-y).
    const stage = $('.lb-stage', lb); let sx = 0; let sy = 0; let tracking = false;
    stage.addEventListener('pointerdown', (e) => { if (e.target.closest('button')) return; tracking = true; sx = e.clientX; sy = e.clientY; });
    stage.addEventListener('pointerup', (e) => {
      if (!tracking) return; tracking = false;
      const dx = e.clientX - sx; const dy = e.clientY - sy;
      if (list.length > 1 && Math.abs(dx) > 48 && Math.abs(dx) > Math.abs(dy) * 1.5) go(dx < 0 ? 1 : -1);
    });
    stage.addEventListener('pointercancel', () => { tracking = false; });
    lb.addEventListener('click', (e) => { if (e.target === lb || e.target.classList.contains('lb-stage')) lb.close(); });
  }

  // Filter galeri (tautan tetap berfungsi tanpa JS)
  $$('[data-filter-group]').forEach((group) => {
    const target = $(group.dataset.filterGroup);
    $$('[data-filter]', group).forEach((chip) => {
      chip.setAttribute('role', 'button');
      chip.setAttribute('aria-pressed', chip.hasAttribute('aria-current') ? 'true' : 'false');
      chip.removeAttribute('aria-current');
    });
    $$('[data-filter]', group).forEach((chip) => chip.addEventListener('click', (e) => {
      e.preventDefault();
      const cat = chip.dataset.filter;
      $$('[data-filter]', group).forEach((c) => c.setAttribute('aria-pressed', c === chip ? 'true' : 'false'));
      $$('[data-cat]', target).forEach((it) => { it.hidden = cat !== 'all' && it.dataset.cat !== cat; });
      history.replaceState(null, '', chip.getAttribute('href'));
    }));
  });

  // Reveal: konten naik bertahap, foto tersingkap seperti tirai (konten terlihat secara default)
  if (!reduceMotion && 'IntersectionObserver' in window) {
    const groups = '.rail-track, .post-grid, .news-side, .outlet-grid, .contact-cards, .facility-list, .hotel-cards, .map-tabs, .room-list';
    const singles = '.section-head, .overview-copy, .overview-visual, .meet-copy, .location-grid > *, .split > .outlet-copy, .news-layout > .post-card, .table-wrap, .accordion, .map-stage, .footer-cta';
    const wipes = '.overview-media, .split > .media, .outlet-card > .media';
    const targets = [];
    $$(groups).forEach((g) => Array.from(g.children).forEach((el, i) => { el.style.setProperty('--i', Math.min(i, 6)); targets.push(el); }));
    $$(singles).forEach((el) => { if (!targets.includes(el)) targets.push(el); });
    $$(wipes).forEach((el) => { el.classList.add('reveal-wipe'); if (!targets.includes(el)) targets.push(el); });
    const io = new IntersectionObserver((entries) => entries.forEach((en) => {
      if (!en.isIntersecting) return;
      const el = en.target;
      el.classList.add('is-revealed');
      io.unobserve(el);
      const delay = (parseFloat(el.style.getPropertyValue('--i')) || 0) * 140;
      setTimeout(() => el.classList.remove('reveal-ready', 'is-revealed', 'reveal-wipe'), 1700 + delay);
    }), { rootMargin: '0px 0px -8% 0px' });
    targets
      .filter((el) => el.getBoundingClientRect().top > window.innerHeight * 0.92)
      .forEach((el) => { el.classList.add('reveal-ready'); io.observe(el); });
    $$('.reveal-wipe:not(.reveal-ready)').forEach((el) => el.classList.remove('reveal-wipe'));
  }
})();
