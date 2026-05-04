<?php
$siteName = 'Juego de Equipo';
$pageTitle = 'Noticias — ' . $siteName;
$pageDescription = 'Todas las noticias, crónicas y novedades de Juego de Equipo.';
$pageUrl = App\absoluteUrl('/notas');
$shareImage = App\absoluteUrl('/assets/share-default.png');
?>
<!DOCTYPE html>
<html class="dark" lang="es">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
<meta name="description" content="<?= htmlspecialchars($pageDescription, ENT_QUOTES, 'UTF-8') ?>">
<meta property="og:site_name" content="<?= htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8') ?>">
<meta property="og:title" content="<?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?>">
<meta property="og:description" content="<?= htmlspecialchars($pageDescription, ENT_QUOTES, 'UTF-8') ?>">
<meta property="og:image" content="<?= htmlspecialchars($shareImage, ENT_QUOTES, 'UTF-8') ?>">
<meta property="og:url" content="<?= htmlspecialchars($pageUrl, ENT_QUOTES, 'UTF-8') ?>">
<meta property="og:type" content="website">
<meta name="twitter:card" content="summary_large_image">
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
.material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; color: #ffffff; }
.glass { background: rgba(255,255,255,0.04); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.08); }
.glass-card { background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.10); }
#mobileMenu.open { max-height: 300px; opacity: 1; }
#mobileMenu { max-height: 0; opacity: 0; overflow: hidden; transition: max-height 0.3s, opacity 0.3s; }
@keyframes shimmer { 0%{background-position:-200% 0} 100%{background-position:200% 0} }
.skeleton { background: linear-gradient(90deg, rgba(255,255,255,.04) 25%, rgba(255,255,255,.08) 50%, rgba(255,255,255,.04) 75%); background-size: 200% 100%; animation: shimmer 1.5s infinite; border-radius: 6px; }
</style>
</head>
<body class="bg-background-dark text-white antialiased selection:bg-primary selection:text-white">
<?php require $root . '/views/partials/gtm-body.php'; ?>

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
      <a class="text-sm font-semibold text-primary" href="/notas">Noticias</a>
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
      <a class="text-sm font-semibold text-primary" href="/notas">Noticias</a>
    </div>
  </div>
</header>

<!-- HERO -->
<section class="pt-24 sm:pt-28 pb-8 sm:pb-12">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <h1 class="text-3xl sm:text-4xl font-black uppercase mb-2">Todas las <span class="text-primary">Noticias</span></h1>
    <p class="text-gray-500 text-sm sm:text-base">Crónicas, análisis, novedades y más de la liga.</p>

    <!-- Filtros -->
    <div class="flex flex-wrap gap-2 mt-6" id="filtros">
      <button data-cat="" class="cat-filter px-4 py-2 rounded-full text-xs sm:text-sm font-bold transition-all bg-primary text-white">Todas</button>
      <button data-cat="Crónica" class="cat-filter px-4 py-2 rounded-full text-xs sm:text-sm font-bold transition-all text-gray-400 bg-white/5 hover:text-white">Crónica</button>
      <button data-cat="Análisis" class="cat-filter px-4 py-2 rounded-full text-xs sm:text-sm font-bold transition-all text-gray-400 bg-white/5 hover:text-white">Análisis</button>
      <button data-cat="Institucional" class="cat-filter px-4 py-2 rounded-full text-xs sm:text-sm font-bold transition-all text-gray-400 bg-white/5 hover:text-white">Institucional</button>
      <button data-cat="Planteles" class="cat-filter px-4 py-2 rounded-full text-xs sm:text-sm font-bold transition-all text-gray-400 bg-white/5 hover:text-white">Planteles</button>
      <button data-cat="Editorial" class="cat-filter px-4 py-2 rounded-full text-xs sm:text-sm font-bold transition-all text-gray-400 bg-white/5 hover:text-white">Editorial</button>
    </div>
  </div>
</section>

<!-- GRID DE NOTICIAS -->
<section class="pb-16 sm:pb-20">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Skeleton loader -->
    <div id="noticiasLoading" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5 sm:gap-6">
      <div class="rounded-2xl overflow-hidden"><div class="skeleton aspect-video"></div><div class="p-5 space-y-3"><div class="skeleton h-5 w-3/4"></div><div class="skeleton h-4 w-full"></div><div class="skeleton h-3 w-1/3 mt-4"></div></div></div>
      <div class="rounded-2xl overflow-hidden"><div class="skeleton aspect-video"></div><div class="p-5 space-y-3"><div class="skeleton h-5 w-3/4"></div><div class="skeleton h-4 w-full"></div><div class="skeleton h-3 w-1/3 mt-4"></div></div></div>
      <div class="rounded-2xl overflow-hidden"><div class="skeleton aspect-video"></div><div class="p-5 space-y-3"><div class="skeleton h-5 w-3/4"></div><div class="skeleton h-4 w-full"></div><div class="skeleton h-3 w-1/3 mt-4"></div></div></div>
    </div>

    <div id="noticiasGrid" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5 sm:gap-6 hidden"></div>

    <!-- Empty state -->
    <div id="noticiasEmpty" class="hidden text-center py-16">
      <span class="material-symbols-outlined text-[64px] text-gray-700 mb-4 block">article</span>
      <p class="text-gray-500 text-lg font-semibold">No hay noticias publicadas aún</p>
      <p class="text-gray-600 text-sm mt-1">Las novedades de la liga aparecerán acá.</p>
    </div>
  </div>
</section>

