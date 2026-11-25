<?php
require_once __DIR__ . '/../includes/init.php';
require_login();
if (!is_post()) {
    redirect(admin_url('galeri'));
}
csrf_check();

$item = db_one('SELECT * FROM gallery_images WHERE id = ?', [post_int('id')]) ?? admin_abort(404, 'Foto tidak ditemukan.');
assert_hotel((int) $item['hotel_id']);
db_query('DELETE FROM gallery_images WHERE id = ?', [$item['id']]);
image_delete_upload($item['image']);
flash('success', 'Foto dihapus dari galeri.');
redirect(admin_url('galeri', ['hotel' => is_super() ? $item['hotel_id'] : null]));
