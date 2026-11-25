<?php
// Khusus super admin; disimpan di tabel settings.
require_once __DIR__ . '/includes/init.php';
require_login();
require_super();

$keys = ['site_name', 'tagline_id', 'tagline_en', 'whatsapp_group'];
$v = [];
foreach ($keys as $k) {
    $v[$k] = (string) setting($k, '');
}
$errors = [];
if (is_post()) {
    csrf_check();
    $v = [
        'site_name' => post_str('site_name', 80),
        'tagline_id' => post_str('tagline_id', 160),
        'tagline_en' => post_str('tagline_en', 160),
        'whatsapp_group' => post_str('whatsapp_group', 30),
    ];
    if ($v['site_name'] === '') {
        $errors['site_name'] = 'Nama situs wajib diisi.';
    }
    if (!valid_phone($v['whatsapp_group'] === '' ? null : $v['whatsapp_group'])) {
        $errors['whatsapp_group'] = 'Isi angka saja, mis. 081234567890.';
    }
    if (!$errors) {
        foreach ($v as $k => $val) {
            db_query('INSERT INTO settings (`key`, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)', [$k, $val]);
        }
        flash('success', 'Pengaturan tersimpan.');
        redirect(admin_url('pengaturan'));
    }
    flash('error', 'Periksa kembali isian yang ditandai.');
}

$title = 'Pengaturan';
$nav = 'pengaturan';
require __DIR__ . '/includes/layout-top.php';
?>
<section class="card settings-form">
  <form class="form" method="post" action="<?= e(admin_url('pengaturan')) ?>" novalidate>
    <?= csrf_field() ?>
    <?= field_input('site_name', 'Nama situs', $v['site_name'], $errors, ['required' => true, 'maxlength' => 80]) ?>
    <div class="form-2">
      <?= field_input('tagline_id', 'Tagline grup', $v['tagline_id'], $errors, ['maxlength' => 160]) ?>
      <?= field_input('tagline_en', 'Tagline (English)', $v['tagline_en'], $errors, ['maxlength' => 160]) ?>
    </div>
    <?= field_input('whatsapp_group', 'Nomor WhatsApp cadangan', $v['whatsapp_group'], $errors, ['inputmode' => 'tel', 'placeholder' => '0812xxxxxxxx', 'hint' => 'dipakai hotel yang belum punya nomor WhatsApp sendiri']) ?>
    <div class="btn-row"><button class="btn btn-primary" type="submit">Simpan pengaturan</button></div>
  </form>
</section>
<?php require __DIR__ . '/includes/layout-bottom.php'; ?>
