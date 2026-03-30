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

function start_session_if_needed(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $isHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

    session_name('geomonitor_backoffice');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();
}

function check_logged_in(): void
{
    start_session_if_needed();

    if (!isset($_SESSION['backoffice_user_id'])) {
        header('Location: login.php');
        exit;
    }
}

function check_login(string $username, string $password): bool
{
    start_session_if_needed();

    $sql =
        'SELECT id, username, password_hash ' .
        'FROM backoffice_users ' .
        'WHERE username = :username AND is_active = TRUE ' .
        'LIMIT 1';

    $stmt = db()->prepare($sql);
    $stmt->bindValue(':username', $username, PDO::PARAM_STR);
    $stmt->execute();
    $row = $stmt->fetch();

    if (!is_array($row)) {
        return false;
    }

    $storedHash = (string) $row['password_hash'];
    if (!password_verify($password, $storedHash)) {
        return false;
    }

    if (password_needs_rehash($storedHash, PASSWORD_DEFAULT)) {
        $newHash = password_hash($password, PASSWORD_DEFAULT);
        $rehashStmt = db()->prepare(
            'UPDATE backoffice_users SET password_hash = :password_hash, updated_at = NOW() WHERE id = :id'
        );
        $rehashStmt->bindValue(':password_hash', $newHash, PDO::PARAM_STR);
        $rehashStmt->bindValue(':id', (int) $row['id'], PDO::PARAM_INT);
        $rehashStmt->execute();
    }

    session_regenerate_id(true);
    $_SESSION['backoffice_user_id'] = (int) $row['id'];
    $_SESSION['backoffice_username'] = (string) $row['username'];

    return true;
}

function logout_user(): void
{
    start_session_if_needed();

    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'] ?? '', (bool) $params['secure'], (bool) $params['httponly']);
    }

    session_destroy();
}

function get_categories(): array
{
    $sql = 'SELECT id, name FROM categories ORDER BY name ASC';

    return db()->query($sql)->fetchAll();
}

function get_categories_with_article_count(): array
{
    $sql =
        'SELECT c.id, c.name, COUNT(ac.article_id) AS article_count ' .
        'FROM categories c ' .
        'LEFT JOIN article_categories ac ON ac.category_id = c.id ' .
        'GROUP BY c.id, c.name ' .
        'ORDER BY c.name ASC';

    return db()->query($sql)->fetchAll();
}

function create_category(string $name): int
{
    $sql = 'INSERT INTO categories (name) VALUES (:name) RETURNING id';
    $stmt = db()->prepare($sql);
    $stmt->bindValue(':name', $name, PDO::PARAM_STR);
    $stmt->execute();

    return (int) $stmt->fetchColumn();
}

function update_category(int $id, string $name): void
{
    $sql = 'UPDATE categories SET name = :name, updated_at = NOW() WHERE id = :id';
    $stmt = db()->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->bindValue(':name', $name, PDO::PARAM_STR);
    $stmt->execute();
}

function delete_category(int $id): void
{
    $sql = 'DELETE FROM categories WHERE id = :id';
    $stmt = db()->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
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
