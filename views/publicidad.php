<?php
// ================================================================
//  CONFIGURACIÓN — Editar antes de cada presentación al cliente
// ================================================================
$contactEmail    = 'contacto@juegodeequipo.com';
$contactWhatsApp = '';   // ej: 5491123456789 (sin + ni espacios; vacío = oculto)

$posiciones = [
    [
        'num'       => '1',
        'id'        => 'home-hero',
        'pagina'    => 'Página Principal',
        'tier'      => '$$$$',
        'desktop'   => 'Billboard 970×90',
        'mobile'    => 'Mobile Large 320×100',
        'ubicacion' => 'Debajo del hero, sobre el carrusel de sponsors — primer impacto visual',
        'precio'    => 'A consultar',
    ],
    [
        'num'       => '2',
        'id'        => 'home-news',
        'pagina'    => 'Página Principal',
        'tier'      => '$$$',
        'desktop'   => 'Leaderboard 728×90',
        'mobile'    => 'Mobile Banner 320×50',
        'ubicacion' => 'Entre últimas noticias y el selector de categoría',
        'precio'    => 'A consultar',
    ],
    [
        'num'       => '3',
        'id'        => 'home-fixture',
        'pagina'    => 'Página Principal',
        'tier'      => '$$$',
        'desktop'   => '2× Rectángulo 300×250',
        'mobile'    => 'Rectángulo 300×250',
        'ubicacion' => 'Entre la sección Fixture y la Tabla de Posiciones',
        'precio'    => 'A consultar',
    ],
    [
        'num'       => '4',
        'id'        => 'home-tabla',
        'pagina'    => 'Página Principal',
        'tier'      => '$$',
        'desktop'   => 'Rectángulo 300×250',
        'mobile'    => 'Rectángulo 300×250',
        'ubicacion' => 'Entre la Tabla de Posiciones y los Goleadores',
        'precio'    => 'A consultar',
    ],
    [
        'num'       => '5',
        'id'        => 'home-prefooter',
        'pagina'    => 'Página Principal',
        'tier'      => '$$',
        'desktop'   => 'Leaderboard 728×90',
        'mobile'    => 'Mobile Banner 320×50',
        'ubicacion' => 'Cierre de página, justo antes del footer',
        'precio'    => 'A consultar',
    ],
    [
        'num'       => '6',
        'id'        => 'home-sticky',
        'pagina'    => 'Página Principal',
        'tier'      => '$$$$',
        'desktop'   => '—',
        'mobile'    => 'Sticky Bottom 320×50',
        'ubicacion' => 'Fijo en la base de la pantalla — solo mobile, visible en todo momento al hacer scroll',
        'precio'    => 'A consultar',
    ],
    [
        'num'       => 'A',
        'id'        => 'nota-article',
        'pagina'    => 'Nota / Artículo',
        'tier'      => '$$$$',
        'desktop'   => 'Leaderboard 728×90',
        'mobile'    => 'Mobile Large 320×100',
        'ubicacion' => 'Entre el copete y el cuerpo del artículo — punto de máxima atención del lector',
        'precio'    => 'A consultar',
    ],
    [
        'num'       => 'B',
        'id'        => 'nota-sidebar',
        'pagina'    => 'Nota / Artículo',
        'tier'      => '$$$',
        'desktop'   => 'Medio Página 300×600',
        'mobile'    => '—',
        'ubicacion' => 'Sidebar lateral sticky — acompaña al lector durante toda la lectura del artículo',
        'precio'    => 'A consultar',
    ],
];

// Configuración visual por tier
function tc(string $t): array {
    return match($t) {
        '$$$$'  => ['clr' => '#fbbf24', 'brd' => 'rgba(251,191,36,.55)',  'bg' => 'rgba(251,191,36,.1)',   'lbl' => 'PREMIUM',  'pill' => 'rgba(251,191,36,.2)',  'row' => 'rgba(251,191,36,.04)'],
        '$$$'   => ['clr' => '#a478ff', 'brd' => 'rgba(164,120,255,.55)', 'bg' => 'rgba(140,82,255,.1)',   'lbl' => 'STANDARD', 'pill' => 'rgba(140,82,255,.2)',  'row' => 'rgba(140,82,255,.03)'],
        default => ['clr' => '#64748b', 'brd' => 'rgba(100,116,139,.4)',  'bg' => 'rgba(100,116,139,.08)', 'lbl' => 'ECONOMY',  'pill' => 'rgba(100,116,139,.16)','row' => 'transparent'],
    };
}

