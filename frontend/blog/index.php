<?php
// Filter: ?kategori=news|event|promo, ?hotel=<slug>, halaman ?hal=2.
require_once __DIR__ . '/../includes/init.php';

$perPage    = 9;
$categories = ['news', 'event', 'promo'];
$category   = in_array($_GET['kategori'] ?? '', $categories, true) ? $_GET['kategori'] : null;

$hotelFilter = find_hotel(is_string($_GET['hotel'] ?? null) ? $_GET['hotel'] : null);
$hotelId = $hotelFilter['id'] ?? null;

$total = post_count($hotelId, $category, false);
$pages = max(1, (int) ceil($total / $perPage));
$pageNo = min($pages, max(1, (int) ($_GET['hal'] ?? 1)));
$list  = posts($perPage, ($pageNo - 1) * $perPage, $hotelId, $category, false);

// Tautan filter mempertahankan filter lain, dan kembali ke halaman 1.
$filterUrl = static fn (array $change) => url('blog', array_merge(['kategori' => $category, 'hotel' => $hotelFilter['slug'] ?? null], $change));

$titleBits = array_filter([$category ? t('blog.cat.' . $category) : null, $hotelFilter['name'] ?? null]);
$page = [
    'title'       => $titleBits ? t('blog.title') . ': ' . implode(' · ', $titleBits) : t('blog.title'),
    'description' => t('meta.blog_description'),
    'og_image'    => $list[0]['cover'] ?? 'surabaya/lobby',
    'robots'      => $pageNo > 1 ? 'noindex, follow' : null,
];

require FRONT . '/includes/header.php';
$heading = t('blog.heading');
$sub     = t('blog.sub');
$crumbs  = [[t('nav.home'), url()], [t('nav.blog'), null]];
include FRONT . '/includes/partials/page-hero.php';
?>
<div class="page-body">
  <div class="container">
    <div class="blog-filters">
      <div class="chips" role="group" aria-label="<?= e(t('blog.filter_cat')) ?>">
        <a class="chip" href="<?= e($filterUrl(['kategori' => null])) ?>"<?= $category === null ? ' aria-current="true"' : '' ?>><?= e(t('blog.all')) ?></a>
        <?php foreach ($categories as $c): ?>
          <a class="chip" href="<?= e($filterUrl(['kategori' => $c])) ?>"<?= $category === $c ? ' aria-current="true"' : '' ?>><?= e(t('blog.cat.' . $c)) ?></a>
        <?php endforeach; ?>
      </div>
      <div class="chips chips--quiet" role="group" aria-label="<?= e(t('blog.filter_hotel')) ?>">
        <a class="chip" href="<?= e($filterUrl(['hotel' => null])) ?>"<?= $hotelFilter === null ? ' aria-current="true"' : '' ?>><?= e(t('blog.all_hotels')) ?></a>
        <?php foreach (hotels() as $h): ?>
          <a class="chip" href="<?= e($filterUrl(['hotel' => $h['slug']])) ?>"<?= ($hotelFilter['slug'] ?? '') === $h['slug'] ? ' aria-current="true"' : '' ?>><?= e($h['short_name']) ?></a>
        <?php endforeach; ?>
      </div>
    </div>

    <?php if ($list): ?>
      <h2 class="sr-only"><?= e(t('blog.title')) ?></h2>
      <div class="post-grid">
        <?php foreach ($list as $i => $p): $lead = true; $grid = true; ?><?php include FRONT . '/includes/partials/post-card.php'; ?><?php endforeach; ?>
      </div>
      <?php if ($pages > 1): ?>
        <nav class="pager" aria-label="<?= e(t('blog.title')) ?>">
          <?php for ($i = 1; $i <= $pages; $i++): ?>
            <?php if ($i === $pageNo): ?><span aria-current="page"><?= $i ?></span><?php else: ?><a href="<?= e(url('blog', ['kategori' => $category, 'hotel' => $hotelFilter['slug'] ?? null, 'hal' => $i > 1 ? $i : null])) ?>" aria-label="<?= e(t('blog.page', ['n' => $i])) ?>"><?= $i ?></a><?php endif; ?>
          <?php endfor; ?>
        </nav>
      <?php endif; ?>
    <?php else: ?>
      <div class="empty"><?= icon('newspaper') ?><p><?= e(t('blog.empty')) ?></p><a class="btn btn-ghost" href="<?= e(url('blog')) ?>"><?= e(t('blog.back')) ?></a></div>
    <?php endif; ?>
  </div>
</div>
<?php require FRONT . '/includes/footer.php'; ?>
