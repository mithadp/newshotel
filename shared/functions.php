<?php
declare(strict_types=1);

/** Mode debug: config 'debug' menang; bila null, aktif hanya untuk permintaan dari komputer ini. */
function is_debug(): bool
{
    $v = config('debug');
    return $v !== null ? (bool) $v : is_local_request();
}

/** True bila permintaan berasal dari komputer ini: alamat loopback dan tanpa header proxy (di belakang reverse proxy semua pengunjung tampak 127.0.0.1). */
function is_local_request(): bool
{
    if (!in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true)) {
        return false;
    }
    foreach (['HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED_HOST', 'HTTP_X_REAL_IP', 'HTTP_FORWARDED', 'HTTP_CF_CONNECTING_IP'] as $h) {
        if (!empty($_SERVER[$h])) {
            return false;
        }
    }
    return true;
}

/** Baca pengaturan dari shared/config.php; kunci bertingkat pakai titik: config('db.name'). */
function config(string $key, mixed $default = null): mixed
{
    static $config = null;
    $config ??= require SHARED . '/config.php';
    $value = $config;
    foreach (explode('.', $key) as $part) {
        if (!is_array($value) || !array_key_exists($part, $value)) {
            return $default;
        }
        $value = $value[$part];
    }
    return $value;
}

/** Pengaturan dari panel admin (tabel settings), mis. setting('whatsapp_group'). Di-cache per request. */
function setting(string $key, mixed $default = null): mixed
{
    static $cache = null;
    if ($cache === null) {
        try {
            $cache = array_column(db_all('SELECT `key`, `value` FROM settings'), 'value', 'key');
        } catch (Throwable) {
            $cache = [];
        }
    }
    return $cache[$key] ?? $default;
}

function is_en(): bool
{
    return LANG === 'en';
}

/** Teks antarmuka dari lang/id.php atau lang/en.php. Kunci yang tidak ada di en.php memakai versi Indonesia. */
function t(string $key, array $replace = []): string
{
    static $id = null, $en = null;
    $id ??= require SHARED . '/lang/id.php';
    if (LANG === 'en') {
        $en ??= require SHARED . '/lang/en.php';
        $s = $en[$key] ?? $id[$key] ?? $key;
    } else {
        $s = $id[$key] ?? $key;
    }
    foreach ($replace as $k => $v) {
        $s = str_replace(':' . $k, (string) $v, $s);
    }
    return $s;
}

/** Pilih teks langsung di halaman: tx('Kamar berbalkon', 'Balcony rooms'). */
function tx(string $id, string $en): string
{
    return LANG === 'en' ? $en : $id;
}

/** Kolom database sesuai bahasa: memakai kolom "{field}_en" jika versi Inggris terisi. */
function tr(?array $row, string $field): string
{
    if (!$row) {
        return '';
    }
    if (LANG === 'en' && !empty($row[$field . '_en'])) {
        return (string) $row[$field . '_en'];
    }
    return (string) ($row[$field] ?? '');
}

/** Folder situs relatif terhadap domain: "" di hosting, "/newshotel-v01" di XAMPP. */
function detect_base_path(): string
{
    $configured = config('base_path');
    if ($configured !== null) {
        return rtrim((string) $configured, '/');
    }
    return path_under_docroot(SIDE === 'back' ? BACK : FRONT);
}

/** Alamat sebuah folder terhadap DOCUMENT_ROOT: "" bila folder itu adalah root domain (hosting), "/a/b" bila di dalamnya (XAMPP). */
function path_under_docroot(string $dir): string
{
    $docRoot = realpath((string) ($_SERVER['DOCUMENT_ROOT'] ?? ''));
    $real = realpath($dir);
    if (!$docRoot || !$real) {
        return '';
    }
    $docRoot = rtrim(str_replace('\\', '/', $docRoot), '/');
    $real = rtrim(str_replace('\\', '/', $real), '/');
    if (stripos($real, $docRoot) !== 0) {
        return '';
    }
    $base = substr($real, strlen($docRoot));
    return $base === '' ? '' : '/' . trim($base, '/');
}

