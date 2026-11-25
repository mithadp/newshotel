<?php
// Pita nama hotel dan kota yang berjalan pelan (dekoratif, aria-hidden; berhenti saat hover atau gerak dikurangi). Butuh: $hotels.
$marqueeItems = [];
foreach ($hotels as $mh) {
    $marqueeItems[] = [$mh['name'], false];
    $marqueeItems[] = [$mh['city'], true];
}
$marqueeItems = array_merge($marqueeItems, $marqueeItems);
$sparkle = '<svg viewBox="0 0 14 14" aria-hidden="true" focusable="false"><path d="M7 0 8.6 5.4 14 7 8.6 8.6 7 14 5.4 8.6 0 7 5.4 5.4Z" fill="currentColor"/></svg>';
?>
<div class="marquee-bar">
<div class="marquee" aria-hidden="true">
  <div class="marquee-track">
    <?php for ($copy = 0; $copy < 2; $copy++): ?>
      <div class="marquee-group">
        <?php foreach ($marqueeItems as [$label, $isCity]): ?>
          <span class="marquee-item<?= $isCity ? ' marquee-item--city' : '' ?>"><?= e($label) ?></span><?= $sparkle ?>
        <?php endforeach; ?>
      </div>
    <?php endfor; ?>
  </div>
</div>
<button type="button" class="motion-pause" data-motion-pause aria-pressed="false" aria-label="<?= e(t('a11y.pause')) ?>"><?= icon('pause', 'icon i-pause') ?><?= icon('play', 'icon i-play') ?></button>
</div>
