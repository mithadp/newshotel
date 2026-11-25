<?php
// Film singkat grup (video 16:9, tanpa suara); app.js memasang sumbernya saat bagian ini mendekati layar.
// Disembunyikan bila berkas video belum ada.
$filmDir = FRONT . '/assets/video/';
if (!is_file($filmDir . 'newshotel-film.mp4')) {
    return;
}
$filmLabel = tx(
    'Film singkat: tiga hotel News Hotel, pilih hotel, jelajahi kamar, lalu hubungi hotel.',
    'Short film: the three News Hotel hotels. Choose a hotel, explore the rooms, then contact the hotel.'
);
?>
<section class="section section--deep film" aria-labelledby="h-film">
  <div class="container">
    <h2 id="h-film" class="sr-only"><?= e(tx('Film singkat News Hotel', 'News Hotel short film')) ?></h2>
    <div class="film-frame" data-film
         data-src-webm="<?= is_file($filmDir . 'newshotel-film.webm') ? e(asset('video/newshotel-film.webm')) : '' ?>"
         data-src-mp4="<?= e(asset('video/newshotel-film.mp4')) ?>">
      <video muted loop playsinline preload="none" width="1280" height="720"
             poster="<?= e(asset('video/newshotel-film-poster.webp')) ?>" aria-label="<?= e($filmLabel) ?>"></video>
    </div>
  </div>
</section>
