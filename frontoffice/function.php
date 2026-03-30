<?php

declare(strict_types=1);

function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $host = getenv('DB_HOST') ?: 'db';
    $port = getenv('DB_PORT') ?: '5432';
    $name = getenv('DB_NAME') ?: 'guerre_news';
    $user = getenv('DB_USER') ?: 'guerre_user';
    $pass = getenv('DB_PASSWORD') ?: 'guerre_password';

    $dsn = sprintf('pgsql:host=%s;port=%s;dbname=%s', $host, $port, $name);

    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    return $pdo;
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function site_name(): string
{
    return 'GeoMonitor';
}

function site_description(): string
{
    return 'Actualites internationales sur les conflits, diplomatie et enjeux humanitaires.';
}

function normalize_slug(string $value): string
{
    $value = trim($value);
    $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
    if ($ascii !== false) {
        $value = $ascii;
    }

    $value = strtolower($value);
    $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';
    $value = trim($value, '-');

    return $value;
}

function normalize_path(string $path): string
{
    $path = trim($path);
    if ($path === '') {
        return '/';
    }

    if ($path[0] !== '/') {
        $path = '/' . $path;
    }

    $path = preg_replace('#/{2,}#', '/', $path) ?? $path;

    return $path;
}

function redirect(string $location, int $statusCode = 302): never
{
    header('Location: ' . $location, true, $statusCode);
    exit;
}

function canonical_url(string $pathWithOptionalQuery): string
{
    $isHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    $scheme = $isHttps ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

    return $scheme . '://' . $host . $pathWithOptionalQuery;
}

function clean_category_ids(array $categoryIds): array
{
    return array_values(array_unique(array_filter(
        array_map(static fn($id): int => (int) $id, $categoryIds),
        static fn(int $id): bool => $id > 0
    )));
}

function nav_categories(): array
{
    $sql = 'SELECT id, name FROM categories ORDER BY name ASC';
    $rows = db()->query($sql)->fetchAll();

    $categories = [];
    foreach ($rows as $row) {
        $categories[] = [
            'id' => (int) $row['id'],
            'name' => (string) $row['name'],
            'slug' => normalize_slug((string) $row['name']),
        ];
    }

    return $categories;
}

function split_nav_categories(array $categories, int $visibleCount = 6): array
{
    $visible = array_slice($categories, 0, $visibleCount);
    $collapsed = array_slice($categories, $visibleCount);

    return [$visible, $collapsed];
}

function category_by_slug(string $slug): ?array
{
    $slug = normalize_slug($slug);
    if ($slug === '') {
        return null;
    }

    foreach (nav_categories() as $category) {
        if ($category['slug'] === $slug) {
            return $category;
        }
    }

    return null;
}

function article_url(string $slug, int $id): string
{
    return '/article/' . normalize_slug($slug) . '-' . $id . '.html';
}

function category_url(string $categorySlug): string
{
    return '/category/' . normalize_slug($categorySlug);
}

function fetch_articles(string $search = '', array $categoryIds = [], int $limit = 60): array
{
    $conditions = [];
    $params = [];

    if ($search !== '') {
        $conditions[] = '(a.header ILIKE :search OR a.content ILIKE :search)';
        $params[':search'] = '%' . $search . '%';
    }

    if ($categoryIds !== []) {
        $placeholders = [];
        foreach ($categoryIds as $index => $categoryId) {
            $placeholder = ':cat_' . $index;
            $placeholders[] = $placeholder;
            $params[$placeholder] = (int) $categoryId;
        }

        $conditions[] =
            'EXISTS (' .
            'SELECT 1 FROM article_categories acf ' .
            'WHERE acf.article_id = a.id AND acf.category_id IN (' . implode(', ', $placeholders) . ')' .
            ')';
    }

    $whereSql = $conditions === [] ? '' : 'WHERE ' . implode(' AND ', $conditions);

    $sql =
        'SELECT a.id, a.header, a.url_slug, a.content, a.latitude, a.longitude, a.created_at,' .
        " COALESCE(string_agg(DISTINCT c.name, ', ' ORDER BY c.name), '') AS categories_text" .
        ' FROM articles a' .
        ' LEFT JOIN article_categories ac ON ac.article_id = a.id' .
        ' LEFT JOIN categories c ON c.id = ac.category_id' .
        ' ' . $whereSql .
        ' GROUP BY a.id' .
        ' ORDER BY a.created_at DESC, a.id DESC' .
        ' LIMIT :limit';

    $stmt = db()->prepare($sql);

    foreach ($params as $name => $value) {
        $type = str_starts_with($name, ':cat_') ? PDO::PARAM_INT : PDO::PARAM_STR;
        $stmt->bindValue($name, $value, $type);
    }

    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll();
}

