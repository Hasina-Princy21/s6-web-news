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
  <link rel="stylesheet" href="assets/css/base.css">
  <link rel="stylesheet" href="assets/css/index.css">
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
      <div class="actions actions-top">
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
