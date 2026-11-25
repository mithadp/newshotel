<?php
require_once __DIR__ . '/../includes/init.php';
require_login();

const GALLERY_CATEGORIES = ['exterior' => 'Eksterior', 'lobby' => 'Lobi', 'room' => 'Kamar', 'dining' => 'Dining', 'meeting' => 'Meeting', 'pool' => 'Kolam renang', 'facility' => 'Fasilitas', 'event' => 'Acara'];

if (is_post()) {
    csrf_check();
    $item = db_one('SELECT * FROM gallery_images WHERE id = ?', [post_int('id')]) ?? admin_abort(404, 'Foto tidak ditemukan.');
    assert_hotel((int) $item['hotel_id']);
    $cat = array_key_exists($_POST['category'] ?? '', GALLERY_CATEGORIES) ? $_POST['category'] : $item['category'];
    db_query(
        'UPDATE gallery_images SET caption = ?, caption_en = ?, category = ?, is_featured = ?, sort = ? WHERE id = ?',
        [post_nullable('caption', 160), post_nullable('caption_en', 160), $cat, post_bool('is_featured'), post_int('sort'), $item['id']]
    );
    flash('success', 'Foto diperbarui.');
    redirect(admin_url('galeri', ['hotel' => is_super() ? ($_GET['hotel'] ?? null) : null]) . '#g' . $item['id']);
}

$hotelId = selected_hotel_id();
$sql = 'SELECT g.*, h.short_name, h.slug AS hotel_slug FROM gallery_images g JOIN hotels h ON h.id = g.hotel_id';
$params = [];
if ($hotelId !== null) {
    $sql .= ' WHERE g.hotel_id = ?';
    $params[] = $hotelId;
}
$items = db_all($sql . ' ORDER BY h.sort, g.sort, g.id', $params);

$title = 'Galeri';
$nav = 'galeri';
require __DIR__ . '/../includes/layout-top.php';
?>
<?php if (is_super()): ?>
  <nav class="tabs" aria-label="Saring hotel">
    <a href="<?= e(admin_url('galeri')) ?>"<?= $hotelId === null ? ' aria-current="page"' : '' ?>>Semua hotel</a>
    <?php foreach (admin_hotels() as $h): ?><a href="<?= e(admin_url('galeri', ['hotel' => $h['id']])) ?>"<?= $hotelId === (int) $h['id'] ? ' aria-current="page"' : '' ?>><?= e($h['short_name']) ?></a><?php endforeach; ?>
  </nav>
<?php endif; ?>

<section class="card">
  <div class="card-head"><h2>Unggah foto baru</h2><span class="small muted">Bisa memilih beberapa foto sekaligus</span></div>
  <form class="upload-form form-grid-admin" method="post" action="<?= e(admin_url('galeri/unggah')) ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <?php if (is_super()): ?><?= field_select('hotel_id', 'Hotel', hotel_options(), $hotelId ?? '', []) ?><?php endif; ?>
    <?= field_select('category', 'Kategori', GALLERY_CATEGORIES, 'facility', []) ?>
    <?= field_input('caption', 'Keterangan', '', [], ['maxlength' => 160, 'hint' => 'dipakai untuk semua foto yang diunggah sekarang']) ?>
    <?= field_input('caption_en', 'Keterangan (English)', '', [], ['maxlength' => 160]) ?>
    <div class="field field--full">
      <label for="g-files">Foto <span aria-hidden="true">*</span></label>
      <input type="file" id="g-files" name="photos[]" accept="image/jpeg,image/png,image/webp" multiple required>
      <small class="muted">JPG, PNG, atau WebP, maks. 5 MB per foto. Jangan unggah foto yang memperlihatkan wajah tamu tanpa izin.</small>
    </div>
    <div class="btn-row field--full"><button class="btn btn-primary" type="submit"><?= icon('upload') ?>Unggah</button></div>
  </form>
</section>

<?php if ($items): ?>
  <div class="gal-grid">
    <?php foreach ($items as $g): ?>
      <article class="card gal-item" id="g<?= (int) $g['id'] ?>">
        <div class="gal-thumb"><?= img($g['image'], (string) $g['caption'], '260px') ?><span class="tag"><?= e($g['short_name']) ?> · <?= e(GALLERY_CATEGORIES[$g['category']] ?? $g['category']) ?></span></div>
        <form class="form" method="post" action="<?= e(admin_url('galeri', ['hotel' => is_super() ? $hotelId : null])) ?>">
          <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $g['id'] ?>">
          <input type="text" name="caption" value="<?= e($g['caption']) ?>" maxlength="160" placeholder="Keterangan" aria-label="Keterangan">
          <input type="text" name="caption_en" value="<?= e($g['caption_en']) ?>" maxlength="160" placeholder="Caption (English)" aria-label="Keterangan English">
          <div class="form-2">
            <select name="category" aria-label="Kategori"><?php foreach (GALLERY_CATEGORIES as $k => $label): ?><option value="<?= e($k) ?>"<?= $g['category'] === $k ? ' selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select>
            <input type="number" name="sort" value="<?= (int) $g['sort'] ?>" aria-label="Urutan" title="Urutan (kecil tampil duluan)">
          </div>
          <div class="gal-checks">
            <?= field_check('is_featured', 'Unggulan', (int) $g['is_featured'] === 1) ?>
          </div>
          <button class="btn btn-ghost btn-sm" type="submit">Simpan</button>
        </form>
        <?= delete_button(admin_url('galeri/hapus'), 'foto ini', ['id' => $g['id']]) ?>
      </article>
    <?php endforeach; ?>
  </div>
<?php else: ?>
  <p class="card muted">Belum ada foto untuk pilihan ini.</p>
<?php endif; ?>
<?php require __DIR__ . '/../includes/layout-bottom.php'; ?>
