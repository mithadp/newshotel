<?php
require_once __DIR__ . '/includes/init.php';

if (current_admin()) {
    redirect(admin_url());
}

$login = '';
$error = null;
if (is_post()) {
    csrf_check();
    $login = mb_strtolower(post_str('username', 160));
    $password = (string) ($_POST['password'] ?? '');
    $reason = null;
    if (login_throttled($login)) {
        $error = 'Terlalu banyak percobaan. Coba lagi dalam ' . LOGIN_WINDOW_MINUTES . ' menit.';
    } elseif ($login === '' || $password === '' || !attempt_login($login, $password, $reason)) {
        $error = $reason === 'weak_remote'
            ? 'Kata sandi ini terlalu pendek untuk dipakai dari luar komputer server. Ganti dulu di komputer server (menu Akun saya, minimal ' . ADMIN_MIN_PASSWORD . ' karakter), atau jalankan scripts/create-admin.php.'
            : 'Username atau kata sandi salah.';
    } else {
        $next = (string) ($_GET['next'] ?? '');
        // Hanya izinkan kembali ke halaman di panel admin ini (cegah open redirect).
        $safe = $next !== '' && str_starts_with($next, BASE_URL . '/') && !str_starts_with($next, '//') ? $next : admin_url();
        redirect($safe);
    }
}
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Masuk · Admin <?= e(setting('site_name', 'News Hotel')) ?></title>
<link rel="icon" href="<?= e(asset('img/favicon-32.png')) ?>" type="image/png" sizes="32x32">
<style>@font-face { font-family: "Schibsted Grotesk"; font-style: normal; font-weight: 400 700; font-display: swap; src: url("<?= e(BASE_URL) ?>/assets/fonts/schibsted-grotesk-400-700.woff2") format("woff2"); }</style>
<link rel="stylesheet" href="<?= e(BASE_URL . '/assets/admin.css?v=' . filemtime(BACK . '/assets/admin.css')) ?>">
</head>
<body class="admin admin-auth">
<main class="auth-shell">
  <?php foreach (take_flashes() as [$type, $msg]): ?>
    <p class="flash flash--<?= e($type) ?>" role="status"><?= icon('info') ?><span><?= e($msg) ?></span></p>
  <?php endforeach; ?>
  <div class="auth-panel">
    <?= img('brand/news-hotel', 'News Hotel', '120px', ['class' => 'auth-logo', 'loading' => 'eager']) ?>
    <h1>Panel admin</h1>
    <p class="muted">News Hotel Surabaya · News Hotel Semarang · Darmo Inn</p>
    <form class="form" method="post" action="<?= e(admin_url('login', ['next' => $_GET['next'] ?? null])) ?>" novalidate>
      <?= csrf_field() ?>
      <div class="field">
        <label for="a-user">Username</label>
        <input type="text" id="a-user" name="username" required autocomplete="username" autocapitalize="none" spellcheck="false" value="<?= e($login) ?>"<?= $error ? ' aria-invalid="true" aria-describedby="a-err"' : '' ?> autofocus>
      </div>
      <div class="field">
        <label for="a-pass">Kata sandi</label>
        <input type="password" id="a-pass" name="password" required autocomplete="current-password">
      </div>
      <?php if ($error): ?><p class="field-error" id="a-err" role="alert"><?= icon('alert') ?><?= e($error) ?></p><?php endif; ?>
      <button class="btn btn-primary btn-block" type="submit">Masuk</button>
    </form>
    <a class="muted small" href="<?= e(url()) ?>">← Kembali ke situs</a>
  </div>
</main>
</body>
</html>
