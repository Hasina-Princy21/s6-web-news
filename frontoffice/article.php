<?php

declare(strict_types=1);

require_once __DIR__ . '/layout.php';

$requestPath = normalize_path((string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH));
$id = (int) ($_GET['id'] ?? 0);
$incomingSlug = normalize_slug((string) ($_GET['slug'] ?? ''));

$categories = [];
$article = null;
$dbError = null;
$httpStatus = 200;

try {
    $categories = nav_categories();

    if ($id > 0) {
        $article = fetch_article_by_id($id);
    }

    if ($article === null) {
        $httpStatus = 404;
    }
} catch (Throwable $exception) {
    $dbError = 'Impossible de charger l article. Verifiez la base et les migrations.';
}

if ($article !== null) {
    $canonicalPath = article_url((string) $article['url_slug'], (int) $article['id']);

    if ($requestPath === '/article.php' || $requestPath !== $canonicalPath || $incomingSlug !== (string) $article['url_slug']) {
        redirect($canonicalPath, 301);
    }

    $pageTitle = (string) $article['header'];
    $metaDescription = article_excerpt((string) $article['content'], 150);
} else {
    $canonicalPath = '/';
    $pageTitle = 'Article Introuvable';
    $metaDescription = 'Article introuvable.';
}

http_response_code($httpStatus);

render_page_head(
    $pageTitle,
    $metaDescription,
    $canonicalPath,
    $httpStatus === 404 ? 'noindex,follow' : 'index,follow'
);
render_site_header($categories, '');
?>

<main class="container">
  <?php if ($dbError !== null): ?>
    <section class="section">
      <div class="notice"><?= e($dbError); ?></div>
    </section>
  <?php endif; ?>

  <?php if ($httpStatus === 404 || $article === null): ?>
    <section class="section">
      <h1 class="hero-title">404</h1>
      <p class="subtitle">L article demande est introuvable.</p>
    </section>
  <?php else: ?>
    <section class="section">
      <h1 class="hero-title"><?= e((string) $article['header']); ?></h1>
      <p class="subtitle">
        <strong>Publie:</strong> <?= e(format_published_date((string) $article['created_at'])); ?>
        <?php if ((string) $article['categories_text'] !== ''): ?>
          | <strong>Categories:</strong> <?= e((string) $article['categories_text']); ?>
        <?php endif; ?>
      </p>
    </section>

    <section class="section">
      <article class="article-content">
        <?= article_content_with_accessibility((string) $article['content']); ?>
      </article>
    </section>
  <?php endif; ?>
</main>

<?php render_site_footer();
