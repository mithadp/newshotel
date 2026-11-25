<?php
declare(strict_types=1);

const ADMIN_IDLE_SECONDS = 2 * 3600;   // keluar otomatis setelah 2 jam tidak aktif
const LOGIN_MAX_ATTEMPTS = 5;           // dalam LOGIN_WINDOW_MINUTES
const LOGIN_WINDOW_MINUTES = 15;

/** Alamat halaman admin: admin_url('kamar/form', ['id' => 3]) → /kamar/form?id=3 (di XAMPP diawali /newshotel-v01/backend) */
function admin_url(string $path = '', array $query = []): string
{
    $path = trim($path, '/');
    $out = BASE_URL . '/' . $path;
    $query = array_filter($query, static fn ($v) => $v !== null && $v !== '');
    return $query ? $out . '?' . http_build_query($query) : $out;
}

function redirect(string $to): never
{
    header('Location: ' . $to, true, 303);
    exit;
}

/** Halaman galat sederhana (400, 403, 404) lalu berhenti. */
function admin_abort(int $code, string $message): never
{
    http_response_code($code);
    $title = match ($code) { 400 => 'Formulir kedaluwarsa', 403 => 'Akses ditolak', 404 => 'Tidak ditemukan', default => 'Galat' };
    echo '<!doctype html><html lang="id"><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">'
        . '<meta name="robots" content="noindex"><title>' . e($title) . '</title>'
        . '<body style="font:15px/1.6 system-ui,sans-serif;max-width:560px;margin:14vh auto;padding:0 20px;color:#1C2024">'
        . '<p style="font-size:3rem;margin:0;color:#D63E00">' . $code . '</p><h1 style="font-weight:600;font-size:1.4rem">' . e($title) . '</h1>'
        . '<p>' . e($message) . '</p><p><a href="' . e(admin_url()) . '">← Kembali ke dasbor</a></p></body></html>';
    exit;
}

function admin_session_start(): void
{
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    session_name('nh_admin');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => BASE_URL === '' ? '/' : BASE_URL,
        'secure'   => $https,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    ini_set('session.use_strict_mode', '1');
    session_start();

    $now = time();
    if (isset($_SESSION['admin_id'], $_SESSION['last_seen']) && $now - (int) $_SESSION['last_seen'] > ADMIN_IDLE_SECONDS) {
        $_SESSION = [];
        session_regenerate_id(true);
        flash('info', 'Sesi berakhir karena lama tidak aktif. Silakan masuk lagi.');
    }
    $_SESSION['last_seen'] = $now;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . e(csrf_token()) . '">';
}

/** Semua POST wajib membawa token yang cocok; jika tidak, hentikan dengan 400. */
function csrf_check(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }
    $sent = (string) ($_POST['_token'] ?? '');
    if ($sent === '' || !hash_equals((string) ($_SESSION['csrf'] ?? ''), $sent)) {
        admin_abort(400, 'Formulir kedaluwarsa atau tidak sah. Muat ulang halaman lalu coba lagi.');
    }
}

function is_post(): bool
{
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = [$type, $message];
}

/** @return array<int, array{0:string,1:string}> */
function take_flashes(): array
{
    $f = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $f;
}