function fetch_article_by_slug(string $slug): ?array
{
    $sql =
        'SELECT a.id, a.header, a.url_slug, a.content, a.latitude, a.longitude, a.created_at,' .
        " COALESCE(string_agg(DISTINCT c.name, ', ' ORDER BY c.name), '') AS categories_text" .
        ' FROM articles a' .
        ' LEFT JOIN article_categories ac ON ac.article_id = a.id' .
        ' LEFT JOIN categories c ON c.id = ac.category_id' .
        ' WHERE a.url_slug = :slug' .
        ' GROUP BY a.id' .
        ' LIMIT 1';

    $stmt = db()->prepare($sql);
    $stmt->bindValue(':slug', $slug, PDO::PARAM_STR);
    $stmt->execute();
    $article = $stmt->fetch();

    return $article === false ? null : $article;
}

function fetch_article_by_id(int $id): ?array
{
    $sql =
        'SELECT a.id, a.header, a.url_slug, a.content, a.latitude, a.longitude, a.created_at,' .
        " COALESCE(string_agg(DISTINCT c.name, ', ' ORDER BY c.name), '') AS categories_text" .
        ' FROM articles a' .
        ' LEFT JOIN article_categories ac ON ac.article_id = a.id' .
        ' LEFT JOIN categories c ON c.id = ac.category_id' .
        ' WHERE a.id = :id' .
        ' GROUP BY a.id' .
        ' LIMIT 1';

    $stmt = db()->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $article = $stmt->fetch();

    return $article === false ? null : $article;
}

function fetch_geo_articles(): array
{
    $sql =
        'SELECT id, header, url_slug, latitude, longitude ' .
        'FROM articles ' .
        'WHERE latitude IS NOT NULL AND longitude IS NOT NULL ' .
        'ORDER BY created_at DESC, id DESC';

    return db()->query($sql)->fetchAll();
}

function article_excerpt(string $content, int $maxLength = 220): string
{
    $plain = trim((string) preg_replace('/\s+/', ' ', strip_tags($content)));

    if (strlen($plain) <= $maxLength) {
        return $plain;
    }

    return substr($plain, 0, $maxLength) . '...';
}

function article_content_with_accessibility(string $content): string
{
    if ($content === '' || !class_exists(DOMDocument::class)) {
        return $content;
    }

    $internalErrors = libxml_use_internal_errors(true);

    $dom = new DOMDocument('1.0', 'UTF-8');
    $dom->loadHTML(
        '<!doctype html><html><body>' . $content . '</body></html>',
        LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
    );

    foreach ($dom->getElementsByTagName('img') as $image) {
        if (!$image->hasAttribute('alt') || trim($image->getAttribute('alt')) === '') {
            $image->setAttribute('alt', 'Illustration article');
        }
        if (!$image->hasAttribute('loading')) {
            $image->setAttribute('loading', 'lazy');
        }
    }

    $body = $dom->getElementsByTagName('body')->item(0);
    if ($body === null) {
        libxml_clear_errors();
        libxml_use_internal_errors($internalErrors);
        return $content;
    }

    $html = '';
    foreach ($body->childNodes as $childNode) {
        $html .= $dom->saveHTML($childNode);
    }

    libxml_clear_errors();
    libxml_use_internal_errors($internalErrors);

    return $html;
}

function format_published_date(?string $rawDate): string
{
    if ($rawDate === null || trim($rawDate) === '') {
        return 'N/A';
    }

    try {
        return (new DateTimeImmutable($rawDate))->format('Y-m-d H:i');
    } catch (Throwable $exception) {
        return 'N/A';
    }
}
