<?php
/**
 * Daftar + form untuk tabel konten hotel yang sederhana (fasilitas, tempat sekitar, dining, ruang meeting).
 *
 * Halaman mengisi $crud lalu memuat file ini:
 *   table, title, nav, path, singular: nama tabel, judul halaman, kunci menu, alamat admin, sebutan satu baris
 *   fields: [kolom => [label, type (text|textarea|number|decimal|select|check|image), required, max, options, hint, list, full]]
 *   slug_from: (opsional) kolom yang dijadikan slug otomatis, unik per hotel
 *   image_folder: folder unggahan untuk field bertipe image
 * Setiap baris punya hotel_id; admin hotel hanya melihat dan mengubah baris hotelnya.
 */
declare(strict_types=1);

$table  = $crud['table'];
$fields = $crud['fields'];
$base   = $crud['path'];
$scope  = admin_hotel_scope();
$listHotel = selected_hotel_id();

$id = (int) ($_GET['id'] ?? 0);
$row = null;
if ($id) {
    $row = db_one("SELECT * FROM `$table` WHERE id = ?", [$id]) ?? admin_abort(404, ucfirst($crud['singular']) . ' tidak ditemukan.');
    assert_hotel((int) $row['hotel_id']);
}

$errors = [];
$values = $row ?? ['hotel_id' => $scope ?? $listHotel ?? (admin_hotels()[0]['id'] ?? 0)];
foreach ($fields as $col => $f) {
    $values[$col] ??= ($f['type'] === 'check' ? ($f['default'] ?? 0) : ($f['type'] === 'number' && empty($f['nullable']) ? 0 : null));
}

if (is_post()) {
    csrf_check();

    if (($_POST['action'] ?? '') === 'delete') {
        $del = db_one("SELECT * FROM `$table` WHERE id = ?", [post_int('id')]) ?? admin_abort(404, 'Data tidak ditemukan.');
        assert_hotel((int) $del['hotel_id']);
        db_query("DELETE FROM `$table` WHERE id = ?", [$del['id']]);
        foreach ($fields as $col => $f) {
            if ($f['type'] === 'image') {
                image_delete_upload($del[$col]);
            }
        }
        flash('success', ucfirst($crud['singular']) . ' dihapus.');
        redirect(admin_url($base, ['hotel' => is_super() ? $del['hotel_id'] : null]));
    }

    $values['hotel_id'] = $scope ?? post_int('hotel_id');
    assert_hotel($values['hotel_id']);
    if (!db_value('SELECT id FROM hotels WHERE id = ?', [$values['hotel_id']])) {
        $errors['hotel_id'] = 'Pilih hotel.';
    }
    foreach ($fields as $col => $f) {
        $max = $f['max'] ?? 255;
        switch ($f['type']) {
            case 'check':
                $values[$col] = post_bool($col);
                break;
            case 'number':
                $values[$col] = isset($f['nullable']) ? post_nullable_int($col) : post_int($col);
                break;
            case 'decimal':
                $raw = str_replace(',', '.', post_str($col, 10));
                $values[$col] = $raw === '' ? null : $raw;
                if ($raw !== '' && !is_numeric($raw)) {
                    $errors[$col] = 'Isi angka, mis. 120 atau 120.5.';
                }
                break;
            case 'select':
                $values[$col] = array_key_exists($_POST[$col] ?? '', $f['options']) ? $_POST[$col] : array_key_first($f['options']);
                break;
            case 'image':
                break;   // diproses setelah validasi
            default:
                $values[$col] = !empty($f['required']) ? post_str($col, $max) : post_nullable($col, $max);
        }
        if (!empty($f['required']) && ($values[$col] === null || $values[$col] === '')) {
            $errors[$col] = $f['label'] . ' wajib diisi.';
        }
    }
    if (!$errors && !empty($crud['slug_from'])) {
        $slug = slugify((string) $values[$crud['slug_from']]) ?: 'item';
        $try = $slug;
        for ($n = 2; db_value("SELECT id FROM `$table` WHERE hotel_id = ? AND slug = ? AND id <> ?", [$values['hotel_id'], $try, $id]); $n++) {
            $try = $slug . '-' . $n;
        }
        $values['slug'] = $try;
    }
    if (!$errors) {
        foreach ($fields as $col => $f) {
            if ($f['type'] === 'image') {
                $values[$col] = handle_image_field($col, $row[$col] ?? null, $crud['image_folder'] ?? 'konten', $errors);
            }
        }
    }
    if (!$errors) {
        $cols = array_merge(['hotel_id'], array_keys($fields), !empty($crud['slug_from']) ? ['slug'] : []);
        $params = array_intersect_key($values, array_flip($cols));
        if ($id) {
            $set = implode(', ', array_map(static fn ($c) => "`$c` = :$c", $cols));
            db_query("UPDATE `$table` SET $set WHERE id = :id", $params + ['id' => $id]);
        } else {
            db_query("INSERT INTO `$table` (`" . implode('`, `', $cols) . '`) VALUES (:' . implode(', :', $cols) . ')', $params);
        }
        flash('success', ucfirst($crud['singular']) . ' tersimpan.');
        redirect(admin_url($base, ['hotel' => is_super() ? $values['hotel_id'] : null]));
    }
    flash('error', 'Periksa kembali isian yang ditandai.');
}

$sql = "SELECT t.*, h.short_name, h.brand FROM `$table` t JOIN hotels h ON h.id = t.hotel_id";
$params = [];
if ($listHotel !== null) {
    $sql .= ' WHERE t.hotel_id = ?';
    $params[] = $listHotel;
}
$rows = db_all($sql . ' ORDER BY h.sort, t.sort, t.id', $params);
$listCols = array_filter($fields, static fn ($f) => !empty($f['list']));