function client_ip(): string
{
    return substr((string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'), 0, 45);
}

function login_throttled(string $email): bool
{
    $since = date('Y-m-d H:i:s', time() - LOGIN_WINDOW_MINUTES * 60);
    $byIp = (int) db_value('SELECT COUNT(*) FROM login_attempts WHERE ip = ? AND attempted_at >= ?', [client_ip(), $since]);
    $byEmail = (int) db_value('SELECT COUNT(*) FROM login_attempts WHERE email = ? AND attempted_at >= ?', [$email, $since]);
    return $byIp >= LOGIN_MAX_ATTEMPTS * 3 || $byEmail >= LOGIN_MAX_ATTEMPTS;
}

/** Panjang minimum kata sandi yang boleh dipakai dari luar komputer ini (sama dengan aturan di halaman Akun). */
const ADMIN_MIN_PASSWORD = 10;

/**
 * Cek username (atau email) + kata sandi. Mengembalikan baris admin jika cocok dan aktif, atau null.
 * $reason diisi 'weak_remote' bila kata sandi benar tetapi terlalu pendek untuk dipakai dari luar komputer ini.
 */
function attempt_login(string $login, string $password, ?string &$reason = null): ?array
{
    $login = mb_strtolower(trim($login));
    $admin = db_one('SELECT * FROM admin_users WHERE (username = ? OR email = ?) AND is_active = 1', [$login, $login]);
    // Tetap jalankan password_verify walau akun tidak ada, agar waktu respons tidak membocorkan akun terdaftar.
    $hash = $admin['password_hash'] ?? password_hash(random_bytes(12), PASSWORD_DEFAULT);
    $ok = password_verify($password, $hash) && $admin !== null;
    if (!$ok) {
        db_query('INSERT INTO login_attempts (ip, email, attempted_at) VALUES (?, ?, NOW())', [client_ip(), substr($login, 0, 160)]);
        return null;
    }
    // Kata sandi pendek (mis. "admin") hanya aman di komputer sendiri. Dari internet ia ditolak sampai diganti.
    if (mb_strlen($password) < ADMIN_MIN_PASSWORD && !is_local_request()) {
        $reason = 'weak_remote';
        return null;
    }
    db_query('DELETE FROM login_attempts WHERE email = ?', [substr($login, 0, 160)]);
    if (password_needs_rehash($admin['password_hash'], PASSWORD_DEFAULT)) {
        db_query('UPDATE admin_users SET password_hash = ? WHERE id = ?', [password_hash($password, PASSWORD_DEFAULT), $admin['id']]);
    }
    db_query('UPDATE admin_users SET last_login_at = NOW() WHERE id = ?', [$admin['id']]);
    session_regenerate_id(true);
    $_SESSION['admin_id'] = (int) $admin['id'];
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $admin;
}

function logout(): void
{
    $_SESSION = [];
    session_regenerate_id(true);
}

function current_admin(): ?array
{
    static $admin = false;
    if ($admin === false) {
        $id = (int) ($_SESSION['admin_id'] ?? 0);
        $admin = $id ? db_one('SELECT * FROM admin_users WHERE id = ? AND is_active = 1', [$id]) : null;
    }
    return $admin;
}

/** Wajib masuk. Pengguna yang wajib ganti kata sandi hanya boleh membuka halaman akun. */
function require_login(bool $allowPasswordChange = false): array
{
    $admin = current_admin();
    if (!$admin) {
        $next = (string) ($_SERVER['REQUEST_URI'] ?? '');
        redirect(admin_url('login', ['next' => str_starts_with($next, BASE_URL . '/') ? $next : null]));
    }
    if ((int) $admin['must_change_password'] === 1 && !$allowPasswordChange) {
        flash('info', 'Demi keamanan, ganti kata sandi Anda terlebih dahulu.');
        redirect(admin_url('akun'));
    }
    return $admin;
}

function is_super(): bool
{
    return (current_admin()['role'] ?? '') === 'super_admin';
}

function require_super(): void
{
    if (!is_super()) {
        admin_abort(403, 'Halaman ini hanya untuk super admin.');
    }
}

/** ID hotel yang boleh diurus admin ini: null = semua hotel (super admin). */
function admin_hotel_scope(): ?int
{
    $a = current_admin();
    if (!$a || $a['role'] === 'super_admin') {
        return null;
    }
    return (int) $a['hotel_id'];
}

function can_hotel(?int $hotelId): bool
{
    $scope = admin_hotel_scope();
    return $scope === null || ($hotelId !== null && $hotelId === $scope);
}

/** Hentikan dengan 403 jika data milik hotel lain. */
function assert_hotel(?int $hotelId): void
{
    if (!can_hotel($hotelId)) {
        admin_abort(403, 'Data ini milik hotel lain dan tidak bisa Anda buka.');
    }
}

/** Hotel yang boleh diurus admin ini (semua untuk super admin). */
function admin_hotels(): array
{
    $scope = admin_hotel_scope();
    $all = db_all('SELECT id, slug, name, short_name, brand FROM hotels ORDER BY sort, id');
    return $scope === null ? $all : array_values(array_filter($all, static fn ($h) => (int) $h['id'] === $scope));
}

/** Hotel yang dipilih lewat ?hotel= (dibatasi lingkup admin). Null = semua hotel yang boleh. */
function selected_hotel_id(): ?int
{
    $scope = admin_hotel_scope();
    if ($scope !== null) {
        return $scope;
    }
    $id = (int) ($_GET['hotel'] ?? 0);
    return $id > 0 ? $id : null;
}
