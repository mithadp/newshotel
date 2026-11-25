<?php
require_once __DIR__ . '/../includes/init.php';
require_login();

$crud = [
    'table' => 'facilities', 'title' => 'Fasilitas hotel', 'nav' => 'fasilitas', 'path' => 'konten/fasilitas', 'singular' => 'fasilitas',
    'fields' => [
        'name'      => ['label' => 'Nama', 'type' => 'text', 'required' => true, 'max' => 120, 'list' => true],
        'name_en'   => ['label' => 'Nama (English)', 'type' => 'text', 'max' => 120],
        'icon'      => ['label' => 'Ikon', 'type' => 'select', 'list' => true, 'options' => [
            'check' => 'Centang (umum)', 'wifi' => 'WiFi', 'parking' => 'Parkir', 'utensils' => 'Restoran', 'coffee' => 'Kafe / kopi',
            'glass' => 'Bar / minuman', 'pool' => 'Kolam renang', 'presentation' => 'Ruang meeting', 'car' => 'Antar-jemput',
            'taxi' => 'Taksi', 'shield' => 'Keamanan', 'spa' => 'Spa / pijat', 'pill' => 'Apotek', 'balcony' => 'Balkon',
            'leaf' => 'Taman', 'clock' => '24 jam', 'pin' => 'Lokasi', 'users' => 'Keluarga / grup', 'baby' => 'Anak',
            'waves' => 'Air / pantai', 'sparkles' => 'Kebersihan', 'banknote' => 'Pembayaran',
        ]],
        'detail'    => ['label' => 'Keterangan', 'type' => 'text', 'max' => 255, 'hint' => 'opsional'],
        'detail_en' => ['label' => 'Keterangan (English)', 'type' => 'text', 'max' => 255],
        'sort'      => ['label' => 'Urutan', 'type' => 'number', 'hint' => 'kecil tampil duluan'],
    ],
];
require __DIR__ . '/../includes/simple-crud.php';
