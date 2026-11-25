<?php
// Ganti kata sandi. Wajib dibuka saat login pertama.
require_once __DIR__ . '/includes/init.php';
$me = require_login(true);

$errors = [];
if (is_post()) {
    csrf_check();
    $current = (string) ($_POST['current_password'] ?? '');
    $new = (string) ($_POST['new_password'] ?? '');
    $confirm = (string) ($_POST['confirm_password'] ?? '');
    if (!password_verify($current, $me['password_hash'])) {
        $errors['current_password'] = 'Kata sandi saat ini salah.';
    }
    if (mb_strlen($new) < 10) {
        $errors['new_password'] = 'Minimal 10 karakter.';
    } elseif ($new === $current) {
        $errors['new_password'] = 'Gunakan kata sandi yang berbeda dari sebelumnya.';
    }
    if ($new !== $confirm) {
        $errors['confirm_password'] = 'Konfirmasi tidak sama.';
    }
    if (!$errors) {
        db_query('UPDATE admin_users SET password_hash = ?, must_change_password = 0 WHERE id = ?', [password_hash($new, PASSWORD_DEFAULT), $me['id']]);
        session_regenerate_id(true);
        flash('success', 'Kata sandi berhasil diganti.');
        redirect(admin_url());
    }
}

$title = 'Akun saya';
$nav = 'akun';
require __DIR__ . '/includes/layout-top.php';
?>
<div class="grid-2">
  <section class="card">
    <div class="card-head"><h2>Ganti kata sandi</h2></div>
    <?php if ((int) $me['must_change_password'] === 1): ?>
      <p class="flash flash--info"><?= icon('info') ?><span>Ini login pertama Anda atau kata sandi baru saja direset. Buat kata sandi baru sebelum melanjutkan.</span></p>
    <?php endif; ?>
    <form class="form narrow" method="post" action="<?= e(admin_url('akun')) ?>" novalidate>
      <?= csrf_field() ?>
      <?= field_input('current_password', 'Kata sandi saat ini', '', $errors, ['type' => 'password', 'required' => true, 'autocomplete' => 'current-password']) ?>
      <?= field_input('new_password', 'Kata sandi baru', '', $errors, ['type' => 'password', 'required' => true, 'autocomplete' => 'new-password', 'hint' => 'minimal 10 karakter']) ?>
      <?= field_input('confirm_password', 'Ulangi kata sandi baru', '', $errors, ['type' => 'password', 'required' => true, 'autocomplete' => 'new-password']) ?>
      <div class="btn-row"><button class="btn btn-primary" type="submit">Simpan kata sandi</button></div>
    </form>
  </section>
  <section class="card">
    <div class="card-head"><h2>Profil</h2></div>
    <dl class="dl">
      <div><dt>Nama</dt><dd><?= e($me['name']) ?></dd></div>
      <div><dt>Username</dt><dd><?= e($me['username'] ?? '—') ?></dd></div>
      <div><dt>Email</dt><dd><?= e($me['email']) ?></dd></div>
      <div><dt>Peran</dt><dd><?= $me['role'] === 'super_admin' ? 'Admin (semua hotel)' : 'Admin hotel' ?></dd></div>
      <div><dt>Login terakhir</dt><dd><?= $me['last_login_at'] ? e(date('d-m-Y H:i', strtotime($me['last_login_at']))) : '—' ?></dd></div>
    </dl>
    <p class="small muted">Nama, username, dan email diubah lewat <code>scripts/create-admin.php</code> di komputer server.</p>
  </section>
</div>
<?php require __DIR__ . '/includes/layout-bottom.php'; ?>