// Wireframe: bloque de contenido genérico
function wfS(string $label, string $minH = '32px', string $sx = ''): string {
    return '<div style="background:rgba(255,255,255,.035);border:1px solid rgba(255,255,255,.07);border-radius:7px;min-height:' . $minH . ';display:flex;align-items:center;justify-content:center;padding:6px 10px;color:rgba(255,255,255,.22);font-size:9px;font-weight:700;letter-spacing:.07em;text-transform:uppercase;margin-bottom:5px;' . $sx . '">' . $label . '</div>';
}

// Wireframe: slot de banner
function wfB(string $num, string $label, string $dims, string $tier, string $minH = '50px', string $sx = ''): string {
    $c = tc($tier);
    return '<div style="background:' . $c['bg'] . ';border:2px dashed ' . $c['brd'] . ';border-radius:7px;min-height:' . $minH . ';display:flex;align-items:center;justify-content:center;flex-direction:column;gap:3px;padding:8px 10px;margin-bottom:5px;text-align:center;' . $sx . '">
        <div style="display:flex;align-items:center;gap:5px;flex-wrap:wrap;justify-content:center">
            <span style="font-size:12px;font-weight:900;color:' . $c['clr'] . '">Slot ' . $num . '</span>
            <span style="font-size:7px;font-weight:800;background:' . $c['pill'] . ';color:' . $c['clr'] . ';padding:2px 7px;border-radius:4px;letter-spacing:.06em">' . $tier . ' · ' . $c['lbl'] . '</span>
        </div>
        <div style="font-size:9px;font-weight:700;color:' . $c['clr'] . ';opacity:.85">' . $label . '</div>
        <div style="font-size:8px;color:rgba(255,255,255,.2)">' . $dims . '</div>
    </div>';
}
?>
<!DOCTYPE html>
<html class="dark" lang="es">
<head>
  <meta charset="utf-8"/>
  <!-- Google Tag Manager -->
  <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-W5HHC9P3');</script>
  <!-- End Google Tag Manager -->
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Publicidad — Juego de Equipo</title>
  <meta name="robots" content="noindex,nofollow"/>
  <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
  <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
  <script>
    tailwind.config = {
      darkMode: "class",
      theme: {
        extend: {
          colors: {
            primary: "#8c52ff",
            "primary-light": "#a478ff",
            "background-dark": "#120d1a",
            "card-dark": "#1a1225",
          },
          fontFamily: { sans: ["Public Sans", "sans-serif"] },
        },
      },
    };
  </script>
  <style>
    body { font-family: "Public Sans", sans-serif; }
    .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
    .glass     { background: rgba(255,255,255,.04); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,.08); }
    .glass-card{ background: rgba(255,255,255,.06); border: 1px solid rgba(255,255,255,.10); }
    .page-tab  { transition: all .2s; cursor: pointer; border-radius: 8px; padding: 8px 20px; font-size: 14px; font-weight: 700; }
    .page-tab.active  { background: #8c52ff; color: #fff; }
    .page-tab:not(.active) { color: rgba(255,255,255,.45); }
    .page-tab:not(.active):hover { color: #fff; background: rgba(255,255,255,.06); }
    .dev-btn   { transition: all .2s; cursor: pointer; border-radius: 6px; padding: 6px 14px; font-size: 11px; font-weight: 700; display: flex; align-items: center; gap: 5px; }
    .dev-btn.active  { background: rgba(255,255,255,.1); color: #fff; }
    .dev-btn:not(.active) { color: rgba(255,255,255,.38); }
    .dev-btn:not(.active):hover { color: #fff; }
    .mac-dots  { display: flex; gap: 5px; }
    .mac-dots span { width: 9px; height: 9px; border-radius: 50%; display: block; }
  </style>
</head>
<body class="bg-background-dark text-white antialiased selection:bg-primary selection:text-white">
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-W5HHC9P3"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->

<!-- HEADER -->
<header class="sticky top-0 z-50 bg-background-dark/90 backdrop-blur-md border-b border-white/8">
  <div class="max-w-6xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
    <a href="/" class="flex items-center gap-2.5">
      <img src="/assets/logo.png" alt="Juego de Equipo" class="h-8 w-auto object-contain"/>
      <span class="font-black text-sm uppercase tracking-tight">Juego de <span class="text-primary">Equipo</span></span>
    </a>
    <a href="/" class="text-xs text-gray-500 hover:text-white transition-colors flex items-center gap-1">
      <span class="material-symbols-outlined text-[15px]">arrow_back</span> Ver el sitio
    </a>
  </div>
</header>

<!-- HERO -->
<section class="relative pt-20 pb-14 overflow-hidden">
  <div class="absolute inset-0 -z-10 overflow-hidden">
    <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-primary/15 blur-[140px] rounded-full"></div>
    <div class="absolute bottom-0 left-0 w-[400px] h-[400px] bg-amber-400/8 blur-[140px] rounded-full"></div>
  </div>
  <div class="max-w-6xl mx-auto px-4 sm:px-6 text-center">
    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-primary/10 border border-primary/20 text-primary text-xs font-bold uppercase tracking-widest mb-6">
      <span class="material-symbols-outlined text-[14px]">campaign</span>
      Kit de Medios · Temporada 2025/26
    </div>
    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black mb-5 leading-[1.1]">
      Publicite en<br/>
      <span class="text-transparent bg-clip-text bg-gradient-to-r from-primary to-amber-400">Juego de Equipo</span>
    </h1>
    <p class="text-gray-400 max-w-2xl mx-auto text-base sm:text-lg mb-12 leading-relaxed">
      La plataforma oficial de la liga local. Llegue a toda la comunidad futbolera con su marca en los espacios publicitarios más estratégicos del sitio.
    </p>

    <!-- Metric cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 max-w-3xl mx-auto">
      <?php
      $metrics = [
          ['icon' => 'sports_soccer', 'val' => '3 Categorías',     'sub' => 'Primera, Tercera y Senior'],
          ['icon' => 'emoji_events',  'val' => 'Liga Oficial',      'sub' => 'Temporada 2025/26 activa'],
          ['icon' => 'update',        'val' => 'Tiempo Real',       'sub' => 'Resultados y tablas en vivo'],
          ['icon' => 'groups',        'val' => 'Comunidad',         'sub' => 'Jugadores, familias y barras'],
      ];
      foreach ($metrics as $m): ?>
        <div class="glass-card rounded-2xl p-4 flex flex-col items-center gap-2 text-center">
          <span class="material-symbols-outlined text-primary" style="font-size:28px"><?= $m['icon'] ?></span>
          <span class="text-sm font-black text-white leading-snug"><?= $m['val'] ?></span>
          <span class="text-xs text-gray-500 leading-snug"><?= $m['sub'] ?></span>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- TIER LEGEND -->
<section class="py-10 border-y border-white/5">
  <div class="max-w-6xl mx-auto px-4 sm:px-6">
    <p class="text-center text-[10px] font-bold tracking-[.2em] text-gray-500 uppercase mb-6">Niveles de posicionamiento</p>
    <div class="grid sm:grid-cols-3 gap-4">
      <?php
      $tiers = [
          ['t' => '$$$$', 'icon' => 'star',        'name' => 'Premium',  'clr' => '#fbbf24', 'bg' => 'rgba(251,191,36,.08)',  'brd' => 'rgba(251,191,36,.25)',  'desc' => 'Mayor visibilidad. Posiciones de primer impacto: bajo el hero, en el artículo y sticky en mobile. El usuario llega con máxima atención.'],
          ['t' => '$$$',  'icon' => 'trending_up', 'name' => 'Standard', 'clr' => '#a478ff', 'bg' => 'rgba(140,82,255,.08)',  'brd' => 'rgba(140,82,255,.25)',  'desc' => 'Alta visibilidad entre secciones de alto consumo: noticias, fixture, tabla y sidebar sticky en artículos.'],
          ['t' => '$$',   'icon' => 'label',       'name' => 'Economy',  'clr' => '#64748b', 'bg' => 'rgba(100,116,139,.06)', 'brd' => 'rgba(100,116,139,.2)', 'desc' => 'Presencia constante a precio accesible. Ideal para marcas que quieren visibilidad sostenida sin la inversión Premium.'],
      ];
      foreach ($tiers as $tr): ?>
        <div style="background:<?= $tr['bg'] ?>;border:1px solid <?= $tr['brd'] ?>;border-radius:16px;padding:22px">
          <div style="display:flex;align-items:center;gap:9px;margin-bottom:11px">
            <span class="material-symbols-outlined" style="color:<?= $tr['clr'] ?>;font-size:22px"><?= $tr['icon'] ?></span>
            <span style="font-size:14px;font-weight:900;color:<?= $tr['clr'] ?>"><?= $tr['t'] ?> &nbsp;<?= $tr['name'] ?></span>
          </div>
          <p style="font-size:12px;color:rgba(255,255,255,.48);line-height:1.65"><?= $tr['desc'] ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- WIREFRAMES -->
<section class="py-16">
  <div class="max-w-6xl mx-auto px-4 sm:px-6">
    <div class="text-center mb-10">
      <h2 class="text-2xl sm:text-3xl font-black uppercase mb-2">Mapa de <span class="text-primary">Posiciones</span></h2>
      <p class="text-gray-500 text-sm">Visualizá dónde aparece cada anuncio dentro del sitio</p>
    </div>

    <!-- PAGE TABS -->
    <div class="flex rounded-xl bg-white/5 p-1 gap-1 w-fit mx-auto mb-10">
      <button class="page-tab active" onclick="switchPage('home', this)">Página Principal</button>
      <button class="page-tab" onclick="switchPage('nota', this)">Nota / Artículo</button>
    </div>

    <!-- ══════════════ HOME ══════════════ -->
    <div id="wf-home">
      <div class="flex items-center gap-2 mb-7 justify-center">
        <span class="text-xs text-gray-500">Vista:</span>
        <div class="flex rounded-lg bg-white/5 p-0.5 gap-0.5">
          <button class="dev-btn active" onclick="switchDevice('home','desktop',this)">
            <span class="material-symbols-outlined" style="font-size:14px">computer</span> Desktop
          </button>
          <button class="dev-btn" onclick="switchDevice('home','mobile',this)">
            <span class="material-symbols-outlined" style="font-size:14px">smartphone</span> Mobile
          </button>
        </div>
      </div>

      <!-- Desktop wireframe HOME -->
      <div id="wf-home-desktop" class="mx-auto" style="max-width:700px">
        <div class="glass rounded-2xl p-5">
          <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px">
            <div class="mac-dots">
              <span style="background:#ef4444"></span>
              <span style="background:#f59e0b"></span>
              <span style="background:#22c55e"></span>
            </div>
            <span style="font-size:10px;color:rgba(255,255,255,.2)">juegodeequipo.com — Página Principal (Desktop)</span>
          </div>
          <?= wfS('Header / Navegación', '32px') ?>
          <?= wfS('HERO: "Viví Cada Partido" + Video', '80px') ?>
          <?= wfB('1', 'Billboard', '970 × 90 px', '$$$$', '54px') ?>
          <?= wfS('Carrusel de Sponsors', '36px') ?>
          <?= wfS('Últimas Noticias (grid 3 columnas)', '60px') ?>
          <?= wfB('2', 'Leaderboard', '728 × 90 px', '$$$', '52px') ?>
          <?= wfS('Selector de Categoría (sticky)', '30px') ?>
          <?= wfS('Fixture & Resultados — cards por jornada', '80px') ?>
          <div style="display:flex;gap:6px;margin-bottom:5px">
            <?= wfB('3a', 'Rectángulo', '300 × 250 px', '$$$', '70px', 'flex:1;margin-bottom:0') ?>
            <?= wfB('3b', 'Rectángulo', '300 × 250 px', '$$$', '70px', 'flex:1;margin-bottom:0') ?>
          </div>
          <div style="margin-bottom:5px"></div>
          <?= wfS('Tabla de Posiciones', '60px') ?>
          <?= wfB('4', 'Rectángulo', '300 × 250 px', '$$', '70px') ?>
          <?= wfS('Ranking Goleadores', '55px') ?>
          <?= wfS('Ranking Tarjetas', '50px') ?>
          <?= wfS('Historial H2H', '50px') ?>
          <?= wfB('5', 'Leaderboard', '728 × 90 px', '$$', '50px') ?>
          <?= wfS('Footer', '38px') ?>
        </div>
        <p class="text-center text-xs text-gray-600 mt-3">El Slot 6 (Sticky Mobile) no aplica en desktop</p>
      </div>

      <!-- Mobile wireframe HOME -->
      <div id="wf-home-mobile" class="mx-auto hidden" style="max-width:340px">
        <div class="glass p-4" style="border-radius:32px">
          <div style="font-size:9px;color:rgba(255,255,255,.2);text-align:center;margin-bottom:12px">
            juegodeequipo.com — Mobile
          </div>
          <?= wfS('Header / Navegación', '28px') ?>
          <?= wfS('HERO', '60px') ?>
          <?= wfB('1', 'Mobile Large', '320 × 100 px', '$$$$', '46px') ?>
          <?= wfS('Carrusel Sponsors', '28px') ?>
          <?= wfS('Últimas Noticias', '52px') ?>
          <?= wfB('2', 'Mobile Banner', '320 × 50 px', '$$$', '38px') ?>
          <?= wfS('Selector de Categoría (sticky)', '28px') ?>
          <?= wfS('Fixture — cards', '70px') ?>
          <?= wfB('3', 'Rectángulo', '300 × 250 px', '$$$', '66px') ?>
          <?= wfS('Tabla de Posiciones', '55px') ?>
          <?= wfB('4', 'Rectángulo', '300 × 250 px', '$$', '66px') ?>
          <?= wfS('Goleadores', '55px') ?>
          <?= wfS('Tarjetas', '45px') ?>
          <?= wfS('H2H', '45px') ?>
          <?= wfB('5', 'Mobile Banner', '320 × 50 px', '$$', '38px') ?>
          <?= wfS('Footer', '36px') ?>
          <div style="position:relative;margin-top:8px">
            <?= wfB('6', 'STICKY BOTTOM — siempre visible al hacer scroll', '320 × 50 px', '$$$$', '42px', 'margin-bottom:0') ?>
            <span style="position:absolute;top:-9px;right:6px;font-size:7px;font-weight:800;background:rgba(251,191,36,.2);color:#fbbf24;padding:2px 7px;border-radius:4px;letter-spacing:.06em">FIJO</span>
          </div>
        </div>
      </div>
    </div>

    <!-- ══════════════ NOTA ══════════════ -->
    <div id="wf-nota" class="hidden">
      <div class="flex items-center gap-2 mb-7 justify-center">
        <span class="text-xs text-gray-500">Vista:</span>
        <div class="flex rounded-lg bg-white/5 p-0.5 gap-0.5">
          <button class="dev-btn active" onclick="switchDevice('nota','desktop',this)">
            <span class="material-symbols-outlined" style="font-size:14px">computer</span> Desktop
          </button>
          <button class="dev-btn" onclick="switchDevice('nota','mobile',this)">
            <span class="material-symbols-outlined" style="font-size:14px">smartphone</span> Mobile
          </button>
        </div>
      </div>

      <!-- Desktop wireframe NOTA -->
      <div id="wf-nota-desktop" class="mx-auto" style="max-width:820px">
        <div class="glass rounded-2xl p-5">
          <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px">
            <div class="mac-dots">
              <span style="background:#ef4444"></span>
              <span style="background:#f59e0b"></span>
              <span style="background:#22c55e"></span>
            </div>
            <span style="font-size:10px;color:rgba(255,255,255,.2)">juegodeequipo.com/noticias/[slug] — Nota (Desktop)</span>
          </div>
          <?= wfS('Header / Navegación', '32px') ?>
          <?= wfS('Imagen Hero del Artículo', '72px') ?>
          <!-- Two-column layout -->
          <div style="display:flex;gap:10px;align-items:flex-start">
            <!-- Article col -->
            <div style="flex:1;min-width:0">
              <?= wfS('Breadcrumb', '22px') ?>
              <?= wfS('Título del Artículo', '50px') ?>
              <?= wfS('Autor · Fecha · Tiempo de lectura', '24px') ?>
              <?= wfS('Copete (borde izquierdo morado)', '38px') ?>
              <?= wfS('─── hr ───', '10px') ?>
              <?= wfB('A', 'Leaderboard', '728 × 90 px', '$$$$', '52px') ?>
              <?= wfS('Cuerpo del artículo (rich text)', '120px') ?>
              <?= wfS('Compartir (mobile, oculto aquí)', '36px') ?>
            </div>
            <!-- Aside col -->
            <div style="width:210px;flex-shrink:0">
              <?= wfS('Autor', '52px') ?>
              <?= wfS('Compartir', '74px') ?>
              <?= wfB('B', 'Medio Página', '300 × 600 px', '$$$', '168px', 'width:100%') ?>
              <?= wfS('También te puede interesar', '80px') ?>
            </div>
          </div>
          <?= wfS('Footer', '38px') ?>
        </div>
      </div>

      <!-- Mobile wireframe NOTA -->
      <div id="wf-nota-mobile" class="mx-auto hidden" style="max-width:340px">
        <div class="glass p-4" style="border-radius:32px">
          <div style="font-size:9px;color:rgba(255,255,255,.2);text-align:center;margin-bottom:12px">
            juegodeequipo.com/noticias/[slug] — Mobile
          </div>
          <?= wfS('Header / Navegación', '28px') ?>
          <?= wfS('Imagen Hero', '62px') ?>
          <?= wfS('Breadcrumb', '20px') ?>
          <?= wfS('Título del Artículo', '50px') ?>
          <?= wfS('Autor · Fecha', '22px') ?>
          <?= wfS('Copete', '36px') ?>
          <?= wfS('─── hr ───', '10px') ?>
          <?= wfB('A', 'Mobile Large', '320 × 100 px', '$$$$', '48px') ?>
          <?= wfS('Cuerpo del artículo', '100px') ?>
          <?= wfS('Compartir', '36px') ?>
          <?= wfS('Footer', '36px') ?>
          <p style="font-size:8px;color:rgba(255,255,255,.18);text-align:center;margin-top:10px">El Slot B (Sidebar) solo aparece en desktop</p>
        </div>
      </div>
    </div>

  </div>
</section>

<!-- TABLA DE POSICIONES -->
<section class="py-16 bg-card-dark/30">
  <div class="max-w-6xl mx-auto px-4 sm:px-6">
    <div class="text-center mb-10">
      <h2 class="text-2xl sm:text-3xl font-black uppercase mb-2">Todos los <span class="text-primary">Espacios</span></h2>
      <p class="text-gray-500 text-sm">Dimensiones, ubicación y nivel de cada posición disponible</p>
    </div>
    <div class="glass-card rounded-2xl overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm min-w-[720px]">
          <thead>
            <tr class="border-b border-white/8 text-gray-400 text-xs uppercase tracking-wider">
              <th class="px-4 py-3.5 text-left w-10">#</th>
              <th class="px-4 py-3.5 text-left">Página</th>
              <th class="px-4 py-3.5 text-left">Ubicación</th>
              <th class="px-4 py-3.5 text-left">Desktop</th>
              <th class="px-4 py-3.5 text-left">Mobile</th>
              <th class="px-4 py-3.5 text-center">Nivel</th>
              <th class="px-4 py-3.5 text-center">Precio</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-white/5">
            <?php foreach ($posiciones as $p):
                $c = tc($p['tier']); ?>
            <tr style="background:<?= $c['row'] ?>">
              <td class="px-4 py-3.5 font-black text-base" style="color:<?= $c['clr'] ?>"><?= htmlspecialchars($p['num'], ENT_QUOTES, 'UTF-8') ?></td>
              <td class="px-4 py-3.5 text-gray-300 font-semibold whitespace-nowrap"><?= htmlspecialchars($p['pagina'], ENT_QUOTES, 'UTF-8') ?></td>
              <td class="px-4 py-3.5 text-gray-400 text-xs leading-relaxed max-w-[220px]"><?= htmlspecialchars($p['ubicacion'], ENT_QUOTES, 'UTF-8') ?></td>
              <td class="px-4 py-3.5 text-gray-300 whitespace-nowrap font-mono text-xs"><?= htmlspecialchars($p['desktop'], ENT_QUOTES, 'UTF-8') ?></td>
              <td class="px-4 py-3.5 text-gray-300 whitespace-nowrap font-mono text-xs"><?= htmlspecialchars($p['mobile'], ENT_QUOTES, 'UTF-8') ?></td>
              <td class="px-4 py-3.5 text-center">
                <span style="display:inline-block;font-size:10px;font-weight:800;padding:3px 10px;border-radius:5px;background:<?= $c['pill'] ?>;color:<?= $c['clr'] ?>;letter-spacing:.05em;white-space:nowrap"><?= htmlspecialchars($p['tier'], ENT_QUOTES, 'UTF-8') ?></span>
              </td>
              <td class="px-4 py-3.5 text-center text-gray-400 font-semibold text-xs"><?= htmlspecialchars($p['precio'], ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    <p class="text-xs text-gray-600 text-center mt-4">Los precios y disponibilidad son a confirmar. Se pueden combinar posiciones en paquetes.</p>
  </div>
</section>

<!-- CTA CONTACTO -->
<section class="py-20">
  <div class="max-w-2xl mx-auto px-4 sm:px-6 text-center">
    <div class="glass-card rounded-3xl p-8 sm:p-12">
      <span class="material-symbols-outlined text-primary mb-4 block" style="font-size:52px">handshake</span>
      <h2 class="text-2xl sm:text-3xl font-black mb-3">¿Te interesa publicitar?</h2>
      <p class="text-gray-400 mb-8 text-sm sm:text-base leading-relaxed">
        Escribinos para conocer disponibilidad, precios y opciones de paquetes combinados para mayor alcance.
      </p>
      <div class="flex flex-col sm:flex-row gap-3 justify-center">
        <a href="mailto:<?= htmlspecialchars($contactEmail, ENT_QUOTES, 'UTF-8') ?>"
           class="inline-flex items-center justify-center gap-2 px-6 py-3.5 bg-primary text-white font-bold rounded-xl hover:shadow-[0_0_24px_rgba(140,82,255,0.4)] transition-all text-sm">
          <span class="material-symbols-outlined" style="font-size:18px">mail</span>
          <?= htmlspecialchars($contactEmail, ENT_QUOTES, 'UTF-8') ?>
        </a>
        <?php if ($contactWhatsApp): ?>
        <a href="https://wa.me/<?= htmlspecialchars($contactWhatsApp, ENT_QUOTES, 'UTF-8') ?>?text=Hola%2C%20me%20interesa%20publicitar%20en%20Juego%20de%20Equipo"
           target="_blank" rel="noopener noreferrer"
           class="inline-flex items-center justify-center gap-2 px-6 py-3.5 bg-green-600/15 border border-green-600/30 text-green-400 font-bold rounded-xl hover:bg-green-600/25 transition-all text-sm">
          <svg class="w-4 h-4 fill-current flex-shrink-0" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 0C5.374 0 0 5.373 0 12c0 2.117.549 4.099 1.507 5.829L0 24l6.335-1.652A11.932 11.932 0 0012 24c6.626 0 12-5.374 12-12S18.626 0 12 0zm0 21.818a9.818 9.818 0 01-5.003-1.37l-.36-.214-3.733.972.994-3.634-.234-.375A9.818 9.818 0 112 12 9.818 9.818 0 0112 21.818z"/></svg>
          WhatsApp
        </a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<!-- FOOTER MINIMAL -->
<footer class="border-t border-white/5 py-8">
  <div class="max-w-6xl mx-auto px-4 sm:px-6 flex flex-col sm:flex-row justify-between items-center gap-3 text-xs text-gray-600">
    <p>&copy; 2026 Juego de Equipo · Documento de presentación interna</p>
    <a href="/" class="hover:text-gray-400 transition-colors">Volver al sitio →</a>
  </div>
</footer>

<script>
function switchPage(page, btn) {
  document.getElementById('wf-home').classList.toggle('hidden', page !== 'home');
  document.getElementById('wf-nota').classList.toggle('hidden', page !== 'nota');
  document.querySelectorAll('.page-tab').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
}

function switchDevice(page, device, btn) {
  ['desktop', 'mobile'].forEach(d => {
    const el = document.getElementById('wf-' + page + '-' + d);
    if (el) el.classList.toggle('hidden', d !== device);
  });
  btn.closest('div').querySelectorAll('.dev-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
}
</script>
</body>
</html>
