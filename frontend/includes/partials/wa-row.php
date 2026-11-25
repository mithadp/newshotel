<?php
// Satu baris hotel di daftar kontak. Butuh: $h (hotel), $a (hasil contact_action($h)).
$rowHref  = $a['kind'] === 'dir' ? url($h['slug']) : $a['href'];
$rowLabel = $a['kind'] === 'dir' ? t('hotel.view') : $a['label'];
$rowIcon  = $a['kind'] === 'dir' ? 'arrow-right' : $a['icon'];
?>
<a class="wa-row" href="<?= e($rowHref) ?>"<?= $a['kind'] === 'wa' ? ' target="_blank" rel="noopener"' : '' ?> data-cta="<?= e($a['kind']) ?>">
  <span class="wa-row-text"><strong><?= e($h['name']) ?></strong><small><?= e($h['city']) ?> · <?= e($rowLabel) ?></small></span>
  <?= icon($rowIcon) ?>
</a>
