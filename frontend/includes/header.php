<?php
/**
 * Halaman mengisi $page sebelum memuat file ini:
 *   title        judul halaman (kosong di beranda)
 *   description  meta description
 *   hotel        array hotel jika halaman milik satu hotel (mengatur tema & tombol WA)
 *   hero         true jika halaman diawali foto hero (header transparan di atasnya)
 *   og_image     kunci gambar untuk pratinjau tautan
 *   preload      <link rel=preload> gambar hero (dari preload_img())
 *   json_ld      array schema.org
 *   og_type, robots, is_home
 */
$page = ($page ?? []) + [
    'title' => '', 'description' => tw('meta.home_description'), 'hotel' => null, 'hero' => false,
    'og_image' => 'surabaya/lobby', 'preload' => '', 'json_ld' => null, 'og_type' => 'website',
    'robots' => null, 'is_home' => false, 'nav' => null,
];
$cur      = $page['hotel'];
$brand    = $cur['brand'] ?? 'news';
$isDarmo  = $brand === 'darmo';
$siteName = (string) setting('site_name');
// Judul yang sudah memuat nama situs (mis. "News Hotel Surabaya") tidak diulang dengan akhiran "— News Hotel".
$fullTitle = $page['is_home'] ? $siteName . ' — ' . t('meta.home_title')
    : ($page['title'] === '' ? $siteName : (stripos($page['title'], $siteName) !== false ? $page['title'] : $page['title'] . ' — ' . $siteName));
$noindex   = $page['robots'] && stripos((string) $page['robots'], 'noindex') !== false;
$ogImage   = img_size($page['og_image'], 1440);
$logoLight = $isDarmo ? 'brand/darmo-inn-light' : 'brand/news-hotel-white';
$homeUrl   = $cur ? url($cur['slug']) : url();
$current   = page_path();
$isCurrent = static fn (string $p) => $current === $p || str_starts_with($current, $p . '/');
// Menu Promo hanya ada bila ada artikel promo (jangan menautkan halaman kosong).
$navLinks  = [['blog', t('nav.blog')]];
if (has_promo()) {
    $navLinks[] = ['blog', t('nav.promo'), ['kategori' => 'promo']];
}
$navLinks[] = ['tentang', t('nav.about')];
$navLinks[] = ['kontak', t('nav.contact')];
$onPromo   = $isCurrent('blog') && ($_GET['kategori'] ?? '') === 'promo';
$isCurrentLink = static fn (string $p, array $q) => $isCurrent($p) && ($p !== 'blog' || !has_promo() || ($q ? $onPromo : !$onPromo));
// Di halaman Kamar/Galeri, tab hotel lain tetap di sub-halaman yang sama.
$subPage   = ($cur && preg_match('#^' . preg_quote($cur['slug'], '#') . '/(kamar|galeri)$#', $current, $sm)) ? '/' . $sm[1] : '';

