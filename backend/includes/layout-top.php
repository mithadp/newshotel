<?php
/**
 * Halaman mengisi sebelum memuat file ini:
 *   $title    judul halaman
 *   $nav      kunci menu aktif (dasbor, hotel, kamar, galeri, blog, fasilitas, sekitar, dining, meeting, pengaturan, akun)
 *   $actions  HTML tombol di kanan atas (opsional)
 */
$me      = current_admin();
$title   = $title ?? 'Admin';
$nav     = $nav ?? '';
$actions = $actions ?? '';
$scopeId = admin_hotel_scope();
$scopeHotel = $scopeId ? db_one('SELECT name FROM hotels WHERE id = ?', [$scopeId]) : null;
$menu = [
    'Utama' => [
        ['dasbor', '', 'layout-dashboard', 'Dasbor'],
        ['hotel', 'hotel', 'hotel', $scopeId ? 'Profil hotel' : 'Profil hotel'],
    ],
    'Konten' => [
        ['kamar', 'kamar', 'bed', 'Kamar'],
        ['galeri', 'galeri', 'image', 'Galeri'],
        ['blog', 'blog', 'newspaper', 'Blog'],
        ['fasilitas', 'konten/fasilitas', 'check', 'Fasilitas'],
        ['sekitar', 'konten/sekitar', 'map-pin', 'Tempat sekitar'],
        ['dining', 'konten/dining', 'utensils', 'Dining'],
        ['meeting', 'konten/meeting', 'presentation', 'Ruang meeting'],
    ],
];
if (is_super()) {
    $menu['Sistem'] = [
        ['pengaturan', 'pengaturan', 'settings', 'Pengaturan'],
    ];
}
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?= e($title) ?> · Admin <?= e(setting('site_name', 'News Hotel')) ?></title>
<link rel="icon" href="<?= e(asset('img/favicon-32.png')) ?>" type="image/png" sizes="32x32">
<style>@font-face { font-family: "Schibsted Grotesk"; font-style: normal; font-weight: 400 700; font-display: swap; src: url("<?= e(BASE_URL) ?>/assets/fonts/schibsted-grotesk-400-700.woff2") format("woff2"); }</style>
<link rel="stylesheet" href="<?= e(BASE_URL . '/assets/admin.css?v=' . filemtime(BACK . '/assets/admin.css')) ?>">
</head>
<body class="admin">
<a class="skip-link" href="#main">Langsung ke konten</a>
<div class="shell">
  <aside class="sidebar" id="sidebar">
    <a class="sb-brand" href="<?= e(admin_url()) ?>">
      <?= img('brand/news-hotel', 'News Hotel', '96px', ['class' => 'sb-logo', 'loading' => 'eager']) ?>
      <span>Admin</span>
    </a>
    <div class="sb-scope">
      <span class="sb-scope-label">Akses</span>
      <strong><?= e($scopeHotel['name'] ?? 'Semua hotel') ?></strong>
      <small><?= e(($me['role'] ?? '') === 'super_admin' ? 'Admin' : 'Admin hotel') ?></small>
    </div>
    <nav class="sb-nav" aria-label="Menu admin">
      <?php foreach ($menu as $menuGroup => $menuItems): ?>
        <p class="sb-group"><?= e($menuGroup) ?></p>
        <ul>
          <?php foreach ($menuItems as [$menuKey, $menuPath, $menuIcon, $menuLabel]): ?>
            <li><a href="<?= e(admin_url($menuPath)) ?>"<?= $nav === $menuKey ? ' aria-current="page"' : '' ?>><?= icon($menuIcon) ?><span><?= e($menuLabel) ?></span></a></li>
          <?php endforeach; ?>
        </ul>
      <?php endforeach; ?>
    </nav>
    <div class="sb-foot">
      <a href="<?= e(url()) ?>" target="_blank" rel="noopener"><?= icon('external-link') ?><span>Lihat situs</span></a>
      <a href="<?= e(admin_url('akun')) ?>"<?= $nav === 'akun' ? ' aria-current="page"' : '' ?>><?= icon('user') ?><span><?= e($me['name'] ?? '') ?></span></a>
      <form method="post" action="<?= e(admin_url('logout')) ?>"><?= csrf_field() ?><button type="submit"><?= icon('log-out') ?><span>Keluar</span></button></form>
    </div>
  </aside>
  <div class="main-col">
    <header class="topbar">
      <button type="button" class="icon-btn sb-toggle" data-sb-toggle aria-controls="sidebar" aria-expanded="false" aria-label="Menu"><?= icon('menu') ?></button>
      <h1 class="page-title"><?= e($title) ?></h1>
      <div class="topbar-actions"><?= $actions ?></div>
    </header>
    <main id="main" class="content" tabindex="-1">
      <?php foreach (take_flashes() as [$type, $msg]): ?>
        <p class="flash flash--<?= e($type) ?>" role="status"><?= icon(['success' => 'circle-check', 'error' => 'alert', 'info' => 'info'][$type] ?? 'info') ?><span><?= e($msg) ?></span></p>
      <?php endforeach; ?>
