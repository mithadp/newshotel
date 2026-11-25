<?php
/**
 * Minify + gzip CSS/JS menjadi "<file>.gz" di samping sumbernya.
 *   php scripts/build-assets.php
 * asset() hanya menyajikan .gz selama tidak lebih lama dari sumbernya, jadi CSS/JS yang diedit
 * tanpa build ulang tidak pernah usang. Bila mod_deflate aktif di server, langkah ini opsional.
 */
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { exit("CLI only\n"); }
$root = dirname(__DIR__) . '/frontend/assets';
$files = array_merge(glob("$root/css/*.css") ?: [], glob("$root/js/*.js") ?: [], ["$root/img/icons.svg"]);
foreach ($files as $file) {
    $src = (string) file_get_contents($file);
    if (str_ends_with($file, '.css')) {
        $min = preg_replace('#/\*.*?\*/#s', '', $src) ?? $src;
        $min = preg_replace('/\s+/', ' ', $min) ?? $min;
        $min = preg_replace('/\s*([{};,>])\s*/', '$1', $min) ?? $min;   // tanpa ":" agar selektor seperti "a :hover" tetap utuh
        $min = str_replace(';}', '}', $min);
    } elseif (str_ends_with($file, '.svg')) {
        $min = preg_replace('/>\s+</', '><', $src) ?? $src;
    } else {
        $min = $src;   // JS hanya di-gzip (minify regex berisiko)
    }
    file_put_contents("$file.gz", gzencode(trim($min), 9));
    printf("%-28s %6.1f KB -> %5.1f KB gz\n", basename($file), strlen($src) / 1024, filesize("$file.gz") / 1024);
}

// Panel admin berdiri sendiri (domain/subdomain lain), jadi sprite ikon disalin ke backend/assets agar satu domain dengan halamannya.
copy("$root/img/icons.svg", dirname(__DIR__) . '/backend/assets/icons.svg');
echo "icons.svg disalin ke backend/assets\n";
