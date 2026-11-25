<?php
require_once __DIR__ . '/../includes/init.php';
require_login();

/** Fasilitas kamar yang bisa dicentang. Fasilitas lain bisa ditulis bebas. */
const AMENITY_CATALOG = [
    ['icon' => 'wifi', 'name' => 'WiFi gratis', 'name_en' => 'Free WiFi'],
    ['icon' => 'snow', 'name' => 'AC dengan remote', 'name_en' => 'Air conditioning'],
    ['icon' => 'tv', 'name' => 'TV LCD', 'name_en' => 'LCD TV'],
    ['icon' => 'shower', 'name' => 'Shower', 'name_en' => 'Shower'],
    ['icon' => 'lock', 'name' => 'Safety box', 'name_en' => 'Safe'],
    ['icon' => 'key', 'name' => 'Kunci keycard', 'name_en' => 'Keycard'],
    ['icon' => 'towel', 'name' => 'Handuk gratis', 'name_en' => 'Free towels'],
    ['icon' => 'slipper', 'name' => 'Sandal hotel', 'name_en' => 'Slippers'],
    ['icon' => 'tooth', 'name' => 'Dental kit', 'name_en' => 'Dental kit'],
    ['icon' => 'glass', 'name' => 'Welcome drink', 'name_en' => 'Welcome drink'],
    ['icon' => 'balcony', 'name' => 'Balkon', 'name_en' => 'Balcony'],
];

$id = (int) ($_GET['id'] ?? 0);
if ($id) {
    $room = db_one('SELECT * FROM room_types WHERE id = ?', [$id]) ?? admin_abort(404, 'Kamar tidak ditemukan.');
    assert_hotel((int) $room['hotel_id']);
} else {
    $scope = admin_hotel_scope();
    $room = [
        'id' => 0, 'hotel_id' => $scope ?? (int) ($_GET['hotel'] ?? (admin_hotels()[0]['id'] ?? 0)), 'slug' => '', 'name' => '', 'name_en' => '',
        'description' => '', 'description_en' => '', 'size_m2' => null, 'bed_type' => '', 'bed_type_en' => '', 'max_adults' => 2, 'max_children' => 0,
        'base_price' => null, 'amenities' => '[]', 'cover_image' => null, 'is_active' => 1, 'sort' => 0,
    ];
}

$catalogNames = array_column(AMENITY_CATALOG, 'name');
$current = room_amenities($room);
$checked = array_values(array_intersect($catalogNames, array_column($current, 'name')));
$extra = implode("\n", array_map(static fn ($a) => $a['name'] . (!empty($a['name_en']) ? ' | ' . $a['name_en'] : ''),
    array_filter($current, static fn ($a) => !in_array($a['name'], $catalogNames, true))));

