<?php
require_once __DIR__ . '/../includes/init.php';
require_login();
if (!is_post()) {
    redirect(admin_url('galeri'));
}
csrf_check();

$categories = ['exterior', 'lobby', 'room', 'dining', 'meeting', 'pool', 'facility', 'event'];
$hotelId = admin_hotel_scope() ?? post_int('hotel_id');
assert_hotel($hotelId);
if (!db_value('SELECT id FROM hotels WHERE id = ?', [$hotelId])) {
    flash('error', 'Pilih hotel.');
    redirect(admin_url('galeri'));
}
$category = in_array($_POST['category'] ?? '', $categories, true) ? $_POST['category'] : 'facility';
$caption = post_nullable('caption', 160);
$captionEn = post_nullable('caption_en', 160);

$files = $_FILES['photos'] ?? null;
$count = is_array($files['name'] ?? null) ? count($files['name']) : 0;
$sort = (int) db_value('SELECT COALESCE(MAX(sort), 0) FROM gallery_images WHERE hotel_id = ?', [$hotelId]);
$ok = 0;
for ($i = 0; $i < $count; $i++) {
    $file = ['name' => $files['name'][$i], 'type' => $files['type'][$i], 'tmp_name' => $files['tmp_name'][$i], 'error' => $files['error'][$i], 'size' => $files['size'][$i]];
    if ($file['error'] === UPLOAD_ERR_NO_FILE) {
        continue;
    }
    try {
        $key = image_store_upload($file, 'galeri');
        db_query(
            'INSERT INTO gallery_images (hotel_id, category, image, caption, caption_en, sort) VALUES (?, ?, ?, ?, ?, ?)',
            [$hotelId, $category, $key, $caption, $captionEn, ++$sort]
        );
        $ok++;
    } catch (RuntimeException $e) {
        flash('error', $file['name'] . ': ' . $e->getMessage());
    }
}
flash($ok ? 'success' : 'error', $ok ? $ok . ' foto ditambahkan ke galeri.' : 'Tidak ada foto yang diunggah.');
redirect(admin_url('galeri', ['hotel' => is_super() ? $hotelId : null]));
