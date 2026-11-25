<?php
require_once __DIR__ . '/../includes/init.php';
require_login();

$crud = [
    'table' => 'attractions', 'title' => 'Tempat sekitar', 'nav' => 'sekitar', 'path' => 'konten/sekitar', 'singular' => 'tempat',
    'fields' => [
        'name'    => ['label' => 'Nama tempat', 'type' => 'text', 'required' => true, 'max' => 120, 'list' => true],
        'name_en' => ['label' => 'Nama (English)', 'type' => 'text', 'max' => 120],
        'note'    => ['label' => 'Keterangan', 'type' => 'text', 'max' => 160, 'list' => true, 'hint' => 'mis. ± 5 menit berkendara'],
        'note_en' => ['label' => 'Keterangan (English)', 'type' => 'text', 'max' => 160],
        'sort'    => ['label' => 'Urutan', 'type' => 'number'],
    ],
];
require __DIR__ . '/../includes/simple-crud.php';
