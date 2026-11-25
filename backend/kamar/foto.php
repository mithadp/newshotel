<?php
require_once __DIR__ . '/../includes/init.php';
require_login();
if (!is_post()) {
    redirect(admin_url('kamar'));
}
csrf_check();

$action = (string) ($_POST['action'] ?? '');
if ($action === 'add') {
    $roomId = post_int('room_id');
    $room = db_one('SELECT id, hotel_id, name FROM room_types WHERE id = ?', [$roomId]) ?? admin_abort(404, 'Kamar tidak ditemukan.');
    assert_hotel((int) $room['hotel_id']);
    $files = $_FILES['photos'] ?? null;
    $count = is_array($files['name'] ?? null) ? count($files['name']) : 0;
    $alt = post_nullable('alt', 160);
    $sort = (int) db_value('SELECT COALESCE(MAX(sort), 0) FROM room_images WHERE room_type_id = ?', [$roomId]);
    $ok = 0;
    for ($i = 0; $i < $count; $i++) {
        $file = ['name' => $files['name'][$i], 'type' => $files['type'][$i], 'tmp_name' => $files['tmp_name'][$i], 'error' => $files['error'][$i], 'size' => $files['size'][$i]];
        if ($file['error'] === UPLOAD_ERR_NO_FILE) {
            continue;
        }
        try {
            $key = image_store_upload($file, 'kamar');
            db_query('INSERT INTO room_images (room_type_id, image, alt, sort) VALUES (?, ?, ?, ?)', [$roomId, $key, $alt, ++$sort]);
            $ok++;
        } catch (RuntimeException $e) {
            flash('error', $file['name'] . ': ' . $e->getMessage());
        }
    }
    if ($ok) {
        flash('success', $ok . ' foto ditambahkan.');
    } elseif ($count === 0 || ($files['error'][0] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        flash('error', 'Pilih foto terlebih dahulu.');
    }
    redirect(admin_url('kamar/form', ['id' => $roomId]) . '#foto');
}

if ($action === 'delete') {
    $photo = db_one('SELECT i.*, r.hotel_id FROM room_images i JOIN room_types r ON r.id = i.room_type_id WHERE i.id = ?', [post_int('photo_id')])
        ?? admin_abort(404, 'Foto tidak ditemukan.');
    assert_hotel((int) $photo['hotel_id']);
    db_query('DELETE FROM room_images WHERE id = ?', [$photo['id']]);
    image_delete_upload($photo['image']);
    flash('success', 'Foto dihapus.');
    redirect(admin_url('kamar/form', ['id' => $photo['room_type_id']]) . '#foto');
}

admin_abort(404, 'Aksi tidak dikenal.');
