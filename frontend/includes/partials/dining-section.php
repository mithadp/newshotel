<?php
// Butuh: $dining, $hotel. Opsional: $diningIntro, $sectionClass.
$single = count($dining) === 1;
?>
<?php $secClass = $sectionClass ?? ''; unset($sectionClass); ?>
<section class="section<?= $secClass !== '' ? ' ' . e($secClass) : '' ?>" id="dining" aria-labelledby="h-dining">
  <div class="container">
    <header class="section-head">
      <h2 id="h-dining"><?= e(t('hotel.dining')) ?></h2>
      <?php if (!empty($diningIntro)): ?><p><?= e($diningIntro) ?></p><?php endif; ?>
    </header>
    <div class="<?= $single ? 'outlet-list' : 'outlet-grid' ?>">
      <?php foreach ($dining as $d): ?>
        <article class="<?= $single ? 'split outlet' : 'outlet-card' ?>">
          <div class="media media--wide"><?= img($d['image'], $d['name'], $single ? '(min-width: 1000px) 50vw, 100vw' : '(min-width: 1000px) 45vw, 100vw') ?></div>
          <div class="outlet-copy">
            <h3><?= e($d['name']) ?></h3>
            <p class="meta-line"><span><?= e(tr($d, 'type')) ?></span><?php if ($d['hours']): ?><span class="sep"><?= e(tr($d, 'hours')) ?></span><?php endif; ?></p>
            <p class="outlet-text"><?= e(tr($d, 'description')) ?></p>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
