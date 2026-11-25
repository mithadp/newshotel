<?php
// Hanya lewat POST (tombol "Keluar" di menu).
require_once __DIR__ . '/includes/init.php';

if (!is_post()) {
    redirect(admin_url());
}
csrf_check();
logout();
flash('success', 'Anda sudah keluar.');
redirect(admin_url('login'));
