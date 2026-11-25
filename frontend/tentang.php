<?php
require_once __DIR__ . '/includes/init.php';

$page = [
    'title'       => t('about.title'),
    'description' => t('meta.about_description'),
    'og_image'    => 'surabaya/news-corner',
];

require FRONT . '/includes/header.php';
$heading = t('about.title');
$sub     = t('about.lead');
$crumbs  = [[t('nav.home'), url()], [t('nav.about'), null]];
include FRONT . '/includes/partials/page-hero.php';
?>
<div class="page-body">
  <div class="container">
    <div class="split">
      <div class="media"><?= img('surabaya/news-corner', 'News Corner, News Hotel Surabaya', '(min-width: 1000px) 50vw, 100vw') ?></div>
      <div class="outlet-copy">
        <h2><?= e(t('about.story_title')) ?></h2>
        <p class="lead"><?= e(t('about.story')) ?></p>
        <h3><?= e(t('about.mission_title')) ?></h3>
        <p><?= e(t('about.mission')) ?></p>
      </div>
    </div>
  </div>
  <section class="section section--flush" aria-labelledby="h-our-hotels">
    <div class="container">
      <header class="section-head"><h2 id="h-our-hotels"><?= e(t('about.hotels_title')) ?></h2></header>
      <div class="hotel-cards">
        <?php foreach (hotels() as $h): ?><?php include FRONT . '/includes/partials/hotel-card.php'; ?><?php endforeach; ?>
      </div>
      <p class="endorse-row"><?= img('brand/graha-pena', 'Graha Pena Jawa Pos', '64px', ['class' => 'endorse-logo-lg']) ?><span><?= e(t('footer.part_of')) ?></span></p>
    </div>
  </section>
</div>
<?php require FRONT . '/includes/footer.php'; ?>
