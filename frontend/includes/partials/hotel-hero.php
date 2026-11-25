<?php
// Hero foto hotel layar penuh. Butuh: $hotel. Opsional: $heroNote (mis. tanggal soft opening).
// view-transition-name sama dengan foto kartu hotel, sehingga foto "mengalir" saat kartu diklik.
$heroNote = $heroNote ?? null;
$vt = 'hotel-' . $hotel['slug'];
?>
<section class="hero hero--hotel" aria-labelledby="hotel-title">
  <div class="hero-media">
    <figure class="hero-slide is-active" style="view-transition-name: <?= e($vt) ?>"><?= img($hotel['hero_image'], $hotel['name'], '100vw', ['loading' => 'eager', 'fetchpriority' => 'high']) ?></figure>
    <div class="hero-scrim" aria-hidden="true"></div>
  </div>
  <div class="hero-copy container">
    <h1 id="hotel-title"><?= e($hotel['name']) ?></h1>
    <?php if (tr($hotel, 'tagline') !== ''): ?><p class="hero-sub"><?= e(tr($hotel, 'tagline')) ?></p><?php endif; ?>
    <p class="hero-meta">
      <span><?= icon('pin') ?><?= e($hotel['address']) ?>, <?= e($hotel['city']) ?></span>
      <?php if ($hotel['status'] === 'soft_opening'): ?><span class="hero-status"><?= e(t('hotel.soft_opening')) ?><?= $heroNote ? ' · ' . e($heroNote) : '' ?></span><?php endif; ?>
    </p>
    <div class="hero-actions">
      <?= wa_button($hotel, 'stay', '', 'btn btn-primary btn-lg', t('wa.reserve')) ?>
      <a class="btn btn-outline-light btn-lg" href="<?= e(url($hotel['slug'] . '/kamar')) ?>"><?= e(t('hotel.see_rooms')) ?></a>
    </div>
  </div>
  <div class="hero-foot container">
    <a class="scroll-cue" href="#tentang"><span><?= e(t('hotel.scroll')) ?></span><i aria-hidden="true"></i></a>
  </div>
</section>
