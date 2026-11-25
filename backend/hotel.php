<?php
require_once __DIR__ . '/includes/init.php';
require_login();

$hotels = admin_hotels();
$id = (int) ($_GET['id'] ?? ($hotels[0]['id'] ?? 0));
assert_hotel($id);
$hotel = db_one('SELECT * FROM hotels WHERE id = ?', [$id]) ?? admin_abort(404, 'Hotel tidak ditemukan.');

$errors = [];
$v = $hotel;
if (is_post()) {
    csrf_check();
    $v = array_merge($hotel, [
        'name' => post_str('name', 120), 'short_name' => post_str('short_name', 60),
        'status' => in_array($_POST['status'] ?? '', ['open', 'soft_opening', 'coming_soon'], true) ? $_POST['status'] : $hotel['status'],
        'address' => post_str('address', 255), 'city' => post_str('city', 80),
        'province' => post_nullable('province', 80), 'postal_code' => post_nullable('postal_code', 10),
        'lat' => post_nullable('lat', 20), 'lng' => post_nullable('lng', 20),
        'whatsapp' => post_nullable('whatsapp', 30), 'phone' => post_nullable('phone', 30),
        'email' => post_nullable('email', 120), 'instagram' => ($ig = post_nullable('instagram', 60)) !== null ? ltrim($ig, '@') : null,
        'total_rooms' => post_nullable_int('total_rooms'),
        'tagline' => post_nullable('tagline', 160), 'tagline_en' => post_nullable('tagline_en', 160),
        'highlight' => post_nullable('highlight', 160), 'highlight_en' => post_nullable('highlight_en', 160),
        'description' => post_nullable('description', 5000), 'description_en' => post_nullable('description_en', 5000),
        'policies' => post_nullable('policies', 5000), 'policies_en' => post_nullable('policies_en', 5000),
        'virtual_tour_url' => post_nullable('virtual_tour_url', 255), 'operator' => post_nullable('operator', 120),
    ]);
    if (is_super()) {
        $v['is_published'] = post_bool('is_published');
    }

    foreach (['name' => 'Nama', 'short_name' => 'Nama pendek', 'address' => 'Alamat', 'city' => 'Kota'] as $f => $label) {
        if ($v[$f] === '') {
            $errors[$f] = $label . ' wajib diisi.';
        }
    }
    foreach (['whatsapp', 'phone'] as $f) {
        if (!valid_phone($v[$f])) {
            $errors[$f] = 'Isi angka saja, mis. 081234567890 atau +62 812-3456-7890.';
        }
    }
    if ($v['email'] !== null && !filter_var($v['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Format email tidak valid.';
    }
    foreach (['lat', 'lng'] as $f) {
        if ($v[$f] !== null && !is_numeric($v[$f])) {
            $errors[$f] = 'Isi angka desimal, mis. -7.3698825.';
        }
    }
    if (!valid_url($v['virtual_tour_url'])) {
        $errors['virtual_tour_url'] = 'Alamat harus diawali https://';
    }
    if ($v['instagram'] !== null && !preg_match('/^[A-Za-z0-9._]{1,60}$/', $v['instagram'])) {
        $errors['instagram'] = 'Isi nama akun saja, tanpa @ dan tanpa alamat lengkap.';
    }
    // Foto baru hanya diproses jika isian lain sudah valid, agar tidak ada file yatim.
    if (!$errors) {
        $v['hero_image'] = handle_image_field('hero_image', $hotel['hero_image'], 'hotel', $errors);
        $v['card_image'] = handle_image_field('card_image', $hotel['card_image'], 'hotel', $errors);
    }

    if (!$errors) {
        $cols = ['name', 'short_name', 'status', 'address', 'city', 'province', 'postal_code', 'lat', 'lng', 'whatsapp', 'phone', 'email', 'instagram',
            'total_rooms', 'tagline', 'tagline_en', 'highlight', 'highlight_en', 'description', 'description_en',
            'policies', 'policies_en', 'virtual_tour_url', 'operator', 'hero_image', 'card_image'];
        if (is_super()) {
            $cols[] = 'is_published';
        }
        $set = implode(', ', array_map(static fn ($c) => "`$c` = :$c", $cols));
        $params = array_intersect_key($v, array_flip($cols)) + ['id' => $id];
        db_query("UPDATE hotels SET $set WHERE id = :id", $params);
        flash('success', 'Profil ' . $v['name'] . ' tersimpan.');
        redirect(admin_url('hotel', ['id' => $id]));
    }
    flash('error', 'Periksa kembali isian yang ditandai.');
}

$title = 'Profil hotel';
$nav = 'hotel';
$actions = '<a class="btn btn-ghost" href="' . e(url($hotel['slug'])) . '" target="_blank" rel="noopener">' . icon('external-link') . 'Lihat halaman</a>';
require __DIR__ . '/includes/layout-top.php';
?>
<?php if (count($hotels) > 1): ?>
  <nav class="tabs" aria-label="Pilih hotel">
    <?php foreach ($hotels as $h): ?><a href="<?= e(admin_url('hotel', ['id' => $h['id']])) ?>"<?= (int) $h['id'] === $id ? ' aria-current="page"' : '' ?>><?= e($h['name']) ?></a><?php endforeach; ?>
  </nav>
<?php endif; ?>

<form class="form-layout" method="post" action="<?= e(admin_url('hotel', ['id' => $id])) ?>" enctype="multipart/form-data" novalidate>
  <?= csrf_field() ?>
  <div class="stack">
    <section class="card" id="kontak">
      <div class="card-head"><h2>Kontak & reservasi</h2></div>
      <p class="small muted">Nomor WhatsApp dipakai untuk semua tombol reservasi hotel ini. Jika kosong, tombol memakai nomor WhatsApp cadangan, lalu nomor telepon.</p>
      <div class="form-grid-admin">
        <?= field_input('whatsapp', 'Nomor WhatsApp reservasi', $v['whatsapp'], $errors, ['inputmode' => 'tel', 'placeholder' => '0812xxxxxxxx', 'hint' => 'boleh diawali 0 atau +62']) ?>
        <?= field_input('phone', 'Telepon', $v['phone'], $errors, ['inputmode' => 'tel']) ?>
        <?= field_input('email', 'Email', $v['email'], $errors, ['type' => 'email']) ?>
        <?= field_input('instagram', 'Instagram', $v['instagram'], $errors, ['placeholder' => 'namaakun', 'hint' => 'tanpa @']) ?>
      </div>
    </section>

    <section class="card" id="info">
      <div class="card-head"><h2>Informasi utama</h2></div>
      <div class="form-grid-admin">
        <?= field_input('name', 'Nama hotel', $v['name'], $errors, ['required' => true, 'maxlength' => 120]) ?>
        <?= field_input('short_name', 'Nama pendek (tab menu)', $v['short_name'], $errors, ['required' => true, 'maxlength' => 60]) ?>
        <?= field_select('status', 'Status', ['open' => 'Buka', 'soft_opening' => 'Soft opening', 'coming_soon' => 'Segera dibuka'], $v['status'], $errors) ?>
        <?= field_input('total_rooms', 'Jumlah kamar', $v['total_rooms'], $errors, ['type' => 'number', 'min' => 1]) ?>
        <?= field_input('address', 'Alamat', $v['address'], $errors, ['required' => true, 'full' => true, 'maxlength' => 255]) ?>
        <?= field_input('city', 'Kota', $v['city'], $errors, ['required' => true]) ?>
        <?= field_input('province', 'Provinsi', $v['province'], $errors) ?>
        <?= field_input('postal_code', 'Kode pos', $v['postal_code'], $errors, ['maxlength' => 10]) ?>
        <?= field_input('operator', 'Pengelola', $v['operator'], $errors) ?>
        <?= field_input('lat', 'Lintang (latitude)', $v['lat'], $errors, ['hint' => 'untuk peta']) ?>
        <?= field_input('lng', 'Bujur (longitude)', $v['lng'], $errors) ?>
        <?= field_input('virtual_tour_url', 'Tautan tur virtual 360°', $v['virtual_tour_url'], $errors, ['type' => 'url', 'full' => true]) ?>
      </div>
    </section>

    <section class="card">
      <div class="card-head"><h2>Teks di halaman hotel</h2></div>
      <p class="small muted">Kolom English boleh kosong; halaman English akan memakai teks Indonesia.</p>
      <div class="form-grid-admin">
        <?= field_input('tagline', 'Tagline', $v['tagline'], $errors, ['maxlength' => 160]) ?>
        <?= field_input('tagline_en', 'Tagline (English)', $v['tagline_en'], $errors, ['maxlength' => 160]) ?>
        <?= field_input('highlight', 'Ciri khas (kartu hotel)', $v['highlight'], $errors, ['maxlength' => 160]) ?>
        <?= field_input('highlight_en', 'Ciri khas (English)', $v['highlight_en'], $errors, ['maxlength' => 160]) ?>
        <?= field_textarea('description', 'Deskripsi', $v['description'], $errors, ['rows' => 7]) ?>
        <?= field_textarea('description_en', 'Deskripsi (English)', $v['description_en'], $errors, ['rows' => 7]) ?>
        <?= field_textarea('policies', 'Kebijakan hotel', $v['policies'], $errors, ['rows' => 7, 'hint' => 'Markdown: awali tiap poin dengan "- "', 'mono' => true]) ?>
        <?= field_textarea('policies_en', 'Kebijakan (English)', $v['policies_en'], $errors, ['rows' => 7, 'mono' => true]) ?>
      </div>
    </section>
  </div>

  <aside class="stack form-side">
    <section class="card">
      <div class="card-head"><h2>Simpan</h2></div>
      <dl class="dl small">
        <div><dt>Alamat halaman</dt><dd><code>/<?= e($hotel['slug']) ?></code></dd></div>
        <div><dt>Tema</dt><dd><?= $hotel['brand'] === 'darmo' ? 'Darmo Inn (plum & emas)' : 'News Hotel (oranye)' ?></dd></div>
      </dl>
      <?php if (is_super()): ?><?= field_check('is_published', 'Tampilkan hotel di situs', (int) $v['is_published'] === 1) ?><?php endif; ?>
      <button class="btn btn-primary btn-block" type="submit">Simpan profil</button>
    </section>
    <section class="card">
      <div class="card-head"><h2>Foto</h2></div>
      <?= field_image('hero_image', 'Foto utama (hero)', $v['hero_image'], $errors) ?>
      <?= field_image('card_image', 'Foto kartu hotel', $v['card_image'], $errors) ?>
    </section>
  </aside>
</form>
<?php require __DIR__ . '/includes/layout-bottom.php'; ?>
