<?php
// Butuh: $gallery, $hotel. Opsional: $sectionClass (bawaan: section--deep).
?>
<?php $secClass = $sectionClass ?? 'section--deep'; unset($sectionClass); ?>
<section class="section<?= $secClass !== '' ? ' ' . e($secClass) : '' ?>" aria-labelledby="h-gal">
  <div class="container">
    <header class="section-head section-head--row">
      <h2 id="h-gal"><?= e(t('hotel.gallery')) ?></h2>
      <a class="link-arrow" href="<?= e(url($hotel['slug'] . '/galeri')) ?>"><?= e(t('hotel.gallery_all')) ?><?= icon('arrow-right') ?></a>
    </header>
  </div>
  <div class="rail rail--gallery" data-rail>
    <div class="rail-track" data-rail-track tabindex="0" role="list" aria-label="<?= e(t('hotel.gallery')) ?>">
      <?php foreach ($gallery as $i => $g): $cap = tr($g, 'caption'); ?>
        <figure class="gallery-slide" role="listitem">
          <button type="button" class="mosaic-item" data-lightbox="hotel" data-full="<?= e(img_url($g['image'])) ?>" data-full-sm="<?= e(img_url($g['image'], 960)) ?>" data-caption="<?= e($cap) ?>" aria-label="<?= e($cap !== '' ? $cap : t('hotel.gallery')) ?>">
            <?= img($g['image'], $cap, '(min-width: 1000px) 60vw, 88vw') ?>
          </button>
          <?php if ($cap !== ''): ?><figcaption><?= e($cap) ?></figcaption><?php endif; ?>
        </figure>
      <?php endforeach; ?>
    </div>
    <div class="container rail-foot">
      <div class="rail-progress" aria-hidden="true"><i data-rail-progress></i></div>
      <div class="rail-nav">
        <button type="button" class="rail-btn" data-rail-prev aria-label="<?= e(t('a11y.prev')) ?>"><?= icon('arrow-left') ?></button>
        <button type="button" class="rail-btn" data-rail-next aria-label="<?= e(t('a11y.next')) ?>"><?= icon('arrow-right') ?></button>
      </div>
    </div>
  </div>
</section>