header('Content-Type: text/html; charset=utf-8');
?>
<!doctype html>
<html lang="<?= e(LANG) ?>" data-brand="<?= e($brand) ?>" class="no-js">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($fullTitle) ?></title>
<meta name="description" content="<?= e($page['description']) ?>">
<?php if ($page['robots']): ?><meta name="robots" content="<?= e($page['robots']) ?>">
<?php endif; ?>
<?php if (!$noindex): ?><link rel="canonical" href="<?= e(lang_url(LANG, true)) ?>">
<link rel="alternate" hreflang="id" href="<?= e(lang_url('id', true)) ?>">
<link rel="alternate" hreflang="en" href="<?= e(lang_url('en', true)) ?>">
<link rel="alternate" hreflang="x-default" href="<?= e(lang_url('id', true)) ?>">
<meta property="og:url" content="<?= e(lang_url(LANG, true)) ?>">
<?php endif; ?>
<meta property="og:site_name" content="<?= e($siteName) ?>">
<meta property="og:title" content="<?= e($page['is_home'] ? $siteName . ' — ' . t('meta.home_title') : ($page['title'] !== '' ? $page['title'] : $siteName)) ?>">
<meta property="og:description" content="<?= e($page['description']) ?>">
<meta property="og:type" content="<?= e($page['og_type']) ?>">
<meta property="og:image" content="<?= e(img_url($page['og_image'], 1440)) ?>">
<?php if ($ogImage): ?><meta property="og:image:width" content="<?= (int) $ogImage[0] ?>">
<meta property="og:image:height" content="<?= (int) $ogImage[1] ?>">
<?php endif; ?><meta property="og:image:alt" content="<?= e($page['title'] !== '' ? $page['title'] : $siteName) ?>">
<meta name="twitter:card" content="summary_large_image">
<meta property="og:locale" content="<?= is_en() ? 'en_US' : 'id_ID' ?>">
<meta name="theme-color" content="#0E1A2B">
<link rel="icon" href="<?= e(BASE_URL . '/favicon.ico') ?>" sizes="any">
<link rel="icon" href="<?= e(asset('img/favicon-32.png')) ?>" type="image/png" sizes="32x32">
<link rel="icon" href="<?= e(asset('img/favicon-192.png')) ?>" type="image/png" sizes="192x192">
<link rel="apple-touch-icon" href="<?= e(asset('img/apple-touch-icon.png')) ?>">
<link rel="preload" href="<?= e(BASE_URL . '/assets/fonts/plus-jakarta-sans-latin.woff2') ?>" as="font" type="font/woff2" crossorigin>
<link rel="preload" href="<?= e(BASE_URL . '/assets/fonts/cormorant-garamond-latin.woff2') ?>" as="font" type="font/woff2" crossorigin>
<?= $page['preload'] ?>
<link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
<script>document.documentElement.classList.replace('no-js','js')</script>
<?php if ($page['json_ld']): foreach ((isset($page['json_ld'][0]) ? $page['json_ld'] : [$page['json_ld']]) as $ld): ?><script type="application/ld+json"><?= json_encode($ld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) ?></script>
<?php endforeach; endif; ?>
</head>
<body class="<?= $page['hero'] ? 'has-hero' : 'no-hero' ?><?= $cur ? ' is-hotel' : '' ?><?= $page['is_home'] ? ' is-home' : '' ?>">
<a class="skip-link" href="#main"><?= e(t('a11y.skip')) ?></a>

<header class="site-header<?= $page['hero'] ? ' is-over-hero' : '' ?>" data-header>
  <div class="header-inner container">
    <div class="header-left">
      <button type="button" class="menu-btn" data-drawer-open aria-controls="site-drawer" aria-expanded="false"><?= icon('menu') ?><span><?= e(t('nav.menu')) ?></span></button>
      <nav class="hotel-tabs" aria-label="<?= e(t('nav.hotels')) ?>">
        <?php if ($cur): ?><a class="hotel-tab hotel-tab--all" href="<?= e(url()) ?>"><?= icon('arrow-left') ?><span><?= e(t('nav.all_hotels')) ?></span></a><?php endif; ?>
        <?php foreach (hotels() as $h): $active = $cur && $cur['id'] === $h['id']; ?>
          <a class="hotel-tab<?= $active ? ' is-active' : '' ?>" href="<?= e(url($h['slug'] . $subPage)) ?>"<?= $active ? ' aria-current="page"' : '' ?> data-brand-tab="<?= e($h['brand']) ?>">
            <span><?= e($h['short_name']) ?></span><?php if ($h['status'] === 'soft_opening'): ?><span class="tab-badge"><?= e(t('hotel.badge_new')) ?></span><?php endif; ?>
          </a>
        <?php endforeach; ?>
      </nav>
    </div>

    <a class="brand<?= $isDarmo ? ' brand--darmo' : '' ?>" href="<?= e($homeUrl) ?>" aria-label="<?= e(($cur['name'] ?? $siteName) . ' — ' . t('nav.home')) ?>">
      <?= img($logoLight, '', $isDarmo ? '120px' : '96px', ['class' => 'brand-logo', 'loading' => 'eager', 'fetchpriority' => 'high', 'aria-hidden' => 'true']) ?>
    </a>

    <div class="header-right">
      <nav class="main-nav" aria-label="<?= e(t('nav.main')) ?>">
        <?php foreach ($navLinks as $nl): [$path, $label] = $nl; $q = $nl[2] ?? []; ?>
          <a href="<?= e(url($path, $q)) ?>"<?= $isCurrentLink($path, $q) ? ' aria-current="page"' : '' ?>><?= e($label) ?></a>
        <?php endforeach; ?>
      </nav>
      <div class="lang-switch" role="group" aria-label="<?= e(t('nav.language')) ?>">
        <a href="<?= e(lang_url('id')) ?>" hreflang="id" lang="id"<?= LANG === 'id' ? ' aria-current="true"' : '' ?>>ID</a>
        <a href="<?= e(lang_url('en')) ?>" hreflang="en" lang="en"<?= LANG === 'en' ? ' aria-current="true"' : '' ?>>EN</a>
      </div>
      <?php if (!$cur): $pickId = 'pick-header'; $pickClass = 'wa-pick--header'; $pickBtnClass = 'btn btn-header'; include FRONT . '/includes/partials/wa-picker.php'; unset($pickBtnClass); ?>
      <?php elseif (has_contact($cur)): ?><?= wa_button($cur, 'stay', '', 'btn btn-header', t('wa.chat')) ?>
      <?php endif; ?>
    </div>
  </div>

  <nav class="hotel-tabs-mobile" aria-label="<?= e(t('nav.hotels')) ?>">
    <?php foreach (hotels() as $h): $active = $cur && $cur['id'] === $h['id']; ?>
      <a class="<?= $active ? 'is-active' : '' ?>" href="<?= e(url($h['slug'] . $subPage)) ?>"<?= $active ? ' aria-current="page"' : '' ?>><?= e($h['short_name']) ?></a>
    <?php endforeach; ?>
  </nav>
