<?php
// Butuh: $facilities. Opsional: $facTitle, $sectionClass.
?>
<?php $secClass = $sectionClass ?? 'section--band'; unset($sectionClass); ?>
<section class="section<?= $secClass !== '' ? ' ' . e($secClass) : '' ?>" id="fasilitas" aria-labelledby="h-fac">
  <div class="container">
    <header class="section-head"><h2 id="h-fac"><?= e($facTitle ?? t('hotel.facilities')) ?></h2></header>
    <ul class="facility-list">
      <?php foreach ($facilities as $f): ?>
        <li><?= icon($f['icon']) ?><div><strong><?= e(tr($f, 'name')) ?></strong><?php if ($f['detail']): ?><span><?= e(tr($f, 'detail')) ?></span><?php endif; ?></div></li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>
