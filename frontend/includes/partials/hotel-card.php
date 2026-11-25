<?php
// Kartu hotel potret (beranda, tentang, "hotel lain"). Butuh: $h.
$cardUrl = url($h['slug']);
?>
<article class="hotel-card" data-brand="<?= e($h['brand']) ?>">
  <a class="hotel-card-link" href="<?= e($cardUrl) ?>">
    <span class="hotel-card-media" style="view-transition-name: hotel-<?= e($h['slug']) ?>">
      <?= img($h['card_image'], $h['name'], '(min-width: 1100px) 400px, (min-width: 700px) 45vw, 85vw') ?>
    </span>
    <?php if ($h['status'] === 'soft_opening'): ?><span class="badge badge--new"><?= e(t('hotel.badge_new')) ?></span><?php endif; ?>
    <div class="hotel-card-body">
      <h3 class="hotel-card-name"><?= e($h['name']) ?></h3>
      <p class="hotel-card-city"><?= e($h['city']) ?></p>
      <p class="hotel-card-highlight"><?= e(tr($h, 'highlight')) ?></p>
      <span class="hotel-card-more"><?= e(t('hotel.view')) ?><?= icon('arrow-right') ?></span>
    </div>
  </a>
</article>
