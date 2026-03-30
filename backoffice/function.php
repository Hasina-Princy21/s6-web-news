<?php

declare(strict_types=1);

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);


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

function get_categories(): array
{
    $sql = 'SELECT id, name FROM categories ORDER BY name ASC';

    return db()->query($sql)->fetchAll();
}

function get_articles(string $search = '', array $categoryIds = []): array
{
    $conditions = [];
    $params = [];

    if ($search !== '') {
        $conditions[] = '(a.header ILIKE :search OR a.url_slug ILIKE :search OR a.content ILIKE :search)';
        $params[':search'] = '%' . $search . '%';
    }

    $categoryFilterJoin = '';
    if ($categoryIds !== []) {
        $placeholders = [];
        foreach ($categoryIds as $index => $categoryId) {
            $placeholder = ':cat_' . $index;
            $placeholders[] = $placeholder;
            $params[$placeholder] = (int) $categoryId;
        }

        $categoryFilterJoin =
            ' INNER JOIN article_categories acf ON acf.article_id = a.id' .
            ' AND acf.category_id IN (' . implode(', ', $placeholders) . ')';
    }

    $whereSql = '';
    if ($conditions !== []) {
        $whereSql = 'WHERE ' . implode(' AND ', $conditions);
    }

    $sql =
        'SELECT a.id, a.header, a.url_slug, a.content, a.latitude, a.longitude, a.created_at,' .
        " COALESCE(string_agg(DISTINCT c.name, ', ' ORDER BY c.name), '') AS categories" .
        ' FROM articles a' .
        $categoryFilterJoin .
        ' LEFT JOIN article_categories ac ON ac.article_id = a.id' .
        ' LEFT JOIN categories c ON c.id = ac.category_id' .
        ' ' . $whereSql .
        ' GROUP BY a.id' .
        ' ORDER BY a.created_at DESC, a.id DESC';

    $stmt = db()->prepare($sql);

    foreach ($params as $name => $value) {
        $type = str_starts_with($name, ':cat_') ? PDO::PARAM_INT : PDO::PARAM_STR;
        $stmt->bindValue($name, $value, $type);
    }

    $stmt->execute();

    return $stmt->fetchAll();
}
