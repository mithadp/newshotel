<?php
require_once __DIR__ . '/../includes/init.php';
require_login();

$hotelId = selected_hotel_id();
$sql = 'SELECT r.*, h.short_name, h.brand, (SELECT COUNT(*) FROM room_images i WHERE i.room_type_id = r.id) AS photos
        FROM room_types r JOIN hotels h ON h.id = r.hotel_id';
$params = [];
if ($hotelId !== null) {
    $sql .= ' WHERE r.hotel_id = ?';
    $params[] = $hotelId;
}
$rooms = db_all($sql . ' ORDER BY h.sort, r.sort, r.id', $params);

$title = 'Kamar';
$nav = 'kamar';
$actions = '<a class="btn btn-primary" href="' . e(admin_url('kamar/form', ['hotel' => $hotelId])) . '">' . icon('plus') . 'Tambah kamar</a>';
require __DIR__ . '/../includes/layout-top.php';
?>
<?php if (is_super()): ?>
  <nav class="tabs" aria-label="Saring hotel">
    <a href="<?= e(admin_url('kamar')) ?>"<?= $hotelId === null ? ' aria-current="page"' : '' ?>>Semua hotel</a>
    <?php foreach (admin_hotels() as $h): ?><a href="<?= e(admin_url('kamar', ['hotel' => $h['id']])) ?>"<?= $hotelId === (int) $h['id'] ? ' aria-current="page"' : '' ?>><?= e($h['short_name']) ?></a><?php endforeach; ?>
  </nav>
<?php endif; ?>

<section class="card card--flush">
  <div class="table-scroll">
    <table class="table table--rows">
      <thead><tr><th>Kamar</th><th>Hotel</th><th class="num">Tarif mulai</th><th>Luas</th><th>Foto</th><th>Status</th><th class="row-actions"><span class="sr-only">Aksi</span></th></tr></thead>
      <tbody>
        <?php foreach ($rooms as $r): ?>
          <tr>
            <td><a class="row-title" href="<?= e(admin_url('kamar/form', ['id' => $r['id']])) ?>"><?= e($r['name']) ?></a></td>
            <td><span class="dot" data-brand="<?= e($r['brand']) ?>"></span><?= e($r['short_name']) ?></td>
            <td class="num"><?= $r['base_price'] !== null ? e(money($r['base_price'])) : '<span class="muted">—</span>' ?></td>
            <td><?= $r['size_m2'] ? e(size_text($r['size_m2'])) : '<span class="muted">—</span>' ?></td>
            <td><?= (int) $r['photos'] + ($r['cover_image'] ? 1 : 0) ?></td>
            <td><span class="chip chip--<?= (int) $r['is_active'] === 1 ? 'published' : 'draft' ?>"><?= (int) $r['is_active'] === 1 ? 'Tampil' : 'Disembunyikan' ?></span></td>
            <td class="row-actions"><a class="btn btn-ghost btn-sm" href="<?= e(admin_url('kamar/form', ['id' => $r['id']])) ?>"><?= icon('pencil') ?>Ubah</a></td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$rooms): ?><tr><td colspan="7" class="empty-row">Belum ada tipe kamar.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</section>
<?php require __DIR__ . '/../includes/layout-bottom.php'; ?>