</header>

<dialog class="drawer" id="site-drawer" aria-label="<?= e(t('nav.menu')) ?>">
  <div class="drawer-head">
    <?= img('brand/news-hotel-white', $siteName, '120px', ['class' => 'drawer-logo']) ?>
    <button type="button" class="icon-btn" data-drawer-close aria-label="<?= e(t('a11y.close')) ?>"><?= icon('x') ?></button>
  </div>
  <p class="drawer-label"><?= e(t('nav.hotels')) ?></p>
  <ul class="drawer-hotels">
    <?php foreach (hotels() as $h): ?>
      <li><a href="<?= e(url($h['slug'])) ?>"<?= $cur && $cur['id'] === $h['id'] ? ' aria-current="page"' : '' ?>><span><?= e($h['name']) ?></span><small><?= e($h['city']) ?></small></a></li>
    <?php endforeach; ?>
  </ul>
  <ul class="drawer-links">
    <li><a href="<?= e(url()) ?>"><?= e(t('nav.home')) ?></a></li>
    <?php foreach ($navLinks as $nl): [$path, $label] = $nl; $q = $nl[2] ?? []; ?>
      <li><a href="<?= e(url($path, $q)) ?>"><?= e($label) ?></a></li>
    <?php endforeach; ?>
  </ul>
  <div class="drawer-foot">
    <?php if ($cur): ?>
      <?= wa_button($cur, 'stay', '', 'btn btn-primary btn-block', t('wa.reserve')) ?>
    <?php else: ?>
      <p class="drawer-label"><?= e(any_whatsapp() ? t('wa.pick_label') : t('wa.contact')) ?></p>
      <ul class="wa-list">
        <?php foreach (hotels_by_contact() as $h): $a = contact_action($h); ?>
          <li data-brand="<?= e($h['brand']) ?>"><?php include FRONT . '/includes/partials/wa-row.php'; ?></li>
        <?php endforeach; ?>
      </ul>
      <?php if (count(array_filter(hotels(), 'has_contact')) < count(hotels())): ?><p class="wa-pick-note"><?= e(t('wa.soon_note')) ?></p><?php endif; ?>
    <?php endif; ?>
    <div class="drawer-row">
      <span class="lang-switch">
        <a href="<?= e(lang_url('id')) ?>" hreflang="id" lang="id"<?= LANG === 'id' ? ' aria-current="true"' : '' ?>>ID</a>
        <a href="<?= e(lang_url('en')) ?>" hreflang="en" lang="en"<?= LANG === 'en' ? ' aria-current="true"' : '' ?>>EN</a>
      </span>
    </div>
  </div>
</dialog>

<main id="main" tabindex="-1">
