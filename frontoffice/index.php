<?php

declare(strict_types=1);

require_once __DIR__ . '/layout.php';

$requestPath = normalize_path((string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH));
$queryString = $_SERVER['QUERY_STRING'] ?? '';

if ($requestPath === '/index.php') {
    redirect('/' . ($queryString !== '' ? '?' . $queryString : ''), 301);
}

$httpStatus = 200;
$pageTitle = 'Liste Articles';
$metaDescription = 'Consultez les dernieres actualites de conflits, diplomatie et humanitaire.';
$canonicalPath = '/';

$categories = [];
$articles = [];
$dbError = null;

if ($requestPath !== '/') {
    $httpStatus = 404;
    $pageTitle = 'Page Introuvable';
    $metaDescription = 'La page demandee est introuvable.';
}

try {
    $categories = nav_categories();
    if ($httpStatus === 200) {
        $articles = fetch_articles('', [], 80);
    }
} catch (Throwable $exception) {
    $dbError = 'Impossible de charger le frontoffice. Verifiez la base et les migrations.';
}

http_response_code($httpStatus);

render_page_head(
    $pageTitle,
    $metaDescription,
    $canonicalPath,
    $httpStatus === 404 ? 'noindex,follow' : 'index,follow'
);
render_site_header($categories, 'home');
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
      <p class="subtitle">La page demandee n'existe pas ou n'est plus disponible.</p>
    </section>
  <?php else: ?>
    <section class="section">
      <h1 class="hero-title">Actualites conflits et diplomatie</h1>
      <p class="subtitle">Liste complete des news. Cliquez sur un article pour lire le contenu complet.</p>
    </section>

    <section class="section">
      <h2 class="page-title">Derniers articles</h2>
      <p class="results-meta"><?= count($articles); ?> article(s)</p>

      <?php if ($articles === []): ?>
        <div class="empty">Aucun article disponible.</div>
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
