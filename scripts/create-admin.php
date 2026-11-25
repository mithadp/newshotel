<?php
/**
 * Membuat atau mengubah satu-satunya akun panel admin, dari terminal.
 *   php scripts/create-admin.php --password "kata-sandi-baru"
 *   php scripts/create-admin.php --username admin --email anda@domain.com --name "Admin"
 * Belum ada akun: dibuat (username bawaan: admin). Ada satu akun: akun itu yang diubah.
 * Lebih dari satu: skrip berhenti, hapus yang lain lewat phpMyAdmin (tabel admin_users).
 * Tanpa --password dicetak kata sandi sementara acak yang wajib diganti saat login pertama. Kata sandi
 * kurang dari 10 karakter hanya bisa dipakai masuk dari komputer ini (lihat backend/includes/auth.php).
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    exit("CLI only\n");
}
define('LANG', 'id');
require dirname(__DIR__) . '/shared/init.php';

$args = getopt('', ['username:', 'email:', 'name:', 'password:']);
$admins = db_all('SELECT * FROM admin_users ORDER BY id');
if (count($admins) > 1) {
    fwrite(STDERR, "Ada " . count($admins) . " akun admin, padahal situs ini dirancang untuk satu admin. Hapus yang lain lewat phpMyAdmin (tabel admin_users) lalu jalankan lagi.\n");
    exit(1);
}
$existing = $admins[0] ?? null;

$username = mb_strtolower(trim((string) ($args['username'] ?? ($existing['username'] ?? 'admin'))));
$email = mb_strtolower(trim((string) ($args['email'] ?? ($existing['email'] ?? 'admin@newshotel.local'))));
$name = trim((string) ($args['name'] ?? ($existing['name'] ?? 'Admin'))) ?: 'Admin';

if (!preg_match('/^[a-z0-9._-]{3,60}$/', $username)) {
    fwrite(STDERR, "Username 3-60 karakter: huruf kecil, angka, titik, garis bawah, atau strip.\n");
    exit(1);
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fwrite(STDERR, "Email tidak valid.\n");
    exit(1);
}

$chosen = isset($args['password']) && $args['password'] !== false && $args['password'] !== '';
if ($chosen) {
    $password = (string) $args['password'];
} else {
    $chars = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789';
    $password = '';
    for ($i = 0; $i < 14; $i++) {
        $password .= $chars[random_int(0, strlen($chars) - 1)];
    }
}
$hash = password_hash($password, PASSWORD_DEFAULT);
$mustChange = $chosen ? 0 : 1;   // kata sandi pilihan sendiri tidak dipaksa ganti; yang acak dipaksa

if ($existing) {
    db_query('UPDATE admin_users SET name = ?, username = ?, email = ?, password_hash = ?, role = \'super_admin\', hotel_id = NULL, is_active = 1, must_change_password = ? WHERE id = ?',
        [$name, $username, $email, $hash, $mustChange, $existing['id']]);
    db_query('DELETE FROM login_attempts');
    echo "Akun admin diperbarui.\n";
} else {
    db_query('INSERT INTO admin_users (name, username, email, password_hash, role, hotel_id, is_active, must_change_password) VALUES (?, ?, ?, ?, \'super_admin\', NULL, 1, ?)',
        [$name, $username, $email, $hash, $mustChange]);
    echo "Akun admin dibuat.\n";
}
echo "  Username   : $username\n";
echo "  Email      : $email   (boleh dipakai untuk masuk juga)\n";
echo $chosen
    ? "  Kata sandi : sesuai --password" . (mb_strlen($password) < 10 ? " (kurang dari 10 karakter: hanya bisa dipakai masuk dari komputer ini)" : "") . "\n"
    : "  Kata sandi : $password   (sementara, wajib diganti saat login pertama)\n";
echo "  Login di   : /admin\n";
