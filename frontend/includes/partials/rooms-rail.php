<?php
// Kamar di beranda hotel: deretan kartu yang bisa digeser. Butuh: $hotel, $rooms. Opsional: $sectionClass (bawaan: section--dark).
$hasContact = has_contact($hotel);
?>
<?php $secClass = $sectionClass ?? 'section--dark'; unset($sectionClass); ?>
<section class="section<?= $secClass !== '' ? ' ' . e($secClass) : '' ?>" id="kamar" aria-labelledby="h-rooms">
  <div class="container">
    <header class="section-head section-head--row">
      <div>
        <h2 id="h-rooms"><?= e(t('hotel.rooms_heading')) ?></h2>
        <p><?= e(t('hotel.rooms_intro')) ?></p>
      </div>
      <a class="link-arrow" href="<?= e(url($hotel['slug'] . '/kamar')) ?>"><?= e(t('hotel.all_rooms')) ?><?= icon('arrow-right') ?></a>
    </header>
  </div>
  <?php if ($rooms): ?>
    <div class="rail" data-rail>
      <div class="rail-track" data-rail-track tabindex="0" role="list" aria-label="<?= e(t('hotel.rooms_heading')) ?>">
        <?php foreach ($rooms as $room): $name = tr($room, 'name'); $detailUrl = url($hotel['slug'] . '/kamar') . '#' . $room['slug']; ?>
          <article class="room-slide" role="listitem">
            <a class="room-slide-media" href="<?= e($detailUrl) ?>" tabindex="-1" aria-hidden="true">
              <?= img($room['cover_image'], $name, '(min-width: 1000px) 440px, 80vw') ?>
            </a>
            <h3><a href="<?= e($detailUrl) ?>"><?= e($name) ?></a></h3>
            <p class="room-slide-specs">
              <?php if ($room['size_m2']): ?><span><?= e(size_text($room['size_m2'])) ?></span><?php endif; ?>
              <?php if ($room['bed_type']): ?><span><?= e(tr($room, 'bed_type')) ?></span><?php endif; ?>
              <?php if (room_capacity($room) !== ''): ?><span><?= e(room_capacity($room)) ?></span><?php endif; ?>
            </p>
            <p class="room-slide-text"><?= e(excerpt(tr($room, 'description'), 130)) ?></p>
            <p class="room-slide-links">
              <a class="link-arrow" href="<?= e($detailUrl) ?>"><?= e(t('room.details')) ?><?= icon('arrow-right') ?></a>
              <?php if ($hasContact): ?><?= wa_button($hotel, 'room', $name, 'link-wa', t('wa.ask_room')) ?><?php endif; ?>
            </p>
          </article>
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
  <?php else: ?>
    <div class="container"><div class="empty"><?= icon('bed') ?><p><?= e(t('hotel.no_rooms')) ?></p><?= wa_button($hotel) ?></div></div>
  <?php endif; ?>
</section>