$errors = [];
$v = $room;
if (is_post()) {
    csrf_check();
    $v = array_merge($room, [
        'hotel_id' => admin_hotel_scope() ?? post_int('hotel_id'),
        'name' => post_str('name', 100), 'name_en' => post_nullable('name_en', 100),
        'slug' => slugify(post_str('slug', 60) ?: post_str('name', 100)),
        'description' => post_nullable('description', 4000), 'description_en' => post_nullable('description_en', 4000),
        'size_m2' => ($s = str_replace(',', '.', post_str('size_m2', 8))) !== '' ? $s : null,
        'bed_type' => post_nullable('bed_type', 60), 'bed_type_en' => post_nullable('bed_type_en', 60),
        'max_adults' => max(0, min(10, post_int('max_adults', 2))), 'max_children' => max(0, min(10, post_int('max_children', 0))),
        'base_price' => post_nullable_int('base_price'),
        'is_active' => post_bool('is_active'), 'sort' => post_int('sort'),
    ]);
    // Fasilitas: dari centang + baris bebas "Nama | Name EN".
    $amenities = array_values(array_filter(AMENITY_CATALOG, static fn ($a) => in_array($a['name'], (array) ($_POST['amenities'] ?? []), true)));
    $extra = post_str('amenities_extra', 2000);
    foreach (preg_split('/\n+/', $extra) ?: [] as $line) {
        [$nameId, $nameEn] = array_map('trim', explode('|', $line . '|'));
        if ($nameId !== '') {
            $amenities[] = ['icon' => 'check', 'name' => mb_substr($nameId, 0, 60), 'name_en' => mb_substr($nameEn, 0, 60)];
        }
    }
    $checked = array_column($amenities, 'name');
    $v['amenities'] = json_encode($amenities, JSON_UNESCAPED_UNICODE);

    assert_hotel($v['hotel_id']);
    if (!db_value('SELECT id FROM hotels WHERE id = ?', [$v['hotel_id']])) {
        $errors['hotel_id'] = 'Pilih hotel.';
    }
    if ($v['name'] === '') {
        $errors['name'] = 'Nama kamar wajib diisi.';
    }
    if ($v['slug'] === '') {
        $errors['slug'] = 'Slug wajib diisi.';
    } elseif (db_value('SELECT id FROM room_types WHERE hotel_id = ? AND slug = ? AND id <> ?', [$v['hotel_id'], $v['slug'], $id])) {
        $errors['slug'] = 'Slug ini sudah dipakai kamar lain di hotel yang sama.';
    }
    if ($v['size_m2'] !== null && (!is_numeric($v['size_m2']) || (float) $v['size_m2'] <= 0 || (float) $v['size_m2'] > 999)) {
        $errors['size_m2'] = 'Isi luas dalam m², mis. 23 atau 23.5.';
    }
    if ($v['base_price'] !== null && ($v['base_price'] < 10000 || $v['base_price'] > 100000000)) {
        $errors['base_price'] = 'Isi tarif dalam rupiah, mis. 350000.';
    }
    if (!$errors) {
        $v['cover_image'] = handle_image_field('cover_image', $room['cover_image'], 'kamar', $errors);
    }

    if (!$errors) {
        $cols = ['hotel_id', 'slug', 'name', 'name_en', 'description', 'description_en', 'size_m2', 'bed_type', 'bed_type_en', 'max_adults',
            'max_children', 'base_price', 'amenities', 'cover_image', 'is_active', 'sort'];
        $params = array_intersect_key($v, array_flip($cols));
        if ($id) {
            $set = implode(', ', array_map(static fn ($c) => "`$c` = :$c", $cols));
            db_query("UPDATE room_types SET $set WHERE id = :id", $params + ['id' => $id]);
        } else {
            db_query('INSERT INTO room_types (`' . implode('`, `', $cols) . '`) VALUES (:' . implode(', :', $cols) . ')', $params);
            $id = (int) db()->lastInsertId();
        }
        flash('success', 'Kamar ' . $v['name'] . ' tersimpan.');
        redirect(admin_url('kamar/form', ['id' => $id]));
    }
    flash('error', 'Periksa kembali isian yang ditandai.');
}

$photos = $id ? db_all('SELECT * FROM room_images WHERE room_type_id = ? ORDER BY sort, id', [$id]) : [];
$hotelSlug = (string) db_value('SELECT slug FROM hotels WHERE id = ?', [$v['hotel_id']]);

$title = $id ? 'Ubah kamar: ' . $room['name'] : 'Tambah kamar';
$nav = 'kamar';
$actions = '<a class="btn btn-ghost" href="' . e(admin_url('kamar')) . '">' . icon('arrow-left') . 'Semua kamar</a>'
    . ($id && $hotelSlug ? ' <a class="btn btn-ghost" href="' . e(url($hotelSlug . '/kamar') . '#' . $room['slug']) . '" target="_blank" rel="noopener">' . icon('external-link') . 'Lihat</a>' : '');
