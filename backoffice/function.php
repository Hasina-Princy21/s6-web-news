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

function normalize_slug(string $value): string
{
    $value = strtolower(trim($value));
    $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';
    $value = trim($value, '-');

    return $value;
}

function clean_category_ids(array $categoryIds): array
{
    return array_values(array_unique(array_filter(
        array_map(static fn($id): int => (int) $id, $categoryIds),
        static fn(int $id): bool => $id > 0
    )));
}

function parse_nullable_float(string $value): ?float
{
    $value = trim($value);
    if ($value === '') {
        return null;
    }

    $normalized = str_replace(',', '.', $value);
    if (!is_numeric($normalized)) {
        return null;
    }

    return (float) $normalized;
}

function insert_article(
    string $header,
    string $urlSlug,
    string $content,
    ?float $latitude,
    ?float $longitude,
    array $categoryIds
): int {
    $pdo = db();
    $pdo->beginTransaction();

    try {
        $insertArticleSql =
            'INSERT INTO articles (header, url_slug, content, latitude, longitude) ' .
            'VALUES (:header, :url_slug, :content, :latitude, :longitude) ' .
            'RETURNING id';

        $stmt = $pdo->prepare($insertArticleSql);
        $stmt->bindValue(':header', $header, PDO::PARAM_STR);
        $stmt->bindValue(':url_slug', $urlSlug, PDO::PARAM_STR);
        $stmt->bindValue(':content', $content, PDO::PARAM_STR);

        if ($latitude === null) {
            $stmt->bindValue(':latitude', null, PDO::PARAM_NULL);
        } else {
            $stmt->bindValue(':latitude', $latitude);
        }

        if ($longitude === null) {
            $stmt->bindValue(':longitude', null, PDO::PARAM_NULL);
        } else {
            $stmt->bindValue(':longitude', $longitude);
        }

        $stmt->execute();
        $articleId = (int) $stmt->fetchColumn();

        if ($categoryIds !== []) {
            $insertCategorySql =
                'INSERT INTO article_categories (article_id, category_id) VALUES (:article_id, :category_id)';
            $linkStmt = $pdo->prepare($insertCategorySql);

            foreach ($categoryIds as $categoryId) {
                $linkStmt->bindValue(':article_id', $articleId, PDO::PARAM_INT);
                $linkStmt->bindValue(':category_id', (int) $categoryId, PDO::PARAM_INT);
                $linkStmt->execute();
            }
        }

        $pdo->commit();

        return $articleId;
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $exception;
    }
}
