<?php

declare(strict_types=1);

require_once __DIR__ . '/function.php';

function e(string $value): string
{
  return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

$search = trim((string) ($_GET['q'] ?? ''));

$rawCategoryIds = $_GET['categories'] ?? [];
if (!is_array($rawCategoryIds)) {
  $rawCategoryIds = [];
}

$selectedCategoryIds = array_values(array_unique(array_filter(
  array_map(static fn($id): int => (int) $id, $rawCategoryIds),
  static fn(int $id): bool => $id > 0
)));

$categories = [];
$articles = [];
$errorMessage = null;
$createdId = isset($_GET['created']) ? (int) $_GET['created'] : 0;

try {
  $categories = get_categories();
  $articles = get_articles($search, $selectedCategoryIds);
} catch (Throwable $exception) {
  $errorMessage = 'Impossible de charger les donnees. Verifiez que les migrations ont bien ete appliquees.';
}
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>GeoMonitor - Backoffice</title>
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
    .logo span{
      color:var(--txt);
      font-weight:300;
    }
    .meta{
      font-family:'Share Tech Mono',monospace;
      color:var(--muted);
      font-size:11px;
      letter-spacing:.08em;
      text-transform:uppercase;
    }
    .shell{
      max-width:960px;
      width:100%;
      margin:48px auto;
      padding:0 16px;
    }
    .card{
      background:linear-gradient(165deg,var(--bg2),var(--bg3));
      border:1px solid var(--border);
      padding:28px;
      margin-bottom:12px;
    }
    h1{
      font-family:'Barlow Condensed',sans-serif;
      font-size:34px;
      letter-spacing:.08em;
      text-transform:uppercase;
      color:var(--acc);
      margin-bottom:10px;
    }
    p{
      font-size:15px;
      color:var(--muted);
      line-height:1.6;
    }
    h2{
      font-family:'Barlow Condensed',sans-serif;
      color:var(--acc);
      text-transform:uppercase;
      letter-spacing:.08em;
      font-size:20px;
      margin-bottom:14px;
    }
    .filters{
      display:grid;
      gap:14px;
    }
    .field label,
    .cats legend{
      display:block;
      font-size:12px;
      color:var(--muted);
      text-transform:uppercase;
      letter-spacing:.08em;
      margin-bottom:7px;
      font-family:'Share Tech Mono',monospace;
    }
    .input{
      width:100%;
      height:40px;
      border:1px solid var(--border);
      background:#0d131d;
      color:var(--txt);
      padding:0 12px;
      font-size:14px;
      outline:none;
    }
    .input:focus{
      border-color:rgba(200,146,42,0.55);
    }
    .cats{
      border:1px solid var(--border);
      padding:10px 12px 12px;
    }
    .cats-grid{
      display:grid;
      grid-template-columns:repeat(auto-fill,minmax(180px,1fr));
      gap:7px;
    }
    .chk{
      display:flex;
      align-items:center;
      gap:8px;
      font-size:13px;
      color:var(--txt);
    }
    .chk input{
      accent-color:var(--acc);
    }
    .actions{
      display:flex;
      gap:8px;
      align-items:center;
    }
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
    .btn.alt{
      background:transparent;
      color:var(--muted);
      border-color:var(--border);
    }
    .meta-line{
      margin-bottom:14px;
      color:var(--muted);
      font-size:13px;
    }
    .article-list{
      display:grid;
      gap:10px;
    }
    .article{
      border:1px solid var(--border);
      background:#0f1622;
      padding:16px;
    }
    .article h3{
      font-family:'Barlow Condensed',sans-serif;
      letter-spacing:.05em;
      font-size:22px;
      color:var(--txt);
      margin-bottom:6px;
    }
    .line{
      font-size:12px;
      color:var(--muted);
      margin-bottom:6px;
      line-height:1.5;
    }
    .line strong{
      color:var(--acc);
      font-family:'Share Tech Mono',monospace;
      font-size:11px;
      letter-spacing:.04em;
      text-transform:uppercase;
      margin-right:6px;
    }
    .excerpt{
      margin-top:8px;
      font-size:14px;
      line-height:1.65;
      color:var(--txt);
    }
    .notice{
      border:1px solid rgba(224,90,42,0.35);
      background:rgba(224,90,42,0.08);
      color:#f2b39c;
      padding:10px 12px;
      margin-bottom:12px;
      font-size:13px;
    }
    .notice.ok{
      border-color:rgba(42,158,106,0.45);
      background:rgba(42,158,106,0.12);
      color:#a9e3c6;
    }
    .empty{
      border:1px dashed var(--border);
      padding:14px;
      color:var(--muted);
      font-size:14px;
    }
    @media (max-width:700px){
      .shell{margin:18px auto;}
      .card{padding:16px;}
      h1{font-size:26px;}
    }
  </style>
</head>
<body>
  <header class="topbar">
    <div class="logo">Geo<span>Monitor</span> Backoffice</div>
    <div class="meta">No JS | PHP brut | SQL brut</div>
  </header>

  <main class="shell">
    <section class="card">
      <h1>Articles</h1>
      <p>Liste backoffice avec filtres simples en SQL brut.</p>
      <div class="actions" style="margin-top:12px;">
        <a class="btn" href="article_insert.php">Inserer un article</a>
        <a class="btn alt" href="categories.php">CRUD categories</a>
      </div>
    </section>

    <section class="card">
      <h2>Filtres</h2>
      <form method="get" class="filters">
        <div class="field">
          <label for="q">Recherche LIKE</label>
          <input id="q" class="input" type="text" name="q" value="<?= e($search); ?>" placeholder="Titre, slug, contenu...">
        </div>

        <fieldset class="cats">
          <legend>Categories</legend>
          <div class="cats-grid">
            <?php foreach ($categories as $category): ?>
              <?php $categoryId = (int) $category['id']; ?>
              <label class="chk">
                <input
                  type="checkbox"
                  name="categories[]"
                  value="<?= $categoryId; ?>"
                  <?= in_array($categoryId, $selectedCategoryIds, true) ? 'checked' : ''; ?>
                >
                <span><?= e((string) $category['name']); ?></span>
              </label>
            <?php endforeach; ?>
          </div>
        </fieldset>

        <div class="actions">
          <button class="btn" type="submit">Filtrer</button>
          <a class="btn alt" href="index.php">Reset</a>
        </div>
      </form>
    </section>

    <section class="card">
      <h2>Resultats</h2>

      <?php if ($errorMessage !== null): ?>
        <div class="notice"><?= e($errorMessage); ?></div>
      <?php endif; ?>

      <?php if ($createdId > 0): ?>
        <div class="notice ok">Article #<?= $createdId; ?> cree avec succes.</div>
      <?php endif; ?>

      <div class="meta-line"><?= count($articles); ?> article(s) trouve(s).</div>

      <?php if ($articles === []): ?>
        <div class="empty">Aucun article pour ce filtre.</div>
      <?php else: ?>
        <div class="article-list">
          <?php foreach ($articles as $article): ?>
            <?php
              $plainContent = trim((string) preg_replace('/\s+/', ' ', strip_tags((string) $article['content'])));
              $excerpt = strlen($plainContent) > 260 ? substr($plainContent, 0, 260) . '...' : $plainContent;

              $createdAt = '';
              if (!empty($article['created_at'])) {
                  try {
                    $createdAt = (new DateTimeImmutable((string) $article['created_at']))->format('Y-m-d H:i');
                  } catch (Throwable $exception) {
                    $createdAt = '';
                  }
              }

              $location = 'Non renseignee';
              if ($article['latitude'] !== null && $article['longitude'] !== null) {
                  $location = $article['latitude'] . ', ' . $article['longitude'];
              }
            ?>
            <article class="article">
              <h3><?= e((string) $article['header']); ?></h3>
              <div class="line"><strong>Slug</strong><?= e((string) $article['url_slug']); ?></div>
              <div class="line"><strong>Categories</strong><?= e((string) ($article['categories'] ?: 'Sans categorie')); ?></div>
              <div class="line"><strong>Localisation</strong><?= e($location); ?></div>
              <div class="line"><strong>Publie</strong><?= e($createdAt !== '' ? $createdAt : 'N/A'); ?></div>
              <div class="excerpt"><?= e($excerpt); ?></div>
            </article>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>
  </main>
</body>
</html>
