<?php

declare(strict_types=1);

require_once __DIR__ . '/function.php';

function render_page_head(
    string $pageTitle,
    string $metaDescription,
    string $canonicalPath,
    string $robotsMeta = 'index,follow',
    bool $includeLeaflet = false
): void {
    $fullTitle = $pageTitle . ' | ' . site_name();
    $canonical = canonical_url($canonicalPath);
    ?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= e($fullTitle); ?></title>
  <meta name="description" content="<?= e($metaDescription); ?>">
  <meta name="keywords" content="actualites, conflits, diplomatie, humanitaire, geopolitique">
  <meta name="robots" content="<?= e($robotsMeta); ?>">
  <meta property="og:title" content="<?= e($fullTitle); ?>">
  <meta property="og:description" content="<?= e($metaDescription); ?>">
  <meta property="og:type" content="website">
  <link rel="canonical" href="<?= e($canonical); ?>">
  <link rel="stylesheet" href="/assets/css/app.min.css">
  <?php if ($includeLeaflet): ?>
    <link
      rel="stylesheet"
      href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
      crossorigin=""
    >
  <?php endif; ?>
</head>
<body>
<?php
}

function render_site_header(array $categories, string $activeMain = '', string $activeCategorySlug = ''): void
{
    [$primaryCategories, $collapsedCategories] = split_nav_categories($categories, 6);

    $hasActiveCollapsed = false;
    foreach ($collapsedCategories as $category) {
        if (($category['slug'] ?? '') === $activeCategorySlug) {
            $hasActiveCollapsed = true;
            break;
        }
    }
    ?>
  <header class="site-header">
    <div class="topbar">
      <a class="logo" href="/">Geo<span>Monitor</span></a>
      <div class="tagline">Frontoffice news</div>
    </div>

    <nav class="nav-primary" aria-label="Navigation principale">
      <a class="nav-link <?= $activeMain === 'home' ? 'active' : ''; ?>" href="/">Liste articles</a>
      <a class="nav-link <?= $activeMain === 'map' ? 'active' : ''; ?>" href="/carte-du-monde">Carte du monde</a>
      <a class="nav-link <?= $activeMain === 'search' ? 'active' : ''; ?>" href="/search">Search</a>
    </nav>

    <nav class="nav-categories" aria-label="Navigation categories">
      <span class="nav-category-label">Categories</span>

      <?php foreach ($primaryCategories as $category): ?>
        <?php $isActive = ($activeCategorySlug !== '' && $category['slug'] === $activeCategorySlug); ?>
        <a class="nav-link <?= $isActive ? 'active' : ''; ?>" href="<?= e(category_url((string) $category['slug'])); ?>">
          <?= e((string) $category['name']); ?>
        </a>
      <?php endforeach; ?>

      <?php if ($collapsedCategories !== []): ?>
        <details class="nav-more" <?= $hasActiveCollapsed ? 'open' : ''; ?>>
          <summary class="nav-link nav-more-summary <?= $hasActiveCollapsed ? 'active' : ''; ?>">Autres categories</summary>
          <div class="nav-more-list">
            <?php foreach ($collapsedCategories as $category): ?>
              <?php $isActive = ($activeCategorySlug !== '' && $category['slug'] === $activeCategorySlug); ?>
              <a class="nav-link <?= $isActive ? 'active' : ''; ?>" href="<?= e(category_url((string) $category['slug'])); ?>">
                <?= e((string) $category['name']); ?>
              </a>
            <?php endforeach; ?>
          </div>
        </details>
      <?php endif; ?>
    </nav>
  </header>
<?php
}

function render_site_footer(): void
{
    ?>
  <footer class="site-footer">
    <p>GeoMonitor frontoffice | URLs normalisees | pages separees | metas SEO.</p>
  </footer>
</body>
</html>
<?php
}

function render_article_card(array $article): void
{
    ?>
<article class="news-card">
  <h3>
    <a href="<?= e(article_url((string) $article['url_slug'], (int) $article['id'])); ?>">
      <?= e((string) $article['header']); ?>
    </a>
  </h3>
  <p class="news-meta"><strong>Publie</strong><?= e(format_published_date((string) $article['created_at'])); ?></p>
  <p class="news-excerpt"><?= e(article_excerpt((string) $article['content'])); ?></p>

  <?php if ((string) ($article['categories_text'] ?? '') !== ''): ?>
    <div class="chips">
      <?php foreach (explode(', ', (string) $article['categories_text']) as $chip): ?>
        <span class="chip"><?= e($chip); ?></span>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</article>
<?php
}
