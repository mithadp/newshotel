<?php
// Butuh: $hotel, $images.
$categories = [];
foreach ($images as $g) {
    $categories[$g['category']] = ($categories[$g['category']] ?? 0) + 1;
}
$active = (string) ($_GET['kategori'] ?? 'all');
if ($active !== 'all' && !isset($categories[$active])) {
    $active = 'all';
}
?>
<?php if ($images): ?>
  <?php if (count($categories) > 1): ?>
  <div class="chips" data-filter-group="#gallery-grid" role="group" aria-label="<?= e(t('gallery.filter')) ?>">
    <a class="chip" href="<?= e(url($hotel['slug'] . '/galeri')) ?>" data-filter="all"<?= $active === 'all' ? ' aria-current="true"' : '' ?>><?= e(t('gallery.all')) ?> <small><?= count($images) ?></small></a>
    <?php foreach ($categories as $cat => $n): ?>
      <a class="chip" href="<?= e(url($hotel['slug'] . '/galeri', ['kategori' => $cat])) ?>" data-filter="<?= e($cat) ?>"<?= $active === $cat ? ' aria-current="true"' : '' ?>><?= e(t('gallery.cat.' . $cat)) ?> <small><?= (int) $n ?></small></a>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
  <div class="gallery-grid" id="gallery-grid">
    <?php foreach ($images as $g): $cap = tr($g, 'caption'); ?>
      <button type="button" class="mosaic-item" data-cat="<?= e($g['category']) ?>" data-lightbox="gallery" data-full="<?= e(img_url($g['image'])) ?>" data-full-sm="<?= e(img_url($g['image'], 960)) ?>" data-caption="<?= e($cap) ?>" aria-label="<?= e($cap !== '' ? $cap : t('hotel.gallery')) ?>"<?= $active !== 'all' && $active !== $g['category'] ? ' hidden' : '' ?>>
        <?= img($g['image'], $cap, '(min-width: 1000px) 33vw, (min-width: 640px) 50vw, 100vw') ?>
        <span class="figcap"><?= e($cap) ?></span>
      </button>
    <?php endforeach; ?>
  </div>
<?php else: ?>
  <div class="empty"><?= icon('image') ?><p><?= e(t('gallery.empty')) ?></p></div>
<?php endif; ?>
