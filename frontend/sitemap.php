<?php
require_once __DIR__ . '/includes/init.php';

$paths = ['', 'blog', 'tentang', 'kontak'];
foreach (hotels() as $h) {
    array_push($paths, $h['slug'], $h['slug'] . '/kamar', $h['slug'] . '/galeri');
}
$lastmod = [];
foreach (db_all("SELECT slug, updated_at FROM posts WHERE status = 'published' AND published_at <= NOW() ORDER BY published_at DESC") as $p) {
    $paths[] = 'blog/' . $p['slug'];
    $lastmod['blog/' . $p['slug']] = date('c', strtotime((string) $p['updated_at']));
}

header('Content-Type: application/xml; charset=utf-8');
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">' . "\n";
foreach ($paths as $path) {
    $id = abs_url($path, [], 'id');
    $en = abs_url($path, [], 'en');
    foreach ([$id, $en] as $loc) {
        echo '<url><loc>' . e($loc) . '</loc>' . (isset($lastmod[$path]) ? '<lastmod>' . e($lastmod[$path]) . '</lastmod>' : '')
            . '<xhtml:link rel="alternate" hreflang="id" href="' . e($id) . '"/>'
            . '<xhtml:link rel="alternate" hreflang="en" href="' . e($en) . '"/>'
            . '<xhtml:link rel="alternate" hreflang="x-default" href="' . e($id) . '"/></url>' . "\n";
    }
}
echo '</urlset>';
