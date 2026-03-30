<?php

declare(strict_types=1);

require_once __DIR__ . '/function.php';

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) ($_POST['action'] ?? '');

    try {
        if ($action === 'create') {
            $name = trim((string) ($_POST['name'] ?? ''));
            if ($name === '') {
                $errors[] = 'Category name is required.';
            }

            if ($errors === []) {
                create_category($name);
                header('Location: categories.php?status=created');
                exit;
            }
        }

        if ($action === 'update') {
            $id = (int) ($_POST['id'] ?? 0);
            $name = trim((string) ($_POST['name'] ?? ''));

            if ($id <= 0) {
                $errors[] = 'Invalid category id.';
            }
            if ($name === '') {
                $errors[] = 'Category name is required.';
            }

            if ($errors === []) {
                update_category($id, $name);
                header('Location: categories.php?status=updated');
                exit;
            }
        }

        if ($action === 'delete') {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id <= 0) {
                $errors[] = 'Invalid category id.';
            }

            if ($errors === []) {
                delete_category($id);
                header('Location: categories.php?status=deleted');
                exit;
            }
        }
    } catch (PDOException $exception) {
        if ($exception->getCode() === '23505') {
            $errors[] = 'Category name already exists.';
        } else {
            $errors[] = 'Database error while saving category.';
        }
    } catch (Throwable $exception) {
        $errors[] = 'Unexpected server error.';
    }
}

$status = (string) ($_GET['status'] ?? '');
$statusMessage = '';
if ($status === 'created') {
    $statusMessage = 'Category created.';
}
if ($status === 'updated') {
    $statusMessage = 'Category updated.';
}
if ($status === 'deleted') {
    $statusMessage = 'Category deleted.';
}

$categories = [];
try {
    $categories = get_categories_with_article_count();
} catch (Throwable $exception) {
    $errors[] = 'Unable to load categories. Check database and migrations.';
}
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>GeoMonitor - Categories</title>
  <link rel="stylesheet" href="assets/css/base.min.css">
  <link rel="stylesheet" href="assets/css/categories.min.css">
</head>
<body>
  <header class="topbar">
    <div class="logo">Geo<span>Monitor</span> Backoffice</div>
    <div class="meta">Categories CRUD</div>
  </header>

  <main class="shell">
    <section class="card">
      <div class="head">
        <div>
          <h1>Categories</h1>
          <p class="muted">Simple CRUD brut en PHP + SQL.</p>
        </div>
        <div class="actions">
          <a class="btn alt" href="article_insert.php">New article</a>
          <a class="btn alt" href="index.php">Back to articles</a>
        </div>
      </div>
    </section>

    <section class="card">
      <h2>Create category</h2>

      <?php if ($statusMessage !== ''): ?>
        <div class="notice ok"><?= e($statusMessage); ?></div>
      <?php endif; ?>

      <?php if ($errors !== []): ?>
        <div class="notice">
          <?php foreach ($errors as $error): ?>
            <div><?= e($error); ?></div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <form method="post" class="row">
        <input type="hidden" name="action" value="create">
        <input class="input" type="text" name="name" placeholder="Category name" required>
        <button class="btn" type="submit">Create</button>
      </form>
    </section>

    <section class="card">
      <h2>Existing categories</h2>

      <?php if ($categories === []): ?>
        <p class="muted">No category yet.</p>
      <?php else: ?>
        <div class="table">
          <?php foreach ($categories as $category): ?>
            <div class="item">
              <form class="inline-form" method="post">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" value="<?= (int) $category['id']; ?>">
                <input class="input" type="text" name="name" value="<?= e((string) $category['name']); ?>" required>
                <button class="btn" type="submit">Update</button>
                <div class="meta-line">Articles linked: <?= (int) $category['article_count']; ?></div>
              </form>

              <form method="post" class="js-delete-category-form">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= (int) $category['id']; ?>">
                <button class="btn danger" type="submit">
                  Delete
                </button>
              </form>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>
  </main>
  <script src="assets/js/categories.min.js"></script>
</body>
</html>
