<?php
// Kartu kamar lengkap untuk halaman Kamar. Butuh: $room, $hotel. Opsional: $roomImages (room_images), $headTag ('h2' | 'h3').
$roomImages = $roomImages ?? [];
$headTag    = $headTag ?? 'h3';
$name       = tr($room, 'name');
$amenities  = room_amenities($room);
$roomPhotos = $roomImages ?: ($room['cover_image'] ? [['image' => $room['cover_image'], 'alt' => $name, 'alt_en' => $name]] : []);
$extraPhotos = max(0, count($roomPhotos) - 3);   // foto ke-4 dst. disembunyikan; foto ke-3 diberi penanda "+N"
?>
<article class="room-row room-row--full" id="<?= e($room['slug']) ?>">
  <div class="room-media">
    <?php if ($roomPhotos): ?>
      <?php foreach ($roomPhotos as $i => $ph): $alt = tr($ph, 'alt') ?: $name; ?>
        <button type="button" class="mosaic-item<?= $i === 0 ? ' is-lead' : '' ?>" data-lightbox="room-<?= e($room['slug']) ?>" data-full="<?= e(img_url($ph['image'])) ?>" data-full-sm="<?= e(img_url($ph['image'], 960)) ?>" data-caption="<?= e($alt) ?>" aria-label="<?= e(t('room.photos') . ': ' . $alt) ?>"<?= $i > 2 ? ' hidden' : '' ?>>
          <?= img($ph['image'], $alt, $i === 0 ? '(min-width: 900px) 520px, 100vw' : '(min-width: 900px) 160px, 33vw') ?>
          <?php if ($i === 2 && $extraPhotos > 0): ?><span class="more-photos" aria-hidden="true">+<?= (int) $extraPhotos ?></span><?php endif; ?>
        </button>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="media"><?= img(null, $name) ?></div>
    <?php endif; ?>
  </div>

  <div class="room-row-body">
    <<?= $headTag ?>><?= e($name) ?></<?= $headTag ?>>
    <ul class="specs">
      <?php if ($room['size_m2']): ?><li><?= icon('ruler') ?><?= e(size_text($room['size_m2'])) ?></li><?php endif; ?>
      <?php if ($room['bed_type']): ?><li><?= icon('bed') ?><?= e(tr($room, 'bed_type')) ?></li><?php endif; ?>
      <?php if (room_capacity($room) !== ''): ?><li><?= icon('users') ?><?= e(room_capacity($room)) ?></li><?php endif; ?>
    </ul>
    <p><?= e(tr($room, 'description')) ?></p>
    <?php if ($amenities): ?>
      <h3 class="sub-h3"><?= e(t('room.amenities')) ?></h3>
      <ul class="amenities">
        <?php foreach ($amenities as $am): ?>
          <li><?= icon($am['icon'] ?? 'check') ?><?= e(is_en() && !empty($am['name_en']) ? $am['name_en'] : $am['name']) ?></li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
    <div class="room-row-foot">
      <?php if ($room['base_price'] !== null): ?>
        <p class="price"><span><?= e(t('price.from')) ?></span><strong class="num"><?= e(money($room['base_price'])) ?></strong><span>/<?= e(t('price.night')) ?></span></p>
      <?php else: ?>
        <p class="price price--tba"><?= e(t('price.coming')) ?></p>
      <?php endif; ?>
      <div class="btn-row">
        <?php if (has_contact($hotel)): ?><?= wa_button($hotel, 'room', $name, 'btn btn-primary btn-sm', t('wa.ask_room')) ?><?php endif; ?>
      </div>
    </div>
  </div>
</article>
