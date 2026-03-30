<?php

declare(strict_types=1);

require_once __DIR__ . '/layout.php';

$requestPath = normalize_path((string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH));
$queryString = $_SERVER['QUERY_STRING'] ?? '';

if ($requestPath === '/search.php') {
    redirect('/search' . ($queryString !== '' ? '?' . $queryString : ''), 301);
}

$httpStatus = 200;
$pageTitle = 'Recherche Articles';
$metaDescription = 'Recherche multicritere par mots cles et categories.';
$canonicalPath = '/search' . ($queryString !== '' ? '?' . $queryString : '');

$searchTerm = trim((string) ($_GET['q'] ?? ''));
$rawCategories = $_GET['categories'] ?? [];
if (!is_array($rawCategories)) {
    $rawCategories = [];
}
$selectedCategoryIds = clean_category_ids($rawCategories);

$categories = [];
$articles = [];
$dbError = null;

if ($requestPath !== '/search') {
    $httpStatus = 404;
    $pageTitle = 'Page Introuvable';
    $metaDescription = 'La page demandee est introuvable.';
    $canonicalPath = '/search';
}

try {
    $categories = nav_categories();
    if ($httpStatus === 200) {
        $articles = fetch_articles($searchTerm, $selectedCategoryIds, 120);
    }
} catch (Throwable $exception) {
    $dbError = 'Impossible de charger la recherche. Verifiez la base et les migrations.';
}

http_response_code($httpStatus);

render_page_head(
    $pageTitle,
    $metaDescription,
    $canonicalPath,
    $httpStatus === 404 ? 'noindex,follow' : 'index,follow'
);
render_site_header($categories, 'search');
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
      <h1 class="hero-title">Search articles</h1>
      <p class="subtitle">Filtrez avec texte et categories.</p>
    </section>

    <section class="section">
      <h2 class="page-title">Filtres</h2>
      <form method="get" action="/search" class="search-form">
        <div class="field">
          <label for="q">Recherche (LIKE)</label>
          <input id="q" class="input" type="text" name="q" value="<?= e($searchTerm); ?>" placeholder="Titre ou contenu...">
        </div>

        <fieldset class="fieldset">
          <legend>Categories</legend>
          <div class="checkbox-grid">
            <?php foreach ($categories as $category): ?>
              <label class="checkbox-item">
                <input
                  type="checkbox"
                  name="categories[]"
                  value="<?= (int) $category['id']; ?>"
                  <?= in_array((int) $category['id'], $selectedCategoryIds, true) ? 'checked' : ''; ?>
                >
                <span><?= e((string) $category['name']); ?></span>
              </label>
            <?php endforeach; ?>
          </div>
        </fieldset>

        <div class="form-actions">
          <button class="btn" type="submit">Rechercher</button>
          <a class="btn alt" href="/search">Reset</a>
        </div>
      </form>
    </section>

    <section class="section">
      <h2 class="page-title">Resultats</h2>
      <p class="results-meta"><?= count($articles); ?> article(s) trouves</p>

      <?php if ($articles === []): ?>
        <div class="empty">Aucun resultat pour ce filtre.</div>
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
