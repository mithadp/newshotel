<?php
// Dibuat oleh PHP agar alamat sitemap selalu benar.
require_once __DIR__ . '/includes/init.php';

header('Content-Type: text/plain; charset=utf-8');
echo "User-agent: *\nAllow: /\n\nSitemap: " . site_origin() . BASE_URL . "/sitemap.xml\n";