$title = $crud['title'];
$nav = $crud['nav'];
$actions = '<a class="btn btn-primary" href="' . e(admin_url($base, ['hotel' => is_super() ? $listHotel : null])) . '#form">' . icon('plus') . 'Tambah</a>';
require __DIR__ . '/layout-top.php';
?>
<?php if (is_super()): ?>
  <nav class="tabs" aria-label="Saring hotel">
    <a href="<?= e(admin_url($base)) ?>"<?= $listHotel === null ? ' aria-current="page"' : '' ?>>Semua hotel</a>
    <?php foreach (admin_hotels() as $h): ?><a href="<?= e(admin_url($base, ['hotel' => $h['id']])) ?>"<?= $listHotel === (int) $h['id'] ? ' aria-current="page"' : '' ?>><?= e($h['short_name']) ?></a><?php endforeach; ?>
  </nav>
<?php endif; ?>

<div class="detail-grid">
  <section class="card card--flush">
    <div class="table-scroll">
      <table class="table table--rows">
        <thead><tr>
          <?php foreach ($listCols as $f): ?><th><?= e($f['label']) ?></th><?php endforeach; ?>
          <th>Hotel</th><th class="num">Urutan</th><th class="row-actions"><span class="sr-only">Aksi</span></th>
        </tr></thead>
        <tbody>
          <?php foreach ($rows as $r): ?>
            <tr<?= $id === (int) $r['id'] ? ' class="is-editing"' : '' ?>>
              <?php $first = true; foreach ($listCols as $col => $f): ?>
                <td><?php
                    $val = $r[$col];
                    $text = match ($f['type']) {
                        'check' => (int) $val === 1 ? 'Ya' : '—',
                        'select' => $f['options'][$val] ?? (string) $val,
                        default => (string) ($val ?? '—'),
                    };
                    if ($f['type'] === 'image') {
                        echo $val ? '<span class="mini-thumb">' . img($val, '', '64px') . '</span>' : '<span class="muted">—</span>';
                    } elseif ($first) {
                        echo '<a class="row-title" href="' . e(admin_url($base, ['id' => $r['id'], 'hotel' => is_super() ? $listHotel : null])) . '#form">' . e($text) . '</a>';
                    } else {
                        echo e(excerpt($text, 60));
                    }
                    $first = false; ?></td>
              <?php endforeach; ?>
              <td class="nowrap"><span class="dot" data-brand="<?= e($r['brand']) ?>"></span><?= e($r['short_name']) ?></td>
              <td class="num"><?= (int) $r['sort'] ?></td>
              <td class="row-actions"><a class="btn btn-ghost btn-sm" href="<?= e(admin_url($base, ['id' => $r['id'], 'hotel' => is_super() ? $listHotel : null])) ?>#form"><?= icon('pencil') ?>Ubah</a></td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$rows): ?><tr><td colspan="<?= count($listCols) + 3 ?>" class="empty-row">Belum ada data.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </section>

  <div class="stack">
    <section class="card" id="form">
      <div class="card-head"><h2><?= $id ? 'Ubah ' . e($crud['singular']) : 'Tambah ' . e($crud['singular']) ?></h2><?php if ($id): ?><a href="<?= e(admin_url($base, ['hotel' => is_super() ? $listHotel : null])) ?>#form">Batal</a><?php endif; ?></div>
      <form class="form" method="post" action="<?= e(admin_url($base, ['id' => $id ?: null, 'hotel' => is_super() ? $listHotel : null])) ?>" enctype="multipart/form-data" novalidate>
        <?= csrf_field() ?>
        <?php if (is_super()): ?><?= field_select('hotel_id', 'Hotel', hotel_options(), $values['hotel_id'], $errors) ?><?php endif; ?>
        <?php foreach ($fields as $col => $f):
            $opts = ['required' => !empty($f['required']), 'hint' => $f['hint'] ?? null, 'maxlength' => $f['max'] ?? null];
            $opts = array_filter($opts, static fn ($x) => $x !== null && $x !== false);
            echo match ($f['type']) {
                'textarea' => field_textarea($col, $f['label'], $values[$col], $errors, $opts + ['rows' => 3]),
                'number' => field_input($col, $f['label'], $values[$col], $errors, $opts + ['type' => 'number', 'min' => 0]),
                'decimal' => field_input($col, $f['label'], $values[$col] !== null ? rtrim(rtrim((string) $values[$col], '0'), '.') : '', $errors, $opts + ['inputmode' => 'decimal']),
                'select' => field_select($col, $f['label'], $f['options'], $values[$col], $errors, $opts),
                'check' => field_check($col, $f['label'], (int) $values[$col] === 1, ['hint' => $f['hint'] ?? null] + []),
                'image' => field_image($col, $f['label'], $values[$col], $errors, ['removable' => true]),
                default => field_input($col, $f['label'], $values[$col], $errors, $opts),
            };
        endforeach; ?>
        <div class="btn-row"><button class="btn btn-primary" type="submit"><?= $id ? 'Simpan perubahan' : 'Tambah' ?></button></div>
      </form>
    </section>
    <?php if ($id): ?>
      <section class="card danger-zone"><?= delete_button(admin_url($base, ['hotel' => is_super() ? $listHotel : null]), $crud['singular'] . ' ini', ['id' => $id, 'action' => 'delete']) ?></section>
    <?php endif; ?>
  </div>
</div>
<?php require __DIR__ . '/layout-bottom.php'; ?>
