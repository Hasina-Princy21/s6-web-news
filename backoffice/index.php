<?php

declare(strict_types=1);
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
  </style>
</head>
<body>
  <header class="topbar">
    <div class="logo">Geo<span>Monitor</span> Backoffice</div>
    <div class="meta">No JS | PHP brut | SQL brut</div>
  </header>

  <main class="shell">
    <section class="card">
      <h1>Page vide</h1>
      <p>Backoffice initialise. Le contenu fonctionnel arrive a l'etape suivante.</p>
    </section>
  </main>
</body>
</html>
