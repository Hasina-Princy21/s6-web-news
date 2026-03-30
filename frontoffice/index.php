<?php

declare(strict_types=1);
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>GeoMonitor - Frontoffice</title>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Share+Tech+Mono&family=Barlow+Condensed:wght@300;400;500;700&family=Barlow:wght@300;400;500&display=swap');
    :root{
      --bg1:#0a0d12;
      --bg2:#101520;
      --acc:#c8922a;
      --txt:#ccd2e0;
      --muted:#6a7490;
      --border:rgba(200,146,42,0.2);
    }
    *{box-sizing:border-box;margin:0;padding:0;}
    body{
      min-height:100vh;
      background:var(--bg1);
      color:var(--txt);
      font-family:'Barlow',sans-serif;
      display:flex;
      flex-direction:column;
    }
    header{
      height:52px;
      display:flex;
      align-items:center;
      justify-content:space-between;
      padding:0 18px;
      background:var(--bg2);
      border-bottom:1px solid var(--border);
    }
    .logo{
      font-family:'Barlow Condensed',sans-serif;
      font-size:20px;
      letter-spacing:.12em;
      color:var(--acc);
      text-transform:uppercase;
      font-weight:700;
    }
    .logo span{color:var(--txt);font-weight:300;}
    .meta{font-family:'Share Tech Mono',monospace;color:var(--muted);font-size:11px;}
    main{padding:34px 18px;font-size:15px;color:var(--muted);}
  </style>
</head>
<body>
  <header>
    <div class="logo">Geo<span>Monitor</span> Frontoffice</div>
    <div class="meta">placeholder</div>
  </header>
  <main>Frontoffice initialise.</main>
</body>
</html>
