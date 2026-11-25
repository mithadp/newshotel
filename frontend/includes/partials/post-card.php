<?php
// Butuh: $p. Opsional: $lead (versi besar dengan ringkasan).
$lead = $lead ?? false;
$grid = $grid ?? false;   // true = kartu di grid 3 kolom
$postUrl = url('blog/' . $p['slug']);
?>
<article class="post-card<?= $lead ? ' post-card--lead' : '' ?>">
  <a class="media" href="<?= e($postUrl) ?>" tabindex="-1" aria-hidden="true">
    <?= img($p['cover'], tr($p, 'title'), ($lead && !$grid) ? '(min-width: 1000px) 58vw, 100vw' : '(min-width: 1000px) 30vw, (min-width: 640px) 45vw, 100vw') ?>
  </a>
  <div class="post-card-body">
    <h3><a href="<?= e($postUrl) ?>"><?= e(tr($p, 'title')) ?></a></h3>
    <p class="meta-line"><span class="post-cat"><?= e(t('blog.cat.' . $p['category'])) ?></span><time class="sep" datetime="<?= e(substr((string) $p['published_at'], 0, 10)) ?>"><?= e(fdate($p['published_at'])) ?></time><span class="sep"><?= e($p['hotel_name'] ?: t('blog.group')) ?></span></p>
    <?php if ($lead && tr($p, 'excerpt') !== ''): ?><p class="post-excerpt"><?= e(tr($p, 'excerpt')) ?></p><?php endif; ?>
  </div>
</article>
