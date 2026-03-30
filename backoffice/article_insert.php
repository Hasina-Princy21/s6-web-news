<?php

declare(strict_types=1);

require_once __DIR__ . '/function.php';

check_logged_in();

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

$form = [
    'header' => '',
    'url_slug' => '',
    'content' => '',
    'latitude' => '',
    'longitude' => '',
    'categories' => [],
];

$errors = [];
$categories = [];

try {
    $categories = get_categories();
} catch (Throwable $exception) {
    $errors[] = 'Database unavailable. Run migrations and ensure PostgreSQL is running.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form['header'] = trim((string) ($_POST['header'] ?? ''));
    $form['url_slug'] = trim((string) ($_POST['url_slug'] ?? ''));
    $form['content'] = (string) ($_POST['content'] ?? '');
    $form['latitude'] = trim((string) ($_POST['latitude'] ?? ''));
    $form['longitude'] = trim((string) ($_POST['longitude'] ?? ''));

    $rawCategories = $_POST['categories'] ?? [];
    if (!is_array($rawCategories)) {
        $rawCategories = [];
    }
    $form['categories'] = clean_category_ids($rawCategories);

    if ($form['header'] === '') {
        $errors[] = 'Header is required.';
    }

    $resolvedSlug = normalize_slug($form['url_slug'] !== '' ? $form['url_slug'] : $form['header']);
    if ($resolvedSlug === '') {
        $errors[] = 'Slug is invalid. Use letters, numbers and dashes.';
    }

    if (trim(strip_tags($form['content'])) === '') {
        $errors[] = 'Content is required.';
    }

    $latRaw = $form['latitude'];
    $lngRaw = $form['longitude'];
    $latitude = parse_nullable_float($latRaw);
    $longitude = parse_nullable_float($lngRaw);

    if ($latRaw !== '' && $latitude === null) {
        $errors[] = 'Latitude must be numeric.';
    }

    if ($lngRaw !== '' && $longitude === null) {
        $errors[] = 'Longitude must be numeric.';
    }

    if (($latRaw === '' && $lngRaw !== '') || ($latRaw !== '' && $lngRaw === '')) {
        $errors[] = 'Fill both latitude and longitude, or leave both empty.';
    }

    if ($latitude !== null && ($latitude < -90 || $latitude > 90)) {
        $errors[] = 'Latitude must be between -90 and 90.';
    }

    if ($longitude !== null && ($longitude < -180 || $longitude > 180)) {
        $errors[] = 'Longitude must be between -180 and 180.';
    }

    if ($errors === []) {
        try {
            $articleId = insert_article(
                $form['header'],
                $resolvedSlug,
                $form['content'],
                $latitude,
                $longitude,
                $form['categories']
            );

            header('Location: index.php?created=' . $articleId);
            exit;
        } catch (PDOException $exception) {
            if ($exception->getCode() === '23505') {
                $errors[] = 'Slug already exists. Please choose another one.';
            } else {
                $errors[] = 'Unable to save article. Check your data and retry.';
            }
        } catch (Throwable $exception) {
            $errors[] = 'Unexpected error while saving article.';
        }
    }
}
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>GeoMonitor - New Article</title>
  <link
    rel="stylesheet"
    href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
    crossorigin=""
  >
  <script src="https://cdn.tiny.cloud/1/rg0f6cakemvp9dufe4yunqc3attvyeug3ch84lpjn96rj4n2/tinymce/8/tinymce.min.js" referrerpolicy="origin" crossorigin="anonymous"></script>
  <link rel="stylesheet" href="assets/css/base.min.css">
  <link rel="stylesheet" href="assets/css/article_insert.min.css">
</head>
<body>
  <header class="topbar">
    <div class="logo">Geo<span>Monitor</span> Backoffice</div>
    <div class="meta"><a href="logout.php" style="color:inherit;text-decoration:none;">Logout</a></div>
  </header>

  <main class="shell">
    <section class="card">
      <div class="head">
        <div>
          <h1>New article</h1>
          <p class="muted">Raw PHP form. Content editor via TinyMCE free, position picker via OpenStreetMap Leaflet.</p>
        </div>
        <div class="actions">
          <a class="btn alt" href="categories.php">Manage categories</a>
          <a class="btn alt" href="index.php">Back to list</a>
        </div>
      </div>
    </section>

    <section class="card">
      <?php if ($errors !== []): ?>
        <div class="notice">
          <strong>Validation errors:</strong>
          <ul>
            <?php foreach ($errors as $error): ?>
              <li><?= e($error); ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <form method="post" action="article_insert.php">
        <div class="row">
          <div class="field">
            <label for="header">Header</label>
            <input id="header" class="input" name="header" type="text" required value="<?= e($form['header']); ?>">
          </div>
          <div class="field">
            <label for="url_slug">URL Slug (optional)</label>
            <input id="url_slug" class="input" name="url_slug" type="text" value="<?= e($form['url_slug']); ?>" placeholder="auto from header if empty">
          </div>
        </div>

        <div class="field">
          <label for="content">Content (formatted HTML)</label>
          <textarea id="content" name="content" class="input content-editor"><?= e($form['content']); ?></textarea>
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
                  <?= in_array($categoryId, $form['categories'], true) ? 'checked' : ''; ?>
                >
                <span><?= e((string) $category['name']); ?></span>
              </label>
            <?php endforeach; ?>
          </div>
        </fieldset>

        <div class="mapbox">
          <div class="label">Position (OpenStreetMap)</div>
          <div id="map"></div>
          <div class="row3">
            <div class="field">
              <label for="latitude">Latitude</label>
              <input id="latitude" class="input" name="latitude" type="text" value="<?= e($form['latitude']); ?>" placeholder="e.g. 48.8566">
            </div>
            <div class="field">
              <label for="longitude">Longitude</label>
              <input id="longitude" class="input" name="longitude" type="text" value="<?= e($form['longitude']); ?>" placeholder="e.g. 2.3522">
            </div>
            <button class="btn alt" type="button" id="clear-position">Clear</button>
          </div>
        </div>

        <div class="actions">
          <button class="btn" type="submit">Save article</button>
          <a class="btn alt" href="index.php">Cancel</a>
        </div>
      </form>
    </section>
  </main>

  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
  <script src="assets/js/article_insert.min.js"></script>
</body>
</html>