/** Alamat situs publik (gambar, CSS, tautan "Lihat situs" di admin). Frontend: BASE_URL; backend: config 'site_url', atau folder frontend lokal. */
function detect_public_url(): string
{
    if (SIDE === 'front') {
        return BASE_URL;
    }
    $configured = (string) config('site_url', '');
    return $configured !== '' ? rtrim($configured, '/') : path_under_docroot(FRONT);
}

/** Skema + domain, mis. "http://localhost". */
function site_origin(): string
{
    $configured = (string) config('site_url', '');
    if ($configured !== '') {
        return rtrim($configured, '/');
    }
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    $host = preg_replace('/[^A-Za-z0-9.\-:\[\]]/', '', (string) ($_SERVER['HTTP_HOST'] ?? 'localhost'));
    return ($https ? 'https' : 'http') . '://' . $host;
}

/** Alamat halaman di situs publik, dengan awalan /en untuk versi Inggris. Contoh: url('blog', ['kategori' => 'promo']). */
function url(string $path = '', array $query = [], ?string $lang = null): string
{
    $lang ??= LANG;
    $path = trim($path, '/');
    $prefix = $lang === 'en' ? '/en' : '';
    if ($path === '') {
        $out = PUBLIC_URL . ($prefix !== '' ? $prefix : '/');
    } else {
        $out = PUBLIC_URL . $prefix . '/' . $path;
    }
    $query = array_filter($query, static fn ($v) => $v !== null && $v !== '' && $v !== false);
    return $query ? $out . '?' . http_build_query($query) : $out;
}

function abs_url(string $path = '', array $query = [], ?string $lang = null): string
{
    return site_origin() . url($path, $query, $lang);
}

/** Path halaman yang sedang dibuka tanpa folder situs dan awalan /en (mis. "surabaya/kamar"). Halaman dapat menetapkannya sendiri. */
function page_path(?string $set = null): string
{
    static $path = null;
    if ($set !== null) {
        $path = trim($set, '/');
    }
    if ($path !== null) {
        return $path;
    }
    $p = rawurldecode((string) (parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH) ?: '/'));
    if (BASE_URL !== '' && str_starts_with($p, BASE_URL)) {
        $p = substr($p, strlen(BASE_URL));
    }
    $p = trim($p, '/');
    if ($p === 'en') {
        $p = '';
    } elseif (str_starts_with($p, 'en/')) {
        $p = substr($p, 3);
    }
    $p = (string) preg_replace('~(^|/)index\.php$~', '', $p);
    $p = (string) preg_replace('~\.php$~', '', $p);
    return $path = trim($p, '/');
}

/** Query string yang layak dibawa ke URL lain (canonical, hreflang, pengalih bahasa); parameter pelacakan tidak ikut. */
function page_query(): array
{
    $q = array_intersect_key($_GET, ['kategori' => 1, 'hotel' => 1, 'hal' => 1]);
    return array_filter($q, static fn ($v) => is_string($v) && $v !== '');
}

/** Halaman yang sama dalam bahasa lain (pengalih ID/EN, hreflang). */
function lang_url(string $lang, bool $absolute = false): string
{
    return $absolute ? abs_url(page_path(), page_query(), $lang) : url(page_path(), page_query(), $lang);
}

/** URL file di assets/ dengan versi (cache busting) dan varian .gz bila tersedia. Hasilnya di-cache per proses. */
function asset(string $path): string
{
    static $cache = [], $gz = null;
    $path = ltrim($path, '/');
    if (isset($cache[$path])) {
        return $cache[$path];
    }
    // .gz hanya dipakai bila server mengirim Content-Encoding-nya (aturan di frontend/.htaccess); atur lewat config 'serve_gz'.
    $gz ??= config('serve_gz') !== null ? (bool) config('serve_gz') : str_contains((string) ($_SERVER['SERVER_SOFTWARE'] ?? ''), 'Apache');
    $file = FRONT . '/assets/' . $path;
    $mtime = is_file($file) ? filemtime($file) : 0;
    $served = $path;
    if ($gz && $mtime && preg_match('/\.(css|js|svg)$/', $path) && is_file($file . '.gz') && filemtime($file . '.gz') >= $mtime) {
        $served .= '.gz';
    }
    return $cache[$path] = PUBLIC_URL . '/assets/' . $served . ($mtime ? '?v=' . $mtime : '');
}

