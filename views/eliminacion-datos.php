<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Eliminación de datos — Quién paga el asado</title>
  <?php require $root . '/views/partials/favicon-links.php'; ?>
  <meta name="description" content="Instrucciones para eliminar los datos personales del bot de WhatsApp Quién paga el asado.">
  <meta name="robots" content="index, follow">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    :root { color-scheme: dark; --background:#120d1a; --surface:#1a1225; --primary:#8c52ff; --primary-light:#b896ff; --text:#f8f5ff; --muted:#b9afc8; --border:rgba(255,255,255,.1); }
    * { box-sizing: border-box; }
    body { margin:0; min-height:100vh; background:radial-gradient(circle at 85% -10%,rgba(140,82,255,.22),transparent 32rem),var(--background); color:var(--text); font-family:"Public Sans",system-ui,sans-serif; font-size:16px; line-height:1.65; }
    a { color:var(--primary-light); }
    a:hover { color:#d3c1ff; }
    header { border-bottom:1px solid var(--border); background:rgba(18,13,26,.9); }
    .bar, main, footer div { width:min(100% - 2rem,760px); margin-inline:auto; }
    .bar { min-height:76px; display:flex; align-items:center; justify-content:space-between; gap:1rem; }
    .bar img { display:block; height:42px; width:auto; }
    .bar a:last-child { font-size:.9rem; font-weight:700; text-decoration:none; }
    main { padding-block:4.5rem; }
    .eyebrow { margin:0 0 .6rem; color:var(--primary-light); font-size:.78rem; font-weight:800; letter-spacing:.13em; text-transform:uppercase; }
    h1 { margin:0; font-size:clamp(2.2rem,7vw,4rem); line-height:1.04; letter-spacing:-.04em; }
    .lead { margin:1rem 0 2rem; color:var(--muted); font-size:1.08rem; }
    ol { margin:0; padding:0; list-style:none; counter-reset:steps; display:grid; gap:1rem; }
    li { counter-increment:steps; position:relative; padding:1.35rem 1.35rem 1.35rem 4.4rem; border:1px solid var(--border); border-radius:16px; background:rgba(26,18,37,.8); color:#ddd6e8; }
    li::before { content:counter(steps); position:absolute; top:1.15rem; left:1.25rem; width:2.15rem; height:2.15rem; display:grid; place-items:center; border-radius:50%; background:var(--primary); color:white; font-weight:800; }
    code { padding:.15rem .45rem; border:1px solid var(--border); border-radius:6px; background:#261a35; color:white; font-family:inherit; font-weight:800; }
    .note { margin-top:1.5rem; padding:1.35rem; border-left:3px solid var(--primary); border-radius:0 12px 12px 0; background:rgba(140,82,255,.1); color:#ddd6e8; }
    footer { margin-top:2rem; border-top:1px solid var(--border); color:var(--muted); font-size:.82rem; }
    footer div { padding-block:1.6rem; display:flex; flex-wrap:wrap; justify-content:space-between; gap:.6rem 1.25rem; }
    footer p { margin:0; }
    @media (max-width:560px) { main { padding-block:3rem; } .bar a:last-child { font-size:0; } .bar a:last-child::after { content:"Volver"; font-size:.9rem; } }
  </style>
</head>
<body>
<?php require $root . '/views/partials/gtm-body.php'; ?>
  <header>
    <div class="bar">
      <a href="/" aria-label="Ir al inicio de Juego de Equipo"><img src="/assets/logo.png" alt="Juego de Equipo"></a>
      <a href="/politica-de-privacidad">Ver política de privacidad</a>
    </div>
  </header>

  <main>
    <p class="eyebrow">Quién paga el asado</p>
    <h1>Eliminación de datos</h1>
    <p class="lead">Podés solicitar la eliminación de tus datos personales directamente desde WhatsApp.</p>

    <ol>
      <li>Abrí la conversación con el bot <strong>Quién paga el asado</strong>.</li>
      <li>Enviá exactamente el mensaje <code>eliminar mis datos</code>.</li>
      <li>El bot anonimizará tu número y tu nombre en las juntadas y te enviará una confirmación por el mismo chat.</li>
    </ol>

    <div class="note">
      Los importes y movimientos compartidos pueden conservarse sin tu identidad para mantener correctas las cuentas de los demás participantes. Si no podés acceder al bot, escribinos desde tu correo a <a href="mailto:contacto@juegodeequipo.com?subject=Eliminaci%C3%B3n%20de%20datos%20-%20Qui%C3%A9n%20paga%20el%20asado">contacto@juegodeequipo.com</a> indicando que necesitás eliminar los datos asociados a tu cuenta de WhatsApp.
    </div>
  </main>

  <footer>
    <div>
      <p>© 2026 Juego de Equipo.</p>
      <p><a href="/politica-de-privacidad">Política de privacidad</a></p>
    </div>
  </footer>
</body>
</html>
