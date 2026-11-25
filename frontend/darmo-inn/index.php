<?php
require_once __DIR__ . '/../includes/init.php';

$hotel = hotel('darmo-inn');
$id = $hotel['id'];

$rooms       = db_all('SELECT * FROM room_types WHERE hotel_id = ? AND is_active = 1 ORDER BY sort, id', [$id]);
$facilities  = db_all('SELECT * FROM facilities WHERE hotel_id = ? ORDER BY sort, id', [$id]);
$attractions = db_all('SELECT * FROM attractions WHERE hotel_id = ? ORDER BY sort, id', [$id]);
$dining      = db_all('SELECT * FROM dining_outlets WHERE hotel_id = ? AND is_active = 1 ORDER BY sort, id', [$id]);
$venues      = db_all('SELECT * FROM meeting_venues WHERE hotel_id = ? AND is_active = 1 ORDER BY sort, id', [$id]);
$gallery     = db_all('SELECT * FROM gallery_images WHERE hotel_id = ? ORDER BY is_featured DESC, sort, id LIMIT 10', [$id]);
$hotelPosts  = posts(3, 0, $id, null, false);

$page = [
    'title'       => $hotel['name'],
    'description' => excerpt(tr($hotel, 'description'), 155),
    'hotel'       => $hotel,
    'hero'        => true,
    'og_image'    => $hotel['hero_image'],
    'preload'     => preload_img($hotel['hero_image']),
    'json_ld'     => hotel_schema($hotel),
];
$navActive = 'home';

require FRONT . '/includes/header.php';
include FRONT . '/includes/partials/hotel-hero.php';
include FRONT . '/includes/partials/hotel-nav.php';
?>

<section class="section intro" id="tentang" aria-labelledby="h-about">
  <div class="container overview">
    <div class="overview-copy">
      <h2 id="h-about"><?= e(t('hotel.about')) ?></h2>
      <?php include FRONT . '/includes/partials/lead-more.php'; ?>
    </div>
    <div class="overview-visual">
      <?php $introKey = 'darmo-inn/reception'; include FRONT . '/includes/partials/intro-media.php'; ?>
    <dl class="facts" aria-label="<?= e(t('hotel.facts')) ?>">
      <?php if ($hotel['total_rooms']): ?><div><dt><?= e(t('hotel.rooms_count')) ?></dt><dd><?= (int) $hotel['total_rooms'] ?></dd></div><?php endif; ?>
      <?php if ($hotel['phone'] || $hotel['email']): ?>
      <div><dt><?= e(t('hotel.contact')) ?></dt><dd>
        <?php if ($hotel['phone']): ?><a href="<?= e(tel_link($hotel['phone'])) ?>"><?= e($hotel['phone']) ?></a><br><?php endif; ?>
        <?php if ($hotel['email']): ?><a href="mailto:<?= e($hotel['email']) ?>"><?= e($hotel['email']) ?></a><?php endif; ?>
      </dd></div>
      <?php else: ?>
      <div><dt><?= e(t('hotel.location')) ?></dt><dd><a href="<?= e(directions_url($hotel)) ?>" target="_blank" rel="noopener"><?= e(t('hotel.directions')) ?></a></dd></div>
      <?php endif; ?>
      <?php if ($hotel['operator']): ?><div><dt><?= e(t('hotel.operator')) ?></dt><dd><?= e($hotel['operator']) ?></dd></div><?php endif; ?>
    </dl>
    </div>
  </div>
</section>

<?php if ($gallery): include FRONT . '/includes/partials/gallery-mosaic.php'; endif; ?>

<?php $sectionClass = 'section--sand'; include FRONT . '/includes/partials/rooms-rail.php'; ?>

<?php if ($dining): $sectionClass = ''; include FRONT . '/includes/partials/dining-section.php'; endif; ?>

<?php if ($facilities): $sectionClass = 'section--sand'; include FRONT . '/includes/partials/facility-list.php'; endif; ?>

<?php if ($venues): $sectionClass = ''; include FRONT . '/includes/partials/meeting-section.php'; endif; ?>

<?php include FRONT . '/includes/partials/location.php'; ?>

<?php include FRONT . '/includes/partials/hotel-closing.php'; ?>

<?php require FRONT . '/includes/footer.php'; ?>
