<?php
require_once __DIR__ . '/../includes/init.php';
$me = require_login();

$cats = ['news' => 'Berita', 'event' => 'Acara', 'promo' => 'Promo'];
$id = (int) ($_GET['id'] ?? 0);
$scope = admin_hotel_scope();
if ($id) {
    $post = db_one('SELECT * FROM posts WHERE id = ?', [$id]) ?? admin_abort(404, 'Artikel tidak ditemukan.');
    // Artikel grup (tanpa hotel) hanya bisa diubah super admin.
    if ($scope !== null && (int) $post['hotel_id'] !== $scope) {
        admin_abort(403, 'Artikel ini milik hotel lain atau artikel grup.');
    }
} else {
    $post = [
        'id' => 0, 'hotel_id' => $scope, 'slug' => '', 'category' => 'news', 'title' => '', 'title_en' => '', 'excerpt' => '', 'excerpt_en' => '',
        'body' => '', 'body_en' => '', 'cover' => null, 'source_url' => null, 'status' => 'draft', 'published_at' => date('Y-m-d H:i:00'),
    ];
}

$errors = [];
$v = $post;
$preview = null;
if (is_post()) {
    csrf_check();
    $hotelIn = $scope ?? (($h = post_int('hotel_id')) > 0 ? $h : null);
    $v = array_merge($post, [
        'hotel_id' => $hotelIn,
        'title' => post_str('title', 180), 'title_en' => post_nullable('title_en', 180),
        'slug' => slugify(post_str('slug', 120) ?: post_str('title', 180)),
        'category' => array_key_exists($_POST['category'] ?? '', $cats) ? $_POST['category'] : 'news',
        'excerpt' => post_nullable('excerpt', 300), 'excerpt_en' => post_nullable('excerpt_en', 300),
        'body' => post_nullable('body', 60000), 'body_en' => post_nullable('body_en', 60000),
        'source_url' => post_nullable('source_url', 255),
        'status' => ($_POST['status'] ?? '') === 'published' ? 'published' : 'draft',
        'published_at' => ($d = post_str('published_at', 20)) !== '' ? date('Y-m-d H:i:00', strtotime($d) ?: time()) : date('Y-m-d H:i:00'),
    ]);

    if (isset($_POST['preview'])) {
        $preview = $v;   // tampilkan pratinjau, jangan simpan (foto sampul baru belum diunggah)
    } else {
        if ($v['hotel_id'] !== null && !db_value('SELECT id FROM hotels WHERE id = ?', [$v['hotel_id']])) {
            $errors['hotel_id'] = 'Pilih hotel.';
        }
        assert_hotel($v['hotel_id'] ?? ($scope === null ? null : -1));
        if ($v['title'] === '') {
            $errors['title'] = 'Judul wajib diisi.';
        }
        if ($v['slug'] === '') {
            $errors['slug'] = 'Slug wajib diisi.';
        } elseif (db_value('SELECT id FROM posts WHERE slug = ? AND id <> ?', [$v['slug'], $id])) {
            $errors['slug'] = 'Slug ini sudah dipakai artikel lain.';
        }
        if ($v['status'] === 'published' && ($v['body'] === null || mb_strlen($v['body']) < 50)) {
            $errors['body'] = 'Isi artikel terlalu pendek untuk diterbitkan (minimal 50 karakter).';
        }
        if (!valid_url($v['source_url'])) {
            $errors['source_url'] = 'Alamat harus diawali https://';
        }
        if (!$errors) {
            $v['cover'] = handle_image_field('cover', $post['cover'], 'blog', $errors);
        }
        if (!$errors) {
            $cols = ['hotel_id', 'slug', 'category', 'title', 'title_en', 'excerpt', 'excerpt_en', 'body', 'body_en', 'cover', 'source_url', 'status', 'published_at'];
            $params = array_intersect_key($v, array_flip($cols));
            if ($id) {
                $set = implode(', ', array_map(static fn ($c) => "`$c` = :$c", $cols));
                db_query("UPDATE posts SET $set WHERE id = :id", $params + ['id' => $id]);
            } else {
                db_query('INSERT INTO posts (`' . implode('`, `', $cols) . '`) VALUES (:' . implode(', :', $cols) . ')', $params);
                $id = (int) db()->lastInsertId();
            }
            flash('success', $v['status'] === 'published' ? 'Artikel tersimpan dan terbit.' : 'Draf tersimpan.');
            redirect(admin_url('blog/form', ['id' => $id]));
        }
        flash('error', 'Periksa kembali isian yang ditandai.');
    }
}

