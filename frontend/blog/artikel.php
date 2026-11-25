<?php
require_once __DIR__ . '/../includes/init.php';

$slug = (string) ($_GET['slug'] ?? '');
$post = preg_match('/^[a-z0-9-]+$/', $slug) ? db_one(
    "SELECT p.*, h.name AS hotel_name, h.slug AS hotel_slug FROM posts p LEFT JOIN hotels h ON h.id = p.hotel_id
     WHERE p.slug = ? AND p.status = 'published' AND p.published_at <= NOW()",
    [$slug]
) : null;
if (!$post) {
    not_found();
}
page_path('blog/' . $post['slug']);

$title     = tr($post, 'title');
$postHotel = find_hotel($post['hotel_slug']);
// "Artikel lainnya": yang sehotel dan sekategori lebih dulu, lalu yang terbaru.
$more      = array_values(array_filter(posts(12), static fn ($p) => $p['id'] !== $post['id']));
usort($more, static fn ($a, $b) => (((int) ($b['hotel_id'] === $post['hotel_id'] && $post['hotel_id'] !== null)) * 2 + (int) ($b['category'] === $post['category']))
    <=> (((int) ($a['hotel_id'] === $post['hotel_id'] && $post['hotel_id'] !== null)) * 2 + (int) ($a['category'] === $post['category'])));
$more      = array_slice($more, 0, 3);

$page = [
    'title'       => $title,
    'description' => tr($post, 'excerpt') ?: excerpt(tr($post, 'body'), 155),
    'og_image'    => $post['cover'],
    'og_type'     => 'article',
    'json_ld'     => [[
        '@context' => 'https://schema.org', '@type' => 'BlogPosting', 'headline' => $title,
        'datePublished' => date('c', strtotime((string) $post['published_at'])),
        'dateModified' => date('c', strtotime((string) ($post['updated_at'] ?? $post['published_at']))),
        'mainEntityOfPage' => abs_url('blog/' . $post['slug']),
        'author' => ['@type' => 'Organization', 'name' => setting('site_name')],
        'image' => img_url($post['cover']), 'url' => abs_url('blog/' . $post['slug']),
        'publisher' => ['@type' => 'Organization', 'name' => setting('site_name'), 'logo' => ['@type' => 'ImageObject', 'url' => img_url('brand/news-hotel', 640)]],
    ], breadcrumb_schema([
        [t('nav.home'), url()], [t('nav.blog'), url('blog')],
        [t('blog.cat.' . $post['category']), url('blog', ['kategori' => $post['category']])], [$title, null],
    ])],
];

require FRONT . '/includes/header.php';
?>
<article class="article">
  <header class="page-hero article-hero">
    <div class="container article-head">
      <nav aria-label="<?= e(t('nav.breadcrumb')) ?>">
      <ol class="crumbs">
        <li><a href="<?= e(url()) ?>"><?= e(t('nav.home')) ?></a></li>
        <li><a href="<?= e(url('blog')) ?>"><?= e(t('nav.blog')) ?></a></li>
        <li><a href="<?= e(url('blog', ['kategori' => $post['category']])) ?>"><?= e(t('blog.cat.' . $post['category'])) ?></a></li>
        <li><span aria-current="page"><?= e(excerpt($title, 40)) ?></span></li>
      </ol>
      </nav>
      <h1><?= e($title) ?></h1>
      <p class="meta-line article-meta">
        <time datetime="<?= e(substr((string) $post['published_at'], 0, 10)) ?>"><?= e(fdate($post['published_at'])) ?></time>
        <span class="sep"><?php if ($postHotel): ?><a href="<?= e(url($postHotel['slug'])) ?>"><?= e($postHotel['name']) ?></a><?php else: ?><?= e(t('blog.group')) ?><?php endif; ?></span>
      </p>
      <?php if (tr($post, 'excerpt') !== ''): ?><p class="article-dek"><?= e(tr($post, 'excerpt')) ?></p><?php endif; ?>
    </div>
  </header>
  <div class="container">
    <figure class="media article-cover"><?= img($post['cover'], $title, '(min-width: 1300px) 1240px, 100vw', ['loading' => 'eager', 'fetchpriority' => 'high']) ?></figure>
    <div class="prose article-body"><?= markdown(tr($post, 'body')) ?></div>
    <?php if ($post['source_url']): ?>
      <p class="source-note article-body"><?= e(t('blog.source')) ?>: <a href="<?= e($post['source_url']) ?>" rel="noopener nofollow" target="_blank"><?= e(parse_url($post['source_url'], PHP_URL_HOST)) ?></a></p>
    <?php endif; ?>

    <?php if ($postHotel): ?>
      <aside class="article-cta" data-brand="<?= e($postHotel['brand']) ?>" aria-label="<?= e($postHotel['name']) ?>">
        <div>
          <p class="article-cta-title"><?= e(t('wa.post_cta', ['hotel' => $postHotel['name']])) ?></p>
          <p class="meta-line"><?= icon('pin') ?><span><?= e($postHotel['address']) ?>, <?= e($postHotel['city']) ?></span></p>
        </div>
        <div class="btn-row">
          <a class="btn btn-ghost" href="<?= e(url($postHotel['slug'])) ?>"><?= e(t('hotel.view')) ?></a>
          <?= wa_button($postHotel, 'post', $title, 'btn btn-primary', t('wa.chat')) ?>
        </div>
      </aside>
    <?php else: ?>
      <aside class="article-cta" aria-label="<?= e(setting('site_name')) ?>">
        <div><p class="article-cta-title"><?= e(t('wa.group_cta')) ?></p></div>
        <div class="btn-row">
          <?php $pickId = 'pick-article'; $pickClass = 'wa-pick--footer'; $pickBtnClass = 'btn btn-primary'; include FRONT . '/includes/partials/wa-picker.php'; ?>
        </div>
      </aside>
    <?php endif; ?>
  </div>
</article>

<?php if ($more): ?>
<section class="section" aria-labelledby="h-more">
  <div class="container">
    <header class="section-head section-head--row">
      <h2 id="h-more"><?= e(t('blog.more')) ?></h2>
      <a class="link-arrow" href="<?= e(url('blog')) ?>"><?= e(t('blog.back')) ?><?= icon('arrow-right') ?></a>
    </header>
    <div class="post-grid">
      <?php foreach (array_slice($more, 0, 3) as $p): $lead = true; ?><?php include FRONT . '/includes/partials/post-card.php'; ?><?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>
<?php require FRONT . '/includes/footer.php'; ?>