require __DIR__ . '/../includes/layout-top.php';
?>
<form class="form-layout" method="post" action="<?= e(admin_url('kamar/form', ['id' => $id ?: null, 'hotel' => $id ? null : $v['hotel_id']])) ?>" enctype="multipart/form-data" novalidate>
  <?= csrf_field() ?>
  <div class="stack">
    <section class="card">
      <div class="card-head"><h2>Detail kamar</h2></div>
      <div class="form-grid-admin">
        <?php if (is_super()): ?><?= field_select('hotel_id', 'Hotel', hotel_options(), $v['hotel_id'], $errors, ['full' => true]) ?><?php endif; ?>
        <?= field_input('name', 'Nama kamar', $v['name'], $errors, ['required' => true, 'maxlength' => 100]) ?>
        <?= field_input('name_en', 'Nama (English)', $v['name_en'], $errors, ['maxlength' => 100]) ?>
        <?= field_input('slug', 'Slug', $v['slug'], $errors, ['maxlength' => 60, 'hint' => 'kosongkan agar dibuat dari nama; dipakai di alamat #slug']) ?>
        <?= field_input('base_price', 'Tarif mulai (Rp/malam)', $v['base_price'], $errors, ['inputmode' => 'numeric', 'placeholder' => '350000', 'hint' => 'kosong = "Tarif segera tersedia"']) ?>
        <?= field_input('size_m2', 'Luas (m²)', $v['size_m2'] !== null ? rtrim(rtrim((string) $v['size_m2'], '0'), '.') : '', $errors, ['inputmode' => 'decimal']) ?>
        <?= field_input('bed_type', 'Jenis ranjang', $v['bed_type'], $errors, ['placeholder' => 'Queen / Twin atau Double']) ?>
        <?= field_input('bed_type_en', 'Jenis ranjang (English)', $v['bed_type_en'], $errors) ?>
        <?= field_input('max_adults', 'Maks. dewasa', $v['max_adults'], $errors, ['type' => 'number', 'min' => 0, 'max' => 10, 'hint' => '0 = kapasitas tamu tidak ditampilkan di situs']) ?>
        <?= field_input('max_children', 'Maks. anak', $v['max_children'], $errors, ['type' => 'number', 'min' => 0, 'max' => 10]) ?>
        <?= field_textarea('description', 'Deskripsi', $v['description'], $errors, ['rows' => 6]) ?>
        <?= field_textarea('description_en', 'Deskripsi (English)', $v['description_en'], $errors, ['rows' => 6]) ?>
      </div>
    </section>

    <section class="card">
      <div class="card-head"><h2>Fasilitas kamar</h2></div>
      <div class="check-grid">
        <?php foreach (AMENITY_CATALOG as $a): ?>
          <label class="check"><input type="checkbox" name="amenities[]" value="<?= e($a['name']) ?>"<?= in_array($a['name'], $checked, true) ? ' checked' : '' ?>><span><?= icon($a['icon']) ?><?= e($a['name']) ?></span></label>
        <?php endforeach; ?>
      </div>
      <?= field_textarea('amenities_extra', 'Fasilitas lain', $extra, $errors, ['rows' => 3, 'hint' => 'satu per baris, format: Nama Indonesia | English name']) ?>
    </section>

    <?php if ($id): ?>
      <section class="card" id="foto">
        <div class="card-head"><h2>Foto tambahan</h2><span class="small muted">Tampil di halaman Kamar, bisa diperbesar</span></div>
        <?php if ($photos): ?>
          <div class="thumbs thumbs--wide">
            <?php foreach ($photos as $ph): ?>
              <figure class="thumb">
                <?= img($ph['image'], $ph['alt'] ?: $room['name'], '200px') ?>
                <figcaption class="small"><?= e($ph['alt'] ?: '—') ?></figcaption>
                <button type="submit" class="icon-btn" form="del-photo-<?= (int) $ph['id'] ?>" aria-label="Hapus foto"><?= icon('trash-2') ?></button>
              </figure>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <p class="muted small">Belum ada foto tambahan.</p>
        <?php endif; ?>
        <div class="upload-inline">
          <input type="file" name="photos[]" accept="image/jpeg,image/png,image/webp" multiple form="add-photo" aria-label="Pilih foto kamar">
          <input type="text" name="alt" placeholder="Keterangan foto (opsional)" maxlength="160" form="add-photo" aria-label="Keterangan foto">
          <button type="submit" class="btn btn-ghost btn-sm" form="add-photo"><?= icon('upload') ?>Unggah foto</button>
        </div>
      </section>
    <?php endif; ?>
  </div>

  <aside class="stack form-side">
    <section class="card">
      <div class="card-head"><h2>Simpan</h2></div>
      <?= field_check('is_active', 'Tampilkan di situs', (int) $v['is_active'] === 1) ?>
      <?= field_input('sort', 'Urutan', $v['sort'], $errors, ['type' => 'number', 'hint' => 'kecil tampil duluan']) ?>
      <button class="btn btn-primary btn-block" type="submit"><?= $id ? 'Simpan perubahan' : 'Tambah kamar' ?></button>
    </section>
    <section class="card">
      <?= field_image('cover_image', 'Foto sampul', $v['cover_image'], $errors, ['removable' => true]) ?>
    </section>
  </aside>
</form>

<?php if ($id): ?>
  <section class="card danger-zone"><div class="card-head"><h2>Hapus kamar</h2></div><?= delete_button(admin_url('kamar/hapus'), 'kamar ' . $room['name'] . ' beserta fotonya', ['id' => $id]) ?></section>
<?php endif; ?>

<?php if ($id): ?>
  <form id="add-photo" method="post" action="<?= e(admin_url('kamar/foto')) ?>" enctype="multipart/form-data" hidden><?= csrf_field() ?><input type="hidden" name="room_id" value="<?= $id ?>"><input type="hidden" name="action" value="add"></form>
  <?php foreach ($photos as $ph): ?>
    <form id="del-photo-<?= (int) $ph['id'] ?>" method="post" action="<?= e(admin_url('kamar/foto')) ?>" hidden><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="photo_id" value="<?= (int) $ph['id'] ?>"></form>
  <?php endforeach; ?>
<?php endif; ?>
<?php require __DIR__ . '/../includes/layout-bottom.php'; ?>
