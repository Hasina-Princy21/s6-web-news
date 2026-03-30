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
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Share+Tech+Mono&family=Barlow+Condensed:wght@300;400;500;700&family=Barlow:wght@300;400;500&display=swap');
    :root{
      --bg1:#0a0d12;
      --bg2:#101520;
      --bg3:#151c28;
      --acc:#c8922a;
      --txt:#ccd2e0;
      --muted:#6a7490;
      --border:rgba(200,146,42,0.2);
      --danger:#e05a2a;
      --safe:#2a9e6a;
    }
    *{box-sizing:border-box;margin:0;padding:0;}
    body{
      min-height:100vh;
      background:radial-gradient(circle at top right,#1a2234 0,#101520 35%,#0a0d12 75%);
      color:var(--txt);
      font-family:'Barlow',sans-serif;
      display:flex;
      flex-direction:column;
    }
    .topbar{
      height:52px;
      background:var(--bg2);
      border-bottom:1px solid var(--border);
      display:flex;
      align-items:center;
      justify-content:space-between;
      padding:0 18px;
    }
    .logo{
      font-family:'Barlow Condensed',sans-serif;
      font-size:20px;
      letter-spacing:.12em;
      color:var(--acc);
      text-transform:uppercase;
      font-weight:700;
    }
    .logo span{color:var(--txt);font-weight:300;}
    .meta{
      font-family:'Share Tech Mono',monospace;
      color:var(--muted);
      font-size:11px;
      letter-spacing:.08em;
      text-transform:uppercase;
    }
    .shell{
      max-width:980px;
      width:100%;
      margin:32px auto;
      padding:0 16px;
    }
    .card{
      background:linear-gradient(165deg,var(--bg2),var(--bg3));
      border:1px solid var(--border);
      padding:20px;
      margin-bottom:12px;
    }
    .head{
      display:flex;
      align-items:flex-end;
      justify-content:space-between;
      gap:10px;
      margin-bottom:8px;
    }
    h1{
      font-family:'Barlow Condensed',sans-serif;
      font-size:32px;
      letter-spacing:.08em;
      text-transform:uppercase;
      color:var(--acc);
      margin-bottom:4px;
    }
    h2{
      font-family:'Barlow Condensed',sans-serif;
      font-size:20px;
      letter-spacing:.08em;
      text-transform:uppercase;
      color:var(--acc);
      margin-bottom:12px;
    }
    .muted{font-size:14px;color:var(--muted);line-height:1.5;}
    .btn{
      height:36px;
      border:1px solid var(--acc);
      background:var(--acc);
      color:#1b1408;
      font-weight:700;
      letter-spacing:.05em;
      text-transform:uppercase;
      font-size:11px;
      padding:0 14px;
      cursor:pointer;
      text-decoration:none;
      display:inline-flex;
      align-items:center;
    }
    .btn.alt{background:transparent;color:var(--muted);border-color:var(--border);}
    .btn.danger{background:transparent;color:var(--danger);border-color:rgba(224,90,42,0.4);}
    .row{display:grid;grid-template-columns:1fr auto;gap:8px;}
    .input{
      width:100%;
      min-height:38px;
      border:1px solid var(--border);
      background:#0d131d;
      color:var(--txt);
      padding:8px 10px;
      font-size:14px;
      outline:none;
    }
    .input:focus{border-color:rgba(200,146,42,0.55);}
    .notice{
      border:1px solid rgba(224,90,42,0.35);
      background:rgba(224,90,42,0.08);
      color:#f2b39c;
      padding:10px 12px;
      margin-bottom:10px;
      font-size:13px;
      line-height:1.5;
    }
    .notice.ok{
      border-color:rgba(42,158,106,0.45);
      background:rgba(42,158,106,0.12);
      color:#a9e3c6;
    }
    .table{display:grid;gap:8px;}
    .item{
      border:1px solid var(--border);
      background:#0f1622;
      padding:10px;
      display:grid;
      grid-template-columns:1fr auto;
      gap:8px;
      align-items:center;
    }
    .item .meta-line{
      font-family:'Share Tech Mono',monospace;
      font-size:11px;
      color:var(--muted);
      margin-top:4px;
    }
    .actions{display:flex;gap:8px;}
    .inline-form{display:flex;gap:8px;align-items:center;}
    .inline-form .input{min-width:220px;}
    @media (max-width:760px){
      .shell{margin:16px auto;}
      .item{grid-template-columns:1fr;}
      .inline-form{flex-wrap:wrap;}
      .inline-form .input{min-width:0;width:100%;}
      .row{grid-template-columns:1fr;}
    }
  </style>
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

              <form method="post">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= (int) $category['id']; ?>">
                <button
                  class="btn danger"
                  type="submit"
                  onclick="return confirm('Delete this category?')"
                >
                  Delete
                </button>
              </form>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>
  </main>
</body>
</html>
