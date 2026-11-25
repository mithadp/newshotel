<?php
// Judul halaman dalam. Butuh: $heading. Opsional: $sub, $crumbs [[label, url|null], ...], $heroImage (kunci gambar di belakang judul).
$heroImage = $heroImage ?? null;
?>
<header class="page-hero<?= $heroImage ? ' page-hero--photo' : '' ?>">
  <?php if ($heroImage): ?><figure class="page-hero-media" aria-hidden="true"><?= img($heroImage, '', '100vw', ['loading' => 'eager', 'fetchpriority' => 'high']) ?></figure><?php endif; ?>
  <div class="container">
    <?php if (!empty($crumbs)): ?>
      <nav aria-label="<?= e(t('nav.breadcrumb')) ?>">
      <ol class="crumbs">
        <?php foreach ($crumbs as [$label, $href]): ?>
          <li><?php if ($href): ?><a href="<?= e($href) ?>"><?= e($label) ?></a><?php else: ?><span aria-current="page"><?= e($label) ?></span><?php endif; ?></li>
        <?php endforeach; ?>
      </ol>
      </nav>
    <?php endif; ?>
    <h1><?= e($heading) ?></h1>
    <?php if (!empty($sub)): ?><p><?= e($sub) ?></p><?php endif; ?>
  </div>
</header>