$title = $id ? 'Ubah artikel' : 'Tulis artikel';
$nav = 'blog';
$actions = '<a class="btn btn-ghost" href="' . e(admin_url('blog')) . '">' . icon('arrow-left') . 'Semua artikel</a>'
    . ($id && $post['status'] === 'published' ? ' <a class="btn btn-ghost" href="' . e(url('blog/' . $post['slug'])) . '" target="_blank" rel="noopener">' . icon('external-link') . 'Lihat</a>' : '');
require __DIR__ . '/../includes/layout-top.php';
?>
<?php if ($preview): ?>
  <section class="card preview">
    <div class="card-head"><h2>Pratinjau (belum disimpan)</h2><a href="#f-title">Lanjut menyunting</a></div>
    <article class="prose-preview">
      <h1><?= e($preview['title']) ?></h1>
      <?php if ($preview['excerpt']): ?><p class="lead"><?= e($preview['excerpt']) ?></p><?php endif; ?>
      <?= markdown($preview['body']) ?>
    </article>
  </section>
<?php endif; ?>

<form class="form-layout" method="post" action="<?= e(admin_url('blog/form', ['id' => $id ?: null])) ?>" enctype="multipart/form-data" novalidate>
  <?= csrf_field() ?>
  <div class="stack">
    <section class="card">
      <div class="card-head"><h2>Isi artikel</h2></div>
      <div class="form-grid-admin">
        <?= field_input('title', 'Judul', $v['title'], $errors, ['required' => true, 'maxlength' => 180, 'full' => true]) ?>
        <?= field_textarea('excerpt', 'Ringkasan', $v['excerpt'], $errors, ['rows' => 2, 'maxlength' => 300, 'hint' => '1–2 kalimat untuk kartu artikel', 'full' => true]) ?>
        <?= field_textarea('body', 'Isi', $v['body'], $errors, ['rows' => 18, 'mono' => true, 'full' => true, 'hint' => 'Markdown: ## Subjudul, **tebal**, *miring*, daftar diawali "- "']) ?>
      </div>
    </section>
    <section class="card">
      <div class="card-head"><h2>Versi English</h2><span class="small muted">Opsional; jika kosong, halaman English memakai teks Indonesia</span></div>
      <div class="form-grid-admin">
        <?= field_input('title_en', 'Title', $v['title_en'], $errors, ['maxlength' => 180, 'full' => true]) ?>
        <?= field_textarea('excerpt_en', 'Excerpt', $v['excerpt_en'], $errors, ['rows' => 2, 'maxlength' => 300, 'full' => true]) ?>
        <?= field_textarea('body_en', 'Body', $v['body_en'], $errors, ['rows' => 12, 'mono' => true, 'full' => true]) ?>
      </div>
    </section>
  </div>

  <aside class="stack form-side">
    <section class="card">
      <div class="card-head"><h2>Terbitkan</h2></div>
      <?= field_select('status', 'Status', ['draft' => 'Draf (tidak tampil)', 'published' => 'Terbit'], $v['status'], $errors) ?>
      <?= field_input('published_at', 'Tanggal terbit', $v['published_at'] ? date('Y-m-d\TH:i', strtotime($v['published_at'])) : '', $errors, ['type' => 'datetime-local', 'hint' => 'tanggal mendatang = terjadwal']) ?>
      <div class="btn-row">
        <button class="btn btn-primary" type="submit">Simpan</button>
        <button class="btn btn-ghost" type="submit" name="preview" value="1">Pratinjau</button>
      </div>
    </section>
    <section class="card">
      <?= field_select('category', 'Kategori', $cats, $v['category'], $errors) ?>
      <?php if (is_super()): ?>
        <?= field_select('hotel_id', 'Hotel', hotel_options(true), $v['hotel_id'] ?? '', $errors) ?>
      <?php endif; ?>
      <?= field_input('slug', 'Slug (alamat)', $v['slug'], $errors, ['maxlength' => 120, 'hint' => 'kosongkan agar dibuat dari judul']) ?>
      <?= field_input('source_url', 'Tautan sumber', $v['source_url'], $errors, ['type' => 'url', 'hint' => 'opsional']) ?>
    </section>
    <section class="card">
      <?= field_image('cover', 'Gambar sampul', $v['cover'], $errors, ['removable' => true]) ?>
    </section>
  </aside>
</form>

<?php if ($id): ?>
  <section class="card danger-zone"><div class="card-head"><h2>Hapus artikel</h2></div><?= delete_button(admin_url('blog/hapus'), 'artikel "' . $post['title'] . '"', ['id' => $id]) ?></section>
<?php endif; ?>
<?php require __DIR__ . '/../includes/layout-bottom.php'; ?>
