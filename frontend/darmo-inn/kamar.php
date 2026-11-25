<?php
require_once __DIR__ . '/../includes/init.php';

$hotel = hotel('darmo-inn');
$rooms = db_all('SELECT * FROM room_types WHERE hotel_id = ? AND is_active = 1 ORDER BY sort, id', [$hotel['id']]);
$photos = [];
foreach (db_all('SELECT i.* FROM room_images i JOIN room_types r ON r.id = i.room_type_id WHERE r.hotel_id = ? ORDER BY i.sort, i.id', [$hotel['id']]) as $ph) {
    $photos[$ph['room_type_id']][] = $ph;
}

$crumbs  = [[t('nav.home'), url()], [$hotel['short_name'], url($hotel['slug'])], [t('nav.rooms'), null]];

$page = [
    'title'       => t('hotel.rooms_title', ['hotel' => $hotel['name']]),
    'description' => tw('meta.rooms_description', ['hotel' => $hotel['name']]),
    'hotel'       => $hotel,
    'og_image'    => $rooms[0]['cover_image'] ?? $hotel['hero_image'],
    'json_ld'     => breadcrumb_schema($crumbs),
];
$navActive = 'kamar';

require FRONT . '/includes/header.php';
$heading = t('hotel.rooms_title', ['hotel' => $hotel['name']]);
$sub     = t('hotel.rooms_sub');
$heroImage = $hotel['hero_image'];
include FRONT . '/includes/partials/page-hero.php';
unset($heroImage);
include FRONT . '/includes/partials/hotel-nav.php';
?>
<div class="page-body">
  <div class="container">
    <?php if ($rooms): ?>
      <div class="room-list room-list--full">
        <?php foreach ($rooms as $room): $roomFull = true; $headTag = 'h2'; $roomImages = $photos[$room['id']] ?? []; ?>
          <?php include FRONT . '/includes/partials/room-card.php'; ?>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="empty"><?= icon('bed') ?><p><?= e(t('hotel.no_rooms')) ?></p><?= wa_button($hotel) ?></div>
    <?php endif; ?>
    <?php if (wa_number($hotel)): ?><p class="page-note"><?= icon('whatsapp') ?><span><?= e(t('wa.how')) ?></span></p><?php endif; ?>
  </div>
</div>
<?php require FRONT . '/includes/footer.php'; ?>