function e(mixed $value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function money(int|float|string|null $amount): string
{
    if ($amount === null || $amount === '') {
        return '';
    }
    return 'Rp' . number_format((float) $amount, 0, ',', '.');
}

function month_names(): array
{
    return LANG === 'en'
        ? ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December']
        : ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
}

/** "21 November 2026" */
function fdate(?string $date): string
{
    $ts = $date ? strtotime($date) : false;
    if ($ts === false) {
        return '';
    }
    return (int) date('j', $ts) . ' ' . month_names()[(int) date('n', $ts) - 1] . ' ' . date('Y', $ts);
}

function excerpt(?string $text, int $len = 160): string
{
    $plain = trim((string) preg_replace('/\s+/', ' ', strip_tags((string) $text)));
    $plain = preg_replace('/[#*_>`\[\]]/', '', $plain) ?? $plain;
    return mb_strlen($plain) > $len ? rtrim(mb_substr($plain, 0, $len - 1)) . '…' : $plain;
}

/** Markdown → HTML aman (Parsedown safe mode). */
function markdown(?string $text): string
{
    require_once SHARED . '/lib/Parsedown.php';
    $pd = new Parsedown();
    $pd->setSafeMode(true);
    return $pd->text((string) $text);
}

/** "17 m²" tanpa nol di belakang koma. */
function size_text(mixed $m2): string
{
    return $m2 === null || $m2 === '' ? '' : rtrim(rtrim((string) $m2, '0'), '.') . ' m²';
}

/** Daftar gambar teroptimasi: "folder/nama" => {w: [...], width, height}. */
function image_manifest(): array
{
    static $m = null;
    if ($m === null) {
        $f = FRONT . '/assets/img/manifest.json';
        $m = is_file($f) ? (json_decode((string) file_get_contents($f), true) ?: []) : [];
    }
    return $m;
}

/** <img> responsif untuk kunci gambar ("surabaya/lobby"). $sizes = atribut sizes; 'defer' => true menunda pemuatan lewat JS. */
function img(?string $key, string $alt, string $sizes = '100vw', array $attrs = []): string
{
    if (!$key) {
        return '<span class="img-missing" role="img" aria-label="' . e($alt) . '"><span>' . e(t('placeholder.photo')) . '</span></span>';
    }
    $m = image_manifest()[$key] ?? null;
    $defer = !empty($attrs['defer']);
    unset($attrs['defer']);
    $attrs += ['loading' => 'lazy', 'decoding' => 'async'];
    if ($m) {
        $srcset = implode(', ', array_map(static fn ($w) => asset("img/$key-$w.webp") . " {$w}w", $m['w']));
        $mid = $m['w'][min(1, count($m['w']) - 1)];
        $attrs = ['src' => asset("img/$key-$mid.webp"), 'srcset' => $srcset, 'sizes' => $sizes, 'width' => $m['width'], 'height' => $m['height']] + $attrs;
    } else {
        $attrs = ['src' => asset(ltrim($key, '/'))] + $attrs;
    }
    if ($defer) {
        foreach (['src', 'srcset'] as $k) {
            if (isset($attrs[$k])) {
                $attrs['data-' . $k] = $attrs[$k];
                unset($attrs[$k]);
            }
        }
    }
    $html = '<img alt="' . e($alt) . '"';
    foreach ($attrs as $k => $v) {
        if ($v === false || $v === null) {
            continue;
        }
        $html .= $v === true ? ' ' . e($k) : ' ' . e($k) . '="' . e($v) . '"';
    }
    return $html . '>';
}

/**
 * Seperti img(), tetapi foto hanya diunduh di layar ≥ $minWidth px; di bawahnya <img> memakai GIF 1×1.
 * 'defer' => true menaruh srcset di <source data-srcset>, diaktifkan hydrate() di app.js.
 */
function img_desktop(?string $key, string $alt, string $sizes = '100vw', array $attrs = [], int $minWidth = 861): string
{
    $m = $key ? (image_manifest()[$key] ?? null) : null;
    if (!$m) {
        return img($key, $alt, $sizes, $attrs);
    }
    $defer = !empty($attrs['defer']);
    unset($attrs['defer']);
    $attrs += ['loading' => 'lazy', 'decoding' => 'async'];
    $srcset = implode(', ', array_map(static fn ($w) => asset("img/$key-$w.webp") . " {$w}w", $m['w']));
    $attrs = ['src' => 'data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==', 'width' => $m['width'], 'height' => $m['height']] + $attrs;
    $html = '<picture><source media="(min-width: ' . $minWidth . 'px)" ' . ($defer ? 'data-srcset' : 'srcset') . '="' . e($srcset) . '" sizes="' . e($sizes) . '">'
        . '<img alt="' . e($alt) . '"';
    foreach ($attrs as $k => $v) {
        if ($v === false || $v === null) {
            continue;
        }
        $html .= $v === true ? ' ' . e($k) : ' ' . e($k) . '="' . e($v) . '"';
    }
    return $html . '></picture>';
}

/** Lebar gambar yang boleh dipakai: yang ≤ $maxWidth (minimal satu, yang terkecil). $maxWidth 0 = semua. */
function capped_widths(array $widths, int $maxWidth): array
{
    if ($maxWidth <= 0) {
        return array_values($widths);
    }
    $kept = array_values(array_filter($widths, static fn ($w) => $w <= $maxWidth));
    return $kept ?: [min($widths)];
}

/** <picture> khusus ponsel (≤860px) dengan <source> bermedia, jadi desktop tidak mengunduh apa pun. Pasangannya: preload_img($key, '100vw', '(max-width: 860px)', $maxWidth). */
function img_phone(?string $key, string $alt = '', int $maxWidth = 720, array $attrs = []): string
{
    $m = $key ? (image_manifest()[$key] ?? null) : null;
    if (!$m) {
        return '';
    }
    $srcset = implode(', ', array_map(static fn ($w) => asset("img/$key-$w.webp") . " {$w}w", capped_widths($m['w'], $maxWidth)));
    $attrs += ['loading' => 'eager', 'fetchpriority' => 'high', 'decoding' => 'async'];
    $attrs = ['src' => 'data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==', 'width' => $m['width'], 'height' => $m['height']] + $attrs;
    $html = '<picture><source media="(max-width: 860px)" srcset="' . e($srcset) . '" sizes="100vw"><img alt="' . e($alt) . '"';
    foreach ($attrs as $k => $v) {
        if ($v !== false && $v !== null) {
            $html .= ' ' . e($k) . '="' . e($v) . '"';
        }
    }
    return $html . '></picture>';
}

/** <link rel=preload> untuk gambar hero (LCP). $media membatasi preload ke lebar layar tertentu, mis. "(min-width: 861px)". */
function preload_img(?string $key, string $sizes = '100vw', string $media = '', int $maxWidth = 0): string
{
    $m = $key ? (image_manifest()[$key] ?? null) : null;
    if (!$m) {
        return '';
    }
    $widths = capped_widths($m['w'], $maxWidth);
    $srcset = implode(', ', array_map(static fn ($w) => asset("img/$key-$w.webp") . " {$w}w", $widths));
    $mid = $widths[min(1, count($widths) - 1)];
    return '<link rel="preload" as="image" type="image/webp" fetchpriority="high"' . ($media !== '' ? ' media="' . e($media) . '"' : '')
        . ' href="' . e(asset("img/$key-$mid.webp")) . '" imagesrcset="' . e($srcset) . '" imagesizes="' . e($sizes) . '">';
}

/** URL absolut gambar (varian terbesar ≤ $preferWidth), untuk og:image dan lightbox. */
function img_url(?string $key, int $preferWidth = 1600): string
{
    if (!$key) {
        return '';
    }
    $m = image_manifest()[$key] ?? null;
    if (!$m) {
        return site_origin() . asset(ltrim($key, '/'));
    }
    $w = $m['w'][0];
    foreach ($m['w'] as $cand) {
        if ($cand <= $preferWidth) {
            $w = $cand;
        }
    }
    $src = asset("img/$key-$w.webp");
    return str_starts_with($src, 'http') ? $src : site_origin() . $src;
}

function icon(string $name, string $class = 'icon'): string
{
    static $sprite = null;
    // Sprite ikon harus satu domain dengan halaman, jadi panel admin memakai salinannya sendiri (backend/assets/icons.svg).
    $sprite ??= SIDE === 'back'
        ? e(BASE_URL . '/assets/icons.svg?v=' . (int) @filemtime(BACK . '/assets/icons.svg'))
        : e(asset('img/icons.svg'));
    return '<svg class="' . e($class) . '" aria-hidden="true" focusable="false"><use href="' . $sprite . '#' . e($name) . '"></use></svg>';
}

function hotel_address(array $h): string
{
    return trim(implode(', ', array_filter([$h['address'], $h['city'], $h['province'], $h['postal_code']])), ', ');
}

function maps_query(array $h): string
{
    if ($h['lat'] !== null && $h['lng'] !== null) {
        return $h['lat'] . ',' . $h['lng'];
    }
    return $h['name'] . ', ' . hotel_address($h);
}

/** "2 dewasa · 1 anak" */
function room_capacity(array $room): string
{
    if ((int) $room['max_adults'] < 1) {
        return '';
    }
    $s = t('room.adults_n', ['n' => $room['max_adults']]);
    if ((int) $room['max_children'] > 0) {
        $s .= ' · ' . t((int) $room['max_children'] === 1 ? 'room.child_1' : 'room.children_n', ['n' => $room['max_children']]);
    }
    return $s;
}

/** Daftar fasilitas kamar dari kolom JSON room_types.amenities. */
function room_amenities(array $room): array
{
    return json_decode((string) ($room['amenities'] ?? '[]'), true) ?: [];
}

/** Nomor WA hotel, atau nomor WA grup dari config, atau null. */
function wa_number(?array $hotel): ?string
{
    $n = trim((string) ($hotel['whatsapp'] ?? ''));
    if ($n === '') {
        $n = trim((string) setting('whatsapp_group', ''));
    }
    return $n !== '' ? $n : null;
}

/** Tautan wa.me; nomor 08xx otomatis menjadi 628xx. */
function wa_link(?string $number, string $text = ''): ?string
{
    if (!$number) {
        return null;
    }
    $n = preg_replace('/\D+/', '', $number) ?? '';
    if (str_starts_with($n, '0')) {
        $n = '62' . substr($n, 1);
    }
    return 'https://wa.me/' . $n . ($text !== '' ? '?text=' . rawurlencode($text) : '');
}

function tel_link(?string $number): ?string
{
    return $number ? 'tel:' . preg_replace('/[^0-9+]/', '', $number) : null;
}

/** Pesan WA yang sudah terisi. $topic: stay | room | meeting | post. */
function wa_text(array $hotel, string $topic = 'stay', string $subject = ''): string
{
    $name = $hotel['name'];
    return match ($topic) {
        'room'    => tx("Halo $name, saya ingin menanyakan ketersediaan dan tarif kamar $subject.", "Hello $name, I would like to ask about availability and rates for the $subject room."),
        'meeting' => tx("Halo $name, saya ingin menanyakan ruang meeting dan paket acara.", "Hello $name, I would like to ask about meeting rooms and event packages."),
        'post'    => tx("Halo $name, saya membaca \"$subject\" di situs News Hotel dan ingin bertanya.", "Hello $name, I read \"$subject\" on the News Hotel website and have a question."),
        default   => tx("Halo $name, saya ingin menanyakan ketersediaan kamar.", "Hello $name, I would like to ask about room availability."),
    };
}

/** True bila ada artikel promo yang sudah terbit (menu Promo hanya muncul bila ada isinya). */
function has_promo(): bool
{
    static $has = null;
    return $has ??= post_count(null, 'promo') > 0;
}

/** Bagian opsional di beranda hotel yang benar-benar punya isi: dipakai sub-menu agar tidak menautkan jangkar kosong. */
function hotel_sections(array $hotel): array
{
    static $cache = [];
    $id = (int) $hotel['id'];
    return $cache[$id] ??= [
        'facilities' => (int) db_value('SELECT COUNT(*) FROM facilities WHERE hotel_id = ?', [$id]) > 0,
        'dining'     => (int) db_value('SELECT COUNT(*) FROM dining_outlets WHERE hotel_id = ? AND is_active = 1', [$id]) > 0,
        'meeting'    => (int) db_value('SELECT COUNT(*) FROM meeting_venues WHERE hotel_id = ? AND is_active = 1', [$id]) > 0,
    ];
}

/** Data terstruktur (schema.org) satu hotel. */
function hotel_schema(array $h): array
{
    $address = array_filter([
        '@type' => 'PostalAddress', 'streetAddress' => $h['address'], 'addressLocality' => $h['city'],
        'addressRegion' => $h['province'], 'postalCode' => $h['postal_code'], 'addressCountry' => 'ID',
    ], static fn ($v) => $v !== null && $v !== '');
    $schema = [
        '@context' => 'https://schema.org', '@type' => 'Hotel', 'name' => $h['name'],
        'url' => abs_url($h['slug']), 'image' => img_url($h['hero_image']),
        'description' => excerpt(tr($h, 'description'), 300),
        'telephone' => $h['phone'] ?: null, 'email' => $h['email'] ?: null,
        'address' => $address,
        'geo' => ($h['lat'] !== null && $h['lng'] !== null) ? ['@type' => 'GeoCoordinates', 'latitude' => (float) $h['lat'], 'longitude' => (float) $h['lng']] : null,
        'sameAs' => $h['instagram'] ? ['https://www.instagram.com/' . $h['instagram'] . '/'] : null,
        'starRating' => $h['star'] ? ['@type' => 'Rating', 'ratingValue' => (int) $h['star']] : null,
        'priceRange' => $h['from_price'] ? money($h['from_price']) . '+' : null,
        'numberOfRooms' => $h['total_rooms'] ? (int) $h['total_rooms'] : null,
    ];
    return array_filter($schema, static fn ($v) => $v !== null && $v !== '');
}

/** BreadcrumbList (schema.org) dari [[label, url|null], ...]; item terakhir (halaman ini) tanpa tautan. */
function breadcrumb_schema(array $crumbs): array
{
    $items = [];
    foreach (array_values($crumbs) as $i => [$label, $href]) {
        $items[] = array_filter(['@type' => 'ListItem', 'position' => $i + 1, 'name' => $label, 'item' => $href ? site_origin() . $href : null]);
    }
    return ['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => $items];
}

/** Lebar x tinggi gambar yang dipilih img_url() untuk $preferWidth (untuk og:image:width/height). */
function img_size(?string $key, int $preferWidth = 1600): ?array
{
    $m = $key ? (image_manifest()[$key] ?? null) : null;
    if (!$m) {
        return null;
    }
    $w = $m['w'][0];
    foreach ($m['w'] as $cand) {
        if ($cand <= $preferWidth) {
            $w = $cand;
        }
    }
    return [$w, (int) round($w * $m['height'] / $m['width'])];
}

/** Teks yang menyebut WhatsApp: kunci "x" dipakai bila ada nomor WhatsApp, selain itu kunci "x_call" (tanpa janji WhatsApp). */
function tw(string $key, array $replace = []): string
{
    return t($key . (any_whatsapp() ? '' : '_call'), $replace);
}

/** True bila minimal satu hotel punya nomor WhatsApp. Menentukan label tombol grup: "Chat WhatsApp" atau "Hubungi hotel". */
function any_whatsapp(): bool
{
    foreach (hotels() as $h) {
        if (wa_number($h)) {
            return true;
        }
    }
    return false;
}

/**
 * Pecah teks pengantar menjadi kalimat pertama dan sisanya (untuk "Baca selengkapnya").
 * Kalimat pertama minimal 40 karakter agar singkatan tidak memotong di tempat yang salah.
 * @return array{0:string,1:string}
 */
function split_lead(string $text): array
{
    $text = trim($text);
    if (preg_match('/^(.{40,}?[.!?])\s+(.+)$/su', $text, $m)) {
        return [$m[1], $m[2]];
    }
    return [$text, ''];
}

/** Tautan petunjuk arah Google Maps ke hotel (lat/lng, atau nama + alamat). */
function directions_url(array $hotel): string
{
    return 'https://www.google.com/maps/dir/?api=1&destination=' . rawurlencode(maps_query($hotel));
}

/**
 * Cara menghubungi hotel, dari yang terbaik: WhatsApp → telepon → petunjuk arah.
 * 'dir' bukan cara memesan; untuk pertanyaan "bisa dihubungi langsung?" pakai has_contact().
 * @return array{kind:string, href:string, icon:string, label:string}
 */
function contact_action(array $hotel, string $topic = 'stay', string $subject = ''): array
{
    if ($n = wa_number($hotel)) {
        return ['kind' => 'wa', 'href' => wa_link($n, wa_text($hotel, $topic, $subject)), 'icon' => 'whatsapp', 'label' => t('wa.chat')];
    }
    if (!empty($hotel['phone'])) {
        return ['kind' => 'tel', 'href' => tel_link($hotel['phone']), 'icon' => 'phone', 'label' => t('wa.call_n', ['n' => $hotel['phone']])];
    }
    return ['kind' => 'dir', 'href' => directions_url($hotel), 'icon' => 'pin', 'label' => t('hotel.directions')];
}

/** True bila tamu bisa menghubungi hotel langsung (WhatsApp atau telepon). */
function has_contact(array $hotel): bool
{
    return in_array(contact_action($hotel)['kind'], ['wa', 'tel'], true);
}

/** Hotel dengan kontak langsung lebih dulu; urutan aslinya dipertahankan di tiap kelompok (usort stabil). */
function hotels_by_contact(): array
{
    $list = hotels();
    usort($list, static fn (array $a, array $b): int => (int) has_contact($b) <=> (int) has_contact($a));
    return $list;
}

/** Tombol ajakan reservasi. $label dipakai bila WhatsApp tersedia; selain itu label mengikuti cara kontak yang ada (telepon atau "Petunjuk arah"). */
function wa_button(array $hotel, string $topic = 'stay', string $subject = '', string $class = 'btn btn-primary', ?string $label = null): string
{
    $a = contact_action($hotel, $topic, $subject);
    $text = $a['kind'] === 'wa' ? ($label ?? $a['label']) : $a['label'];
    $extra = $a['kind'] === 'tel' ? '' : ' target="_blank" rel="noopener"';
    $suffix = ['wa' => ' (WhatsApp)', 'tel' => '', 'dir' => ' (Google Maps)'][$a['kind']];
    $aria = ' aria-label="' . e($text . ' — ' . $hotel['name'] . $suffix) . '"';
    return '<a class="' . e($class) . '" href="' . e($a['href']) . '"' . $extra . $aria . ' data-cta="' . e($a['kind']) . '">'
        . icon($a['icon']) . '<span>' . e($text) . '</span></a>';
}

/** Kirim status 404 lalu berhenti (tanpa halaman khusus). */
function not_found(): never
{
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    echo '404 Not Found';
    exit;
}