<!-- FOOTER -->
<footer class="bg-background-dark pt-12 sm:pt-16 pb-8 border-t border-white/5">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-8 sm:gap-10 mb-10 sm:mb-14">
      <div class="sm:col-span-2">
        <div class="flex items-center gap-2 mb-4">
          <img src="/assets/logo.png" alt="Juego de Equipo" class="h-8 w-auto object-contain"/>
        </div>
        <p class="text-gray-500 max-w-sm mb-6 text-sm">La liga de fútbol. Seguí todos los resultados, posiciones y goleadores en tiempo real.</p>
      </div>
      <div>
        <h4 class="font-bold uppercase text-xs tracking-widest mb-4 text-gray-400">Secciones</h4>
        <ul class="space-y-3 text-sm text-gray-500">
          <li><a class="hover:text-white transition-colors" href="/#fixture">Fixture</a></li>
          <li><a class="hover:text-white transition-colors" href="/#tabla">Tabla de Posiciones</a></li>
          <li><a class="hover:text-white transition-colors" href="/#goleadores">Goleadores</a></li>
          <li><a class="hover:text-white transition-colors" href="/notas">Noticias</a></li>
        </ul>
      </div>
      <div>
        <h4 class="font-bold uppercase text-xs tracking-widest mb-4 text-gray-400">Links</h4>
        <ul class="space-y-3 text-sm text-gray-500">
          <li><a class="hover:text-white transition-colors" href="/estadisticas">Estadísticas</a></li>
          <li><a class="hover:text-white transition-colors" href="/fixture">Fixture completo</a></li>
        </ul>
      </div>
    </div>
    <div class="pt-6 border-t border-white/5 flex flex-col sm:flex-row justify-between items-center gap-3 text-xs text-gray-600">
      <p>&copy; 2026 Juego de Equipo. Todos los derechos reservados.</p>
      <p>Temporada 2025/26</p>
    </div>
  </div>
</footer>

<script>
const API_BASE = '/api';
const enc = (s) => encodeURIComponent(s || '');
const escapeHtml = (s) => {
  const d = document.createElement('div');
  d.textContent = s;
  return d.innerHTML;
};

let currentCat = '';

async function loadNoticias() {
  const loading = document.getElementById('noticiasLoading');
  const grid = document.getElementById('noticiasGrid');
  const empty = document.getElementById('noticiasEmpty');

  loading.classList.remove('hidden');
  grid.classList.add('hidden');
  empty.classList.add('hidden');

  try {
    let url = API_BASE + '/noticias?limit=50';
    if (currentCat) url += '&categoria=' + enc(currentCat);
    const r = await fetch(url);
    const noticias = await r.json();

    loading.classList.add('hidden');

    if (!Array.isArray(noticias) || !noticias.length) {
      empty.classList.remove('hidden');
      return;
    }

    grid.classList.remove('hidden');
    grid.innerHTML = noticias.map((n) => {
      const fecha = n.publishedAt || n.createdAt;
      const fechaStr = fecha
        ? new Date(fecha).toLocaleDateString('es-AR', { day: 'numeric', month: 'long', year: 'numeric' })
        : '';
      const img = n.imagenUrl
        ? `<img src="${n.imagenUrl}" alt="${escapeHtml(n.titulo || '')}" class="absolute inset-0 w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" loading="lazy"/>`
        : `<div class="absolute inset-0 bg-gradient-to-br from-primary/20 to-card-dark flex items-center justify-center"><span class="material-symbols-outlined" style="font-size:48px;color:rgba(140,82,255,0.3)">article</span></div>`;
      return `<a href="/noticias/${enc(n.slug)}" class="group glass-card rounded-2xl overflow-hidden hover:border-primary/30 transition-all duration-300 flex flex-col">
        <div class="relative aspect-video overflow-hidden bg-card-dark">
          ${img}
          <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
          ${n.categoria ? `<span class="absolute bottom-3 left-3 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-primary/90 text-white">${escapeHtml(n.categoria)}</span>` : ''}
        </div>
        <div class="p-4 sm:p-5 flex flex-col flex-1">
          <h3 class="font-black text-base sm:text-lg leading-snug mb-2 line-clamp-2 group-hover:text-primary-light transition-colors">${escapeHtml(n.titulo || '')}</h3>
          ${n.copete ? `<p class="text-gray-400 text-sm leading-relaxed line-clamp-2 mb-3">${escapeHtml(n.copete)}</p>` : ''}
          <div class="mt-auto flex items-center justify-between text-xs text-gray-500">
            <span>${escapeHtml(n.autorNombre || 'Redacción')}</span>
            <span>${fechaStr}</span>
          </div>
        </div>
      </a>`;
    }).join('');
  } catch (e) {
    loading.classList.add('hidden');
    empty.classList.remove('hidden');
    console.error('Error cargando noticias:', e);
  }
}

// Filtros por categoría
document.querySelectorAll('.cat-filter').forEach((btn) => {
  btn.addEventListener('click', () => {
    document.querySelectorAll('.cat-filter').forEach((b) => {
      b.classList.remove('bg-primary', 'text-white');
      b.classList.add('text-gray-400', 'bg-white/5');
    });
    btn.classList.add('bg-primary', 'text-white');
    btn.classList.remove('text-gray-400', 'bg-white/5');
    currentCat = btn.dataset.cat;
    loadNoticias();
  });
});

// Mobile menu
document.getElementById('menuToggle')?.addEventListener('click', () => {
  document.getElementById('mobileMenu')?.classList.toggle('open');
});

// Init
loadNoticias();
</script>
</body>
</html>
