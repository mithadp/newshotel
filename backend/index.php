<?php
require_once __DIR__ . '/includes/init.php';
require_login();

$hotels = admin_hotels();
$ids = array_map(static fn ($h) => (int) $h['id'], $hotels);
$in = implode(',', $ids ?: [0]);   // aman: hanya angka dari database

$counts = [
    'Kamar'   => [(int) db_value("SELECT COUNT(*) FROM room_types WHERE hotel_id IN ($in)"), 'kamar'],
    'Foto galeri' => [(int) db_value("SELECT COUNT(*) FROM gallery_images WHERE hotel_id IN ($in)"), 'galeri'],
    'Artikel terbit' => [(int) db_value("SELECT COUNT(*) FROM posts WHERE status = 'published'" . (is_super() ? '' : " AND hotel_id IN ($in)")), 'blog'],
    'Draf artikel' => [(int) db_value("SELECT COUNT(*) FROM posts WHERE status = 'draft'" . (is_super() ? '' : " AND hotel_id IN ($in)")), 'blog'],
];

// Data yang belum lengkap → tautan langsung ke formnya.
$todo = [];
foreach ($hotels as $h) {
    $full = db_one('SELECT * FROM hotels WHERE id = ?', [$h['id']]);
    $items = [];
    if (!$full['whatsapp']) {
        $items[] = ['Nomor WhatsApp belum diisi (tombol reservasi belum membuka WhatsApp).', admin_url('hotel', ['id' => $h['id']]) . '#kontak', true];
    }
    if (!$full['phone']) {
        $items[] = ['Nomor telepon belum diisi.', admin_url('hotel', ['id' => $h['id']]) . '#kontak', false];
    }
    if (!$full['email']) {
        $items[] = ['Email belum diisi.', admin_url('hotel', ['id' => $h['id']]) . '#kontak', false];
    }
    foreach (db_all('SELECT id, name, base_price, size_m2 FROM room_types WHERE hotel_id = ? AND is_active = 1 ORDER BY sort, id', [$h['id']]) as $r) {
        if ($r['base_price'] === null) {
            $items[] = ['"' . $r['name'] . '": tarif mulai belum diisi.', admin_url('kamar/form', ['id' => $r['id']]), false];
        }
    }
    $todo[] = [$h, $items];
}

$recent = db_all(
    'SELECT p.id, p.title, p.status, p.updated_at, h.short_name FROM posts p LEFT JOIN hotels h ON h.id = p.hotel_id'
    . (is_super() ? '' : " WHERE p.hotel_id IN ($in)") . ' ORDER BY p.updated_at DESC LIMIT 5'
);

$title = 'Dasbor';
$nav = 'dasbor';
$actions = '<a class="btn btn-primary" href="' . e(admin_url('blog/form')) . '">' . icon('plus') . 'Tulis artikel</a>';
require __DIR__ . '/includes/layout-top.php';
?>
<div class="kpis kpis--4">
  <?php foreach ($counts as $label => [$n, $path]): ?>
    <a class="kpi" href="<?= e(admin_url($path)) ?>"><span><?= e($label) ?></span><strong><?= $n ?></strong></a>
  <?php endforeach; ?>
</div>

<div class="grid-2">
  <section class="card">
    <div class="card-head"><h2>Data yang belum lengkap</h2></div>
    <p class="small muted">Data ini tampil di situs sebagai tanda "Contoh", "Tarif segera tersedia", atau "Kontak segera tersedia" sampai dilengkapi.</p>
    <?php foreach ($todo as [$h, $items]): ?>
      <div class="todo-hotel">
        <h3><span class="dot" data-brand="<?= e($h['brand']) ?>"></span><?= e($h['name']) ?></h3>
        <?php if ($items): ?>
          <ul class="todo">
            <?php foreach ($items as [$text, $href, $important]): ?>
              <li<?= $important ? ' class="is-important"' : '' ?>><a href="<?= e($href) ?>"><?= icon($important ? 'alert' : 'circle-x') ?><span><?= e($text) ?></span></a></li>
            <?php endforeach; ?>
          </ul>
        <?php else: ?>
          <p class="small ok-line"><?= icon('circle-check') ?> Semua data utama sudah lengkap.</p>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
    <?php if (is_super() && trim((string) setting('whatsapp_group', '')) === ''): ?>
      <p class="small muted">Tip: isi nomor WhatsApp cadangan di <a href="<?= e(admin_url('pengaturan')) ?>">Pengaturan</a> agar tombol WhatsApp tetap berfungsi untuk hotel yang belum punya nomor.</p>
    <?php endif; ?>
  </section>

  <section class="card">
    <div class="card-head"><h2>Artikel terbaru</h2><a href="<?= e(admin_url('blog')) ?>">Semua artikel</a></div>
    <?php if ($recent): ?>
      <ul class="list">
        <?php foreach ($recent as $p): ?>
          <li><a href="<?= e(admin_url('blog/form', ['id' => $p['id']])) ?>"><?= e($p['title']) ?></a><span class="nowrap"><span class="chip chip--<?= e($p['status']) ?>"><?= $p['status'] === 'published' ? 'Terbit' : 'Draf' ?></span> <span class="small muted"><?= e($p['short_name'] ?? 'Grup') ?></span></span></li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <p class="muted">Belum ada artikel.</p>
    <?php endif; ?>
    <div class="card-head"><h2>Pintasan</h2></div>
    <div class="btn-row">
      <?php foreach ($hotels as $h): ?>
        <a class="btn btn-ghost btn-sm" href="<?= e(admin_url('hotel', ['id' => $h['id']])) ?>"><?= icon('hotel') ?><?= e($h['short_name']) ?></a>
      <?php endforeach; ?>
      <a class="btn btn-ghost btn-sm" href="<?= e(admin_url('galeri')) ?>"><?= icon('upload') ?>Unggah foto</a>
    </div>
  </section>
</div>
<?php require __DIR__ . '/includes/layout-bottom.php'; ?>
