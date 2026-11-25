<?php
require_once __DIR__ . '/includes/init.php';

$hotels = hotels();
$posts  = posts(4);
$promos = posts(3, 0, null, 'promo');   // tampil hanya bila ada artikel promo
$venues = db_all(
    'SELECT v.*, h.slug AS hotel_slug FROM meeting_venues v JOIN hotels h ON h.id = v.hotel_id
     WHERE v.is_active = 1 AND h.is_published = 1 ORDER BY h.sort, v.sort, v.id'
);
$attractions = [];
foreach (db_all('SELECT * FROM attractions ORDER BY hotel_id, sort, id') as $a) {
    $attractions[$a['hotel_id']][] = $a;
}

$page = [
    'is_home'  => true,
    'hero'     => true,
    'og_image' => $hotels[0]['hero_image'] ?? 'surabaya/lobby',
    'preload'  => preload_img($hotels[0]['hero_image'] ?? null, '100vw', '(min-width: 861px)')
                . preload_img($hotels[0]['hero_image'] ?? null, '100vw', '(max-width: 860px)', 720),
    'json_ld'  => [
        '@context' => 'https://schema.org', '@type' => 'Organization', 'name' => setting('site_name'),
        'url' => abs_url(), 'logo' => img_url('brand/news-hotel', 640),
        'subOrganization' => array_map(static fn ($h) => ['@type' => 'Hotel', 'name' => $h['name'], 'url' => abs_url($h['slug'])], $hotels),
    ],
];

require FRONT . '/includes/header.php';
?>

<section class="hero hero--group" data-hero aria-label="<?= e(t('home.hero_label')) ?>">
  <div class="hero-media" aria-hidden="true">
    <?php foreach ($hotels as $i => $h): ?>
      <figure class="hero-slide<?= $i === 0 ? ' is-active' : '' ?>" data-slide="<?= e($h['slug']) ?>" data-brand="<?= e($h['brand']) ?>">
        <?= img_desktop($h['hero_image'], '', '100vw', ['loading' => $i === 0 ? 'eager' : 'lazy', 'fetchpriority' => $i === 0 ? 'high' : null, 'defer' => $i > 0]) ?>
      </figure>
    <?php endforeach; ?>
    <div class="hero-scrim" aria-hidden="true"></div>
  </div>
  <div class="hero-phone-bg" aria-hidden="true"><?= img_phone($hotels[0]['hero_image'] ?? null) ?></div>
  <div class="hero-stage container">
    <div class="hero-head">
      <h1><?= e(t('home.hero_title')) ?></h1>
      <p class="hero-sub"><?= e(t('home.hero_sub')) ?></p>
    </div>
    <div class="hero-cards">
      <?php include FRONT . '/includes/partials/hero-hotels.php'; ?>
    </div>
    <div class="hero-cta">
      <?php $pickId = 'pick-hero'; $pickClass = 'wa-pick--hero'; $pickBtnClass = 'btn btn-primary btn-lg'; include FRONT . '/includes/partials/wa-picker.php'; ?>
    </div>
  </div>
</section>

<?php include FRONT . '/includes/partials/marquee.php'; ?>

<?php include FRONT . '/includes/partials/film.php'; ?>

<?php
// Pita foto cerita grup: satu foto lebar penuh (sudut media Surabaya, 1840 px).
$bandRow = db_one('SELECT * FROM gallery_images WHERE image = ? LIMIT 1', ['surabaya/news-corner']);
$bandAlt = $bandRow ? tr($bandRow, 'caption') : '';
if ($bandAlt === '') {
    $bandAlt = tx('Sudut media di News Hotel Surabaya', 'The media corner at News Hotel Surabaya');
}
?>
<section class="photo-band" id="cerita" aria-labelledby="h-story">
  <figure class="photo-band-media parallax" data-parallax="0.06"><?= img('surabaya/news-corner', $bandAlt, '100vw') ?></figure>
  <div class="container">
    <div class="photo-band-copy">
      <h2 id="h-story"><?= e(t('home.story_title')) ?></h2>
      <p><?= e(t('home.statement')) ?></p>
      <a class="link-arrow" href="<?= e(url('tentang')) ?>"><?= e(t('home.statement_link')) ?><?= icon('arrow-right') ?></a>
      <small class="photo-band-credit"><?= e(tx('Foto: News Hotel Surabaya', 'Photo: News Hotel Surabaya')) ?></small>
    </div>
  </div>
</section>

<?php if ($promos): ?>
<section class="section section--band" aria-labelledby="h-promo">
  <div class="container">
    <header class="section-head section-head--row">
      <h2 id="h-promo"><?= e(t('home.promo_title')) ?></h2>
      <a class="link-arrow" href="<?= e(url('blog', ['kategori' => 'promo'])) ?>"><?= e(t('common.see_all')) ?><?= icon('arrow-right') ?></a>
    </header>
    <div class="post-grid post-grid--<?= min(3, count($promos)) ?>">
      <?php foreach ($promos as $p): $lead = false; $grid = true; ?><?php include FRONT . '/includes/partials/post-card.php'; ?><?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if ($posts): $lead = true; $grid = false; $p = $posts[0]; ?>
<section class="section section--sand" aria-labelledby="h-news">
  <div class="container">
    <header class="section-head section-head--row">
      <h2 id="h-news"><?= e(t('home.news_title')) ?></h2>
      <a class="link-arrow" href="<?= e(url('blog')) ?>"><?= e(t('common.see_all')) ?><?= icon('arrow-right') ?></a>
    </header>
    <div class="news-layout">
      <?php include FRONT . '/includes/partials/post-card.php'; ?>
      <div class="news-side">
        <?php $lead = false; foreach (array_slice($posts, 1) as $p): ?><?php include FRONT . '/includes/partials/post-card.php'; ?><?php endforeach; ?>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<?php include FRONT . '/includes/partials/hotel-map.php'; ?>

<?php if ($venues): $meetHotel = find_hotel($venues[0]['hotel_slug']); ?>
<section class="section section--band meet-band" aria-labelledby="h-meet">
  <figure class="strip-media"><?= img('surabaya/reception-wide', t('home.meet_img_alt'), '100vw') ?></figure>
  <div class="container meet-band-grid">
    <div class="meet-copy">
      <h2 id="h-meet"><?= e(t('home.meet_title')) ?></h2>
      <p class="lead"><?= e(t('home.meet_body')) ?></p>
    </div>
    <div class="btn-row">
      <?php if (has_contact($meetHotel)): ?><?= wa_button($meetHotel, 'meeting', '', 'btn btn-primary', t('wa.ask_meeting')) ?><?php endif; ?>
      <a class="btn btn-ghost" href="<?= e(url($meetHotel['slug'])) ?>#meeting"><?= e(t('home.meet_more')) ?></a>
    </div>
  </div>
</section>
<?php endif; ?>

<?php require FRONT . '/includes/footer.php'; ?>
