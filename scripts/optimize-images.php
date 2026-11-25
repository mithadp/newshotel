<?php
/**
 * Bangun varian WebP responsif untuk setiap gambar sumber di image-map.php dan untuk logo.
 *   php scripts/optimize-images.php            (lewati kunci yang sudah dibangun)
 *   php scripts/optimize-images.php --force    (bangun ulang semuanya)
 * Menulis frontend/assets/img/manifest.json berisi lebar varian, ukuran asli, dan asal foto.
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    exit("CLI only\n");
}
ini_set('memory_limit', '1024M');
define('BASE_PATH', dirname(__DIR__));
define('FRONT', BASE_PATH . '/frontend');
require BASE_PATH . '/shared/images.php';

$force = in_array('--force', $argv, true);
$out   = BASE_PATH . '/frontend/assets/img/';
$manifestFile = $out . 'manifest.json';
$manifest = is_file($manifestFile) ? (json_decode((string) file_get_contents($manifestFile), true) ?: []) : [];

$map = require __DIR__ . '/image-map.php';
foreach ($map as $item) {
    $src = BASE_PATH . '/' . $item['src'];
    if (!is_file($src)) {
        fwrite(STDERR, "MISSING {$item['src']}\n");
        continue;
    }
    if (!$force && isset($manifest[$item['key']])) {
        continue;
    }
    $im = image_load($src);
    $meta = image_variants($im, $out . $item['key'], $item['widths'] ?? IMAGE_WIDTHS, $item['quality'] ?? 72);
    $meta['origin'] = $item['origin'] . ' (sumber: ' . $item['src'] . ')';
    $manifest[$item['key']] = $meta;
    imagedestroy($im);
    printf("%-34s %s\n", $item['key'], implode(',', $meta['w']));
}

/** Pangkas ke kotak piksel dengan alpha di bawah $limit (alpha GD: 0 buram … 127 bening). */
function crop_to_ink(GdImage $im, int $pad = 8, int $limit = 100): GdImage
{
    $w = imagesx($im);
    $h = imagesy($im);
    $minX = $w; $minY = $h; $maxX = 0; $maxY = 0;
    for ($y = 0; $y < $h; $y++) {
        for ($x = 0; $x < $w; $x++) {
            if ((imagecolorat($im, $x, $y) >> 24 & 127) < $limit) {
                $minX = min($minX, $x); $maxX = max($maxX, $x);
                $minY = min($minY, $y); $maxY = max($maxY, $y);
            }
        }
    }
    $minX = max(0, $minX - $pad); $minY = max(0, $minY - $pad);
    $maxX = min($w - 1, $maxX + $pad); $maxY = min($h - 1, $maxY + $pad);
    $cw = $maxX - $minX + 1; $ch = $maxY - $minY + 1;
    $c = imagecreatetruecolor($cw, $ch);
    imagealphablending($c, false);
    imagesavealpha($c, true);
    imagefill($c, 0, 0, imagecolorallocatealpha($c, 0, 0, 0, 127));
    imagecopy($c, $im, 0, 0, $minX, $minY, $cw, $ch);
    return $c;
}

/** Warnai ulang setiap piksel ke $rgb dengan alpha tetap. */
function recolor(GdImage $im, array $rgb): GdImage
{
    $w = imagesx($im); $h = imagesy($im);
    $o = imagecreatetruecolor($w, $h);
    imagealphablending($o, false);
    imagesavealpha($o, true);
    for ($y = 0; $y < $h; $y++) {
        for ($x = 0; $x < $w; $x++) {
            $a = imagecolorat($im, $x, $y) >> 24 & 127;
            imagesetpixel($o, $x, $y, imagecolorallocatealpha($o, $rgb[0], $rgb[1], $rgb[2], $a));
        }
    }
    return $o;
}

/**
 * Pisahkan logo Darmo Inn dari latar plum: tiap piksel dimodelkan sebagai campuran latar plum dan tinta
 * putih atau emas; kecocokan terbaik memberi warna tinta + alpha. $inkMap mewarnai ulang logo yang sama
 * (mis. putih → plum untuk latar terang).
 */
