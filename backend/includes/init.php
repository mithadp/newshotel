<?php
declare(strict_types=1);

define('SIDE', 'back');
define('LANG', 'id');   // antarmuka admin berbahasa Indonesia; konten tetap diisi ID + EN

require_once dirname(__DIR__, 2) . '/shared/init.php';
require_once SHARED . '/images.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/form.php';

header('X-Robots-Tag: noindex, nofollow');
header('Cache-Control: no-store');
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');

admin_session_start();
