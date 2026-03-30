<?php

declare(strict_types=1);

require_once __DIR__ . '/layout.php';

$requestPath = normalize_path((string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH));
$rawSlug = (string) ($_GET['slug'] ?? '');
$normalizedSlug = normalize_slug($rawSlug);

if ($normalizedSlug === '') {
    http_response_code(404);
    render_page_head('Categorie Introuvable', 'Categorie introuvable.', '/category/introuvable', 'noindex,follow');
    render_site_header([], '');
    ?>
    <main class="container">
      <section class="section">
        <h1 class="hero-title">404</h1>
        <p class="subtitle">Categorie introuvable.</p>
      </section>
    </main>
    <?php
    render_site_footer();
    exit;
}

$canonicalPath = category_url($normalizedSlug);
if ($requestPath === '/category.php') {
    redirect($canonicalPath, 301);
}

if ($requestPath !== $canonicalPath) {
    redirect($canonicalPath, 301);
}

$categories = [];
$selectedCategory = null;
$articles = [];
$dbError = null;
$httpStatus = 200;

try {
    $categories = nav_categories();
    $selectedCategory = category_by_slug($normalizedSlug);

    if ($selectedCategory === null) {
        $httpStatus = 404;
    } else {
        $articles = fetch_articles('', [(int) $selectedCategory['id']], 120);
    }
} catch (Throwable $exception) {
    $dbError = 'Impossible de charger la categorie. Verifiez la base et les migrations.';
}

if ($selectedCategory === null) {
    $httpStatus = 404;
}

http_response_code($httpStatus);

$pageTitle = $httpStatus === 404
    ? 'Categorie Introuvable'
    : 'Categorie ' . (string) $selectedCategory['name'];
$metaDescription = $httpStatus === 404
    ? 'Categorie introuvable.'
    : 'Articles classes dans la categorie ' . (string) $selectedCategory['name'] . '.';

render_page_head(
    $pageTitle,
    $metaDescription,
    $canonicalPath,
    $httpStatus === 404 ? 'noindex,follow' : 'index,follow'
);
render_site_header($categories, '', $normalizedSlug);
?>

<main class="container">
  <?php if ($dbError !== null): ?>
    <section class="section">
      <div class="notice"><?= e($dbError); ?></div>
    </section>
  <?php endif; ?>

  <?php if ($httpStatus === 404): ?>
    <section class="section">
      <h1 class="hero-title">404</h1>
      <p class="subtitle">Categorie introuvable.</p>
    </section>
  <?php else: ?>
    <section class="section">
      <h1 class="hero-title">Categorie: <?= e((string) $selectedCategory['name']); ?></h1>
      <p class="subtitle">Articles associes a cette categorie.</p>
    </section>

    <section class="section">
      <h2 class="page-title">Articles de categorie</h2>
      <p class="results-meta"><?= count($articles); ?> article(s)</p>

      <?php if ($articles === []): ?>
        <div class="empty">Aucun article dans cette categorie.</div>
      <?php else: ?>
        <div class="card-grid">
          <?php foreach ($articles as $article): ?>
            <?php render_article_card($article); ?>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>
  <?php endif; ?>
</main>

<?php render_site_footer();
