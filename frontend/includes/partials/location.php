<?php
// Lokasi: peta Google (loading="lazy"), alamat, petunjuk arah, tempat sekitar. Butuh: $hotel, $attractions.
$mapQuery = rawurlencode(maps_query($hotel));
?>
<?php $secClass = $sectionClass ?? 'section--sand'; unset($sectionClass); ?>
<section class="section<?= $secClass !== '' ? ' ' . e($secClass) : '' ?>" id="lokasi" aria-labelledby="h-loc">
  <div class="container location-grid">
    <div class="map-embed">
      <iframe src="https://www.google.com/maps?q=<?= e($mapQuery) ?>&amp;z=16&amp;output=embed&amp;hl=<?= e(LANG) ?>" title="<?= e($hotel['name'] . ' — ' . t('hotel.location')) ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
    </div>
    <div class="address-block">
      <h2 id="h-loc"><?= e(t('hotel.location')) ?></h2>
      <address><?= e($hotel['name']) ?><br><?= e(hotel_address($hotel)) ?></address>
      <p><a class="link-arrow" href="https://www.google.com/maps/dir/?api=1&amp;destination=<?= e($mapQuery) ?>" rel="noopener" target="_blank"><?= e(t('hotel.directions')) ?><?= icon('external-link') ?></a></p>
      <?php if ($attractions): ?>
        <h3 class="nearby-title"><?= e(t('hotel.nearby')) ?></h3>
        <ul class="nearby">
          <?php foreach ($attractions as $a): ?><li><span><?= e(tr($a, 'name')) ?></span><?php if ($a['note']): ?><small><?= e(tr($a, 'note')) ?></small><?php endif; ?></li><?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>
  </div>
</section>
