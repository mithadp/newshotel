<?php
// Peta 3 hotel di beranda. Butuh: $hotels, $attractions (dikelompokkan per hotel_id).
// Tiap hotel punya satu iframe yang data-src-nya dipasang app.js saat bagian ini mendekati layar dan hotelnya dipilih.
// Tanpa JS, panel tampil berurutan dan peta hotel pertama muncul lewat <noscript>.
// Lokasi dari lat/lng bila ada, jika tidak dari nama + alamat; jangan mengarang koordinat.
?>
<section class="section section--deep" id="peta" aria-labelledby="h-map">
  <div class="container map-layout" data-map-explorer>
    <div class="map-side">
      <header class="section-head">
        <h2 id="h-map"><?= e(t('home.map_title')) ?></h2>
      </header>
      <div class="map-tabs" role="tablist" aria-orientation="vertical" aria-label="<?= e(t('nav.hotels')) ?>">
        <?php foreach ($hotels as $i => $h): ?>
          <button type="button" class="map-tab" role="tab" id="mt-<?= e($h['slug']) ?>" aria-controls="mp-<?= e($h['slug']) ?>" aria-selected="<?= $i === 0 ? 'true' : 'false' ?>" data-map-tab="<?= e($h['slug']) ?>" data-brand="<?= e($h['brand']) ?>">
            <span class="map-tab-name"><?= e($h['name']) ?></span>
            <span class="map-tab-city"><?= e($h['city']) ?></span>
          </button>
        <?php endforeach; ?>
      </div>
      <?php foreach ($hotels as $i => $h): $near = array_slice($attractions[$h['id']] ?? [], 0, 3); $mq = rawurlencode(maps_query($h)); ?>
        <div class="map-panel" role="tabpanel" id="mp-<?= e($h['slug']) ?>" aria-labelledby="mt-<?= e($h['slug']) ?>" data-map-panel="<?= e($h['slug']) ?>" tabindex="0">
          <div class="map-panel-media">
            <?= img($h['hero_image'], $h['name'], '(min-width: 560px) 140px, 90vw') ?>
          </div>
          <div class="map-panel-body">
            <p class="map-panel-address"><?= icon('pin') ?><span><?= e(hotel_address($h)) ?></span></p>
            <?php if ($near): ?>
              <ul class="map-nearby" aria-label="<?= e(t('hotel.nearby')) ?>">
                <?php foreach ($near as $a): ?><li><span><?= e(tr($a, 'name')) ?></span><?php if ($a['note']): ?><small><?= e(tr($a, 'note')) ?></small><?php endif; ?></li><?php endforeach; ?>
              </ul>
            <?php endif; ?>
            <div class="btn-row">
              <?php if (has_contact($h)): ?><?= wa_button($h, 'stay', '', 'btn btn-primary btn-sm', t('wa.chat')) ?><?php endif; ?>
              <a class="link-arrow" href="<?= e(url($h['slug'])) ?>"><?= e(t('hotel.view')) ?><?= icon('arrow-right') ?></a>
              <a class="link-arrow" href="https://www.google.com/maps/dir/?api=1&amp;destination=<?= e($mq) ?>" rel="noopener" target="_blank"><?= e(t('hotel.directions')) ?><?= icon('external-link') ?></a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="map-stage">
      <?php foreach ($hotels as $i => $h): ?>
        <?php $mapSrc = 'https://www.google.com/maps?q=' . rawurlencode(maps_query($h)) . '&z=16&output=embed&hl=' . LANG; ?>
        <iframe class="map-frame" data-map-frame="<?= e($h['slug']) ?>"<?= $i === 0 ? '' : ' hidden' ?> data-src="<?= e($mapSrc) ?>"
          title="<?= e($h['name'] . ' — ' . t('hotel.location')) ?>" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
        <?php if ($i === 0): ?><noscript><iframe class="map-frame" src="<?= e($mapSrc) ?>" title="<?= e($h['name'] . ' — ' . t('hotel.location')) ?>" referrerpolicy="no-referrer-when-downgrade"></iframe></noscript><?php endif; ?>
      <?php endforeach; ?>
    </div>
  </div>
</section>
