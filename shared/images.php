<?php
/**
 * Pipeline gambar (GD): memuat foto, memutar sesuai EXIF, menulis varian WebP responsif ke
 * frontend/assets/img/ dan mendaftarkannya di manifest.json agar img() memakai srcset.
 */
declare(strict_types=1);

const IMAGE_WIDTHS = [480, 720, 960, 1440, 1920];
const IMAGE_MAX_BYTES = 5 * 1024 * 1024;

/** Muat file gambar ke GD dengan orientasi EXIF diterapkan. */
function image_load(string $file): GdImage
{
    $info = @getimagesize($file);
    if ($info === false) {
        throw new RuntimeException('File bukan gambar yang valid.');
    }
    $im = match ($info[2]) {
        IMAGETYPE_JPEG => @imagecreatefromjpeg($file),
        IMAGETYPE_PNG  => @imagecreatefrompng($file),
        IMAGETYPE_WEBP => @imagecreatefromwebp($file),
        default        => false,
    };
    if (!$im instanceof GdImage) {
        throw new RuntimeException('Format gambar tidak didukung (pakai JPG, PNG, atau WebP).');
    }
    if ($info[2] === IMAGETYPE_JPEG && function_exists('exif_read_data')) {
        $orientation = (int) (@exif_read_data($file)['Orientation'] ?? 1);
        $im = match ($orientation) {
            3       => imagerotate($im, 180, 0),
            6       => imagerotate($im, -90, 0),
            8       => imagerotate($im, 90, 0),
            default => $im,
        };
    }
    imagepalettetotruecolor($im);
    return $im;
}

/**
 * Tulis varian "<destBase>-<lebar>.webp" untuk setiap lebar yang tidak melebihi sumber.
 * Encode ulang membuang metadata (termasuk lokasi GPS dari kamera).
 * @return array{w:int[],width:int,height:int}
 */
function image_variants(GdImage $src, string $destBase, array $widths = IMAGE_WIDTHS, int $quality = 72): array
{
    $sw = imagesx($src);
    $sh = imagesy($src);
    $made = [];
    $targets = array_values(array_unique(array_map(static fn ($w) => min((int) $w, $sw), $widths)));
    sort($targets);
    foreach ($targets as $tw) {
        $th = (int) round($sh * $tw / $sw);
        $dst = imagecreatetruecolor($tw, $th);
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $tw, $th, $sw, $sh);
        if (!is_dir(dirname($destBase))) {
            mkdir(dirname($destBase), 0775, true);
        }
        imagewebp($dst, "$destBase-$tw.webp", $quality);
        imagedestroy($dst);
        $made[] = $tw;
    }
    $lw = max($made);
    return ['w' => $made, 'width' => $lw, 'height' => (int) round($sh * $lw / $sw)];
}

function manifest_path(): string
{
    return FRONT . '/assets/img/manifest.json';
}

/** Baca–ubah–tulis manifest.json dengan kunci file (aman jika dua admin mengunggah bersamaan). */
function manifest_update(callable $change): void
{
    $fh = fopen(manifest_path(), 'c+');
    if (!$fh) {
        throw new RuntimeException('manifest.json tidak bisa dibuka.');
    }
    try {
        flock($fh, LOCK_EX);
        $data = json_decode((string) stream_get_contents($fh), true) ?: [];
        $data = $change($data);
        ftruncate($fh, 0);
        rewind($fh);
        fwrite($fh, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        fflush($fh);
    } finally {
        flock($fh, LOCK_UN);
        fclose($fh);
    }
}

/**
 * Proses satu file unggahan ($_FILES['x'] atau satu elemen dari unggahan banyak file)
 * menjadi varian WebP di frontend/assets/img/uploads/<folder>/<acak>-<lebar>.webp.
 * Mengembalikan kunci gambar, mis. "uploads/galeri/3f9a1c2e7b".
 */
function image_store_upload(array $file, string $folder, string $origin = 'Diunggah lewat panel admin'): string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new RuntimeException(match ($file['error'] ?? UPLOAD_ERR_NO_FILE) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'File terlalu besar (maksimal 5 MB).',
            UPLOAD_ERR_NO_FILE => 'Pilih file gambar terlebih dahulu.',
            default => 'Unggahan gagal, silakan coba lagi.',
        });
    }
    if (!is_uploaded_file($file['tmp_name'])) {
        throw new RuntimeException('Unggahan tidak valid.');
    }
    if ($file['size'] > IMAGE_MAX_BYTES) {
        throw new RuntimeException('File terlalu besar (maksimal 5 MB).');
    }
    $info = @getimagesize($file['tmp_name']);
    if ($info === false || !in_array($info[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP], true)) {
        throw new RuntimeException('File harus berupa gambar JPG, PNG, atau WebP.');
    }
    if ($info[0] * $info[1] > 40_000_000) {
        throw new RuntimeException('Resolusi gambar terlalu besar (maksimal 40 megapiksel).');
    }
    $folder = preg_replace('/[^a-z0-9-]/', '', strtolower($folder)) ?: 'lain';
    $key = 'uploads/' . $folder . '/' . bin2hex(random_bytes(6));
    ini_set('memory_limit', '512M');
    $im = image_load($file['tmp_name']);
    $meta = image_variants($im, FRONT . '/assets/img/' . $key, IMAGE_WIDTHS, 78);
    imagedestroy($im);
    $meta['origin'] = $origin . ' (' . date('Y-m-d H:i') . ')';
    manifest_update(static function (array $m) use ($key, $meta) {
        $m[$key] = $meta;
        return $m;
    });
    return $key;
}

/** Hapus file varian dan entri manifest sebuah gambar unggahan. Gambar bawaan (bukan uploads/) tidak disentuh. */
function image_delete_upload(?string $key): void
{
    if (!$key || !str_starts_with($key, 'uploads/') || str_contains($key, '..')) {
        return;
    }
    foreach (glob(FRONT . '/assets/img/' . $key . '-*.webp') ?: [] as $f) {
        @unlink($f);
    }
    manifest_update(static function (array $m) use ($key) {
        unset($m[$key]);
        return $m;
    });
}
