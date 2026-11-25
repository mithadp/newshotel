<?php
// Sub-menu hotel (lengket di bawah header). Butuh: $hotel, $navActive ('home' | 'kamar' | 'galeri').
$has   = hotel_sections($hotel);
$home  = url($hotel['slug']);
$items = [
    'home'      => [t('nav.overview'), $home],
    'kamar'     => [t('nav.rooms'), url($hotel['slug'] . '/kamar')],
    'galeri'    => [t('nav.gallery'), url($hotel['slug'] . '/galeri')],
    'fasilitas' => [t('nav.facilities'), $home . '#fasilitas'],
    'dining'    => [t('nav.dining'), $home . '#dining'],
    'meeting'   => [t('nav.meeting'), $home . '#meeting'],
    'lokasi'    => [t('nav.location'), $home . '#lokasi'],
];
// Bagian opsional hanya ditautkan bila hotel punya isinya.
foreach (['fasilitas' => 'facilities', 'dining' => 'dining', 'meeting' => 'meeting'] as $k => $flag) {
    if (empty($has[$flag])) {
        unset($items[$k]);
    }
}
?>
<nav class="hotel-nav" aria-label="<?= e($hotel['name']) ?>" data-hotel-nav>
  <div class="container hotel-nav-inner">
    <ul>
      <?php foreach ($items as $key => [$label, $href]): ?>
        <li><a class="sub" href="<?= e($href) ?>"<?= $key === $navActive ? ' aria-current="page"' : '' ?>><?= e($label) ?></a></li>
      <?php endforeach; ?>
    </ul>
    <?php if (has_contact($hotel)): ?><?= wa_button($hotel, 'stay', '', 'btn btn-primary btn-sm', t('wa.chat')) ?><?php endif; ?>
  </div>
</nav>
