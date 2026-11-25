<?php
require_once __DIR__ . '/../includes/init.php';
require_login();
if (!is_post()) {
    redirect(admin_url('kamar'));
}
csrf_check();

$room = db_one('SELECT * FROM room_types WHERE id = ?', [post_int('id')]) ?? admin_abort(404, 'Kamar tidak ditemukan.');
assert_hotel((int) $room['hotel_id']);

$photos = db_all('SELECT image FROM room_images WHERE room_type_id = ?', [$room['id']]);
db_query('DELETE FROM room_types WHERE id = ?', [$room['id']]);   // room_images ikut terhapus (ON DELETE CASCADE)
image_delete_upload($room['cover_image']);
foreach ($photos as $p) {
    image_delete_upload($p['image']);
}
flash('success', 'Kamar ' . $room['name'] . ' dihapus.');
redirect(admin_url('kamar'));
