<?php

declare(strict_types=1);

require_once __DIR__ . '/function.php';

start_session_if_needed();

if (isset($_SESSION['backoffice_user_id'])) {
    header('Location: index.php');
    exit;
}

$username = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $errorMessage = 'Username and password are required.';
    } elseif (check_login($username, $password)) {
        header('Location: index.php');
        exit;
    } else {
        $errorMessage = 'Invalid credentials.';
    }
}
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>GeoMonitor - Backoffice Login</title>
  <link rel="stylesheet" href="assets/css/base.min.css">
  <style>
    .login-shell { max-width: 460px; margin: 70px auto; padding: 0 16px; }
    .login-card { background: linear-gradient(165deg,var(--bg2),var(--bg3)); border: 1px solid var(--border); padding: 20px; }
    .login-title { font-family: 'Barlow Condensed',sans-serif; font-size: 30px; letter-spacing: .08em; text-transform: uppercase; color: var(--acc); margin-bottom: 8px; }
    .muted { color: var(--muted); margin-bottom: 14px; }
    .field { margin-bottom: 12px; }
    .field label { display: block; font-family: 'Share Tech Mono',monospace; color: var(--muted); font-size: 11px; text-transform: uppercase; letter-spacing: .08em; margin-bottom: 6px; }
    .input { width: 100%; min-height: 40px; border: 1px solid var(--border); background: #0d131d; color: var(--txt); padding: 9px 11px; outline: none; }
    .input:focus { border-color: rgba(200,146,42,0.55); }
    .btn { min-height: 36px; border: 1px solid var(--acc); background: var(--acc); color: #1b1408; font-weight: 700; text-transform: uppercase; font-family: 'Barlow Condensed',sans-serif; letter-spacing: .06em; font-size: 13px; padding: 0 14px; cursor: pointer; }
    .notice { border: 1px solid rgba(224,90,42,.35); background: rgba(224,90,42,.1); color: #f4bea9; font-size: 13px; line-height: 1.5; padding: 10px 12px; margin-bottom: 12px; }
  </style>
</head>
<body>
  <header class="topbar">
    <div class="logo">Geo<span>Monitor</span> Backoffice</div>
    <div class="meta">Authentication</div>
  </header>

  <main class="login-shell">
    <section class="login-card">
      <h1 class="login-title">Login</h1>
      <p class="muted">Please sign in to access the backoffice.</p>

      <?php if ($errorMessage !== ''): ?>
        <div class="notice"><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></div>
      <?php endif; ?>

      <form method="post" action="login.php">
        <div class="field">
          <label for="username">Username</label>
          <input id="username" name="username" class="input" type="text" value="<?= htmlspecialchars('admin', ENT_QUOTES, 'UTF-8'); ?>" required>
        </div>

        <div class="field">
          <label for="password">Password</label>
          <input id="password" name="password" class="input" type="password" required value="<?= htmlspecialchars('Admin123!', ENT_QUOTES, 'UTF-8'); ?>">
        </div>

        <button class="btn" type="submit">Sign in</button>
      </form>
    </section>
  </main>
</body>
</html>
