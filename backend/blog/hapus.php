<?php
require_once __DIR__ . '/../includes/init.php';
require_login();
if (!is_post()) {
    redirect(admin_url('blog'));
}
csrf_check();

$post = db_one('SELECT * FROM posts WHERE id = ?', [post_int('id')]) ?? admin_abort(404, 'Artikel tidak ditemukan.');
$scope = admin_hotel_scope();
if ($scope !== null && (int) $post['hotel_id'] !== $scope) {
    admin_abort(403, 'Artikel ini milik hotel lain atau artikel grup.');
}
db_query('DELETE FROM posts WHERE id = ?', [$post['id']]);
image_delete_upload($post['cover']);
flash('success', 'Artikel "' . $post['title'] . '" dihapus.');
redirect(admin_url('blog'));
