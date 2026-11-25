<?php
/** Koneksi PDO dan query bersama. Query khusus satu halaman ditulis langsung di halaman itu (db_all, db_one). */
declare(strict_types=1);

function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $c = config('db');
        $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s', $c['host'], $c['port'], $c['name'], $c['charset']);
        $pdo = new PDO($dsn, $c['user'], $c['pass'], [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_STRINGIFY_FETCHES  => false,
        ]);
        $pdo->exec("SET time_zone = '+07:00'");
    }
    return $pdo;
}

/** Jalankan query dengan parameter (selalu prepared statement). */
function db_query(string $sql, array $params = []): PDOStatement
{
    $stmt = db()->prepare($sql);
    foreach ($params as $k => $v) {
        $key = is_int($k) ? $k + 1 : ':' . ltrim($k, ':');
        $type = match (true) {
            is_int($v)  => PDO::PARAM_INT,
            is_bool($v) => PDO::PARAM_BOOL,
            $v === null => PDO::PARAM_NULL,
            default     => PDO::PARAM_STR,
        };
        $stmt->bindValue($key, $v, $type);
    }
    $stmt->execute();
    return $stmt;
}

function db_all(string $sql, array $params = []): array
{
    return db_query($sql, $params)->fetchAll();
}

function db_one(string $sql, array $params = []): ?array
{
    $row = db_query($sql, $params)->fetch();
    return $row === false ? null : $row;
}

function db_value(string $sql, array $params = []): mixed
{
    $v = db_query($sql, $params)->fetchColumn();
    return $v === false ? null : $v;
}

/** Semua hotel yang tampil (urut kolom sort), masing-masing dengan from_price = tarif kamar termurah. */
function hotels(): array
{
    static $list = null;
    $list ??= db_all(
        'SELECT h.*, (SELECT MIN(r.base_price) FROM room_types r
                       WHERE r.hotel_id = h.id AND r.is_active = 1 AND r.base_price IS NOT NULL) AS from_price
         FROM hotels h WHERE h.is_published = 1 ORDER BY h.sort, h.id'
    );
    return $list;
}

/** Hotel berdasarkan slug, atau null jika tidak ada / tidak dipublikasikan. */
function find_hotel(?string $slug): ?array
{
    foreach (hotels() as $h) {
        if ($h['slug'] === $slug) {
            return $h;
        }
    }
    return null;
}

/** Hotel untuk halaman hotel; tampilkan 404 jika tidak ada atau tidak dipublikasikan. */
function hotel(string $slug): array
{
    return find_hotel($slug) ?? not_found();
}

/** Hotel lain selain $hotel. */
function other_hotels(array $hotel): array
{
    return array_values(array_filter(hotels(), static fn ($h) => $h['id'] !== $hotel['id']));
}

/** Artikel blog terbit, terbaru dulu. $hotelId: artikel hotel itu + artikel grup. $category: news | event | promo. */
function posts(int $limit = 9, int $offset = 0, ?int $hotelId = null, ?string $category = null, bool $groupToo = true): array
{
    [$where, $params] = posts_where($hotelId, $category, $groupToo);
    return db_all(
        'SELECT p.*, h.name AS hotel_name, h.slug AS hotel_slug, h.brand AS hotel_brand
         FROM posts p LEFT JOIN hotels h ON h.id = p.hotel_id
         WHERE ' . $where . ' ORDER BY p.published_at DESC, p.id DESC LIMIT ' . (int) $limit . ' OFFSET ' . (int) $offset,
        $params
    );
}

function post_count(?int $hotelId = null, ?string $category = null, bool $groupToo = true): int
{
    [$where, $params] = posts_where($hotelId, $category, $groupToo);
    return (int) db_value('SELECT COUNT(*) FROM posts p WHERE ' . $where, $params);
}

function posts_where(?int $hotelId, ?string $category, bool $groupToo): array
{
    $where = "p.status = 'published' AND p.published_at <= NOW()";
    $params = [];
    if ($hotelId !== null) {
        $where .= $groupToo ? ' AND (p.hotel_id = :hid OR p.hotel_id IS NULL)' : ' AND p.hotel_id = :hid';
        $params['hid'] = $hotelId;
    }
    if ($category !== null) {
        $where .= ' AND p.category = :cat';
        $params['cat'] = $category;
    }
    return [$where, $params];
}
