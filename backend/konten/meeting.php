<?php
require_once __DIR__ . '/../includes/init.php';
require_login();

$crud = [
    'table' => 'meeting_venues', 'title' => 'Ruang meeting', 'nav' => 'meeting', 'path' => 'konten/meeting', 'singular' => 'ruang',
    'image_folder' => 'meeting',
    'fields' => [
        'name'           => ['label' => 'Nama ruang', 'type' => 'text', 'required' => true, 'max' => 120, 'list' => true],
        'name_en'        => ['label' => 'Nama (English)', 'type' => 'text', 'max' => 120],
        'description'    => ['label' => 'Deskripsi', 'type' => 'textarea', 'max' => 2000],
        'description_en' => ['label' => 'Deskripsi (English)', 'type' => 'textarea', 'max' => 2000],
        'size_m2'        => ['label' => 'Luas (m²)', 'type' => 'decimal'],
        'cap_theatre'    => ['label' => 'Kapasitas teater', 'type' => 'number', 'nullable' => true, 'list' => true],
        'cap_classroom'  => ['label' => 'Kapasitas kelas', 'type' => 'number', 'nullable' => true],
        'cap_ushape'     => ['label' => 'Kapasitas U-shape', 'type' => 'number', 'nullable' => true],
        'cap_banquet'    => ['label' => 'Kapasitas banquet', 'type' => 'number', 'nullable' => true],
        'image'          => ['label' => 'Foto', 'type' => 'image'],
        'is_active'      => ['label' => 'Tampilkan di situs', 'type' => 'check', 'default' => 1],
        'sort'           => ['label' => 'Urutan', 'type' => 'number'],
    ],
];
require __DIR__ . '/../includes/simple-crud.php';
