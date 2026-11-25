<?php
require_once __DIR__ . '/../includes/init.php';
require_login();

$crud = [
    'table' => 'dining_outlets', 'title' => 'Dining', 'nav' => 'dining', 'path' => 'konten/dining', 'singular' => 'outlet',
    'slug_from' => 'name', 'image_folder' => 'dining',
    'fields' => [
        'name'           => ['label' => 'Nama', 'type' => 'text', 'required' => true, 'max' => 120, 'list' => true],
        'type'           => ['label' => 'Jenis', 'type' => 'text', 'required' => true, 'max' => 60, 'list' => true, 'hint' => 'mis. Restoran, Kafe, Bar'],
        'type_en'        => ['label' => 'Jenis (English)', 'type' => 'text', 'max' => 60],
        'description'    => ['label' => 'Deskripsi', 'type' => 'textarea', 'max' => 2000],
        'description_en' => ['label' => 'Deskripsi (English)', 'type' => 'textarea', 'max' => 2000],
        'hours'          => ['label' => 'Jam buka', 'type' => 'text', 'max' => 120, 'hint' => 'mis. 06.00–22.00 atau Buka 24 jam'],
        'hours_en'       => ['label' => 'Jam buka (English)', 'type' => 'text', 'max' => 120],
        'image'          => ['label' => 'Foto', 'type' => 'image', 'list' => true],
        'is_active'      => ['label' => 'Tampilkan di situs', 'type' => 'check', 'default' => 1],
        'sort'           => ['label' => 'Urutan', 'type' => 'number'],
    ],
];
require __DIR__ . '/../includes/simple-crud.php';
