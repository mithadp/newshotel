<?php
// Kartu hotel di layar pertama beranda. Butuh: $hotels.
?>
<ul class="hero-hotels" aria-label="<?= e(t('home.cards_label')) ?>">
  <?php foreach ($hotels as $i => $h): $photo = $h['hero_image'] ?: $h['card_image']; ?>
    <li class="hero-hotel" data-brand="<?= e($h['brand']) ?>" style="--i: <?= (int) $i ?>">
      <a class="hero-hotel-link<?= $i === 0 ? ' is-active' : '' ?>" href="<?= e(url($h['slug'])) ?>" data-hero-card="<?= e($h['slug']) ?>">
        <span class="hero-hotel-media" style="view-transition-name: hotel-<?= e($h['slug']) ?>">
          <?= img($photo, '', '(min-width: 861px) 160px, 40vw', ['loading' => 'eager']) ?>
          <?php if ($h['status'] === 'soft_opening'): ?><span class="badge badge--new"><?= e(t('hotel.badge_new')) ?></span><?php endif; ?>
        </span>
        <span class="hero-hotel-body">
          <strong class="hero-hotel-name"><?= e($h['name']) ?></strong>
          <span class="hero-hotel-city"><?= e($h['city']) ?></span>
          <?php if (tr($h, 'highlight') !== ''): ?><span class="hero-hotel-hl"><?= e(tr($h, 'highlight')) ?></span><?php endif; ?>
        </span>
        <span class="hero-hotel-go" aria-hidden="true"><?= icon('arrow-right') ?></span>
        <span class="hero-hotel-bar" aria-hidden="true"><i></i></span>
      </a>
    </li>
  <?php endforeach; ?>
</ul>