function key_darmo(GdImage $src, array $inkMap): GdImage
{
    $B = [76, 8, 45];
    $inks = ['white' => [255, 255, 255], 'gold' => [235, 182, 106]];
    $w = imagesx($src); $h = imagesy($src);
    $o = imagecreatetruecolor($w, $h);
    imagealphablending($o, false);
    imagesavealpha($o, true);
    for ($y = 0; $y < $h; $y++) {
        for ($x = 0; $x < $w; $x++) {
            $c = imagecolorat($src, $x, $y);
            $P = [$c >> 16 & 255, $c >> 8 & 255, $c & 255];
            $best = null;
            foreach ($inks as $name => $F) {
                $d = [$F[0] - $B[0], $F[1] - $B[1], $F[2] - $B[2]];
                $v = [$P[0] - $B[0], $P[1] - $B[1], $P[2] - $B[2]];
                $len2 = $d[0] ** 2 + $d[1] ** 2 + $d[2] ** 2;
                $a = max(0.0, min(1.0, ($v[0] * $d[0] + $v[1] * $d[1] + $v[2] * $d[2]) / $len2));
                $res = ($v[0] - $a * $d[0]) ** 2 + ($v[1] - $a * $d[1]) ** 2 + ($v[2] - $a * $d[2]) ** 2;
                if ($best === null || $res < $best[2]) {
                    $best = [$name, $a, $res];
                }
            }
            [$name, $a] = $best;
            $a = $a < 0.14 ? 0.0 : min(1.0, ($a - 0.14) / 0.80);   // buang tekstur kisi tipis
            $ink = $inkMap[$name];
            imagesetpixel($o, $x, $y, imagecolorallocatealpha($o, $ink[0], $ink[1], $ink[2], (int) round(127 * (1 - $a))));
        }
    }
    return $o;
}

$brand = [];
$newsSrc = BASE_PATH . '/docs/asset/Logo/logo-newshotel.png';
if (is_file($newsSrc)) {
    $im = @imagecreatefrompng($newsSrc);
    imagesavealpha($im, true);
    $im = crop_to_ink($im, 6);
    $brand['brand/news-hotel'] = [$im, 'Logo News Hotel, docs/asset/Logo/logo-newshotel.png'];
    $brand['brand/news-hotel-white'] = [recolor($im, [255, 255, 255]), 'Logo News Hotel diwarnai putih untuk latar gelap'];
}
$darmoSrc = BASE_PATH . '/docs/asset/Logo/logo-darmoin.jpeg';
if (is_file($darmoSrc)) {
    $raw = imagecreatefromjpeg($darmoSrc);
    $light = crop_to_ink(key_darmo($raw, ['white' => [255, 255, 255], 'gold' => [235, 182, 106]]), 6);
    $dark  = crop_to_ink(key_darmo($raw, ['white' => [76, 8, 45], 'gold' => [196, 140, 62]]), 6);
    $brand['brand/darmo-inn-light'] = [$light, 'Logo Darmo Inn, latar ungu dihapus (tinta putih dan emas)'];
    $brand['brand/darmo-inn-dark']  = [$dark, 'Logo Darmo Inn, latar dihapus dan diwarnai ungu dan emas tua untuk latar terang'];
}
$jpSrc = BASE_PATH . '/docs/asset/Logo/logograhapena.png';
if (is_file($jpSrc)) {
    $im = @imagecreatefrompng($jpSrc);
    imagesavealpha($im, true);
    $brand['brand/graha-pena'] = [crop_to_ink($im, 6), 'Logo Graha Pena Jawa Pos, docs/asset/Logo/logograhapena.png'];
}

foreach ($brand as $key => [$im, $origin]) {
    if (!$force && isset($manifest[$key])) {
        continue;
    }
    $meta = image_variants($im, $out . $key, [160, 320, 640], 90);
    $meta['origin'] = $origin;
    $manifest[$key] = $meta;
    printf("%-34s %s\n", $key, implode(',', $meta['w']));
}

ksort($manifest);
file_put_contents($manifestFile, json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
echo "manifest: " . count($manifest) . " keys\n";
