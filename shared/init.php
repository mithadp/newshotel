<?php
/** Dimuat pertama oleh frontend/includes/init.php dan backend/includes/init.php. */
declare(strict_types=1);

define('ROOT', dirname(__DIR__));
define('SHARED', ROOT . '/shared');
define('FRONT', ROOT . '/frontend');
define('BACK', ROOT . '/backend');

require SHARED . '/functions.php';
require SHARED . '/db.php';

date_default_timezone_set((string) config('timezone', 'Asia/Jakarta'));
error_reporting(E_ALL);
ini_set('display_errors', is_debug() ? '1' : '0');

/** Sisi yang dibuka: 'front' atau 'back'; skrip terminal dianggap situs publik. */
if (!defined('SIDE')) {
    define('SIDE', 'front');
}

/** Alamat folder yang sedang dibuka tamu (frontend atau backend) terhadap domain: "" di hosting, "/newshotel-v01/frontend" di XAMPP. */
define('BASE_URL', detect_base_path());
/** Alamat situs publik. Dari frontend sama dengan BASE_URL; dari backend diambil dari config 'site_url' atau folder frontend. */
define('PUBLIC_URL', detect_public_url());

/** Bahasa halaman publik: "en" jika URL diawali /en/ (lihat frontend/.htaccess) atau ?lang=en, selain itu "id". */
if (!defined('LANG')) {
    define('LANG', str_starts_with((string) ($_GET['lang'] ?? ''), 'en') ? 'en' : 'id');
}

// Kesalahan tak terduga (mis. database mati): tampilkan halaman 500.
set_exception_handler(static function (Throwable $e): void {
    error_log((string) $e);
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: text/html; charset=utf-8');
    }
    $detail = is_debug() ? '<pre style="white-space:pre-wrap;font:13px/1.5 monospace">' . e((string) $e) . '</pre>' : '';
    echo '<!doctype html><html lang="' . e(LANG) . '"><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">'
        . '<title>' . e(t('error.500_title')) . '</title>'
        . '<body style="font:16px/1.6 system-ui,sans-serif;max-width:640px;margin:12vh auto;padding:0 20px;color:#1C2024">'
        . '<h1 style="font-weight:400">' . e(t('error.500_title')) . '</h1><p>' . e(t('error.500_body')) . '</p>' . $detail . '</body></html>';
});

header_remove('X-Powered-By');

// Kompres HTML di PHP (XAMPP tidak mengaktifkan mod_deflate).
if (!ini_get('zlib.output_compression') && extension_loaded('zlib') && !headers_sent()) {
    ini_set('zlib.output_compression', '1');
}
