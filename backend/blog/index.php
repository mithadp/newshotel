<?php
require_once __DIR__ . '/../includes/init.php';
require_login();

$cats = ['news' => 'Berita', 'event' => 'Acara', 'promo' => 'Promo'];
$status = in_array($_GET['status'] ?? '', ['draft', 'published'], true) ? $_GET['status'] : null;
$cat = array_key_exists($_GET['kategori'] ?? '', $cats) ? $_GET['kategori'] : null;
$scope = admin_hotel_scope();

$where = ['1 = 1'];
$params = [];
if ($scope !== null) {
    $where[] = 'p.hotel_id = ?';          // admin hotel hanya melihat artikel hotelnya
    $params[] = $scope;
} elseif (($hid = (int) ($_GET['hotel'] ?? 0)) > 0) {
    $where[] = 'p.hotel_id = ?';
    $params[] = $hid;
}
if ($status) {
    $where[] = 'p.status = ?';
    $params[] = $status;
}
if ($cat) {
    $where[] = 'p.category = ?';
    $params[] = $cat;
}
$posts = db_all(
    'SELECT p.id, p.slug, p.title, p.category, p.status, p.published_at, p.updated_at, h.short_name, h.brand
     FROM posts p LEFT JOIN hotels h ON h.id = p.hotel_id
     WHERE ' . implode(' AND ', $where) . ' ORDER BY COALESCE(p.published_at, p.updated_at) DESC, p.id DESC',
    $params
);

$title = 'Blog';
$nav = 'blog';
$actions = '<a class="btn btn-primary" href="' . e(admin_url('blog/form')) . '">' . icon('plus') . 'Tulis artikel</a>';
require __DIR__ . '/../includes/layout-top.php';
$q = static fn (array $change) => admin_url('blog', array_merge(['status' => $status, 'kategori' => $cat, 'hotel' => $_GET['hotel'] ?? null], $change));
?>
<div class="filters-bar">
  <nav class="quick-filters" aria-label="Status">
    <a href="<?= e($q(['status' => null])) ?>"<?= $status === null ? ' aria-current="page"' : '' ?>>Semua</a>
    <a href="<?= e($q(['status' => 'published'])) ?>"<?= $status === 'published' ? ' aria-current="page"' : '' ?>>Terbit</a>
    <a href="<?= e($q(['status' => 'draft'])) ?>"<?= $status === 'draft' ? ' aria-current="page"' : '' ?>>Draf</a>
  </nav>
  <nav class="quick-filters" aria-label="Kategori">
    <a href="<?= e($q(['kategori' => null])) ?>"<?= $cat === null ? ' aria-current="page"' : '' ?>>Semua kategori</a>
    <?php foreach ($cats as $k => $label): ?><a href="<?= e($q(['kategori' => $k])) ?>"<?= $cat === $k ? ' aria-current="page"' : '' ?>><?= e($label) ?></a><?php endforeach; ?>
  </nav>
</div>

<section class="card card--flush">
  <div class="table-scroll">
    <table class="table table--rows">
      <thead><tr><th>Judul</th><th>Kategori</th><th>Hotel</th><th>Status</th><th>Tanggal terbit</th><th class="row-actions"><span class="sr-only">Aksi</span></th></tr></thead>
      <tbody>
        <?php foreach ($posts as $p): $scheduled = $p['status'] === 'published' && $p['published_at'] && strtotime($p['published_at']) > time(); ?>
          <tr>
            <td><a class="row-title" href="<?= e(admin_url('blog/form', ['id' => $p['id']])) ?>"><?= e($p['title']) ?></a></td>
            <td><?= e($cats[$p['category']] ?? $p['category']) ?></td>
            <td><?php if ($p['short_name']): ?><span class="dot" data-brand="<?= e($p['brand']) ?>"></span><?= e($p['short_name']) ?><?php else: ?><span class="muted">Grup</span><?php endif; ?></td>
            <td><span class="chip chip--<?= $scheduled ? 'draft' : e($p['status']) ?>"><?= $scheduled ? 'Terjadwal' : ($p['status'] === 'published' ? 'Terbit' : 'Draf') ?></span></td>
            <td class="nowrap"><?= $p['published_at'] ? e(date('d-m-Y H:i', strtotime($p['published_at']))) : '—' ?></td>
            <td class="row-actions nowrap">
              <?php if ($p['status'] === 'published' && !$scheduled): ?><a class="btn btn-ghost btn-sm" href="<?= e(url('blog/' . $p['slug'])) ?>" target="_blank" rel="noopener" aria-label="Lihat di situs"><?= icon('external-link') ?></a><?php endif; ?>
              <a class="btn btn-ghost btn-sm" href="<?= e(admin_url('blog/form', ['id' => $p['id']])) ?>"><?= icon('pencil') ?>Ubah</a>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$posts): ?><tr><td colspan="6" class="empty-row">Belum ada artikel untuk pilihan ini.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</section>
<?php require __DIR__ . '/../includes/layout-bottom.php'; ?>
