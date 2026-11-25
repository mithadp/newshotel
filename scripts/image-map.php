<?php
/**
 * Peta sumber → gambar teroptimasi. Sumber ada di docs/asset (aslinya tidak diubah).
 *   key    : kunci manifest; file jadi frontend/assets/img/<key>-<lebar>.webp
 *   widths : lebar varian (bawaan 480/960/1440/1920, dibatasi lebar sumber)
 *   origin : asal foto, disimpan di manifest
 * Sengaja tidak dimasukkan: foto stok (mosogep4.jpg, spa-massage.jpg, restaurant.png), panorama terpotong
 * 8–9k px (Resto2.JPG, Ruang Meeting.JPG di folder utama), dan semua foto grand opening Darmo Inn
 * (wajah terlihat; izin publikasi belum ada).
 */
$S = 'docs/asset/newshotel_surabaya';
$D = 'docs/asset/darmoinn';
$M = 'docs/asset/newshotel_semarang';

return [
    // News Hotel Surabaya (foto dari situs hotel tahun 2016)
    ['src' => "$S/hires/slide-3.jpg",   'key' => 'surabaya/lobby',            'origin' => 'Foto hotel, slideshow newshotelsby.com lama'],
    ['src' => "$S/hires/slide-1.jpg",   'key' => 'surabaya/news-corner',      'origin' => 'Foto hotel, slideshow newshotelsby.com lama'],
    ['src' => "$S/hires/slide-2.jpg",   'key' => 'surabaya/garden-terrace',   'origin' => 'Foto hotel, slideshow newshotelsby.com lama'],
    ['src' => "$S/hires/room_ex.jpg",   'key' => 'surabaya/room-executive',   'origin' => 'Foto hotel, slideshow newshotelsby.com lama'],
    ['src' => "$S/hires/lobby.jpg",     'key' => 'surabaya/reception-wide',   'origin' => 'Foto hotel (panorama), slideshow situs lama'],
    ['src' => "$S/hires/Resto2.jpg",    'key' => 'surabaya/restaurant-wide',  'origin' => 'Foto hotel (panorama), slideshow situs lama'],
    ['src' => "$S/room/1-delux.jpg",    'key' => 'surabaya/room-deluxe',      'origin' => 'Foto hotel, galeri situs lama'],
    ['src' => "$S/room/2-twin.jpg",     'key' => 'surabaya/room-superior',    'origin' => 'Foto hotel, galeri situs lama'],
    ['src' => "$S/room/3-executive.jpg",'key' => 'surabaya/room-executive-2', 'origin' => 'Foto hotel, galeri situs lama'],
    ['src' => "$S/room/4-standart.jpg", 'key' => 'surabaya/room-standard',    'origin' => 'Foto hotel, galeri situs lama'],
    ['src' => "$S/1977e92fe8bd932eb5765846f205ffa7.JPG", 'key' => 'surabaya/room-deluxe-2',  'origin' => 'Foto hotel, galeri situs lama'],
    ['src' => "$S/03e9a78be9e098aef4a2f6b8f3b1015a.JPG", 'key' => 'surabaya/room-superior-2','origin' => 'Foto hotel, galeri situs lama'],
    ['src' => "$S/0acfa8f480dddc41a7d6904d61a165e1.JPG", 'key' => 'surabaya/room-sitting',   'origin' => 'Foto hotel, galeri situs lama'],
    ['src' => "$S/094d7aa480b87412612edc84774715c9.JPG", 'key' => 'surabaya/corridor',       'origin' => 'Foto hotel, galeri situs lama'],
    ['src' => "$S/a5149865b3231ddb6d61902bd0312c8b.JPG", 'key' => 'surabaya/lobby-lounge',   'origin' => 'Foto hotel, galeri situs lama'],
    ['src' => "$S/c8a3b1885873e86dbc49d564489e80cf.JPG", 'key' => 'surabaya/lobby-reading',  'origin' => 'Foto hotel, galeri situs lama'],
    ['src' => "$S/f097b8f08871fa12b0d16e9f3b0a2e71.JPG", 'key' => 'surabaya/buffet',         'origin' => 'Foto hotel, galeri situs lama'],
    ['src' => "$S/meetingroom.jpg",     'key' => 'surabaya/meeting',          'origin' => 'Foto hotel, galeri situs lama'],
    ['src' => "$S/dragstrore.jpg",      'key' => 'surabaya/drugstore',        'origin' => 'Foto hotel, galeri situs lama'],
    ['src' => "$S/suttle_services.jpg", 'key' => 'surabaya/shuttle',          'origin' => 'Foto hotel, galeri situs lama'],

    // Darmo Inn by News Hotel (render tahap desain + satu foto jadi)
    ['src' => "$D/loby.jpeg",                  'key' => 'darmo-inn/reception',        'origin' => 'Foto resepsionis yang sudah jadi'],
    ['src' => "$D/Facade_depan_darmoinn.jpeg", 'key' => 'darmo-inn/facade-render', 'quality' => 60,    'origin' => 'Render arsitek (tahap desain)'],
    ['src' => "$D/facade_loby.jpeg",           'key' => 'darmo-inn/lobby-render',     'origin' => 'Render arsitek (tahap desain)'],
    ['src' => "$D/lobby-ornamen.jpeg",         'key' => 'darmo-inn/lobby-ornament-render', 'origin' => 'Render arsitek (tahap desain)'],
    ['src' => "$D/sofa-lobby.jpeg",            'key' => 'darmo-inn/lounge-render',    'origin' => 'Render arsitek (tahap desain)'],
    ['src' => "$D/mini-garden.jpeg",           'key' => 'darmo-inn/mini-garden-render', 'quality' => 60,'origin' => 'Render arsitek (tahap desain)'],
    ['src' => "$D/outdoor-garden.jpeg",        'key' => 'darmo-inn/garden-render', 'quality' => 60,    'origin' => 'Render arsitek (tahap desain)'],
    ['src' => "$D/room.jpeg",                  'key' => 'darmo-inn/room-render',      'origin' => 'Render arsitek (tahap desain)'],

    // News Hotel Semarang (dibuka 31 Okt 2026)
    ['src' => "$M/foto-depan.jpeg",              'key' => 'semarang/facade', 'quality' => 60,        'origin' => 'Foto hotel saat soft opening, Nov 2026'],
    ['src' => "$M/hotel.jpeg",                   'key' => 'semarang/facade-tall',   'origin' => 'Foto hotel saat soft opening, Nov 2026'],
    ['src' => "$M/hires/facade-render-clean.jpeg",'key' => 'semarang/facade-render','origin' => 'Render arsitek (tahap desain)'],

    // News Hotel Semarang: foto kamar dari hotel
    ['src' => "$M/kamar/business-room.jpeg",          'key' => 'semarang/room-business',         'origin' => 'Foto kamar dari hotel, 24-11-2026'],
    ['src' => "$M/kamar/deluxe-double-pool-access.jpeg",'key' => 'semarang/room-deluxe-double-pool','origin' => 'Foto kamar dari hotel, 24-11-2026'],
    ['src' => "$M/kamar/deluxe-twin-1.jpeg",          'key' => 'semarang/room-deluxe-twin-1',    'origin' => 'Foto kamar dari hotel, 24-11-2026'],
    ['src' => "$M/kamar/deluxe-twin-2.jpeg",          'key' => 'semarang/room-deluxe-twin-2',    'origin' => 'Foto kamar dari hotel, 24-11-2026'],
    ['src' => "$M/kamar/executive-1.jpeg",            'key' => 'semarang/room-executive-1',      'origin' => 'Foto kamar dari hotel, 24-11-2026'],
    ['src' => "$M/kamar/executive-2.jpeg",            'key' => 'semarang/room-executive-2',      'origin' => 'Foto kamar dari hotel, 24-11-2026'],
    ['src' => "$M/kamar/executive-3.jpeg",            'key' => 'semarang/room-executive-3',      'origin' => 'Foto kamar dari hotel, 24-11-2026'],
    ['src' => "$M/kamar/family-triple-1.jpeg",        'key' => 'semarang/room-family-triple-1',  'origin' => 'Foto kamar dari hotel, 24-11-2026'],
    ['src' => "$M/kamar/family-triple-2.jpeg",        'key' => 'semarang/room-family-triple-2',  'origin' => 'Foto kamar dari hotel, 24-11-2026'],
    ['src' => "$M/kamar/family-triple-bath.jpeg",     'key' => 'semarang/room-bathroom',         'origin' => 'Foto kamar mandi dari hotel, 24-11-2026'],
    ['src' => "$M/kamar/superior-double-1.jpeg",      'key' => 'semarang/room-superior-double-1','origin' => 'Foto kamar dari hotel, 24-11-2026'],
    ['src' => "$M/kamar/superior-double-2.jpeg",      'key' => 'semarang/room-superior-double-2','origin' => 'Foto kamar dari hotel, 24-11-2026'],

    // Darmo Inn Superior Double/Twin: foto dari listing Traveloka (disediakan hotel)
    ['src' => "$D/kamar/superior-twin-traveloka.jpg",   'key' => 'darmo-inn/room-superior-twin',   'origin' => 'Foto kamar dari listing Traveloka (dari hotel), 24-11-2026'],
    ['src' => "$D/kamar/superior-double-traveloka.jpg", 'key' => 'darmo-inn/room-superior-double', 'origin' => 'Render kamar dari listing Traveloka (dari hotel), 24-11-2026'],

    // Logo (diproses terpisah, lihat optimize-images.php)
];
