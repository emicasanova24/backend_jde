<?php
$siteName = 'Juego de Equipo';
$noticia = $nota ?? null;
$pageTitle = $noticia
    ? (($noticia['titulo'] ?? 'Nota') . ' — ' . $siteName)
    : ('Nota no encontrada — ' . $siteName);
$pageDescription = $noticia['metaDescription'] ?? 'Segu\u00ed las novedades de Juego de Equipo.';
$pageUrl = $noticia['pageUrl'] ?? App\absoluteUrl('/noticias/' . rawurlencode((string) ($notaSlug ?? 'noticia')));
$shareImage = $noticia['shareImageUrl'] ?? App\absoluteUrl('/assets/share-default.png');
$whatsAppShareText = $noticia ? (($noticia['titulo'] ?? 'Juego de Equipo') . ' — ' . $pageUrl) : $pageUrl;
?>
<!DOCTYPE html>
<html class="dark" lang="es">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
  <meta name="description" content="<?= htmlspecialchars($pageDescription, ENT_QUOTES, 'UTF-8') ?>">
  <meta property="og:type" content="article">
  <meta property="og:site_name" content="<?= htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8') ?>">
  <meta property="og:title" content="<?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?>">
  <meta property="og:description" content="<?= htmlspecialchars($pageDescription, ENT_QUOTES, 'UTF-8') ?>">
  <meta property="og:image" content="<?= htmlspecialchars($shareImage, ENT_QUOTES, 'UTF-8') ?>">
  <meta property="og:image:width" content="1200">
  <meta property="og:image:height" content="630">
  <meta property="og:url" content="<?= htmlspecialchars($pageUrl, ENT_QUOTES, 'UTF-8') ?>">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="<?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?>">
  <meta name="twitter:description" content="<?= htmlspecialchars($pageDescription, ENT_QUOTES, 'UTF-8') ?>">
  <meta name="twitter:image" content="<?= htmlspecialchars($shareImage, ENT_QUOTES, 'UTF-8') ?>">
  <?php require $root . '/views/partials/favicon-links.php'; ?>
  <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
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
    .glass { background: rgba(255,255,255,0.04); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.08); }
    .glass-card { background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.10); }
    #mobileMenu.open { max-height: 300px; opacity: 1; }
    #mobileMenu { max-height: 0; opacity: 0; overflow: hidden; transition: max-height 0.3s, opacity 0.3s; }

    /* Prose del artículo */
    .article-body p  { margin-bottom: 1rem; line-height: 1.85; color: #d1d5db; }
    .article-body h2 { font-size: 1.5rem; font-weight: 800; margin: 2rem 0 0.6rem; color: #ffffff; }
    .article-body h3 { font-size: 1.2rem; font-weight: 700; margin: 1.75rem 0 0.5rem; color: #e5e7eb; }
    .article-body ul, .article-body ol { padding-left: 1.5rem; margin-bottom: 1rem; color: #d1d5db; }
    .article-body li { margin-bottom: 0.4rem; line-height: 1.75; }
    .article-body a  { color: #a478ff; text-decoration: underline; transition: color .2s; }
    .article-body a:hover { color: #8c52ff; }
    .article-body strong { color: #ffffff; font-weight: 700; }
    .article-body em { color: #e5e7eb; }
    .article-body blockquote { border-left: 3px solid #8c52ff; padding-left: 1rem; margin: 1.5rem 0; color: #9ca3af; font-style: italic; }
    .article-body .gallery-item { margin: 0 0 1.25rem; }
    .article-body .gallery-item img { width: 100%; border-radius: 12px; display: block; }
    .article-body .gallery-item figcaption { margin-top: .45rem; font-size: .85rem; color: #9ca3af; }
    .boletin-embed { width: 100%; height: 1000px; border: 0; border-radius: 12px; background: #0f0f12; }
    @media (max-width: 768px) {
      .boletin-embed { height: 500px; }
    }

    /* Skeleton loader */
    @keyframes shimmer { 0%{background-position:-200% 0} 100%{background-position:200% 0} }
    .skeleton { background: linear-gradient(90deg, rgba(255,255,255,.04) 25%, rgba(255,255,255,.08) 50%, rgba(255,255,255,.04) 75%); background-size: 200% 100%; animation: shimmer 1.5s infinite; border-radius: 6px; }

    /* Toast */
    #toast { transition: opacity .3s, transform .3s; }
    #toast.show { opacity: 1; transform: translateY(0); }
    #toast { opacity: 0; transform: translateY(8px); }
  </style>
</head>
<body class="bg-background-dark text-white antialiased selection:bg-primary selection:text-white">

<!-- HEADER -->
<header class="fixed top-0 w-full z-50 bg-background-dark/80 backdrop-blur-md border-b border-white/10">
  <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 sm:h-20 flex items-center justify-between">
    <a href="/" class="flex items-center gap-2">
      <img src="/assets/logo.png" alt="Juego de Equipo" class="h-9 sm:h-11 w-auto object-contain"/>
    </a>
    <div class="hidden md:flex items-center space-x-6 lg:space-x-8">
      <a class="text-sm font-semibold hover:text-primary transition-colors" href="/#fixture">Fixture</a>
      <a class="text-sm font-semibold hover:text-primary transition-colors" href="/#tabla">Tabla</a>
      <a class="text-sm font-semibold hover:text-primary transition-colors" href="/#goleadores">Goleadores</a>
      <a class="text-sm font-semibold hover:text-primary transition-colors" href="/#tarjetas">Tarjetas</a>
    </div>
    <button id="menuToggle" class="md:hidden text-white p-2">
      <span class="material-symbols-outlined text-[28px]">menu</span>
    </button>
  </nav>
  <div id="mobileMenu" class="md:hidden bg-background-dark/95 border-t border-white/5 px-4">
    <div class="flex flex-col gap-3 py-4">
      <a class="text-sm font-semibold hover:text-primary transition-colors" href="/#fixture">Fixture</a>
      <a class="text-sm font-semibold hover:text-primary transition-colors" href="/#tabla">Tabla</a>
      <a class="text-sm font-semibold hover:text-primary transition-colors" href="/#goleadores">Goleadores</a>
      <a class="text-sm font-semibold hover:text-primary transition-colors" href="/#tarjetas">Tarjetas</a>
    </div>
  </div>
</header>

<!-- HERO IMAGEN -->
<div class="relative w-full h-[50vh] sm:h-[60vh] mt-16 sm:mt-20 overflow-hidden bg-gradient-to-br from-primary/20 to-background-dark" id="heroWrap">
  <?php if ($noticia && !empty($noticia['imagenUrl'])): ?>
    <img src="<?= htmlspecialchars($noticia['imagenUrl'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string) ($noticia['titulo'] ?: 'Imagen de la nota'), ENT_QUOTES, 'UTF-8') ?>" class="absolute inset-0 w-full h-full object-cover"/>
  <?php endif; ?>
  <!-- Overlay gradiente -->
  <div class="absolute inset-0 bg-gradient-to-t from-background-dark via-background-dark/40 to-transparent"></div>
  <?php if ($noticia && !empty($noticia['imagenAlt'])): ?>
    <div class="absolute bottom-6 right-6 sm:right-10 max-w-[70vw] rounded-full bg-black/35 px-3 py-1.5 text-[11px] font-semibold uppercase tracking-wider text-white/90 backdrop-blur-sm text-right">
      <?= htmlspecialchars((string) $noticia['imagenAlt'], ENT_QUOTES, 'UTF-8') ?>
    </div>
  <?php endif; ?>
</div>

<!-- CONTENIDO PRINCIPAL -->
<main class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">

  <!-- Breadcrumb -->
  <nav class="flex items-center gap-2 text-xs text-gray-500 mb-8 flex-wrap">
    <a href="/" class="hover:text-primary transition-colors">Inicio</a>
    <span class="text-gray-700">/</span>
    <a href="/" class="hover:text-primary transition-colors">Noticias</a>
    <span class="text-gray-700">/</span>
    <span id="breadcrumbTitulo" class="text-gray-400 line-clamp-1 max-w-[200px] sm:max-w-xs"><?= htmlspecialchars((string) ($noticia['titulo'] ?? 'No encontrada'), ENT_QUOTES, 'UTF-8') ?></span>
  </nav>

  <!-- Error 404 (oculto por defecto) -->
  <div id="errorSection" class="<?= $noticia ? 'hidden ' : '' ?>text-center py-24">
    <span class="material-symbols-outlined text-6xl text-gray-700 mb-4 block">article</span>
    <h2 class="text-2xl font-black mb-2 text-gray-300">Nota no encontrada</h2>
    <p class="text-gray-500 mb-8">La nota que buscás no existe o fue eliminada.</p>
    <a href="/" class="px-6 py-3 bg-primary text-white font-bold rounded-xl hover:bg-primary-light transition-colors">Volver al inicio</a>
  </div>

  <!-- Grid principal + sidebar -->
  <div id="articleWrap" class="<?= $noticia ? '' : 'hidden ' ?>grid lg:grid-cols-[1fr_300px] gap-10 xl:gap-14">

    <!-- ============================ COLUMNA PRINCIPAL ============================ -->
    <article>
      <!-- Tag + cabecera -->
      <div class="mb-6 space-y-3">
        <div id="metaBadges" class="flex flex-wrap gap-2"></div>
        <h1 id="artTitulo" class="text-3xl sm:text-4xl lg:text-5xl font-black leading-tight text-white"><?= htmlspecialchars((string) ($noticia['titulo'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h1>
        <!-- Meta línea -->
        <div id="metaLinea" class="flex flex-wrap items-center gap-3 text-sm text-gray-400">
          <span id="metaAutor" class="flex items-center gap-1.5">
            <span class="material-symbols-outlined text-[16px] text-gray-500">account_circle</span>
            <span><?= htmlspecialchars((string) ($noticia['autorNombre'] ?? 'Redacción'), ENT_QUOTES, 'UTF-8') ?></span>
          </span>
          <span class="text-gray-700">·</span>
          <time id="metaFecha"><?= htmlspecialchars((string) ($noticia['formattedDate'] ?? ''), ENT_QUOTES, 'UTF-8') ?></time>
          <?php if ($noticia): ?>
            <span class="text-gray-700">·</span>
            <span><?= htmlspecialchars((string) ($noticia['readingTime'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
          <?php endif; ?>
        </div>
      </div>

      <!-- Copete -->
      <p id="artCopete" class="text-lg sm:text-xl text-gray-300 italic leading-relaxed border-l-4 border-primary pl-5 mb-8"><?= htmlspecialchars((string) ($noticia['copete'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>

      <hr class="border-white/5 mb-8"/>

      <!-- BANNER A: in-article / $$$$ PREMIUM -->
      <?php $bannerType = 'leaderboard';   $bannerId = 'nota-article-d'; include $root . '/views/partials/banner.php'; ?>
      <?php $bannerType = 'mobile-large';  $bannerId = 'nota-article-m'; include $root . '/views/partials/banner.php'; ?>

      <!-- Contenido rich text -->
      <div id="artContenido" class="article-body text-base leading-relaxed"><?= $noticia['contenido'] ?? '' ?></div>

      <?php if (($noticia['tipoContenido'] ?? 'comun') === 'boletin' && !empty($noticia['boletinPdfUrl'])): ?>
        <section class="mt-8">
          <h2 class="text-xl font-bold mb-3">Boletín</h2>
          <iframe src="<?= htmlspecialchars((string) $noticia['boletinPdfUrl'], ENT_QUOTES, 'UTF-8') ?>" class="boletin-embed" title="Boletín PDF" loading="lazy"></iframe>
        </section>
      <?php endif; ?>

      <!-- Compartir (mobile — debajo del contenido) -->
      <div class="mt-10 pt-8 border-t border-white/5 flex flex-wrap items-center gap-3 lg:hidden">
        <span class="text-sm font-bold text-gray-400 mr-1">Compartir:</span>
        <button id="copyLinkMobile" class="flex items-center gap-2 px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-sm font-semibold hover:bg-white/10 transition-colors">
          <span class="material-symbols-outlined text-[16px]">link</span> Copiar link
        </button>
        <button id="instagramMobile" class="flex items-center gap-2 px-4 py-2 rounded-xl bg-pink-500/15 border border-pink-500/30 text-pink-300 text-sm font-semibold hover:bg-pink-500/25 transition-colors">
          <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24" aria-hidden="true"><path d="M7.8 2h8.4C19.4 2 22 4.6 22 7.8v8.4c0 3.2-2.6 5.8-5.8 5.8H7.8C4.6 22 2 19.4 2 16.2V7.8C2 4.6 4.6 2 7.8 2zm8.3 2H7.9A3.9 3.9 0 004 7.9v8.2A3.9 3.9 0 007.9 20h8.2a3.9 3.9 0 003.9-3.9V7.9A3.9 3.9 0 0016.1 4zM12 7a5 5 0 110 10 5 5 0 010-10zm0 2a3 3 0 100 6 3 3 0 000-6zm5.3-2.7a1.2 1.2 0 110 2.4 1.2 1.2 0 010-2.4z"/></svg>
          Instagram
        </button>
        <a id="whatsappMobile" href="https://wa.me/?text=<?= rawurlencode($whatsAppShareText) ?>" target="_blank" rel="noopener noreferrer" class="flex items-center gap-2 px-4 py-2 rounded-xl bg-green-600/15 border border-green-600/30 text-green-400 text-sm font-semibold hover:bg-green-600/25 transition-colors">
          <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 0C5.374 0 0 5.373 0 12c0 2.117.549 4.099 1.507 5.829L0 24l6.335-1.652A11.932 11.932 0 0012 24c6.626 0 12-5.374 12-12S18.626 0 12 0zm0 21.818a9.818 9.818 0 01-5.003-1.37l-.36-.214-3.733.972.994-3.634-.234-.375A9.818 9.818 0 112 12 9.818 9.818 0 0112 21.818z"/></svg>
          WhatsApp
        </a>
      </div>
    </article>

    <!-- ============================ SIDEBAR ============================ -->
    <aside class="space-y-5 lg:sticky lg:top-28 lg:self-start">

      <!-- Autor -->
      <div class="glass-card rounded-2xl p-5">
        <p class="text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-3">Autor</p>
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-full bg-primary/20 flex items-center justify-center flex-shrink-0">
            <span class="material-symbols-outlined text-primary text-[20px]">account_circle</span>
          </div>
          <div>
            <p id="sideAutor" class="font-semibold text-sm text-white"><?= htmlspecialchars((string) ($noticia['autorNombre'] ?? 'Redacción'), ENT_QUOTES, 'UTF-8') ?></p>
            <p class="text-xs text-gray-500">Redacción</p>
          </div>
        </div>
      </div>

      <!-- Compartir (desktop) -->
      <div class="glass-card rounded-2xl p-5 hidden lg:block">
        <p class="text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-4">Compartir</p>
        <div class="space-y-2">
          <button id="copyLinkDesktop" class="w-full flex items-center gap-2.5 px-4 py-2.5 rounded-xl bg-white/5 border border-white/10 text-sm font-semibold hover:bg-white/10 transition-colors text-left">
            <span class="material-symbols-outlined text-[18px] text-gray-400">link</span> Copiar enlace
          </button>
          <button id="instagramDesktop" class="w-full flex items-center gap-2.5 px-4 py-2.5 rounded-xl bg-pink-500/10 border border-pink-500/25 text-pink-300 text-sm font-semibold hover:bg-pink-500/20 transition-colors text-left">
            <svg class="w-4 h-4 fill-current flex-shrink-0" viewBox="0 0 24 24" aria-hidden="true"><path d="M7.8 2h8.4C19.4 2 22 4.6 22 7.8v8.4c0 3.2-2.6 5.8-5.8 5.8H7.8C4.6 22 2 19.4 2 16.2V7.8C2 4.6 4.6 2 7.8 2zm8.3 2H7.9A3.9 3.9 0 004 7.9v8.2A3.9 3.9 0 007.9 20h8.2a3.9 3.9 0 003.9-3.9V7.9A3.9 3.9 0 0016.1 4zM12 7a5 5 0 110 10 5 5 0 010-10zm0 2a3 3 0 100 6 3 3 0 000-6zm5.3-2.7a1.2 1.2 0 110 2.4 1.2 1.2 0 010-2.4z"/></svg>
            Instagram
          </button>
          <a id="whatsappDesktop" href="https://wa.me/?text=<?= rawurlencode($whatsAppShareText) ?>" target="_blank" rel="noopener noreferrer" class="w-full flex items-center gap-2.5 px-4 py-2.5 rounded-xl bg-green-600/10 border border-green-600/25 text-green-400 text-sm font-semibold hover:bg-green-600/20 transition-colors">
            <svg class="w-4 h-4 fill-current flex-shrink-0" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 0C5.374 0 0 5.373 0 12c0 2.117.549 4.099 1.507 5.829L0 24l6.335-1.652A11.932 11.932 0 0012 24c6.626 0 12-5.374 12-12S18.626 0 12-0zm0 21.818a9.818 9.818 0 01-5.003-1.37l-.36-.214-3.733.972.994-3.634-.234-.375A9.818 9.818 0 112 12 9.818 9.818 0 0112 21.818z"/></svg>
            WhatsApp
          </a>
        </div>
      </div>

      <!-- BANNER B: sidebar / $$$ STANDARD -->
      <?php $bannerType = 'half-page'; $bannerId = 'nota-sidebar'; include $root . '/views/partials/banner.php'; ?>

      <!-- También te puede interesar -->
      <div id="relacionadasCard" class="glass-card rounded-2xl p-5 <?= !empty($noticia['relacionadas']) ? '' : 'hidden' ?>">
        <p class="text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-4">También te puede interesar</p>
        <div id="relacionadasList" class="space-y-4">
          <?php foreach (($noticia['relacionadas'] ?? []) as $relacionada): ?>
            <a href="/noticias/<?= rawurlencode((string) $relacionada['slug']) ?>" class="flex gap-3 group hover:opacity-80 transition-opacity">
              <?php if (!empty($relacionada['imagenUrl'])): ?>
                <img src="<?= htmlspecialchars((string) $relacionada['imagenUrl'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string) ($relacionada['titulo'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" class="w-20 h-16 rounded-lg object-cover flex-shrink-0"/>
              <?php else: ?>
                <div class="w-20 h-16 rounded-lg bg-primary/10 flex items-center justify-center flex-shrink-0"><span class="material-symbols-outlined text-primary text-[20px]">article</span></div>
              <?php endif; ?>
              <div class="min-w-0">
                <?php if (!empty($relacionada['categoria'])): ?>
                  <span class="text-[10px] font-bold uppercase tracking-wider text-primary"><?= htmlspecialchars((string) $relacionada['categoria'], ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>
                <p class="text-sm font-semibold text-gray-200 leading-snug mt-0.5 line-clamp-2 group-hover:text-white transition-colors"><?= htmlspecialchars((string) ($relacionada['titulo'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
              </div>
            </a>
          <?php endforeach; ?>
        </div>
      </div>

    </aside>
  </div>
</main>

<!-- FOOTER -->
<footer class="bg-background-dark pt-12 pb-8 border-t border-white/5 mt-16">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8 mb-10">
      <div class="sm:col-span-2 lg:col-span-1">
        <div class="flex items-center gap-2 mb-3">
          <img src="/assets/logo.png" alt="Juego de Equipo" class="h-8 w-auto object-contain"/>
        </div>
        <p class="text-gray-500 text-sm max-w-xs">La liga de fútbol. Seguí todos los resultados, posiciones y goleadores en tiempo real.</p>
      </div>
      <div>
        <h4 class="font-bold uppercase text-xs tracking-widest mb-4 text-gray-400">Secciones</h4>
        <ul class="space-y-2 text-sm text-gray-500">
          <li><a class="hover:text-white transition-colors" href="/#fixture">Fixture</a></li>
          <li><a class="hover:text-white transition-colors" href="/#tabla">Tabla de Posiciones</a></li>
          <li><a class="hover:text-white transition-colors" href="/#goleadores">Goleadores</a></li>
          <li><a class="hover:text-white transition-colors" href="/#tarjetas">Tarjetas</a></li>
        </ul>
      </div>
      <div>
        <h4 class="font-bold uppercase text-xs tracking-widest mb-4 text-gray-400">Links</h4>
        <ul class="space-y-2 text-sm text-gray-500">
          <li><a class="hover:text-white transition-colors" href="/estadisticas">Estadísticas</a></li>
          <li><a class="hover:text-white transition-colors" href="/#h2h">Historial H2H</a></li>
        </ul>
      </div>
    </div>
    <div class="border-t border-white/5 pt-6 flex flex-col sm:flex-row justify-between items-center gap-3 text-xs text-gray-600">
      <p>&copy; <?= date('Y') ?> Juego de Equipo. Todos los derechos reservados.</p>
      <p>Temporada 2025/26</p>
    </div>
  </div>
</footer>

<!-- Toast "Enlace copiado" -->
<div id="toast" class="fixed bottom-6 right-6 z-[100] flex items-center gap-2 bg-card-dark border border-white/10 text-sm font-semibold px-4 py-3 rounded-xl shadow-xl pointer-events-none">
  <span class="material-symbols-outlined text-primary text-[18px]">check_circle</span>
  Enlace copiado
</div>

<script>
(function () {
  'use strict';

  const nota = <?= json_encode($noticia, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
  const pageUrl = <?= json_encode($pageUrl, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
  const whatsAppShareText = <?= json_encode($whatsAppShareText, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

  // ─── Helpers ────────────────────────────────────────────────────────────
  const $ = (id) => document.getElementById(id);

  function showToast() {
    const t = $('toast');
    t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), 2500);
  }

  function readingTime(html) {
    const words = html.replace(/<[^>]+>/g, ' ').trim().split(/\s+/).filter(Boolean).length;
    const mins = Math.max(1, Math.ceil(words / 200));
    return mins + ' min de lectura';
  }

  // ─── Mobile menu ────────────────────────────────────────────────────────
  $('menuToggle').addEventListener('click', () => {
    $('mobileMenu').classList.toggle('open');
  });

  // ─── Compartir ──────────────────────────────────────────────────────────
  function copyLink() {
    navigator.clipboard.writeText(pageUrl).then(showToast).catch(() => {
      prompt('Copiá el enlace:', pageUrl);
    });
  }

  function shareInstagram() {
    const openInstagram = () => {
      // En mobile intenta app; en desktop abre web.
      const isMobile = /Android|iPhone|iPad|iPod/i.test(navigator.userAgent);
      if (isMobile) {
        window.location.href = 'instagram://app';
        setTimeout(() => {
          window.open('https://www.instagram.com/', '_blank', 'noopener');
        }, 700);
        return;
      }
      window.open('https://www.instagram.com/', '_blank', 'noopener');
    };

    navigator.clipboard.writeText(pageUrl).then(() => {
      showToast();
      openInstagram();
    }).catch(() => {
      prompt('Copiá este enlace para Instagram:', pageUrl);
      openInstagram();
    });
  }

  $('copyLinkDesktop').addEventListener('click', copyLink);
  $('copyLinkMobile').addEventListener('click', copyLink);
  $('instagramDesktop')?.addEventListener('click', shareInstagram);
  $('instagramMobile')?.addEventListener('click', shareInstagram);

  function setWhatsapp(text) {
    const url = 'https://wa.me/?text=' + encodeURIComponent(text);
    $('whatsappDesktop').href = url;
    $('whatsappMobile').href  = url;
  }
  setWhatsapp(whatsAppShareText);

  if (nota) {
    const badges = $('metaBadges');
    if (nota.categoria) {
      badges.insertAdjacentHTML('beforeend', `<span class="px-2.5 py-1 rounded-full text-xs font-bold bg-primary/15 text-primary border border-primary/20 uppercase tracking-wider">${nota.categoria}</span>`);
    }
    if (nota.tag) {
      badges.insertAdjacentHTML('beforeend', `<span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-white/5 text-gray-400 border border-white/10 uppercase tracking-wider">${nota.tag}</span>`);
    }
  }
})();
</script>
</body>
</html>
