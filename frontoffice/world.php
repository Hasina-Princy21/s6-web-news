<?php

declare(strict_types=1);

require_once __DIR__ . '/layout.php';

$requestPath = normalize_path((string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH));
$queryString = $_SERVER['QUERY_STRING'] ?? '';

if ($requestPath === '/world.php') {
    redirect('/carte-du-monde' . ($queryString !== '' ? '?' . $queryString : ''), 301);
}

$httpStatus = 200;
$pageTitle = 'Carte du Monde';
$metaDescription = 'Visualisation geographique des articles avec localisation.';
$canonicalPath = '/carte-du-monde';

$categories = [];
$geoArticles = [];
$dbError = null;

if ($requestPath !== '/carte-du-monde') {
    $httpStatus = 404;
    $pageTitle = 'Page Introuvable';
    $metaDescription = 'La page demandee est introuvable.';
}

try {
    $categories = nav_categories();
    if ($httpStatus === 200) {
        $geoArticles = fetch_geo_articles();
    }
} catch (Throwable $exception) {
    $dbError = 'Impossible de charger la carte. Verifiez la base et les migrations.';
}

$mapData = [];
foreach ($geoArticles as $geoArticle) {
    $mapData[] = [
        'title' => (string) $geoArticle['header'],
        'lat' => (float) $geoArticle['latitude'],
        'lng' => (float) $geoArticle['longitude'],
        'url' => article_url((string) $geoArticle['url_slug'], (int) $geoArticle['id']),
    ];
}

$jsonMapData = json_encode(
    $mapData,
    JSON_UNESCAPED_SLASHES |
    JSON_UNESCAPED_UNICODE |
    JSON_HEX_TAG |
    JSON_HEX_AMP |
    JSON_HEX_APOS |
    JSON_HEX_QUOT
);
if ($jsonMapData === false) {
    $jsonMapData = '[]';
}

http_response_code($httpStatus);

render_page_head(
    $pageTitle,
    $metaDescription,
    $canonicalPath,
    $httpStatus === 404 ? 'noindex,follow' : 'index,follow',
    $httpStatus === 200
);
render_site_header($categories, 'map');
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
      <h1 class="hero-title">Carte du monde des articles</h1>
      <p class="subtitle">Les points affichent les articles avec latitude et longitude.</p>
    </section>

    <section class="section">
      <h2 class="page-title">Localisations</h2>

      <?php if ($geoArticles === []): ?>
        <div class="empty">Aucun article geolocalise pour le moment.</div>
      <?php else: ?>
        <div id="world-map" aria-label="Carte geographique mondiale"></div>

        <div class="world-list">
          <?php foreach ($geoArticles as $geoArticle): ?>
            <article class="world-item">
              <h3>
                <a href="<?= e(article_url((string) $geoArticle['url_slug'], (int) $geoArticle['id'])); ?>">
                  <?= e((string) $geoArticle['header']); ?>
                </a>
              </h3>
              <p class="news-meta">
                <strong>Coordonnees</strong><?= e((string) $geoArticle['latitude']); ?>, <?= e((string) $geoArticle['longitude']); ?>
              </p>
            </article>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>
  <?php endif; ?>
</main>

<?php if ($httpStatus === 200): ?>
  <script id="world-map-data" type="application/json"><?= $jsonMapData; ?></script>
  <script
    src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
    integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
    crossorigin=""
  ></script>
  <script src="/assets/js/world-map.js"></script>
<?php endif; ?>

<?php render_site_footer();
