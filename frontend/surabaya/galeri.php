<?php
require_once __DIR__ . '/../includes/init.php';

$hotel  = hotel('surabaya');
$images = db_all('SELECT * FROM gallery_images WHERE hotel_id = ? ORDER BY sort, id', [$hotel['id']]);

$crumbs  = [[t('nav.home'), url()], [$hotel['short_name'], url($hotel['slug'])], [t('nav.gallery'), null]];

$page = [
    'title'       => t('gallery.title', ['hotel' => $hotel['name']]),
    'description' => t('meta.gallery_description', ['hotel' => $hotel['name']]),
    'hotel'       => $hotel,
    'og_image'    => $images[0]['image'] ?? $hotel['hero_image'],
    'json_ld'     => breadcrumb_schema($crumbs),
];
$navActive = 'galeri';

require FRONT . '/includes/header.php';
$heading = t('gallery.title', ['hotel' => $hotel['name']]);
$sub     = null;
$heroImage = $hotel['hero_image'];
include FRONT . '/includes/partials/page-hero.php';
unset($heroImage);
include FRONT . '/includes/partials/hotel-nav.php';
?>
<div class="page-body">
  <div class="container">
    <?php include FRONT . '/includes/partials/gallery-grid.php'; ?>
  </div>
</div>
<?php require FRONT . '/includes/footer.php'; ?>
