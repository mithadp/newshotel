<?php
// Foto pembuka halaman hotel (paralaks), alt dari keterangan galeri. Butuh: $introKey, $hotel.
$introRow = db_one('SELECT * FROM gallery_images WHERE image = ? LIMIT 1', [$introKey]);
$introAlt = $introRow ? tr($introRow, 'caption') : '';
if ($introAlt === '') {
    $introAlt = $hotel['name'];
}
?>
<figure class="overview-media parallax" data-parallax="0.08">
  <?= img($introKey, $introAlt, '(min-width: 1000px) 44vw, 92vw') ?>
</figure>
