<?php
/**
 * Pengaturan situs. Di hosting jangan ubah file ini: buat shared/config.local.php
 * yang mengembalikan kunci yang ingin diganti, misalnya:
 *   <?php return ['debug' => false, 'db' => ['user' => 'u123', 'pass' => 'rahasia']];
 */
$config = [
    // null = otomatis: detail error hanya tampil dari komputer ini (XAMPP). Di hosting set false.
    'debug'    => null,
    // Sajikan file .gz dari scripts/build-assets.php. null = otomatis (hanya Apache); set false di nginx/LiteSpeed.
    'serve_gz' => null,
    'timezone' => 'Asia/Jakarta',

    // URL situs publik, mis. 'https://newshotel.co.id'. Kosongkan di XAMPP; wajib diisi bila admin ada di domain lain.
    'site_url' => '',
    // Folder situs terhadap domain, mis. '/newshotel-v01'. Kosongkan untuk deteksi otomatis.
    'base_path' => null,

    'db' => [
        'host'    => '127.0.0.1',
        'port'    => 3306,
        'name'    => 'newshotel',
        'user'    => 'root',
        'pass'    => '',
        'charset' => 'utf8mb4',
    ],
];

if (is_file(__DIR__ . '/config.local.php')) {
    $config = array_replace_recursive($config, require __DIR__ . '/config.local.php');
}

return $config;
